<?php
// modules/Ventes/Models/VenteComptant.php

namespace ventes\traitements\models;

use Database;

class VenteComptant extends \Model {

    protected string $table = 'ventes_comptant';
    protected string $cle   = 'id';

    public function listerTout(): array {
        return \Database::query(
            "SELECT vc.*, COALESCE(c.raison_sociale, 'Client anonyme') AS client_nom
             FROM ventes_comptant vc
             LEFT JOIN clients c ON c.id = vc.client_id
             ORDER BY vc.date_vente DESC, vc.id DESC"
        )->fetchAll();
    }

    public function trouverParId(int $id): array {
        $v = \Database::query(
            "SELECT vc.*, COALESCE(c.raison_sociale, 'Client anonyme') AS client_nom,
                    c.telephone AS client_telephone
             FROM ventes_comptant vc
             LEFT JOIN clients c ON c.id = vc.client_id
             WHERE vc.id = :id",
            [':id' => $id]
        )->fetch();

        if (!$v) {
            throw new \RuntimeException("Vente comptant introuvable (id={$id}).");
        }

        $v['lignes'] = $this->lignes($id);
        return $v;
    }

    public function lignes(int $venteId): array {
        return \Database::query(
            "SELECT lvc.*, p.designation, p.unite, p.code AS produit_code
             FROM lignes_vente_comptant lvc
             JOIN produits p ON p.id = lvc.produit_id
             WHERE lvc.vente_comptant_id = :id",
            [':id' => $venteId]
        )->fetchAll();
    }

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO ventes_comptant
                 (numero, client_id, date_vente, montant_ht, montant_tva, montant_ttc,
                  remise_montant, montant_percu, mode_paiement, notes, utilisateur_id)
             VALUES (:numero, :client_id, :date_vente, :montant_ht, :montant_tva, :montant_ttc,
                     :remise_montant, :montant_percu, :mode_paiement, :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }

    public function ajouterLigne(array $d): void {
        \Database::query(
            "INSERT INTO lignes_vente_comptant
                 (vente_comptant_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct)
             VALUES (:vente_comptant_id, :produit_id, :quantite, :prix_unitaire_ht, :tva, :remise_pct)",
            $d
        );
    }

    public function supprimer(int $id): void {
        \Database::query("DELETE FROM ventes_comptant WHERE id = :id", [':id' => $id]);
    }

    // ----------------------------------------------------------------
    // RESTAURATION DEPUIS LA CORBEILLE
    // ----------------------------------------------------------------

    public function restaurer(array $principal, array $lignes): void {
        \Database::query(
            "INSERT INTO ventes_comptant
                (id, numero, client_id, date_vente, montant_ht, montant_tva, montant_ttc,
                 remise_montant, montant_percu, mode_paiement, notes, utilisateur_id)
             VALUES
                (:id, :numero, :client_id, :date_vente, :montant_ht, :montant_tva, :montant_ttc,
                 :remise_montant, :montant_percu, :mode_paiement, :notes, :utilisateur_id)",
            [
                ':id'             => (int) $principal['id'],
                ':numero'         => $principal['numero'],
                ':client_id'      => $principal['client_id'] ? (int) $principal['client_id'] : null,
                ':date_vente'     => $principal['date_vente'],
                ':montant_ht'     => (float) $principal['montant_ht'],
                ':montant_tva'    => (float) $principal['montant_tva'],
                ':montant_ttc'    => (float) $principal['montant_ttc'],
                ':remise_montant' => $principal['remise_montant'] !== '' ? (float) $principal['remise_montant'] : 0,
                ':montant_percu'  => (float) $principal['montant_percu'],
                ':mode_paiement'  => $principal['mode_paiement'] ?: null,
                ':notes'          => $principal['notes'] ?: null,
                ':utilisateur_id' => $principal['utilisateur_id'] ? (int) $principal['utilisateur_id'] : null,
            ]
        );

        foreach ($lignes as $l) {
            \Database::query(
                "INSERT INTO lignes_vente_comptant
                    (id, vente_comptant_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct)
                 VALUES
                    (:id, :vente_comptant_id, :produit_id, :quantite, :prix_unitaire_ht, :tva, :remise_pct)",
                [
                    ':id'                => (int) $l['id'],
                    ':vente_comptant_id' => (int) $principal['id'],
                    ':produit_id'        => (int) $l['produit_id'],
                    ':quantite'          => (float) $l['quantite'],
                    ':prix_unitaire_ht'  => (float) $l['prix_unitaire_ht'],
                    ':tva'               => $l['tva'] !== '' ? (float) $l['tva'] : 0,
                    ':remise_pct'        => $l['remise_pct'] !== '' ? (float) $l['remise_pct'] : 0,
                ]
            );
        }

        \Database::query("SELECT setval('ventes_comptant_id_seq', (SELECT MAX(id) FROM ventes_comptant))");
        \Database::query("SELECT setval('lignes_vente_comptant_id_seq', (SELECT MAX(id) FROM lignes_vente_comptant))");
    }

    public function etatJournalier(string $date): array {
        return \Database::query(
            "SELECT vc.numero, COALESCE(c.raison_sociale, 'Anonyme') AS client_nom,
                    vc.montant_ttc, vc.remise_montant, vc.montant_percu,
                    vc.monnaie_rendue, vc.mode_paiement
             FROM ventes_comptant vc
             LEFT JOIN clients c ON c.id = vc.client_id
             WHERE vc.date_vente = :date
             ORDER BY vc.id",
            [':date' => $date]
        )->fetchAll();
    }
}
