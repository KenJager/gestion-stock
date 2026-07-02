<?php
// modules/approvisionnements/models/ReglementFournisseur.php

namespace approvisionnements\models;

require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Database.php';

use \Model;
use \Database;

class ReglementFournisseur extends Model {
    
    protected string $table = 'reglements_fournisseur';
    
    /**
     * Liste tous les règlements avec les infos de la facture et du fournisseur
     */
    public function listerTout(): array {
        $sql = "SELECT r.*, f.numero AS facture_numero,
                       fr.raison_sociale AS fournisseur_nom
                FROM " . $this->table . " r
                JOIN factures_fournisseur f ON f.id = r.facture_id
                JOIN fournisseurs fr ON fr.id = f.fournisseur_id
                ORDER BY r.date_reglement DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
    
    /**
     * Récupère un règlement par son ID
     */
    public function trouverParId(int $id): array {
        $sql = "SELECT r.*, f.numero AS facture_numero,
                       fr.raison_sociale AS fournisseur_nom
                FROM " . $this->table . " r
                JOIN factures_fournisseur f ON f.id = r.facture_id
                JOIN fournisseurs fr ON fr.id = f.fournisseur_id
                WHERE r.id = :id";

        $resultat = $this->requete($sql, [':id' => $id]);
        $row = $resultat->unRecuperer();
        if (!$row) {
            throw new \RuntimeException("Enregistrement introuvable dans {$this->table} : id = {$id}");
        }
        return $row;
    }
    
    /**
     * Crée un règlement
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO " . $this->table . " 
                (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, notes)
                VALUES (:facture_id, :date_reglement, :montant, :mode_paiement, :reference, :banque_id, :notes)
                RETURNING id";
        
        $resultat = $this->requete($sql, $donnees);
        $row = $resultat->unRecuperer();
        return (int) $row['id'];
    }
    
    /**
     * Supprime un règlement
     */
    public function supprimer(int $id): void {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $this->requete($sql, [':id' => $id]);
    }

    public function restaurer(array $principal): void {
        $this->requete(
            "INSERT INTO reglements_fournisseur
                (id, facture_id, date_reglement, montant, mode_paiement, reference, notes, banque_id)
             VALUES
                (:id, :facture_id, :date_reglement, :montant, :mode_paiement, :reference, :notes, :banque_id)",
            [
                ':id'             => (int)   $principal['id'],
                ':facture_id'     => (int)   $principal['facture_id'],
                ':date_reglement' =>          $principal['date_reglement'],
                ':montant'        => (float)  $principal['montant'],
                ':mode_paiement'  =>          $principal['mode_paiement'] ?: null,
                ':reference'      =>          $principal['reference'] ?: null,
                ':notes'          =>          $principal['notes'] ?: null,
                ':banque_id'      =>          $principal['banque_id'] ? (int) $principal['banque_id'] : null,
            ]
        );
        Database::connexion()->exec("SELECT setval(pg_get_serial_sequence('reglements_fournisseur','id'), MAX(id)) FROM reglements_fournisseur");
    }
}