<?php
// modules/approvisionnements/models/Produit.php

namespace approvisionnements\models;

class ProduitModel extends \Model {

    protected string $table = 'produits';

    /**
     * Verrouille et retourne le stock actuel d'un produit.
     */
    public function trouverStockPourMaj(int $id): float {
        $sql = "SELECT stock_actuel FROM produits WHERE id = :id FOR UPDATE";
        $stmt = \Database::query($sql, [':id' => $id]);
        $resultat = $stmt->fetch();

        if (!$resultat) {
            throw new \RuntimeException("Produit ID {$id} introuvable.");
        }
        return (float) $resultat['stock_actuel'];
    }

    /**
     * Met à jour le stock actuel d'un produit.
     */
    public function mettreAJourStock(int $id, float $nouveauStock): void {
        $sql = "UPDATE produits SET stock_actuel = :stock WHERE id = :id";
        \Database::query($sql, [':stock' => $nouveauStock, ':id' => $id]);
    }
}