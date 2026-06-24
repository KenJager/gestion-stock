<?php
// modules/ventes/services/StockService.php

namespace ventes\services;

use Database;

class StockService
{
    /**
     * Sortie de stock
     */
    public function sortir(int $produitId, float $quantite, string $source, int $sourceId): void {
        // Récupérer le stock actuel
        $sql = "SELECT stock_actuel FROM produits WHERE id = :id";
        $stmt = Database::query($sql, [':id' => $produitId]);
        $row = $stmt->fetch();
        
        if (!$row) {
            throw new \RuntimeException("Produit #{$produitId} introuvable.");
        }
        
        $stockAvant = (float) $row['stock_actuel'];
        $stockApres = $stockAvant - $quantite;
        
        // Mettre à jour le stock
        Database::query(
            "UPDATE produits SET stock_actuel = :stock_apres WHERE id = :id",
            [':stock_apres' => $stockApres, ':id' => $produitId]
        );
        
        // Journaliser le mouvement
        Database::query(
            "INSERT INTO mouvements_stock 
                (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id)
             VALUES 
                (:produit_id, 'SORTIE', :source, :source_id, :quantite, :stock_avant, :stock_apres, :utilisateur_id)",
            [
                ':produit_id'     => $produitId,
                ':source'         => $source,
                ':source_id'      => $sourceId,
                ':quantite'       => $quantite,
                ':stock_avant'    => $stockAvant,
                ':stock_apres'    => $stockApres,
                ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
            ]
        );
    }
    
    /**
     * Entrée de stock
     */
    public function entrer(int $produitId, float $quantite, string $source, int $sourceId): void {
        // Récupérer le stock actuel
        $sql = "SELECT stock_actuel FROM produits WHERE id = :id";
        $stmt = Database::query($sql, [':id' => $produitId]);
        $row = $stmt->fetch();
        
        if (!$row) {
            throw new \RuntimeException("Produit #{$produitId} introuvable.");
        }
        
        $stockAvant = (float) $row['stock_actuel'];
        $stockApres = $stockAvant + $quantite;
        
        // Mettre à jour le stock
        Database::query(
            "UPDATE produits SET stock_actuel = :stock_apres WHERE id = :id",
            [':stock_apres' => $stockApres, ':id' => $produitId]
        );
        
        // Journaliser le mouvement
        Database::query(
            "INSERT INTO mouvements_stock 
                (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id)
             VALUES 
                (:produit_id, 'ENTREE', :source, :source_id, :quantite, :stock_avant, :stock_apres, :utilisateur_id)",
            [
                ':produit_id'     => $produitId,
                ':source'         => $source,
                ':source_id'      => $sourceId,
                ':quantite'       => $quantite,
                ':stock_avant'    => $stockAvant,
                ':stock_apres'    => $stockApres,
                ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
            ]
        );
    }
}