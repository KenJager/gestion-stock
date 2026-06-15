<?php
// modules/approvisionnements/services/BonReceptionService.php

namespace approvisionnements\services;

use approvisionnements\models\BonCommandeModel;
use approvisionnements\models\LigneCommandeFournisseurModel;
use approvisionnements\models\ProduitModel;
use approvisionnements\models\MouvementStockModel;

class BonReceptionService {

    private BonCommandeModel $modeleBonCmd;
    private LigneCommandeFournisseurModel $modeleLigneCmd;
    private ProduitModel $modeleProduit;
    private MouvementStockModel $modeleMvt;

    public function __construct() {
        $this->modeleBonCmd   = new BonCommandeModel();
        $this->modeleLigneCmd = new LigneCommandeFournisseurModel();
        $this->modeleProduit  = new ProduitModel();
        $this->modeleMvt      = new MouvementStockModel();
    }

    /**
     * Liste tous les bons de réception.
     */
    public function listerToutes(): array {
        $sql = "SELECT br.*, f.raison_sociale AS fournisseur_nom,
                       bcf.numero AS commande_numero
                FROM bons_reception br
                JOIN fournisseurs f ON f.id = br.fournisseur_id
                JOIN bons_commande_fournisseur bcf ON bcf.id = br.bon_commande_id
                ORDER BY br.date_reception DESC";
        return \Database::query($sql)->fetchAll();
    }

    /**
     * Liste les bons de commande éligibles à une réception.
     */
    public function listerCommandesEligibles(): array {
        $sql = "SELECT bcf.id, bcf.numero, f.raison_sociale AS fournisseur_nom, bcf.date_commande
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.statut IN ('VALIDÉ', 'REÇU_PARTIEL')
                ORDER BY bcf.date_commande DESC";
        return \Database::query($sql)->fetchAll();
    }

    /**
     * Enregistre un bon de réception et met à jour le stock.
     */
    public function enregistrer(array $donnees, array $lignesRecues): int {
        \Database::debuterTransaction();
        try {
            // Récupérer le fournisseur depuis la commande
            $sqlFour = "SELECT fournisseur_id FROM bons_commande_fournisseur WHERE id = :id";
            $fournisseurId = \Database::query($sqlFour, [':id' => $donnees['bon_commande_id']])->fetchColumn();

            // Générer un numéro
            $numero = 'BR-' . date('Ymd') . '-' . random_int(1000, 9999);

            $sql = "INSERT INTO bons_reception
                    (numero, bon_commande_id, fournisseur_id, date_reception, statut, notes, utilisateur_id)
                    VALUES (:numero, :bc_id, :four_id, :date, 'REÇU', :notes, :uid)
                    RETURNING id";
            $row = \Database::query($sql, [
                ':numero'  => $numero,
                ':bc_id'   => $donnees['bon_commande_id'],
                ':four_id' => $fournisseurId,
                ':date'    => $donnees['date_reception'],
                ':notes'   => $donnees['notes'] ?? null,
                ':uid'     => $donnees['utilisateur_id'],
            ])->fetch();
            $receptionId = (int)$row['id'];

            // Traiter chaque ligne reçue
            foreach ($lignesRecues as $lr) {
                $ligneCmd = $this->modeleLigneCmd->trouverParId($lr['ligne_commande_id']);
                $produitId = (int)$ligneCmd['produit_id'];
                $qteRecue  = (float)$lr['quantite_recue'];
                $prix      = (float)$ligneCmd['prix_unitaire_ht'];
                $tva       = (float)$ligneCmd['tva'];

                // Insérer ligne de réception
                $sqlL = "INSERT INTO lignes_reception
                         (bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva)
                         VALUES (:br_id, :pid, :qte, :prix, :tva)";
                \Database::query($sqlL, [
                    ':br_id' => $receptionId,
                    ':pid'   => $produitId,
                    ':qte'   => $qteRecue,
                    ':prix'  => $prix,
                    ':tva'   => $tva,
                ]);

                // Mise à jour stock
                $stockAvant = $this->modeleProduit->trouverStockPourMaj($produitId);
                $stockApres = $stockAvant + $qteRecue;
                $this->modeleProduit->mettreAJourStock($produitId, $stockApres);

                // Mouvement de stock
                $this->modeleMvt->enregistrer([
                    'produit_id'     => $produitId,
                    'type_mouvement' => 'ENTRÉE',
                    'source'         => 'RECEPTION',
                    'source_id'      => $receptionId,
                    'quantite'       => $qteRecue,
                    'stock_avant'    => $stockAvant,
                    'stock_apres'    => $stockApres,
                    'utilisateur_id' => $donnees['utilisateur_id'],
                ]);

                // Mise à jour quantité reçue dans la ligne de commande
                $qteActuelle = (float)$ligneCmd['quantite_recue'];
                $this->modeleLigneCmd->mettreAJourQuantiteRecue(
                    $lr['ligne_commande_id'],
                    $qteActuelle + $qteRecue
                );
            }

            // Recalcul du statut du bon de commande
            $this->recalculerStatutBonCommande((int)$donnees['bon_commande_id']);

            \Database::validerTransaction();
            return $receptionId;
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Trouve un bon de réception par ID.
     */
    public function trouverParId(int $id): array {
        $sql = "SELECT br.*, f.raison_sociale AS fournisseur_nom,
                       bcf.numero AS commande_numero
                FROM bons_reception br
                JOIN fournisseurs f ON f.id = br.fournisseur_id
                JOIN bons_commande_fournisseur bcf ON bcf.id = br.bon_commande_id
                WHERE br.id = :id";
        $r = \Database::query($sql, [':id' => $id])->fetch();
        if (!$r) throw new \RuntimeException("Bon de réception introuvable.");
        return $r;
    }

    /**
     * Liste les lignes d'un bon de réception.
     */
    public function listerLignes(int $receptionId): array {
        $sql = "SELECT lr.*, p.code, p.designation, p.unite
                FROM lignes_reception lr
                JOIN produits p ON p.id = lr.produit_id
                WHERE lr.bon_reception_id = :id";
        return \Database::query($sql, [':id' => $receptionId])->fetchAll();
    }

    /**
     * Recalcule le statut d'un bon de commande après réception.
     */
    private function recalculerStatutBonCommande(int $bonCommandeId): void {
        $stats = $this->modeleBonCmd->getStatutsReception($bonCommandeId);
        $total = (int)$stats['total_lignes'];
        $totalRecues = (int)$stats['recues_total'];
        $partiel = (int)$stats['recues_partiel'];

        $nouveauStatut = 'VALIDÉ';
        if ($totalRecues === $total) {
            $nouveauStatut = 'REÇU_TOTAL';
        } elseif ($partiel > 0 || $totalRecues > 0) {
            $nouveauStatut = 'REÇU_PARTIEL';
        }
        $this->modeleBonCmd->modifierStatut($bonCommandeId, $nouveauStatut);
    }
}