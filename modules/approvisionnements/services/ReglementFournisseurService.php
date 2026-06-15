<?php
// modules/approvisionnements/services/ReglementFournisseurService.php

namespace approvisionnements\services;

use approvisionnements\models\ReglementFournisseurModel;
use approvisionnements\models\FactureFournisseurModel;

class ReglementFournisseurService {

    private ReglementFournisseurModel $modeleReglement;
    private FactureFournisseurModel $modeleFacture;

    public function __construct() {
        $this->modeleReglement = new ReglementFournisseurModel();
        $this->modeleFacture   = new FactureFournisseurModel();
    }

    /**
     * Retourne l'historique des règlements pour un fournisseur.
     */
    public function listerHistoriqueParFournisseur(int $fournisseurId): array {
        return $this->modeleReglement->listerParFournisseur($fournisseurId);
    }

    /**
     * Enregistre un règlement sur une facture fournisseur.
     */
    public function enregistrer(array $reglement): int {
        \Database::debuterTransaction();
        try {
            // Vérifier le solde de la facture
            $facture = $this->modeleFacture->trouverParIdPourMaj($reglement['facture_id']);
            $soldeActuel = (float) $facture['montant_ttc'] - (float) $facture['montant_regle'];

            if ($reglement['montant'] > $soldeActuel) {
                throw new \RuntimeException("Le montant du règlement dépasse le solde dû ({$soldeActuel})");
            }

            $reglementId = $this->modeleReglement->creer($reglement);

            // Mettre à jour la facture
            $nouveauRegle  = (float) $facture['montant_regle'] + $reglement['montant'];
            $nouveauStatut = ($nouveauRegle >= (float) $facture['montant_ttc']) ? 'REGLÉE' : 'PARTIELLEMENT_REGLÉE';

            $this->modeleFacture->mettreAJourReglement(
                $reglement['facture_id'],
                $nouveauRegle,
                $nouveauStatut
            );

            \Database::validerTransaction();
            return $reglementId;
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Annule un règlement.
     */
    public function annuler(int $reglementId): void {
        \Database::debuterTransaction();
        try {
            // Récupérer les infos avant suppression
            $reg = $this->modeleReglement->trouverParId($reglementId);
            $factureId = (int) $reg['facture_id'];
            $montant   = (float) $reg['montant'];

            // Supprimer le règlement
            $this->modeleReglement->supprimer($reglementId);

            // Mettre à jour la facture
            $facture = $this->modeleFacture->trouverParIdPourMaj($factureId);
            $nouveauRegle  = (float) $facture['montant_regle'] - $montant;
            $nouveauStatut = $nouveauRegle <= 0 ? 'NON_REGLÉE' : 'PARTIELLEMENT_REGLÉE';

            $this->modeleFacture->mettreAJourReglement($factureId, $nouveauRegle, $nouveauStatut);

            \Database::validerTransaction();
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }
}