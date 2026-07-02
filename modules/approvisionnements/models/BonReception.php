<?php
// modules/approvisionnements/models/BonReception.php

namespace approvisionnements\models;

require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Database.php';

use \Database;
use \Model;

class BonReception extends Model {
    
    protected string $table = 'bons_reception';
    
    /**
     * Récupère toutes les réceptions avec le nom du fournisseur
     */
    public function listerTout(): array {
        $sql = "SELECT br.*, f.raison_sociale AS fournisseur_nom
                FROM " . $this->table . " br
                JOIN fournisseurs f ON f.id = br.fournisseur_id
                ORDER BY br.date_reception DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
    
    /**
     * Récupère une réception avec ses lignes
     */
    public function trouverComplet(int $id): array|false {
        $sql = "SELECT br.*, f.raison_sociale AS fournisseur_nom
                FROM " . $this->table . " br
                JOIN fournisseurs f ON f.id = br.fournisseur_id
                WHERE br.id = :id";
        
        $resultat = $this->requete($sql, [':id' => $id]);
        $reception = $resultat->unRecuperer();
        
        if (!$reception) {
            return false;
        }
        
        $sqlLignes = "SELECT lr.*, p.designation, p.code, p.unite
                      FROM lignes_reception lr
                      JOIN produits p ON p.id = lr.produit_id
                      WHERE lr.bon_reception_id = :id";
        
        $resultatLignes = $this->requete($sqlLignes, [':id' => $id]);
        $reception['lignes'] = $resultatLignes->toutRecuperer();
        
        return $reception;
    }
    
    /**
     * Récupère les commandes validées (pour la réception)
     */
    public function getCommandesValidees(): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.statut IN ('VALIDÉ', 'REÇU_PARTIEL')
                ORDER BY bcf.date_commande DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
    
    /**
     * Récupère les détails d'une commande pour la réception
     */
    public function getCommandeDetails(int $commandeId): array|false {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.id = :id";
        
        $resultat = $this->requete($sql, [':id' => $commandeId]);
        $commande = $resultat->unRecuperer();
        
        if (!$commande) {
            return false;
        }
        
        $sqlLignes = "SELECT lcf.*, p.designation, p.code, p.unite
                      FROM lignes_commande_fournisseur lcf
                      JOIN produits p ON p.id = lcf.produit_id
                      WHERE lcf.bon_commande_id = :id";
        
        $resultatLignes = $this->requete($sqlLignes, [':id' => $commandeId]);
        $commande['lignes'] = $resultatLignes->toutRecuperer();
        
        return $commande;
    }
    
    /**
     * Crée une réception avec ses lignes et met à jour le stock
     */
  public function creerAvecLignes(array $donneesReception, array $lignes): int {
    $db = \Database::connexion();
    
    try {
        $db->beginTransaction();
        
        // Insertion de la réception
        $sql = "INSERT INTO " . $this->table . " 
                (numero, bon_commande_id, fournisseur_id, date_reception, statut, notes)
                VALUES (:numero, :bon_commande_id, :fournisseur_id, :date_reception, 'REÇU', :notes)
                RETURNING id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($donneesReception);
        $receptionId = $stmt->fetch()['id'];
        
        // Récupérer les quantités commandées
        $commande = $this->getCommandeDetails($donneesReception[':bon_commande_id']);
        $quantitesCommandees = [];
        foreach ($commande['lignes'] as $ligne) {
            $quantitesCommandees[$ligne['produit_id']] = $ligne['quantite_commandee'];
        }
        
        // Insertion des lignes de réception et mise à jour du stock
        $sqlLigne = "INSERT INTO lignes_reception 
                     (bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva, date_peremption)
                     VALUES (:bon_reception_id, :produit_id, :quantite_recue, :prix_unitaire_ht, :tva, :date_peremption)";
        
        $sqlUpdateStock = "UPDATE produits SET stock_actuel = stock_actuel + :quantite WHERE id = :produit_id";
        
        $stmtLigne = $db->prepare($sqlLigne);
        $stmtStock = $db->prepare($sqlUpdateStock);
        $receptionComplete = true;
        
        foreach ($lignes as $ligne) {
            $stmtLigne->execute([
                ':bon_reception_id' => $receptionId,
                ':produit_id' => $ligne['produit_id'],
                ':quantite_recue' => $ligne['quantite_recue'],
                ':prix_unitaire_ht' => $ligne['prix_unitaire_ht'],
                ':tva' => $ligne['tva'] ?? 19.25,
                ':date_peremption' => $ligne['date_peremption'] ?? null
            ]);
            
            // Mise à jour du stock
            $stmtStock->execute([
                ':quantite' => $ligne['quantite_recue'],
                ':produit_id' => $ligne['produit_id']
            ]);
            
            // Vérifier si la quantité reçue est inférieure à la quantité commandée
            if (isset($quantitesCommandees[$ligne['produit_id']]) && 
                $ligne['quantite_recue'] < $quantitesCommandees[$ligne['produit_id']]) {
                $receptionComplete = false;
            }
        }
        
        // Mise à jour du statut de la commande
        $statut = $receptionComplete ? 'REÇU_TOTAL' : 'REÇU_PARTIEL';
        $sqlUpdateCommande = "UPDATE bons_commande_fournisseur SET statut = :statut WHERE id = :commande_id";
        $stmtUpdate = $db->prepare($sqlUpdateCommande);
        $stmtUpdate->execute([
            ':statut' => $statut,
            ':commande_id' => $donneesReception[':bon_commande_id']
        ]);
        
        $db->commit();
        return (int) $receptionId;
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
/**
 * Met à jour une réception avec ses lignes
 */
public function mettreAJour(int $id, array $donneesReception, array $lignes): void {
    $db = \Database::connexion();
    
    try {
        $db->beginTransaction();
        
        // 1. Récupérer l'ancienne réception pour connaître les anciennes quantités
        $ancienneReception = $this->trouverComplet($id);
        if (!$ancienneReception) {
            throw new \Exception('Réception non trouvée.');
        }
        
        // 2. Annuler les anciens mouvements de stock
        foreach ($ancienneReception['lignes'] as $ligne) {
            $sql = "UPDATE produits SET stock_actuel = stock_actuel - :quantite WHERE id = :produit_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':quantite' => $ligne['quantite_recue'],
                ':produit_id' => $ligne['produit_id']
            ]);
        }
        
        // 3. Supprimer les anciennes lignes de réception
        $sql = "DELETE FROM lignes_reception WHERE bon_reception_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        // 4. Mettre à jour l'en-tête de la réception
        $sql = "UPDATE " . $this->table . " 
                SET bon_commande_id = :bon_commande_id,
                    date_reception = :date_reception,
                    notes = :notes
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($donneesReception, [':id' => $id]));
        
        // 5. Insérer les nouvelles lignes et mettre à jour le stock
        $sqlLigne = "INSERT INTO lignes_reception 
                     (bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva, date_peremption)
                     VALUES (:bon_reception_id, :produit_id, :quantite_recue, :prix_unitaire_ht, :tva, :date_peremption)";
        $stmtLigne = $db->prepare($sqlLigne);
        $stmtStock = $db->prepare("UPDATE produits SET stock_actuel = stock_actuel + :quantite WHERE id = :produit_id");
        
        foreach ($lignes as $ligne) {
            $stmtLigne->execute([
                ':bon_reception_id' => $id,
                ':produit_id' => $ligne['produit_id'],
                ':quantite_recue' => $ligne['quantite_recue'],
                ':prix_unitaire_ht' => $ligne['prix_unitaire_ht'],
                ':tva' => $ligne['tva'] ?? 19.25,
                ':date_peremption' => $ligne['date_peremption'] ?? null
            ]);
            
            $stmtStock->execute([
                ':quantite' => $ligne['quantite_recue'],
                ':produit_id' => $ligne['produit_id']
            ]);
        }
        
        // 6. Mettre à jour le statut de la commande
        $commande = $this->getCommandeDetails($donneesReception[':bon_commande_id']);
        $quantitesCommandees = [];
        foreach ($commande['lignes'] as $ligne) {
            $quantitesCommandees[$ligne['produit_id']] = $ligne['quantite_commandee'];
        }
        
        $receptionComplete = true;
        foreach ($lignes as $ligne) {
            if (isset($quantitesCommandees[$ligne['produit_id']]) && 
                $ligne['quantite_recue'] < $quantitesCommandees[$ligne['produit_id']]) {
                $receptionComplete = false;
            }
        }
        
        $statut = $receptionComplete ? 'REÇU_TOTAL' : 'REÇU_PARTIEL';
        $sqlUpdateCommande = "UPDATE bons_commande_fournisseur SET statut = :statut WHERE id = :commande_id";
        $stmtUpdate = $db->prepare($sqlUpdateCommande);
        $stmtUpdate->execute([
            ':statut' => $statut,
            ':commande_id' => $donneesReception[':bon_commande_id']
        ]);
        
        $db->commit();
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
/**
 * Supprime une réception (annule la mise à jour du stock)
 */
public function supprimer(int $id): void {
    $db = \Database::connexion();
    
    try {
        $db->beginTransaction();
        
        // 1. Récupérer la réception
        $reception = $this->trouverComplet($id);
        if (!$reception) {
            throw new \Exception('Réception non trouvée.');
        }
        
        // 2. Annuler les mouvements de stock
        foreach ($reception['lignes'] as $ligne) {
            $sql = "UPDATE produits SET stock_actuel = stock_actuel - :quantite WHERE id = :produit_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':quantite' => $ligne['quantite_recue'],
                ':produit_id' => $ligne['produit_id']
            ]);
        }
        
        // 3. Supprimer les lignes de réception
        $sql = "DELETE FROM lignes_reception WHERE bon_reception_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        // 4. Supprimer l'en-tête de la réception
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        // 5. Restaurer le statut de la commande (retour à VALIDÉ)
        $sqlUpdateCommande = "UPDATE bons_commande_fournisseur SET statut = 'VALIDÉ' WHERE id = :commande_id";
        $stmtUpdate = $db->prepare($sqlUpdateCommande);
        $stmtUpdate->execute([':commande_id' => $reception['bon_commande_id']]);
        
        $db->commit();

    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

public function restaurer(array $principal, array $lignes): void {
    $db = \Database::connexion();

    $db->prepare(
        "INSERT INTO bons_reception
            (id, numero, bon_commande_id, fournisseur_id, date_reception, statut, notes)
         VALUES
            (:id, :numero, :bon_commande_id, :fournisseur_id, :date_reception, :statut, :notes)"
    )->execute([
        ':id'             => (int)   $principal['id'],
        ':numero'         =>          $principal['numero'],
        ':bon_commande_id'=> $principal['bon_commande_id'] ? (int) $principal['bon_commande_id'] : null,
        ':fournisseur_id' => (int)   $principal['fournisseur_id'],
        ':date_reception' =>          $principal['date_reception'],
        ':statut'         =>          $principal['statut'] ?? 'REÇU',
        ':notes'          =>          $principal['notes'] ?: null,
    ]);

    $stmtLigne = $db->prepare(
        "INSERT INTO lignes_reception
            (id, bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva, date_peremption)
         VALUES
            (:id, :bon_reception_id, :produit_id, :quantite_recue, :prix_unitaire_ht, :tva, :date_peremption)"
    );
    $stmtStock = $db->prepare("UPDATE produits SET stock_actuel = stock_actuel + :qte WHERE id = :produit_id");

    foreach ($lignes as $l) {
        $stmtLigne->execute([
            ':id'              => (int)   $l['id'],
            ':bon_reception_id'=> (int)   $principal['id'],
            ':produit_id'      => (int)   $l['produit_id'],
            ':quantite_recue'  => (float) $l['quantite_recue'],
            ':prix_unitaire_ht'=> (float) $l['prix_unitaire_ht'],
            ':tva'             => (float) ($l['tva'] ?? 19.25),
            ':date_peremption' =>          $l['date_peremption'] ?: null,
        ]);
        $stmtStock->execute([':qte' => (float) $l['quantite_recue'], ':produit_id' => (int) $l['produit_id']]);
    }

    $db->exec("SELECT setval(pg_get_serial_sequence('bons_reception','id'), MAX(id)) FROM bons_reception");
}
}