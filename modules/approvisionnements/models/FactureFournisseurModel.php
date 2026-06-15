<?php
// modules/approvisionnements/models/FactureFournisseur.php

namespace approvisionnements\models;

class FactureFournisseurModel extends \Model {

    protected string $table = 'factures_fournisseur';

    /**
     * Liste toutes les factures fournisseur.
     */
    public function listerTout(): array {
        $sql = "SELECT ff.*, f.raison_sociale AS fournisseur_nom
                FROM factures_fournisseur ff
                JOIN fournisseurs f ON f.id = ff.fournisseur_id
                ORDER BY ff.date_facture DESC";
        return \Database::query($sql)->fetchAll();
    }

    /**
     * Liste les factures non réglées.
     */
    public function listerNonReglees(): array {
        $sql = "SELECT ff.id, ff.numero, f.raison_sociale AS fournisseur_nom,
                       ff.date_facture, ff.montant_ttc, ff.solde, ff.date_echeance
                FROM factures_fournisseur ff
                JOIN fournisseurs f ON f.id = ff.fournisseur_id
                WHERE ff.statut != 'REGLÉE'
                ORDER BY ff.date_echeance ASC";
        return \Database::query($sql)->fetchAll();
    }

    /**
     * Crée une facture fournisseur.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO factures_fournisseur
                (numero, bon_reception_id, fournisseur_id, date_facture, date_echeance,
                 montant_ht, montant_tva, montant_ttc, notes, utilisateur_id)
                VALUES
                (:numero, :bon_reception_id, :fournisseur_id, :date_facture, :date_echeance,
                 :montant_ht, :montant_tva, :montant_ttc, :notes, :utilisateur_id)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':numero'           => $donnees['numero'],
            ':bon_reception_id' => $donnees['bon_reception_id'],
            ':fournisseur_id'   => $donnees['fournisseur_id'],
            ':date_facture'     => $donnees['date_facture'],
            ':date_echeance'    => $donnees['date_echeance'] ?? null,
            ':montant_ht'       => $donnees['montant_ht'],
            ':montant_tva'      => $donnees['montant_tva'],
            ':montant_ttc'      => $donnees['montant_ttc'],
            ':notes'            => $donnees['notes'] ?? null,
            ':utilisateur_id'   => $donnees['utilisateur_id'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }

    /**
     * Met à jour le montant réglé et le statut.
     */
    public function mettreAJourReglement(int $id, float $montantRegle, string $statut): void {
        $sql = "UPDATE factures_fournisseur
                SET montant_regle = :montant_regle, statut = :statut
                WHERE id = :id";
        \Database::query($sql, [
            ':montant_regle' => $montantRegle,
            ':statut'        => $statut,
            ':id'            => $id,
        ]);
    }

    /**
     * Verrouille et retourne une facture pour mise à jour concurrente.
     */
    public function trouverParIdPourMaj(int $id): array {
        $sql = "SELECT * FROM factures_fournisseur WHERE id = :id FOR UPDATE";
        $resultat = \Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            throw new \RuntimeException("Facture fournisseur ID {$id} introuvable.");
        }
        return $resultat;
    }
}