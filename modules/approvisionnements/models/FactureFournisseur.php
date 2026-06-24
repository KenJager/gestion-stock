<?php
// modules/approvisionnements/models/FactureFournisseur.php

namespace approvisionnements\models;

require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Database.php';

use \Model;
use \Database;

class FactureFournisseur extends Model {
    
    protected string $table = 'factures_fournisseur';
    
    /**
     * Liste toutes les factures avec le nom du fournisseur
     */
    public function listerTout(): array {
        $sql = "SELECT f.*, fr.raison_sociale AS fournisseur_nom,
                       r.numero AS reception_numero
                FROM " . $this->table . " f
                JOIN fournisseurs fr ON fr.id = f.fournisseur_id
                LEFT JOIN bons_reception r ON r.id = f.bon_reception_id
                ORDER BY f.date_facture DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
    
    /**
     * Récupère une facture par son ID avec ses informations jointes
     * Retourne false si non trouvée
     */
    public function trouverAvecJointure(int $id): array|false {
        if ($id <= 0) {
            return false;
        }
        
        $sql = "SELECT f.*, fr.raison_sociale AS fournisseur_nom,
                       r.numero AS reception_numero
                FROM " . $this->table . " f
                JOIN fournisseurs fr ON fr.id = f.fournisseur_id
                LEFT JOIN bons_reception r ON r.id = f.bon_reception_id
                WHERE f.id = :id";

        $resultat = $this->requete($sql, [':id' => $id]);
        return $resultat->unRecuperer();
    }
    
    /**
     * Récupère une facture avec ses règlements
     * Retourne false si non trouvée
     */
    public function trouverComplet(int $id): array|false {
        if ($id <= 0) {
            return false;
        }
        
        $facture = $this->trouverAvecJointure($id);
        if (!$facture) {
            return false;
        }
        
        $sqlReglements = "SELECT r.*, b.nom AS banque_nom
                          FROM reglements_fournisseur r
                          LEFT JOIN banques b ON b.id = r.banque_id
                          WHERE r.facture_id = :id
                          ORDER BY r.date_reglement DESC";
        
        $resultatReglements = $this->requete($sqlReglements, [':id' => $id]);
        $facture['reglements'] = $resultatReglements->toutRecuperer();
        
        return $facture;
    }
    
    /**
     * Récupère les réceptions qui n'ont pas encore de facture
     */
    public function getReceptionsSansFacture(): array {
        $sql = "SELECT r.*, f.raison_sociale AS fournisseur_nom
                FROM bons_reception r
                JOIN fournisseurs f ON f.id = r.fournisseur_id
                WHERE r.id NOT IN (
                    SELECT bon_reception_id FROM factures_fournisseur WHERE bon_reception_id IS NOT NULL
                )
                ORDER BY r.date_reception DESC";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
    
    /**
     * Crée une facture à partir d'une réception
     */
    public function creerDepuisReception(array $donneesFacture, array $lignes = []): int {
        $db = \Database::connexion();
        
        try {
            $db->beginTransaction();
            
            // Insertion de la facture
            $sql = "INSERT INTO " . $this->table . " 
                    (numero, bon_reception_id, fournisseur_id, date_facture, date_echeance,
                     montant_ht, montant_tva, montant_ttc, notes, statut)
                    VALUES (:numero, :bon_reception_id, :fournisseur_id, :date_facture, :date_echeance,
                            :montant_ht, :montant_tva, :montant_ttc, :notes, 'NON_REGLÉE')
                    RETURNING id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($donneesFacture);
            $factureId = $stmt->fetch()['id'];
            
            $db->commit();
            return (int) $factureId;
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Met à jour le montant réglé et le statut d'une facture
     */
    public function mettreAJourMontantRegle(int $factureId): void {
        if ($factureId <= 0) {
            return;
        }
        
        $sql = "SELECT COALESCE(SUM(montant), 0) AS total_regle
                FROM reglements_fournisseur
                WHERE facture_id = :facture_id";
        
        $resultat = $this->requete($sql, [':facture_id' => $factureId]);
        $row = $resultat->unRecuperer();
        $totalRegle = round(floatval($row['total_regle'] ?? 0), 2);
        
        $facture = $this->trouverAvecJointure($factureId);
        if (!$facture) {
            return;
        }
        
        $montantTTC = round(floatval($facture['montant_ttc']), 2);
        
        // Déterminer le statut
        if ($totalRegle >= $montantTTC) {
            $statut = 'REGLÉE';
        } elseif ($totalRegle > 0) {
            $statut = 'PARTIELLEMENT_REGLÉE';
        } else {
            $statut = 'NON_REGLÉE';
        }
        
        $sqlUpdate = "UPDATE " . $this->table . " 
                      SET montant_regle = :montant_regle,
                          statut = :statut
                      WHERE id = :id";
        
        $this->requete($sqlUpdate, [
            ':montant_regle' => $totalRegle,
            ':statut' => $statut,
            ':id' => $factureId
        ]);
    }
    
    /**
     * Supprime une facture
     */
    public function supprimer(int $id): void {
        if ($id <= 0) {
            return;
        }
        
        parent::supprimer($id);
    }
}