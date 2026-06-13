<?php
// modules/structure/models/FractionModel.php

require_once ROOT . '/core/Model.php';

class FractionModel extends Model
{
    protected string $table = 'fractions_produit';

    public function creer(array $donnees): int {
        $sql = "INSERT INTO {$this->table} (produit_pere_id, produit_fils_id, quantite_fils)
                VALUES (:produit_pere_id, :produit_fils_id, :quantite_fils) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':produit_pere_id' => $donnees['produit_pere_id'],
            ':produit_fils_id' => $donnees['produit_fils_id'],
            ':quantite_fils'   => $donnees['quantite_fils'],
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function listerParProduitPere(int $produitPereId): array {
        $sql = "SELECT f.*, p.designation AS fils_designation, p.unite AS fils_unite
                FROM {$this->table} f
                JOIN produits p ON p.id = f.produit_fils_id
                WHERE f.produit_pere_id = :pere_id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':pere_id' => $produitPereId]);
        return $stmt->fetchAll();
    }
}