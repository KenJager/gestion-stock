<?php
// modules/structure/models/CategorieClientModel.php

require_once ROOT . '/core/Model.php';

class CategorieClientModel extends Model
{
    protected string $table = 'categories_clients';

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
        $sql = "INSERT INTO {$this->table} (code, libelle, remise_pct, description)
                VALUES (:code, :libelle, :remise_pct, :description) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':remise_pct'  => $donnees['remise_pct'] ?? 0,
            ':description' => $donnees['description'] ?? null,
        ]);
        $res = $stmt->fetch();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET 
                    code = :code, 
                    libelle = :libelle, 
                    remise_pct = :remise_pct, 
                    description = :description 
                WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':remise_pct'  => $donnees['remise_pct'] ?? 0,
            ':description' => $donnees['description'] ?? null,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Compter les clients utilisant cette catégorie
    public function compterClientsParCategorie(int $categorieId): int {
        $sql = "SELECT COUNT(*) as total FROM clients WHERE categorie_id = :categorie_id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':categorie_id' => $categorieId]);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }
}