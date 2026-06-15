<?php
// modules/approvisionnements/models/ReglementFournisseur.php

namespace approvisionnements\models;

class ReglementFournisseurModel extends \Model {

    protected string $table = 'reglements_fournisseur';

    /**
     * Historique des règlements pour un fournisseur.
     */
    public function listerParFournisseur(int $fournisseurId): array {
        $sql = "SELECT rf.date_reglement, ff.numero AS facture_numero, rf.montant,
                       rf.mode_paiement, u.nom || ' ' || u.prenom AS utilisateur
                FROM reglements_fournisseur rf
                JOIN factures_fournisseur ff ON rf.facture_id = ff.id
                LEFT JOIN utilisateurs u ON rf.utilisateur_id = u.id
                WHERE ff.fournisseur_id = :fournisseur_id
                ORDER BY rf.date_reglement DESC";
        return \Database::query($sql, [':fournisseur_id' => $fournisseurId])->fetchAll();
    }

    /**
     * Crée un règlement.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO reglements_fournisseur
                (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, notes, utilisateur_id)
                VALUES
                (:facture_id, :date_reglement, :montant, :mode_paiement, :reference, :banque_id, :notes, :utilisateur_id)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':facture_id'     => $donnees['facture_id'],
            ':date_reglement' => $donnees['date_reglement'],
            ':montant'        => $donnees['montant'],
            ':mode_paiement'  => $donnees['mode_paiement'],
            ':reference'      => $donnees['reference'] ?? null,
            ':banque_id'      => $donnees['banque_id'] ?? null,
            ':notes'          => $donnees['notes'] ?? null,
            ':utilisateur_id' => $donnees['utilisateur_id'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }
}