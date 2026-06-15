<?php
// modules/approvisionnements/models/FournisseurModel.php

namespace approvisionnements\models;

require_once ROOT . '/core/Model.php';

class FournisseurModel extends \Model
{
    protected string $table = 'fournisseurs';

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY raison_sociale";
        return $this->pdo()->query($sql)->fetchAll();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        if (!$res) {
            throw new \RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO {$this->table} (
                    code, raison_sociale, contact, telephone, email,
                    adresse, ville, pays, actif
                ) VALUES (
                    :code, :raison_sociale, :contact, :telephone, :email,
                    :adresse, :ville, :pays, :actif
                ) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':code'           => $donnees['code'],
            ':raison_sociale' => $donnees['raison_sociale'],
            ':contact'        => $donnees['contact'] ?? null,
            ':telephone'      => $donnees['telephone'] ?? null,
            ':email'          => $donnees['email'] ?? null,
            ':adresse'        => $donnees['adresse'] ?? null,
            ':ville'          => $donnees['ville'] ?? null,
            ':pays'           => $donnees['pays'] ?? 'Cameroun',
            ':actif'          => $donnees['actif'] ?? true,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET
                    code = :code, raison_sociale = :raison_sociale,
                    contact = :contact, telephone = :telephone, email = :email,
                    adresse = :adresse, ville = :ville, pays = :pays, actif = :actif
                WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id'             => $id,
            ':code'           => $donnees['code'],
            ':raison_sociale' => $donnees['raison_sociale'],
            ':contact'        => $donnees['contact'] ?? null,
            ':telephone'      => $donnees['telephone'] ?? null,
            ':email'          => $donnees['email'] ?? null,
            ':adresse'        => $donnees['adresse'] ?? null,
            ':ville'          => $donnees['ville'] ?? null,
            ':pays'           => $donnees['pays'] ?? 'Cameroun',
            ':actif'          => $donnees['actif'] ?? true,
        ]);
    }
}