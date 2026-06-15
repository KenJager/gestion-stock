<?php
// modules/approvisionnements/models/BonCommande.php

namespace approvisionnements\models;

class BonCommandeModel extends \Model {

    protected string $table = 'bons_commande_fournisseur';

    /**
     * Liste tous les bons de commande avec le nom du fournisseur.
     */
    public function listerTout(): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                ORDER BY bcf.date_commande DESC";
        return \Database::query($sql)->fetchAll();
    }

    /**
     * Trouve un bon de commande par ID avec le nom du fournisseur.
     */
    public function trouverParId(int $id): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.id = :id";
        $resultat = \Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            throw new \RuntimeException("Bon de commande ID {$id} introuvable.");
        }
        return $resultat;
    }

    /**
     * Crée un nouveau bon de commande.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO bons_commande_fournisseur
                (numero, fournisseur_id, date_commande, date_livraison_prevue, statut, notes, utilisateur_id)
                VALUES
                (:numero, :fournisseur_id, :date_commande, :date_livraison_prevue, 'BROUILLON', :notes, :utilisateur_id)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':numero'                => $donnees['numero'],
            ':fournisseur_id'        => $donnees['fournisseur_id'],
            ':date_commande'         => $donnees['date_commande'],
            ':date_livraison_prevue' => $donnees['date_livraison_prevue'] ?? null,
            ':notes'                 => $donnees['notes'] ?? null,
            ':utilisateur_id'        => $donnees['utilisateur_id'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }

    /**
     * Met à jour les totaux d'un bon de commande.
     */
    public function mettreAJourTotaux(int $id, float $deltaHt, float $deltaTva, float $deltaTtc): void {
        $sql = "UPDATE bons_commande_fournisseur
                SET montant_ht  = montant_ht + :ht,
                    montant_tva = montant_tva + :tva,
                    montant_ttc = montant_ttc + :ttc
                WHERE id = :id";
        \Database::query($sql, [
            ':ht'   => $deltaHt,
            ':tva'  => $deltaTva,
            ':ttc'  => $deltaTtc,
            ':id'   => $id,
        ]);
    }

    /**
     * Modifie le statut d'un bon de commande.
     */
    public function modifierStatut(int $id, string $statut): void {
        $sql = "UPDATE bons_commande_fournisseur SET statut = :statut WHERE id = :id";
        \Database::query($sql, [':statut' => $statut, ':id' => $id]);
    }

    /**
     * Récupère les statistiques de réception des lignes d'un bon.
     */
    public function getStatutsReception(int $bonCommandeId): array {
        $sql = "SELECT
                    COUNT(*) AS total_lignes,
                    SUM(CASE WHEN quantite_recue >= quantite_commandee THEN 1 ELSE 0 END) AS recues_total,
                    SUM(CASE WHEN quantite_recue > 0 AND quantite_recue < quantite_commandee THEN 1 ELSE 0 END) AS recues_partiel,
                    SUM(CASE WHEN quantite_recue = 0 THEN 1 ELSE 0 END) AS non_recues
                FROM lignes_commande_fournisseur
                WHERE bon_commande_id = :id";
        return \Database::query($sql, [':id' => $bonCommandeId])->fetch();
    }

    /**
     * Vérifie s'il existe des bons de réception liés.
     */
    public function aDesReceptions(int $id): bool {
        $sql = "SELECT COUNT(*) FROM bons_reception WHERE bon_commande_id = :id";
        return ((int) \Database::query($sql, [':id' => $id])->fetchColumn()) > 0;
    }
}