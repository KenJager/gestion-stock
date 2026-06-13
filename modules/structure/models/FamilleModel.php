<?php
// modules/structure/models/FamilleModel.php

require_once ROOT . '/core/Model.php';

class FamilleModel extends Model
{
    protected string $table = 'familles_produits';

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY libelle";
        return $this->pdo()->query($sql)->fetchAll();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO {$this->table} (code, libelle, description)
                VALUES (:code, :libelle, :description) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':description' => $donnees['description'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET code = :code, libelle = :libelle,
                description = :description WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':description' => $donnees['description'] ?? null,
        ]);
    }
}