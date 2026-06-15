<?php
// modules/approvisionnements/services/BonCommandeService.php

namespace approvisionnements\services;

use approvisionnements\models\BonCommandeModel;
use approvisionnements\models\LigneCommandeFournisseurModel;

class BonCommandeService {

    private BonCommandeModel $modeleBon;
    private LigneCommandeFournisseurModel $modeleLigne;

    public function __construct() {
        $this->modeleBon   = new BonCommandeModel();
        $this->modeleLigne = new LigneCommandeFournisseurModel();
    }

    /**
     * Retourne tous les bons de commande fournisseur.
     */
    public function listerTous(): array {
        return $this->modeleBon->listerTout();
    }

    /**
     * Récupère un bon de commande par son ID.
     */
    public function trouverParId(int $id): array {
        return $this->modeleBon->trouverParId($id);
    }

    /**
     * Retourne les lignes d'une commande.
     */
    public function listerLignes(int $bonCommandeId): array {
        return $this->modeleLigne->listerParBonCommande($bonCommandeId);
    }

    /**
     * Crée un nouveau bon de commande.
     */
    public function creer(array $donnees): int {
        return $this->modeleBon->creer($donnees);
    }

    /**
     * Ajoute une ligne à un bon de commande et met à jour les totaux.
     */
    public function ajouterLigne(array $ligne): int {
        $montantHt  = $ligne['quantite_commandee'] * $ligne['prix_unitaire_ht'];
        $montantTva = $montantHt * ($ligne['tva'] / 100);
        $montantTtc = $montantHt + $montantTva;

        \Database::debuterTransaction();
        try {
            $ligneId = $this->modeleLigne->creer($ligne);

            $this->modeleBon->mettreAJourTotaux(
                $ligne['bon_commande_id'],
                $montantHt,
                $montantTva,
                $montantTtc
            );

            \Database::validerTransaction();
            return $ligneId;
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Modifie le statut d'un bon de commande.
     */
    public function modifierStatut(int $bonCommandeId, string $nouveauStatut): void {
        $valides = ['BROUILLON', 'VALIDÉ', 'REÇU_PARTIEL', 'REÇU_TOTAL', 'ANNULÉ'];
        if (!in_array($nouveauStatut, $valides, true)) {
            throw new \InvalidArgumentException("Statut invalide : {$nouveauStatut}");
        }
        $this->modeleBon->modifierStatut($bonCommandeId, $nouveauStatut);
    }

    /**
     * Met à jour la quantité reçue d'une ligne et recalcule le statut du bon.
     */
    public function mettreAJourQuantiteRecueLigne(int $ligneCommandeId, float $nouvelleQuantiteRecue): void {
        \Database::debuterTransaction();
        try {
            $this->modeleLigne->mettreAJourQuantiteRecue($ligneCommandeId, $nouvelleQuantiteRecue);

            // Récupérer le bon_commande_id depuis la ligne
            $ligneData = $this->modeleLigne->trouverAvecBon($ligneCommandeId);
            $bonCommandeId = (int) $ligneData['bon_id'];

            // Calcul du nouveau statut
            $stats = $this->modeleBon->getStatutsReception($bonCommandeId);
            $total     = (int) $stats['total_lignes'];
            $totalRecues = (int) $stats['recues_total'];
            $partiel   = (int) $stats['recues_partiel'];

            $nouveauStatut = 'VALIDÉ';
            if ($totalRecues === $total) {
                $nouveauStatut = 'REÇU_TOTAL';
            } elseif ($partiel > 0 || $totalRecues > 0) {
                $nouveauStatut = 'REÇU_PARTIEL';
            }

            $this->modeleBon->modifierStatut($bonCommandeId, $nouveauStatut);

            \Database::validerTransaction();
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Supprime une ligne de commande (seulement si le bon est en BROUILLON).
     */
    public function supprimerLigneCommande(int $ligneCommandeId): void {
        \Database::debuterTransaction();
        try {
            $data = $this->modeleLigne->trouverAvecBon($ligneCommandeId);

            if ($data['bon_statut'] !== 'BROUILLON') {
                throw new \RuntimeException("Impossible de supprimer une ligne d'un bon non BROUILLON.");
            }

            $montantHtLigne  = (float) $data['montant_ht'];
            $tvaLigne        = (float) $data['tva'];
            $montantTvaLigne = $montantHtLigne * ($tvaLigne / 100);
            $montantTtcLigne = $montantHtLigne + $montantTvaLigne;
            $bonId           = (int) $data['bon_id'];

            $this->modeleLigne->supprimer($ligneCommandeId);

            $this->modeleBon->mettreAJourTotaux(
                $bonId,
                -$montantHtLigne,
                -$montantTvaLigne,
                -$montantTtcLigne
            );

            \Database::validerTransaction();
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Supprime un bon de commande complet.
     */
    public function supprimerBonCommande(int $bonCommandeId): void {
        \Database::debuterTransaction();
        try {
            if ($this->modeleBon->aDesReceptions($bonCommandeId)) {
                throw new \RuntimeException("Impossible : des bons de réception sont liés à cette commande.");
            }

            $bon = $this->modeleBon->trouverParId($bonCommandeId);
            if (!in_array($bon['statut'], ['BROUILLON', 'ANNULÉ'], true)) {
                throw new \RuntimeException("Impossible de supprimer un bon avec statut {$bon['statut']}.");
            }

            $this->modeleBon->supprimer($bonCommandeId);

            \Database::validerTransaction();
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }
}