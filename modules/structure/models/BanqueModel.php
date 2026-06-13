<?php
// modules/structure/models/BanqueModel.php

require_once ROOT . '/core/Model.php';

class BanqueModel extends Model
{
    protected string $table = 'banques';

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY nom";
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
        $sql = "INSERT INTO {$this->table} (code, nom, adresse, telephone, numero_compte)
                VALUES (:code, :nom, :adresse, :telephone, :numero_compte) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':code'          => $donnees['code'],
            ':nom'           => $donnees['nom'],
            ':adresse'       => $donnees['adresse'] ?? null,
            ':telephone'     => $donnees['telephone'] ?? null,
            ':numero_compte' => $donnees['numero_compte'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET
                    code = :code, nom = :nom, adresse = :adresse,
                    telephone = :telephone, numero_compte = :numero_compte
                WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id'            => $id,
            ':code'          => $donnees['code'],
            ':nom'           => $donnees['nom'],
            ':adresse'       => $donnees['adresse'] ?? null,
            ':telephone'     => $donnees['telephone'] ?? null,
            ':numero_compte' => $donnees['numero_compte'] ?? null,
        ]);
    }
}