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

    // ... Vos méthodes existantes (listerAvecFamille, trouverParId, creer, existeParCode, etc.) ...

    /**
     * Supprime un produit et retourne ses données structurées pour la Corbeille
     */
    public function preparerSuppressionEtRecupererDonnees(int $id): array {
        $pdo = $this->pdo();
        
        // 1. Récupérer le produit principal (père ou fils isolé)
        $produit = $this->trouverParId($id);
        
        $fractions = [];
        $produitsFils = [];

        // 2. Si c'est un produit père, on récupère ses relations et ses fils
        if ((int)$produit['est_pere'] === 1) {
            // Récupérer les lignes de liaisons
            $sqlFractions = "SELECT * FROM fractions_produit WHERE produit_pere_id = :id";
            $stmtF = $pdo->prepare($sqlFractions);
            $stmtF->execute([':id' => $id]);
            $fractions = $stmtF->fetchAll();

            // Récupérer les données complètes de chaque produit fils
            $sqlFils = "SELECT p.* FROM produits p 
                        JOIN fractions_produit fp ON p.id = fp.produit_fils_id 
                        WHERE fp.produit_pere_id = :id";
            $stmtFils = $pdo->prepare($sqlFils);
            $stmtFils->execute([':id' => $id]);
            $produitsFils = $stmtFils->fetchAll();
        } else {
            // Si c'est un produit fils supprimé individuellement :
            // On récupère uniquement ses relations pour les archiver avec lui
            $sqlFractions = "SELECT * FROM fractions_produit WHERE produit_fils_id = :id";
            $stmtF = $pdo->prepare($sqlFractions);
            $stmtF->execute([':id' => $id]);
            $fractions = $stmtF->fetchAll();
        }

        return [
            'principal' => $produit,
            'liees' => [
                'fractions_produit' => $fractions,
                'produits_fils'     => $produitsFils
            ]
        ];
    }

    /**
     * Exécute la suppression physique d'un produit en cascade en base de données
     */
    public function supprimerEnCascade(int $id, bool $estPere): void {
        $pdo = $this->pdo();
        
        if ($estPere) {
            // Supprimer d'abord les lignes de la table produits pour les fils
            $sqlSupprFils = "DELETE FROM produits WHERE id IN (
                                SELECT produit_fils_id FROM fractions_produit WHERE produit_pere_id = :id
                             )";
            $stmt = $pdo->prepare($sqlSupprFils);
            $stmt->execute([':id' => $id]);
        }
        
        // La suppression du produit lui-même (père ou fils isolé)
        // Note: Le ON DELETE CASCADE sur la clé étrangère de fractions_produit videra automatiquement cette table.
        $sqlSupprPrincipal = "DELETE FROM produits WHERE id = :id";
        $stmt = $pdo->prepare($sqlSupprPrincipal);
        $stmt->execute([':id' => $id]);
    }
    
    /**
     * Méthode d'insertion avec ID forcé (Spécifique pour la Restauration sans auto-increment)
     */
    public function restaurerAvecId(array $p): void {
        $sql = "INSERT INTO produits (
                    id, code, designation, famille_id, est_pere,
                    unite, prix_achat_ht, prix_vente_ht, tva,
                    stock_actuel, stock_minimum, date_peremption, actif
                ) VALUES (
                    :id, :code, :designation, :famille_id, :est_pere,
                    :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                    :stock_actuel, :stock_minimum, :date_peremption, :actif
                )";
                
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id'               => (int)$p['id'],
            ':code'             => $p['code'],
            ':designation'      => $p['designation'],
            ':famille_id'       => !empty($p['famille_id']) ? (int)$p['famille_id'] : null,
            ':est_pere'         => ($p['est_pere'] === 't' || $p['est_pere'] === '1') ? 1 : 0,
            ':unite'            => $p['unite'] ?? null,
            ':prix_achat_ht'    => $p['prix_achat_ht'] ?? 0,
            ':prix_vente_ht'    => $p['prix_vente_ht'] ?? 0,
            ':tva'              => $p['tva'] ?? 0,
            ':stock_actuel'     => $p['stock_actuel'] ?? 0,
            ':stock_minimum'    => $p['stock_minimum'] ?? 0,
            ':date_peremption'  => !empty($p['date_peremption']) ? $p['date_peremption'] : null,
            ':actif'            => ($p['actif'] === 't' || $p['actif'] === '1') ? 1 : 0
        ]);
    }
}