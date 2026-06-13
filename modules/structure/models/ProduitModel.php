<?php
// modules/structure/models/ProduitModel.php

require_once ROOT . '/core/Model.php';

class ProduitModel extends Model
{
    protected string $table = 'produits';

    public function listerAvecFamille(): array {
        $sql = "SELECT p.*, f.libelle AS famille_libelle
                FROM produits p
                LEFT JOIN familles_produits f ON f.id = p.famille_id
                ORDER BY p.designation";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM produits WHERE id = :id";
        $res = $this->bd()->requete($sql, [':id' => $id])->unRecuperer();
        if (!$res) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $res;
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO produits (
                    code, designation, famille_id, produit_pere_id, est_pere,
                    unite, prix_achat_ht, prix_vente_ht, tva,
                    stock_actuel, stock_minimum, date_peremption, actif
                ) VALUES (
                    :code, :designation, :famille_id, :produit_pere_id, :est_pere,
                    :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                    :stock_actuel, :stock_minimum, :date_peremption, :actif
                ) RETURNING id";
        $res = $this->bd()->requete($sql, [
            ':code'             => $donnees['code'],
            ':designation'      => $donnees['designation'],
            ':famille_id'       => $donnees['famille_id'],
            ':produit_pere_id'  => $donnees['produit_pere_id'] ?? null,
            ':est_pere'         => $donnees['est_pere'] ?? false,
            ':unite'            => $donnees['unite'] ?? null,
            ':prix_achat_ht'    => $donnees['prix_achat_ht'] ?? 0,
            ':prix_vente_ht'    => $donnees['prix_vente_ht'] ?? 0,
            ':tva'              => $donnees['tva'] ?? 0,
            ':stock_actuel'     => $donnees['stock_actuel'] ?? 0,
            ':stock_minimum'    => $donnees['stock_minimum'] ?? 0,
            ':date_peremption'  => $donnees['date_peremption'] ?? null,
            ':actif'            => $donnees['actif'] ?? true,
        ])->unRecuperer();
        return (int)$res['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE produits SET
                    code = :code, designation = :designation, famille_id = :famille_id,
                    unite = :unite, prix_achat_ht = :prix_achat_ht, prix_vente_ht = :prix_vente_ht,
                    tva = :tva, stock_actuel = :stock_actuel, stock_minimum = :stock_minimum,
                    date_peremption = :date_peremption, actif = :actif
                WHERE id = :id";
        $this->bd()->requete($sql, [
            ':id'               => $id,
            ':code'             => $donnees['code'],
            ':designation'      => $donnees['designation'],
            ':famille_id'       => $donnees['famille_id'],
            ':unite'            => $donnees['unite'] ?? null,
            ':prix_achat_ht'    => $donnees['prix_achat_ht'] ?? 0,
            ':prix_vente_ht'    => $donnees['prix_vente_ht'] ?? 0,
            ':tva'              => $donnees['tva'] ?? 0,
            ':stock_actuel'     => $donnees['stock_actuel'] ?? 0,
            ':stock_minimum'    => $donnees['stock_minimum'] ?? 0,
            ':date_peremption'  => $donnees['date_peremption'] ?? null,
            ':actif'            => $donnees['actif'] ?? true,
        ]);
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM produits WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $id]);
    }
}