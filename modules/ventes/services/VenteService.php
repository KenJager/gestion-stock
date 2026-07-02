<?php
// modules/ventes/Services/VenteService.php

namespace ventes\services;

use ventes\models\CommandeClient;
use ventes\models\LivraisonClient;
use ventes\models\FactureClient;
use ventes\models\ReglementClient;
use ventes\models\VenteComptant;
use ventes\models\BonSortie;
use Database;

class VenteService {

    private CommandeClient  $modCommande;
    private LivraisonClient $modLivraison;
    private FactureClient   $modFacture;
    private ReglementClient $modReglement;
    private VenteComptant   $modComptant;
    private BonSortie       $modSortie;
    private StockService    $stock;

    public function __construct() {
        $this->modCommande  = new CommandeClient();
        $this->modLivraison = new LivraisonClient();
        $this->modFacture   = new FactureClient();
        $this->modReglement = new ReglementClient();
        $this->modComptant  = new VenteComptant();
        $this->modSortie    = new BonSortie();
        $this->stock        = new StockService();
    }

    // ================================================================
    // RÉCUPÉRATION DES DONNÉES POUR LES FORMULAIRES
    // ================================================================

    /**
     * Liste tous les clients actifs
     */
    public function listerClientsActifs(): array {
        return Database::query(
            "SELECT id, code, raison_sociale, contact, telephone, email 
             FROM clients 
             WHERE actif = true 
             ORDER BY raison_sociale"
        )->fetchAll();
    }

    /**
     * Liste tous les produits actifs avec leurs informations
     */
    public function listerProduitsActifs(): array {
        return Database::query(
            "SELECT p.id, p.code, p.designation, p.unite, p.prix_vente_ht, p.tva, p.stock_actuel,
                    f.libelle AS famille_libelle,
                    fp.produit_pere_id AS parent_id,
                    CASE 
                        WHEN p.est_pere = true THEN 'pere'
                        WHEN fp.produit_pere_id IS NOT NULL THEN 'fils'
                        ELSE 'simple'
                    END AS type_produit
             FROM produits p
             LEFT JOIN familles_produits f ON f.id = p.famille_id
             LEFT JOIN fractions_produit fp ON fp.produit_fils_id = p.id
             WHERE p.actif = true
             ORDER BY p.designation"
        )->fetchAll();
    }

    // ================================================================
    // COMMANDES
    // ================================================================

    public function enregistrerCommande(array $post): int {
        $lignes    = $post['lignes'] ?? [];
        $remisePct = (float) ($post['remise_pct'] ?? 0);
        $totaux    = $this->totauxLignes($lignes, $remisePct);

        Database::debuterTransaction();
        try {
            $id = $this->modCommande->creer([
                ':numero'                => $this->numero('BCC'),
                ':client_id'             => (int) $post['client_id'],
                ':date_commande'         => date('Y-m-d'),
                ':date_livraison_prevue' => $post['date_livraison_prevue'] ?: null,
                ':montant_ht'            => $totaux['ht'],
                ':montant_tva'           => $totaux['tva'],
                ':montant_ttc'           => $totaux['ttc'],
                ':remise_pct'            => $remisePct,
                ':notes'                 => $post['notes'] ?? null,
                ':utilisateur_id'        => $_SESSION['utilisateur_id'] ?? null,
            ]);

            foreach ($lignes as $l) {
                $this->modCommande->ajouterLigne([
                    ':bon_commande_id'    => $id,
                    ':produit_id'         => (int) $l['produit_id'],
                    ':quantite_commandee' => (float) $l['quantite'],
                    ':prix_unitaire_ht'   => (float) $l['prix_unitaire_ht'],
                    ':tva'                => (float) ($l['tva'] ?? 0),
                    ':remise_pct'         => (float) ($l['remise_pct'] ?? 0),
                ]);
            }

            Database::validerTransaction();
            return $id;
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    public function modifierCommande(int $id, array $post): void {
        $lignes    = $post['lignes'] ?? [];
        $remisePct = (float) ($post['remise_pct'] ?? 0);
        $totaux    = $this->totauxLignes($lignes, $remisePct);

        Database::debuterTransaction();
        try {
            $this->modCommande->modifier($id, [
                ':client_id'             => (int) $post['client_id'],
                ':date_livraison_prevue' => $post['date_livraison_prevue'] ?: null,
                ':remise_pct'            => $remisePct,
                ':montant_ht'            => $totaux['ht'],
                ':montant_tva'           => $totaux['tva'],
                ':montant_ttc'           => $totaux['ttc'],
                ':notes'                 => $post['notes'] ?? null,
            ]);

            $this->modCommande->supprimerLignes($id);
            foreach ($lignes as $l) {
                $this->modCommande->ajouterLigne([
                    ':bon_commande_id'    => $id,
                    ':produit_id'         => (int) $l['produit_id'],
                    ':quantite_commandee' => (float) $l['quantite'],
                    ':prix_unitaire_ht'   => (float) $l['prix_unitaire_ht'],
                    ':tva'                => (float) ($l['tva'] ?? 0),
                    ':remise_pct'         => (float) ($l['remise_pct'] ?? 0),
                ]);
            }

            Database::validerTransaction();
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    // ================================================================
    // LIVRAISONS
    // ================================================================

    public function livrerProduits(int $commandeId, array $lignes): int {
        $commande = $this->modCommande->trouverParId($commandeId);

        Database::debuterTransaction();
        try {
            $livId = $this->modLivraison->creer([
                ':numero'          => $this->numero('BLC'),
                ':bon_commande_id' => $commandeId,
                ':client_id'       => $commande['client_id'],
                ':date_livraison'  => date('Y-m-d'),
                ':notes'           => null,
                ':utilisateur_id'  => $_SESSION['utilisateur_id'] ?? null,
            ]);

            foreach ($lignes as $l) {
                $qte = (float) $l['quantite_livree'];
                if ($qte <= 0) continue;

                $this->modLivraison->ajouterLigne([
                    ':bon_livraison_id' => $livId,
                    ':produit_id'       => (int) $l['produit_id'],
                    ':quantite_livree'  => $qte,
                    ':prix_unitaire_ht' => (float) $l['prix_unitaire_ht'],
                    ':tva'              => (float) ($l['tva'] ?? 0),
                    ':remise_pct'       => (float) ($l['remise_pct'] ?? 0),
                ]);

                $this->modCommande->mettreAJourQuantiteLivree((int) $l['ligne_id'], $qte);
                $this->stock->sortir((int) $l['produit_id'], $qte, 'VENTE', $livId);
            }

            $this->rafraichirStatutCommande($commandeId);
            Database::validerTransaction();
            return $livId;
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    private function rafraichirStatutCommande(int $commandeId): void {
        $commande = $this->modCommande->trouverParId($commandeId);
        $complet  = true;
        foreach ($commande['lignes'] as $l) {
            if ((float) $l['quantite_livree'] < (float) $l['quantite_commandee']) {
                $complet = false;
                break;
            }
        }
        $this->modCommande->changerStatut($commandeId, $complet ? 'LIVRÉ_TOTAL' : 'LIVRÉ_PARTIEL');
    }

    // ================================================================
    // FACTURES
    // ================================================================

    public function facturer(int $livraisonId, array $post): int {
        $livraison = $this->modLivraison->trouverParId($livraisonId);
        $totaux    = $this->totauxLignes($livraison['lignes'], 0);
        $remise    = (float) ($post['remise_montant'] ?? 0);

        Database::debuterTransaction();
        try {
            $factureId = $this->modFacture->creer([
                ':numero'           => $this->numero('FC'),
                ':bon_livraison_id' => $livraisonId,
                ':client_id'        => $livraison['client_id'],
                ':date_facture'     => date('Y-m-d'),
                ':date_echeance'    => $post['date_echeance'] ?: null,
                ':type_vente'       => $post['type_vente'] ?? 'CREDIT',
                ':montant_ht'       => $totaux['ht'],
                ':montant_tva'      => $totaux['tva'],
                ':montant_ttc'      => $totaux['ttc'],
                ':remise_montant'   => $remise,
                ':notes'            => $post['notes'] ?? null,
                ':utilisateur_id'   => $_SESSION['utilisateur_id'] ?? null,
            ]);

            foreach ($livraison['lignes'] as $l) {
                $this->modFacture->ajouterLigne([
                    ':facture_id'       => $factureId,
                    ':produit_id'       => (int) $l['produit_id'],
                    ':quantite'         => (float) $l['quantite_livree'],
                    ':prix_unitaire_ht' => (float) $l['prix_unitaire_ht'],
                    ':tva'              => (float) $l['tva'],
                    ':remise_pct'       => (float) $l['remise_pct'],
                ]);
            }

            Database::validerTransaction();
            return $factureId;
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    // ================================================================
    // RÈGLEMENTS
    // ================================================================

    public function reglerFacture(int $factureId, array $post): void {
        $facture = $this->modFacture->trouverParId($factureId);
        $montant = (float) $post['montant'];
        $solde   = (float) $facture['solde'];

        if ($montant <= 0) {
            throw new \RuntimeException("Le montant doit être positif.");
        }
        if ($montant > $solde + 0.01) {
            throw new \RuntimeException(
                "Montant ({$montant} FCFA) supérieur au solde restant ({$solde} FCFA)."
            );
        }

        Database::debuterTransaction();
        try {
            $this->modReglement->creer([
                ':facture_id'     => $factureId,
                ':date_reglement' => date('Y-m-d'),
                ':montant'        => $montant,
                ':mode_paiement'  => $post['mode_paiement'] ?? null,
                ':reference'      => $post['reference'] ?? null,
                ':banque_id'      => $post['banque_id'] ?: null,
                ':notes'          => $post['notes'] ?? null,
                ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
            ]);

            $this->modFacture->incrementerMontantRegle($factureId, $montant);
            $statut = ($solde - $montant) <= 0.01 ? 'REGLÉE' : 'PARTIELLEMENT_REGLÉE';
            $this->modFacture->changerStatut($factureId, $statut);

            Database::validerTransaction();
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    // ================================================================
    // VENTE COMPTANT
    // ================================================================

    public function venteComptant(array $post): int {
        $lignes = $post['lignes'] ?? [];
        $totaux = $this->totauxLignes($lignes, 0);
        $remise = (float) ($post['remise_montant'] ?? 0);

        Database::debuterTransaction();
        try {
            $id = $this->modComptant->creer([
                ':numero'         => $this->numero('VC'),
                ':client_id'      => $post['client_id'] ?: null,
                ':date_vente'     => date('Y-m-d'),
                ':montant_ht'     => $totaux['ht'],
                ':montant_tva'    => $totaux['tva'],
                ':montant_ttc'    => $totaux['ttc'],
                ':remise_montant' => $remise,
                ':montant_percu'  => (float) $post['montant_percu'],
                ':mode_paiement'  => $post['mode_paiement'] ?? null,
                ':notes'          => $post['notes'] ?? null,
                ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
            ]);

            foreach ($lignes as $l) {
                $this->modComptant->ajouterLigne([
                    ':vente_comptant_id' => $id,
                    ':produit_id'        => (int) $l['produit_id'],
                    ':quantite'          => (float) $l['quantite'],
                    ':prix_unitaire_ht'  => (float) $l['prix_unitaire_ht'],
                    ':tva'               => (float) ($l['tva'] ?? 0),
                    ':remise_pct'        => (float) ($l['remise_pct'] ?? 0),
                ]);
                $this->stock->sortir((int) $l['produit_id'], (float) $l['quantite'], 'VENTE_COMPTANT', $id);
            }

            Database::validerTransaction();
            return $id;
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    // ================================================================
    // BON DE SORTIE
    // ================================================================

    public function sortirProduits(array $post): int {
        $lignes = $post['lignes'] ?? [];

        Database::debuterTransaction();
        try {
            $id = $this->modSortie->creer([
                ':numero'         => $this->numero('BS'),
                ':motif'          => $post['motif'],
                ':date_sortie'    => date('Y-m-d'),
                ':notes'          => $post['notes'] ?? null,
                ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
            ]);

            foreach ($lignes as $l) {
                $this->modSortie->ajouterLigne([
                    ':bon_sortie_id' => $id,
                    ':produit_id'    => (int) $l['produit_id'],
                    ':quantite'      => (float) $l['quantite'],
                    ':prix_unitaire' => (float) ($l['prix_unitaire'] ?? 0),
                    ':observations'  => $l['observations'] ?? null,
                ]);
                $this->stock->sortir((int) $l['produit_id'], (float) $l['quantite'], 'PERTE', $id);
            }

            Database::validerTransaction();
            return $id;
        } catch (\Exception $e) {
            Database::annulerTransaction();
            throw $e;
        }
    }

    // ================================================================
    // UTILITAIRES
    // ================================================================

    public function totauxLignes(array $lignes, float $remisePctGlobale): array {
        $ht = 0.0; $tva = 0.0;
        foreach ($lignes as $l) {
            $qte  = (float) ($l['quantite'] ?? $l['quantite_commandee'] ?? $l['quantite_livree'] ?? 0);
            $pu   = (float) ($l['prix_unitaire_ht'] ?? 0);
            $taux = (float) ($l['tva'] ?? 0);
            $rem  = (float) ($l['remise_pct'] ?? 0);
            $htL  = $qte * $pu * (1 - $rem / 100);
            $ht  += $htL;
            $tva += $htL * ($taux / 100);
        }
        $ht = $ht * (1 - $remisePctGlobale / 100);
        return [
            'ht'  => round($ht,       2),
            'tva' => round($tva,       2),
            'ttc' => round($ht + $tva, 2),
        ];
    }

    private function numero(string $prefixe): string {
        return $prefixe . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}