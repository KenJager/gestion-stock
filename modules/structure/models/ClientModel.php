<?php
// modules/structure/models/ClientModel.php

require_once ROOT . '/core/Model.php';

class ClientModel extends Model
{
    protected string $table = 'clients';

    public function listerAvecCategorie(): array {
        $sql = "SELECT c.*, cc.libelle AS categorie_libelle
                FROM clients c
                LEFT JOIN categories_clients cc ON cc.id = c.categorie_id
                ORDER BY c.raison_sociale";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM clients WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO clients (
                    code, raison_sociale, contact, telephone, email,
                    adresse, ville, pays, categorie_id, actif
                ) VALUES (
                    :code, :raison_sociale, :contact, :telephone, :email,
                    :adresse, :ville, :pays, :categorie_id, :actif
                ) RETURNING id";
        $res = $this->bd()->requete($sql, [
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
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE clients SET
                    code = :code, raison_sociale = :raison_sociale,
                    contact = :contact, telephone = :telephone, email = :email,
                    adresse = :adresse, ville = :ville, pays = :pays,
                    categorie_id = :categorie_id, actif = :actif
                WHERE id = :id";
        $this->bd()->requete($sql, [
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

    public function supprimer(int $id): void {
        $sql = "DELETE FROM clients WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}