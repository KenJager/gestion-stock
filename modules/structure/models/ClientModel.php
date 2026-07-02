<?php
// modules/structure/models/ClientModel.php

require_once ROOT . '/core/Model.php';

class ClientModel extends Model
{
    protected string $table = 'clients';

    /**
     * Liste tous les clients actifs
     */
    public function listerActifs(): array {
        $sql = "SELECT id, code, raison_sociale, contact, telephone, email 
                FROM {$this->table} 
                WHERE actif = true 
                ORDER BY raison_sociale";
        return $this->pdo()->query($sql)->fetchAll();
    }

    public function listerAvecCategorie(): array {
        $sql = "SELECT c.*, cc.libelle AS categorie_libelle
                FROM {$this->table} c
                LEFT JOIN categories_clients cc ON cc.id = c.categorie_id
                ORDER BY c.raison_sociale";
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
        $sql = "INSERT INTO {$this->table} (
                    code, raison_sociale, contact, telephone, email,
                    adresse, ville, pays, categorie_id, actif
                ) VALUES (
                    :code, :raison_sociale, :contact, :telephone, :email,
                    :adresse, :ville, :pays, :categorie_id, :actif
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
            ':categorie_id'   => $donnees['categorie_id'] ?? null,
            ':actif'          => $donnees['actif'] ?? true,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET
                    code = :code, raison_sociale = :raison_sociale,
                    contact = :contact, telephone = :telephone, email = :email,
                    adresse = :adresse, ville = :ville, pays = :pays,
                    categorie_id = :categorie_id, actif = :actif
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
            ':categorie_id'   => $donnees['categorie_id'] ?? null,
            ':actif'          => $donnees['actif'] ?? true,
        ]);
    }

    /**
     * Récupère un client avec sa catégorie
     */
    public function trouverAvecCategorie(int $id): ?array {
        $sql = "SELECT c.*, cc.libelle AS categorie_libelle, cc.code AS categorie_code
                FROM clients c
                LEFT JOIN categories_clients cc ON cc.id = c.categorie_id
                WHERE c.id = :id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}