<?php
// modules/approvisionnements/services/StockService.php

namespace approvisionnements\services;

use approvisionnements\models\ProduitModel;
use approvisionnements\models\MouvementStockModel;

class StockService {

    private ProduitModel $modeleProduit;
    private MouvementStockModel $modeleMouvement;

    public function __construct() {
        $this->modeleProduit   = new ProduitModel();
        $this->modeleMouvement = new MouvementStockModel();
    }

    /**
     * Ajuste le stock d'un produit à une nouvelle valeur.
     * Enregistre la différence comme un mouvement de type AJUSTEMENT.
     */
    public function ajusterStock(array $ajustement): void {
        \Database::debuterTransaction();
        try {
            $ancienStock = $this->modeleProduit->trouverStockPourMaj($ajustement['produit_id']);
            $diff = $ajustement['nouveau_stock'] - $ancienStock;

            if ($diff == 0) {
                \Database::validerTransaction();
                return;
            }

            $typeMouvement = ($diff > 0) ? 'ENTRÉE' : 'SORTIE';
            $quantite = abs($diff);

            // Mettre à jour le stock
            $this->modeleProduit->mettreAJourStock(
                $ajustement['produit_id'],
                $ajustement['nouveau_stock']
            );

            // Journaliser le mouvement
            $this->modeleMouvement->enregistrer([
                'produit_id'     => $ajustement['produit_id'],
                'type_mouvement' => $typeMouvement,
                'source'         => 'AJUSTEMENT',
                'source_id'      => null,
                'quantite'       => $quantite,
                'stock_avant'    => $ancienStock,
                'stock_apres'    => $ajustement['nouveau_stock'],
                'utilisateur_id' => $ajustement['utilisateur_id'] ?? null,
            ]);

            \Database::validerTransaction();
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            throw $e;
        }
    }

    /**
     * Retourne l'historique des mouvements d'un produit.
     */
    public function listerMouvementsProduit(int $produitId): array {
        return $this->modeleMouvement->listerParProduit($produitId);
    }
}