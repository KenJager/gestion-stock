<?php
// modules/approvisionnements/services/DonService.php

namespace approvisionnements\services;

use approvisionnements\models\EntreeDonModel;
use approvisionnements\models\LigneEntreeDonModel;
use approvisionnements\models\ProduitModel;
use approvisionnements\models\MouvementStockModel;

class DonService {

    private EntreeDonModel $modeleEntree;
    private LigneEntreeDonModel $modeleLigne;
    private ProduitModel $modeleProduit;
    private MouvementStockModel $modeleMouvement;

    public function __construct() {
        $this->modeleEntree    = new EntreeDonModel();
        $this->modeleLigne     = new LigneEntreeDonModel();
        $this->modeleProduit   = new ProduitModel();
        $this->modeleMouvement = new MouvementStockModel();
    }

    /**
     * Retourne les entrées de dons sur une période donnée.
     */
    public function listerParPeriode(string $dateDebut, string $dateFin): array {
        return $this->modeleEntree->listerParPeriode($dateDebut, $dateFin);
    }

    /**
     * Enregistre un don avec ses lignes (mise à jour du stock et mouvements).
     */
    public function enregistrer(array $don): int {
        \Database::debuterTransaction();
        try {
            $donId = $this->modeleEntree->creer($don);

            foreach ($don['lignes'] as $ligne) {
                $this->modeleLigne->creer([
                    'entree_don_id'   => $donId,
                    'produit_id'      => $ligne['produit_id'],
                    'quantite'        => $ligne['quantite'],
                    'valeur_estimee'  => $ligne['valeur_estimee'] ?? null,
                    'date_peremption' => $ligne['date_peremption'] ?? null,
                ]);

                // Mise à jour du stock
                $stockAvant = $this->modeleProduit->trouverStockPourMaj($ligne['produit_id']);
                $stockApres = $stockAvant + $ligne['quantite'];

                $this->modeleProduit->mettreAJourStock($ligne['produit_id'], $stockApres);

                // Journaliser le mouvement
                $this->modeleMouvement->enregistrer([
                    'produit_id'     => $ligne['produit_id'],
                    'type_mouvement' => 'ENTRÉE',
                    'source'         => 'DON',
                    'source_id'      => $donId,
                    'quantite'       => $ligne['quantite'],
                    'stock_avant'    => $stockAvant,
                    'stock_apres'    => $stockApres,
                ]);
            }

            \Database::validerTransaction();
            return $donId;
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }
}