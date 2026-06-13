<?php
// modules/structure/models/FournisseurModel.php

require_once ROOT . '/core/Model.php';

class FournisseurModel extends Model
{
    protected string $table = 'fournisseurs';

    public function listerTout(): array {
        $sql = "SELECT * FROM fournisseurs ORDER BY raison_sociale";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM fournisseurs WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO fournisseurs (
                    code, raison_sociale, contact, telephone, email,
                    adresse, ville, pays, actif
                ) VALUES (
                    :code, :raison_sociale, :contact, :telephone, :email,
                    :adresse, :ville, :pays, :actif
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
            ':actif'          => $donnees['actif'] ?? true,
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE fournisseurs SET
                    code = :code, raison_sociale = :raison_sociale,
                    contact = :contact, telephone = :telephone, email = :email,
                    adresse = :adresse, ville = :ville, pays = :pays, actif = :actif
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
            ':actif'          => $donnees['actif'] ?? true,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM fournisseurs WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}