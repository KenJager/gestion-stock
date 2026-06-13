<?php
// modules/structure/models/ProduitModel.php

require_once ROOT . '/core/Model.php';

class ProduitModel extends Model
{
    protected string $table = 'produits';

    public function listerAvecFamille(): array {
        $sql = "SELECT p.*, f.libelle AS famille_libelle
                FROM {$this->table} p
                LEFT JOIN familles_produits f ON f.id = p.famille_id
                ORDER BY p.designation";
        return $this->pdo()->query($sql)->fetchAll();
    }

    public function trouverParId(string|int $id): array {
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
        // Supprimer produit_pere_id de la requête
        $sql = "INSERT INTO {$this->table} (
                    code, designation, famille_id, est_pere,
                    unite, prix_achat_ht, prix_vente_ht, tva,
                    stock_actuel, stock_minimum, date_peremption, actif
                ) VALUES (
                    :code, :designation, :famille_id, :est_pere,
                    :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                    :stock_actuel, :stock_minimum, :date_peremption, :actif
                ) RETURNING id";
        $stmt = $this->pdo()->prepare($sql);

        $estPere = $donnees['est_pere'] ?? false;
        $estPereBool = filter_var($estPere, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $estPereVal = $estPereBool === null ? 0 : ($estPereBool ? 1 : 0);

        $actif = $donnees['actif'] ?? true;
        $actifBool = filter_var($actif, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $actifVal = $actifBool === null ? 1 : ($actifBool ? 1 : 0);

        $datePeremption = $donnees['date_peremption'] ?? null;
        if ($datePeremption === '' || $datePeremption === null) {
            $datePeremption = null;
        }

        $stmt->execute([
            ':code'             => $donnees['code'],
            ':designation'      => $donnees['designation'],
            ':famille_id'       => $donnees['famille_id'],
            ':est_pere'         => $estPereVal,
            ':unite'            => $donnees['unite'] ?? null,
            ':prix_achat_ht'    => $donnees['prix_achat_ht'] ?? 0,
            ':prix_vente_ht'    => $donnees['prix_vente_ht'] ?? 0,
            ':tva'              => $donnees['tva'] ?? 0,
            ':stock_actuel'     => $donnees['stock_actuel'] ?? 0,
            ':stock_minimum'    => $donnees['stock_minimum'] ?? 0,
            ':date_peremption'  => $datePeremption,
            ':actif'            => $actifVal,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE {$this->table} SET
                    code = :code, designation = :designation, famille_id = :famille_id,
                    unite = :unite, prix_achat_ht = :prix_achat_ht, prix_vente_ht = :prix_vente_ht,
                    tva = :tva, stock_actuel = :stock_actuel, stock_minimum = :stock_minimum,
                    date_peremption = :date_peremption, actif = :actif
                WHERE id = :id";
        $stmt = $this->pdo()->prepare($sql);

        $actif = $donnees['actif'] ?? true;
        $actifBool = filter_var($actif, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $actifVal = $actifBool === null ? 1 : ($actifBool ? 1 : 0);

        $stmt->execute([
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
            ':actif'            => $actifVal,
        ]);
    }

    public function existeParCode(string $code, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM produits WHERE code = :code";
        $params = [':code' => $code];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function listerProduitsPeres(): array {
        $sql = "SELECT id, code, designation FROM produits WHERE est_pere = true ORDER BY code";
        $stmt = $this->pdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function listerFilsParProduitPere(int $produitPereId): array {
        // Cherche les fils dans fractions_produit (c'est maintenant la seule source)
        $sql = "SELECT p.*, fp.quantite_fils
                FROM produits p
                JOIN fractions_produit fp ON fp.produit_fils_id = p.id
                WHERE fp.produit_pere_id = :pere_id";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':pere_id' => $produitPereId]);
        return $stmt->fetchAll();
    }
}