<?php
// modules/structure/services/ProduitService.php

require_once ROOT . '/core/Database.php';
require_once ROOT . '/modules/structure/models/ProduitModel.php';
require_once ROOT . '/modules/structure/models/FractionModel.php';

class ProduitService
{
    private ProduitModel $produitModel;
    private FractionModel $fractionModel;

    public function __construct() {
        $this->produitModel = new ProduitModel();
        $this->fractionModel = new FractionModel();
    }

    /**
     * Crée un produit père et ses produits fils en transaction
     */
    public function creerProduitAvecFractions(array $donneesPere, array $fils): int {
        $bd = Database::getInstance();
        try {
            $bd->connexion->beginTransaction();

            // Créer le produit père
            $donneesPere['est_pere'] = true;
            $idPere = $this->produitModel->creer($donneesPere);

            // Pour chaque fils, créer le produit et la fraction
            foreach ($fils as $f) {
                $donneesFils = [
                    'code'          => $f['code'],
                    'designation'   => $f['designation'],
                    'famille_id'    => $f['famille_id'],
                    'produit_pere_id'=> $idPere,
                    'est_pere'      => false,
                    'unite'         => $f['unite'] ?? null,
                    'prix_vente_ht' => $f['prix_vente_ht'] ?? 0,
                    'tva'           => $donneesPere['tva'] ?? 0,
                    'stock_actuel'  => 0,
                    'actif'         => true,
                ];
                $idFils = $this->produitModel->creer($donneesFils);

                $this->fractionModel->creer([
                    'produit_pere_id' => $idPere,
                    'produit_fils_id' => $idFils,
                    'quantite_fils'   => $f['quantite_fils'] ?? 1,
                ]);
            }

            $bd->connexion->commit();
            return $idPere;
        } catch (Exception $e) {
            $bd->connexion->rollBack();
            throw $e;
        }
    }
}