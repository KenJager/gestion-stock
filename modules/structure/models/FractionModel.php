<?php
// modules/structure/models/FractionModel.php

require_once ROOT . '/core/Model.php';

class FractionModel extends Model
{
    protected string $table = 'fractions_produit';

    public function creer(array $donnees): int {
        $sql = "INSERT INTO fractions_produit (produit_pere_id, produit_fils_id, quantite_fils)
                VALUES (:produit_pere_id, :produit_fils_id, :quantite_fils) RETURNING id";
        $res = $this->bd()->requete($sql, [
            ':produit_pere_id' => $donnees['produit_pere_id'],
            ':produit_fils_id' => $donnees['produit_fils_id'],
            ':quantite_fils'   => $donnees['quantite_fils'],
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function listerParProduitPere(int $produitPereId): array {
        $sql = "SELECT f.*, p.designation AS fils_designation, p.unite AS fils_unite
                FROM fractions_produit f
                JOIN produits p ON p.id = f.produit_fils_id
                WHERE f.produit_pere_id = :pere_id";
        return $this->bd()->requete($sql, [':pere_id' => $produitPereId])->toutRecuperer();
    }
}