<?php
// modules/ventes/models/BonSortie.php

namespace ventes\traitements\models;

use Database;

class BonSortie extends \Model {

    protected string $table = 'bons_sortie';
    protected string $cle   = 'id';

    public function listerTout(): array {
        return \Database::query(
            "SELECT * FROM bons_sortie ORDER BY date_sortie DESC, id DESC"
        )->fetchAll();
    }

    public function trouverParId(int $id): array {
        $bon = \Database::query(
            "SELECT * FROM bons_sortie WHERE id = :id",
            [':id' => $id]
        )->fetch();

        if (!$bon) {
            throw new \RuntimeException("Bon de sortie introuvable (id={$id}).");
        }

        $bon['lignes'] = $this->lignes($id);
        return $bon;
    }

    public function lignes(int $bonSortieId): array {
        return \Database::query(
            "SELECT lbs.*, p.designation, p.unite, p.code AS produit_code
             FROM lignes_bon_sortie lbs
             JOIN produits p ON p.id = lbs.produit_id
             WHERE lbs.bon_sortie_id = :id",
            [':id' => $bonSortieId]
        )->fetchAll();
    }

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO bons_sortie (numero, motif, date_sortie, notes, utilisateur_id)
             VALUES (:numero, :motif, :date_sortie, :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }

    public function ajouterLigne(array $d): void {
        \Database::query(
            "INSERT INTO lignes_bon_sortie
                 (bon_sortie_id, produit_id, quantite, prix_unitaire, observations)
             VALUES (:bon_sortie_id, :produit_id, :quantite, :prix_unitaire, :observations)",
            $d
        );
    }

    public function modifier(int $id, array $d): void {
        $d[':id'] = $id;
        \Database::query(
            "UPDATE bons_sortie SET motif = :motif, notes = :notes WHERE id = :id",
            $d
        );
    }

    public function supprimer(int $id): void {
        \Database::query("DELETE FROM bons_sortie WHERE id = :id", [':id' => $id]);
    }

    // ----------------------------------------------------------------
    // RESTAURATION DEPUIS LA CORBEILLE
    // ----------------------------------------------------------------

    public function restaurer(array $principal, array $lignes): void {
        \Database::query(
            "INSERT INTO bons_sortie (id, numero, motif, date_sortie, notes, utilisateur_id)
             VALUES (:id, :numero, :motif, :date_sortie, :notes, :utilisateur_id)",
            [
                ':id'             => (int) $principal['id'],
                ':numero'         => $principal['numero'],
                ':motif'          => $principal['motif'],
                ':date_sortie'    => $principal['date_sortie'],
                ':notes'          => $principal['notes'] ?: null,
                ':utilisateur_id' => $principal['utilisateur_id'] ? (int) $principal['utilisateur_id'] : null,
            ]
        );

        foreach ($lignes as $l) {
            \Database::query(
                "INSERT INTO lignes_bon_sortie
                    (id, bon_sortie_id, produit_id, quantite, prix_unitaire, observations)
                 VALUES
                    (:id, :bon_sortie_id, :produit_id, :quantite, :prix_unitaire, :observations)",
                [
                    ':id'            => (int) $l['id'],
                    ':bon_sortie_id' => (int) $principal['id'],
                    ':produit_id'    => (int) $l['produit_id'],
                    ':quantite'      => (float) $l['quantite'],
                    ':prix_unitaire' => $l['prix_unitaire'] !== '' ? (float) $l['prix_unitaire'] : 0,
                    ':observations'  => $l['observations'] ?: null,
                ]
            );
        }

        \Database::query("SELECT setval('bons_sortie_id_seq', (SELECT MAX(id) FROM bons_sortie))");
        \Database::query("SELECT setval('lignes_bon_sortie_id_seq', (SELECT MAX(id) FROM lignes_bon_sortie))");
    }
}
