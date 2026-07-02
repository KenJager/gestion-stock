<?php
// modules/approvisionnements/models/BonCommande.php

namespace approvisionnements\traitements\models;

require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Database.php';

use \Model;
use \Database;


class BonCommande extends Model {
    
    protected string $table = 'bons_commande_fournisseur';
    
    /**
     * Récupère toutes les commandes avec le nom du fournisseur
     */
    public function listerTout(): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM " . $this->table . " bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                ORDER BY bcf.date_commande DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
 


/**
 * Crée une commande avec ses lignes (transaction)
 */
public function creerAvecLignes(array $donneesCommande, array $lignes): int {
    $db = Database::connexion();
    
    try {
        $db->beginTransaction();
        
        // Insertion de la commande
        $sql = "INSERT INTO " . $this->table . " 
                (numero, fournisseur_id, date_commande, statut, montant_ht, montant_tva, montant_ttc, notes)
                VALUES (:numero, :fournisseur_id, :date_commande, 'BROUILLON', :montant_ht, :montant_tva, :montant_ttc, :notes)
                RETURNING id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($donneesCommande);
        $commandeId = $stmt->fetch()['id'];
        
        // Insertion des lignes
        $sqlLigne = "INSERT INTO lignes_commande_fournisseur 
                     (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
                     VALUES (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)";
        
        $stmtLigne = $db->prepare($sqlLigne);
        foreach ($lignes as $ligne) {
            $stmtLigne->execute([
                ':bon_commande_id' => $commandeId,
                ':produit_id' => $ligne['produit_id'],
                ':quantite_commandee' => $ligne['quantite'],
                ':prix_unitaire_ht' => $ligne['prix_unitaire'],
                ':tva' => $ligne['tva'] ?? 19.25
            ]);
        }
        
        $db->commit();
        return (int) $commandeId;
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
/**
 * Change le statut d'une commande
 */
public function changerStatut(int $id, string $statut): void {
    $sql = "UPDATE " . $this->table . " SET statut = :statut WHERE id = :id";
    $this->requete($sql, [':id' => $id, ':statut' => $statut]);
}
/**
 * Met à jour une commande
 */
public function mettreAJour(int $id, array $donnees): void {
    $sql = "UPDATE " . $this->table . " 
            SET fournisseur_id = :fournisseur_id,
                date_commande = :date_commande,
                notes = :notes,
                montant_ht = :montant_ht,
                montant_tva = :montant_tva,
                montant_ttc = :montant_ttc
            WHERE id = :id";
    
    $donnees['id'] = $id;
    $this->requete($sql, $donnees);
}

/**
 * Supprime les lignes d'une commande
 */
public function supprimerLignes(int $commandeId): void {
    $sql = "DELETE FROM lignes_commande_fournisseur WHERE bon_commande_id = :id";
    $this->requete($sql, [':id' => $commandeId]);
}

/**
 * Ajoute une ligne à une commande
 */
public function ajouterLigne(int $commandeId, array $ligne): void {
    $sql = "INSERT INTO lignes_commande_fournisseur 
            (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
            VALUES (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)";
    
    $this->requete($sql, [
        ':bon_commande_id' => $commandeId,
        ':produit_id' => $ligne['produit_id'],
        ':quantite_commandee' => $ligne['quantite'],
        ':prix_unitaire_ht' => $ligne['prix_unitaire'],
        ':tva' => $ligne['tva'] ?? 19.25
    ]);
}

/**
 * Supprime une commande (cascade vers les lignes)
 */
public function supprimer(int $id): void {
    $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
    $this->requete($sql, [':id' => $id]);
}

public function restaurer(array $principal, array $lignes): void {
    $this->requete(
        "INSERT INTO bons_commande_fournisseur
            (id, numero, fournisseur_id, date_commande, statut, montant_ht, montant_tva, montant_ttc, notes)
         VALUES
            (:id, :numero, :fournisseur_id, :date_commande, :statut, :montant_ht, :montant_tva, :montant_ttc, :notes)",
        [
            ':id'            => (int)    $principal['id'],
            ':numero'        =>           $principal['numero'],
            ':fournisseur_id'=> (int)    $principal['fournisseur_id'],
            ':date_commande' =>           $principal['date_commande'],
            ':statut'        =>           $principal['statut'],
            ':montant_ht'    => (float)  $principal['montant_ht'],
            ':montant_tva'   => (float)  $principal['montant_tva'],
            ':montant_ttc'   => (float)  $principal['montant_ttc'],
            ':notes'         =>           $principal['notes'] ?: null,
        ]
    );

    foreach ($lignes as $l) {
        $this->requete(
            "INSERT INTO lignes_commande_fournisseur
                (id, bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
             VALUES
                (:id, :bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)",
            [
                ':id'                 => (int)   $l['id'],
                ':bon_commande_id'    => (int)   $principal['id'],
                ':produit_id'         => (int)   $l['produit_id'],
                ':quantite_commandee' => (float) $l['quantite_commandee'],
                ':prix_unitaire_ht'   => (float) $l['prix_unitaire_ht'],
                ':tva'                => (float) $l['tva'],
            ]
        );
    }

    Database::connexion()->exec("SELECT setval(pg_get_serial_sequence('bons_commande_fournisseur','id'), MAX(id)) FROM bons_commande_fournisseur");
}
public function trouverParId(int $id): array {
    $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
            FROM " . $this->table . " bcf
            JOIN fournisseurs f ON f.id = bcf.fournisseur_id
            WHERE bcf.id = :id";

    $resultat = $this->requete($sql, [':id' => $id]);
    $row = $resultat->unRecuperer();
    if (!$row) {
        throw new \RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
    }
    return $row;
}
    public function trouverComplet(int $id): array|false {
        $commande = $this->trouverParId($id);
        if (!$commande) {
            return false;
        }
        
        $sqlLignes = "SELECT lcf.*, p.designation, p.code, p.unite
                      FROM lignes_commande_fournisseur lcf
                      JOIN produits p ON p.id = lcf.produit_id
                      WHERE lcf.bon_commande_id = :id";
        
        $resultatLignes = $this->requete($sqlLignes, [':id' => $id]);
        $commande['lignes'] = $resultatLignes->toutRecuperer();
        
        return $commande;
    }

}