<?php
// modules/structure/models/CategorieClientModel.php

require_once ROOT . '/core/Model.php';

class CategorieClientModel extends Model
{
    protected string $table = 'categories_clients';

    public function listerTout(): array {
        $sql = "SELECT * FROM categories_clients ORDER BY libelle";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM categories_clients WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO categories_clients (code, libelle, remise_pct, description)
                VALUES (:code, :libelle, :remise_pct, :description) RETURNING id";
        $res = $this->bd()->requete($sql, [
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':remise_pct'  => $donnees['remise_pct'] ?? 0,
            ':description' => $donnees['description'] ?? null,
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE categories_clients SET
                    code = :code, libelle = :libelle, remise_pct = :remise_pct, description = :description
                WHERE id = :id";
        $this->bd()->requete($sql, [
            ':id'          => $id,
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':remise_pct'  => $donnees['remise_pct'] ?? 0,
            ':description' => $donnees['description'] ?? null,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM categories_clients WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}