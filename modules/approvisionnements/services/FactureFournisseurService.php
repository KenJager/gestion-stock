<?php
// modules/approvisionnements/services/FactureFournisseurService.php

namespace approvisionnements\services;

use approvisionnements\models\FactureFournisseurModel;

class FactureFournisseurService {

    private FactureFournisseurModel $modele;

    public function __construct() {
        $this->modele = new FactureFournisseurModel();
    }

    /**
     * Liste toutes les factures fournisseur.
     */
    public function listerToutes(): array {
        return $this->modele->listerTout();
    }

    /**
     * Retourne les factures non réglées.
     */
    public function listerNonReglees(): array {
        return $this->modele->listerNonReglees();
    }

    /**
     * Crée une facture fournisseur à partir d'un bon de réception.
     */
    public function creerDepuisReception(array $donnees): int {
        \Database::debuterTransaction();
        try {
            // Récupérer fournisseur depuis le BR
            $sqlFour = "SELECT fournisseur_id FROM bons_reception WHERE id = :id";
            $stmtFour = \Database::query($sqlFour, [':id' => $donnees['bon_reception_id']]);
            $fournisseurId = $stmtFour->fetchColumn();
            if (!$fournisseurId) {
                throw new \RuntimeException("Bon de réception introuvable.");
            }

            // Calculer totaux depuis les lignes de réception
            $sqlTotaux = "SELECT
                            COALESCE(SUM(quantite_recue * prix_unitaire_ht), 0) AS montant_ht,
                            COALESCE(SUM(quantite_recue * prix_unitaire_ht * tva / 100), 0) AS montant_tva
                          FROM lignes_reception
                          WHERE bon_reception_id = :id";
            $stmtTot = \Database::query($sqlTotaux, [':id' => $donnees['bon_reception_id']]);
            $totaux = $stmtTot->fetch();

            $montantHt  = (float) $totaux['montant_ht'];
            $montantTva = (float) $totaux['montant_tva'];
            $montantTtc = $montantHt + $montantTva;

            $donnees['fournisseur_id'] = $fournisseurId;
            $donnees['montant_ht']     = $montantHt;
            $donnees['montant_tva']    = $montantTva;
            $donnees['montant_ttc']    = $montantTtc;

            $factureId = $this->modele->creer($donnees);

            \Database::validerTransaction();
            return $factureId;
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }
}