<?php
// modules/structure/models/FamilleModel.php

require_once ROOT . '/core/Model.php';

class FamilleModel extends Model
{
    protected string $table = 'familles_produits';

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY libelle";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO {$this->table} (code, libelle, description)
                VALUES (:code, :libelle, :description) RETURNING id";
        $res = $this->bd()->requete($sql, [
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':description' => $donnees['description'] ?? null,
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET code = :code, libelle = :libelle,
                description = :description WHERE id = :id";
        $this->bd()->requete($sql, [
            ':id'          => $id,
            ':code'        => $donnees['code'],
            ':libelle'     => $donnees['libelle'],
            ':description' => $donnees['description'] ?? null,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}