<?php
// modules/structure/models/BanqueModel.php

require_once ROOT . '/core/Model.php';

class BanqueModel extends Model
{
    protected string $table = 'banques';

    public function listerTout(): array {
        $sql = "SELECT * FROM banques ORDER BY nom";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM banques WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO banques (code, nom, adresse, telephone, numero_compte)
                VALUES (:code, :nom, :adresse, :telephone, :numero_compte) RETURNING id";
        $res = $this->bd()->requete($sql, [
            ':code'          => $donnees['code'],
            ':nom'           => $donnees['nom'],
            ':adresse'       => $donnees['adresse'] ?? null,
            ':telephone'     => $donnees['telephone'] ?? null,
            ':numero_compte' => $donnees['numero_compte'] ?? null,
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE banques SET
                    code = :code, nom = :nom, adresse = :adresse,
                    telephone = :telephone, numero_compte = :numero_compte
                WHERE id = :id";
        $this->bd()->requete($sql, [
            ':id'            => $id,
            ':code'          => $donnees['code'],
            ':nom'           => $donnees['nom'],
            ':adresse'       => $donnees['adresse'] ?? null,
            ':telephone'     => $donnees['telephone'] ?? null,
            ':numero_compte' => $donnees['numero_compte'] ?? null,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM banques WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}