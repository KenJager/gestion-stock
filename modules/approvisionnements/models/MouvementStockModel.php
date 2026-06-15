<?php
// modules/approvisionnements/models/MouvementStock.php

namespace approvisionnements\models;

class MouvementStockModel extends \Model {

    protected string $table = 'mouvements_stock';

    /**
     * Enregistre un mouvement de stock.
     */
    public function enregistrer(array $donnees): int {
        $sql = "INSERT INTO mouvements_stock
                (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id)
                VALUES
                (:produit_id, :type_mouvement, :source, :source_id, :quantite, :stock_avant, :stock_apres, :utilisateur_id)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':produit_id'     => $donnees['produit_id'],
            ':type_mouvement' => $donnees['type_mouvement'],
            ':source'         => $donnees['source'],
            ':source_id'      => $donnees['source_id'] ?? null,
            ':quantite'       => $donnees['quantite'],
            ':stock_avant'    => $donnees['stock_avant'],
            ':stock_apres'    => $donnees['stock_apres'],
            ':utilisateur_id' => $donnees['utilisateur_id'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }

    /**
     * Liste les mouvements d'un produit.
     */
    public function listerParProduit(int $produitId): array {
        $sql = "SELECT m.*, p.designation, u.nom || ' ' || u.prenom AS utilisateur
                FROM mouvements_stock m
                JOIN produits p ON p.id = m.produit_id
                LEFT JOIN utilisateurs u ON u.id = m.utilisateur_id
                WHERE m.produit_id = :id
                ORDER BY m.date_mouvement DESC";
        return \Database::query($sql, [':id' => $produitId])->fetchAll();
    }
}