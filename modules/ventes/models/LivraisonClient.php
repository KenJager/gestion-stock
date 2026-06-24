<?php
// modules/Ventes/Models/LivraisonClient.php

namespace ventes\models;

use Database;

class LivraisonClient extends \Model {

    protected string $table = 'bons_livraison_client';
    protected string $cle   = 'id';

    public function listerTout(): array {
        return \Database::query(
            "SELECT blc.*, c.raison_sociale AS client_nom,
                    bcc.numero AS commande_numero
             FROM bons_livraison_client blc
             JOIN clients c ON c.id = blc.client_id
             LEFT JOIN bons_commande_client bcc ON bcc.id = blc.bon_commande_id
             ORDER BY blc.date_livraison DESC, blc.id DESC"
        )->fetchAll();
    }

    public function trouverParId(int $id): array {
        $liv = \Database::query(
            "SELECT blc.*, c.raison_sociale AS client_nom,
                    c.adresse AS client_adresse, c.telephone AS client_telephone,
                    bcc.numero AS commande_numero
             FROM bons_livraison_client blc
             JOIN clients c ON c.id = blc.client_id
             LEFT JOIN bons_commande_client bcc ON bcc.id = blc.bon_commande_id
             WHERE blc.id = :id",
            [':id' => $id]
        )->fetch();

        if (!$liv) {
            throw new \RuntimeException("Bon de livraison introuvable (id={$id}).");
        }

        $liv['lignes'] = $this->lignes($id);
        return $liv;
    }

    public function lignes(int $bonLivraisonId): array {
        return \Database::query(
            "SELECT llc.*, p.designation, p.unite, p.code AS produit_code
             FROM lignes_livraison_client llc
             JOIN produits p ON p.id = llc.produit_id
             WHERE llc.bon_livraison_id = :id",
            [':id' => $bonLivraisonId]
        )->fetchAll();
    }

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO bons_livraison_client
                 (numero, bon_commande_id, client_id, date_livraison, statut, notes, utilisateur_id)
             VALUES (:numero, :bon_commande_id, :client_id, :date_livraison, 'LIVRÉ', :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }

    public function ajouterLigne(array $d): void {
        \Database::query(
            "INSERT INTO lignes_livraison_client
                 (bon_livraison_id, produit_id, quantite_livree, prix_unitaire_ht, tva, remise_pct)
             VALUES (:bon_livraison_id, :produit_id, :quantite_livree, :prix_unitaire_ht, :tva, :remise_pct)",
            $d
        );
    }

    public function modifier(int $id, array $d): void {
        $d[':id'] = $id;
        \Database::query(
            "UPDATE bons_livraison_client
             SET date_livraison = :date_livraison, notes = :notes
             WHERE id = :id",
            $d
        );
    }

    public function supprimer(int $id): void {
        \Database::query("DELETE FROM bons_livraison_client WHERE id = :id", [':id' => $id]);
    }

    // ----------------------------------------------------------------
    // RESTAURATION DEPUIS LA CORBEILLE
    // ----------------------------------------------------------------

    public function restaurer(array $principal, array $lignes): void {
        \Database::query(
            "INSERT INTO bons_livraison_client
                (id, numero, bon_commande_id, client_id, date_livraison, statut, notes, utilisateur_id)
             VALUES
                (:id, :numero, :bon_commande_id, :client_id, :date_livraison, :statut, :notes, :utilisateur_id)",
            [
                ':id'              => (int) $principal['id'],
                ':numero'          => $principal['numero'],
                ':bon_commande_id' => $principal['bon_commande_id'] ? (int) $principal['bon_commande_id'] : null,
                ':client_id'       => (int) $principal['client_id'],
                ':date_livraison'  => $principal['date_livraison'],
                ':statut'          => $principal['statut'],
                ':notes'           => $principal['notes'] ?: null,
                ':utilisateur_id'  => $principal['utilisateur_id'] ? (int) $principal['utilisateur_id'] : null,
            ]
        );

        foreach ($lignes as $l) {
            \Database::query(
                "INSERT INTO lignes_livraison_client
                    (id, bon_livraison_id, produit_id, quantite_livree, prix_unitaire_ht, tva, remise_pct)
                 VALUES
                    (:id, :bon_livraison_id, :produit_id, :quantite_livree, :prix_unitaire_ht, :tva, :remise_pct)",
                [
                    ':id'               => (int) $l['id'],
                    ':bon_livraison_id' => (int) $principal['id'],
                    ':produit_id'       => (int) $l['produit_id'],
                    ':quantite_livree'  => (float) $l['quantite_livree'],
                    ':prix_unitaire_ht' => (float) $l['prix_unitaire_ht'],
                    ':tva'              => $l['tva'] !== '' ? (float) $l['tva'] : 0,
                    ':remise_pct'       => $l['remise_pct'] !== '' ? (float) $l['remise_pct'] : 0,
                ]
            );
        }

        \Database::query("SELECT setval('bons_livraison_client_id_seq', (SELECT MAX(id) FROM bons_livraison_client))");
        \Database::query("SELECT setval('lignes_livraison_client_id_seq', (SELECT MAX(id) FROM lignes_livraison_client))");
    }
}
