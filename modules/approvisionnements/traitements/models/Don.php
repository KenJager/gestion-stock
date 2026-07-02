<?php
// modules/approvisionnements/models/Don.php

namespace approvisionnements\traitements\models;

require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Database.php';

use \Model;
use \Database;

class Don extends Model {
    
    protected string $table = 'entrees_dons';
    
    /**
     * Liste tous les dons avec le total des valeurs estimées
     */
  /**
 * Liste tous les dons avec le total des valeurs estimées
 */
public function listerTout(): array {
    $sql = "SELECT d.*, 
                   COALESCE(SUM(l.quantite * l.valeur_estimee), 0) AS total_valeur_estimee
            FROM " . $this->table . " d
            LEFT JOIN lignes_entree_don l ON l.entree_don_id = d.id
            GROUP BY d.id
            ORDER BY d.date_entree DESC";
    
    $resultat = $this->requete($sql);
    return $resultat->toutRecuperer();
}
    /**
     * Récupère un don complet avec ses lignes
     */
    public function trouverComplet(int $id): array|false {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $resultat = $this->requete($sql, [':id' => $id]);
        $don = $resultat->unRecuperer();
        
        if (!$don) {
            return false;
        }
        
        $sqlLignes = "SELECT l.*, p.designation, p.code, p.unite
                      FROM lignes_entree_don l
                      JOIN produits p ON p.id = l.produit_id
                      WHERE l.entree_don_id = :id";
        
        $resultatLignes = $this->requete($sqlLignes, [':id' => $id]);
        $don['lignes'] = $resultatLignes->toutRecuperer();
        
        // Calculer le total estimé
        $total = 0;
        foreach ($don['lignes'] as $ligne) {
            $total += $ligne['quantite'] * $ligne['valeur_estimee'];
        }
        $don['total_valeur_estimee'] = $total;
        
        return $don;
    }
    
    /**
     * Crée un don avec ses lignes et met à jour le stock
     */
    public function creerAvecLignes(array $donneesDon, array $lignes): int {
        $db = \Database::connexion();
        
        try {
            $db->beginTransaction();
            
            // Insertion du don
            $sql = "INSERT INTO " . $this->table . " 
                    (numero, donateur, date_entree, notes)
                    VALUES (:numero, :donateur, :date_entree, :notes)
                    RETURNING id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($donneesDon);
            $donId = $stmt->fetch()['id'];
            
            // Insertion des lignes et mise à jour du stock
            $sqlLigne = "INSERT INTO lignes_entree_don 
                         (entree_don_id, produit_id, quantite, valeur_estimee, date_peremption)
                         VALUES (:entree_don_id, :produit_id, :quantite, :valeur_estimee, :date_peremption)";
            
            $sqlUpdateStock = "UPDATE produits SET stock_actuel = stock_actuel + :quantite WHERE id = :produit_id";
            
            $stmtLigne = $db->prepare($sqlLigne);
            $stmtStock = $db->prepare($sqlUpdateStock);
            
            foreach ($lignes as $ligne) {
                $stmtLigne->execute([
                    ':entree_don_id' => $donId,
                    ':produit_id' => $ligne['produit_id'],
                    ':quantite' => $ligne['quantite'],
                    ':valeur_estimee' => $ligne['valeur_estimee'] ?? 0,
                    ':date_peremption' => $ligne['date_peremption'] ?? null
                ]);
                
                // Mise à jour du stock
                $stmtStock->execute([
                    ':quantite' => $ligne['quantite'],
                    ':produit_id' => $ligne['produit_id']
                ]);
            }
            
            $db->commit();
            return (int) $donId;
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Met à jour un don avec ses lignes (annule l'ancien stock, applique le nouveau)
     */
    public function mettreAJour(int $id, array $donneesDon, array $lignes): void {
        $db = \Database::connexion();
        
        try {
            $db->beginTransaction();
            
            // 1. Récupérer l'ancien don
            $ancienDon = $this->trouverComplet($id);
            if (!$ancienDon) {
                throw new \Exception('Don non trouvé.');
            }
            
            // 2. Annuler les anciens mouvements de stock
            foreach ($ancienDon['lignes'] as $ligne) {
                $sql = "UPDATE produits SET stock_actuel = stock_actuel - :quantite WHERE id = :produit_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':quantite' => $ligne['quantite'],
                    ':produit_id' => $ligne['produit_id']
                ]);
            }
            
            // 3. Supprimer les anciennes lignes
            $sql = "DELETE FROM lignes_entree_don WHERE entree_don_id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // 4. Mettre à jour l'en-tête
            $sql = "UPDATE " . $this->table . " 
                    SET donateur = :donateur,
                        date_entree = :date_entree,
                        notes = :notes
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge($donneesDon, [':id' => $id]));
            
            // 5. Insérer les nouvelles lignes et mettre à jour le stock
            $sqlLigne = "INSERT INTO lignes_entree_don 
                         (entree_don_id, produit_id, quantite, valeur_estimee, date_peremption)
                         VALUES (:entree_don_id, :produit_id, :quantite, :valeur_estimee, :date_peremption)";
            $stmtLigne = $db->prepare($sqlLigne);
            $stmtStock = $db->prepare("UPDATE produits SET stock_actuel = stock_actuel + :quantite WHERE id = :produit_id");
            
            foreach ($lignes as $ligne) {
                $stmtLigne->execute([
                    ':entree_don_id' => $id,
                    ':produit_id' => $ligne['produit_id'],
                    ':quantite' => $ligne['quantite'],
                    ':valeur_estimee' => $ligne['valeur_estimee'] ?? 0,
                    ':date_peremption' => $ligne['date_peremption'] ?? null
                ]);
                
                $stmtStock->execute([
                    ':quantite' => $ligne['quantite'],
                    ':produit_id' => $ligne['produit_id']
                ]);
            }
            
            $db->commit();
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Supprime un don (annule le stock)
     */
    public function supprimer(int $id): void {
        $db = \Database::connexion();
        
        try {
            $db->beginTransaction();
            
            // Récupérer le don pour annuler le stock
            $don = $this->trouverComplet($id);
            if (!$don) {
                throw new \Exception('Don non trouvé.');
            }
            
            // Annuler les mouvements de stock
            foreach ($don['lignes'] as $ligne) {
                $sql = "UPDATE produits SET stock_actuel = stock_actuel - :quantite WHERE id = :produit_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':quantite' => $ligne['quantite'],
                    ':produit_id' => $ligne['produit_id']
                ]);
            }
            
            // Supprimer les lignes
            $sql = "DELETE FROM lignes_entree_don WHERE entree_don_id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // Supprimer l'en-tête
            $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $db->commit();

        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function restaurer(array $principal, array $lignes): void {
        $db = \Database::connexion();

        $db->prepare(
            "INSERT INTO entrees_dons (id, numero, donateur, date_entree, notes)
             VALUES (:id, :numero, :donateur, :date_entree, :notes)"
        )->execute([
            ':id'          => (int) $principal['id'],
            ':numero'      =>        $principal['numero'],
            ':donateur'    =>        $principal['donateur'] ?: null,
            ':date_entree' =>        $principal['date_entree'],
            ':notes'       =>        $principal['notes'] ?: null,
        ]);

        $stmtLigne = $db->prepare(
            "INSERT INTO lignes_entree_don
                (id, entree_don_id, produit_id, quantite, valeur_estimee, date_peremption)
             VALUES (:id, :entree_don_id, :produit_id, :quantite, :valeur_estimee, :date_peremption)"
        );
        $stmtStock = $db->prepare("UPDATE produits SET stock_actuel = stock_actuel + :qte WHERE id = :produit_id");

        foreach ($lignes as $l) {
            $stmtLigne->execute([
                ':id'           => (int)   $l['id'],
                ':entree_don_id'=> (int)   $principal['id'],
                ':produit_id'   => (int)   $l['produit_id'],
                ':quantite'     => (float) $l['quantite'],
                ':valeur_estimee'=> (float) ($l['valeur_estimee'] ?? 0),
                ':date_peremption'=> $l['date_peremption'] ?: null,
            ]);
            $stmtStock->execute([':qte' => (float) $l['quantite'], ':produit_id' => (int) $l['produit_id']]);
        }

        $db->exec("SELECT setval(pg_get_serial_sequence('entrees_dons','id'), MAX(id)) FROM entrees_dons");
    }
}