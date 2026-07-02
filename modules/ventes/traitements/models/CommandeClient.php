<?php
// modules/Ventes/Models/CommandeClient.php

namespace ventes\traitements\models;

use Database;

class CommandeClient extends \Model {

    protected string $table = 'bons_commande_client';
    protected string $cle   = 'id';

    // ----------------------------------------------------------------
    // LECTURE
    // ----------------------------------------------------------------

    public function listerTout(): array {
        return \Database::query(
            "SELECT bcc.*, c.raison_sociale AS client_nom
             FROM bons_commande_client bcc
             JOIN clients c ON c.id = bcc.client_id
             ORDER BY bcc.date_commande DESC, bcc.id DESC"
        )->fetchAll();
    }

    public function trouverParId(int $id): array {
        $commande = \Database::query(
            "SELECT bcc.*,
                    c.raison_sociale AS client_nom,
                    c.adresse        AS client_adresse,
                    c.telephone      AS client_telephone
             FROM bons_commande_client bcc
             JOIN clients c ON c.id = bcc.client_id
             WHERE bcc.id = :id",
            [':id' => $id]
        )->fetch();

        if (!$commande) {
            throw new \RuntimeException("Commande client introuvable (id={$id}).");
        }

        $commande['lignes'] = $this->lignes($id);
        return $commande;
    }

    public function lignes(int $bonCommandeId): array {
        return \Database::query(
            "SELECT lcc.*, p.designation, p.unite, p.code AS produit_code
             FROM lignes_commande_client lcc
             JOIN produits p ON p.id = lcc.produit_id
             WHERE lcc.bon_commande_id = :id",
            [':id' => $bonCommandeId]
        )->fetchAll();
    }

    // ----------------------------------------------------------------
    // ÉCRITURE
    // ----------------------------------------------------------------

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO bons_commande_client
                 (numero, client_id, date_commande, date_livraison_prevue,
                  statut, montant_ht, montant_tva, montant_ttc, remise_pct, notes, utilisateur_id)
             VALUES (:numero, :client_id, :date_commande, :date_livraison_prevue,
                     'EN_ATTENTE', :montant_ht, :montant_tva, :montant_ttc, :remise_pct, :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }

    public function ajouterLigne(array $d): void {
        \Database::query(
            "INSERT INTO lignes_commande_client
                 (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva, remise_pct)
             VALUES (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva, :remise_pct)",
            $d
        );
    }

    public function modifier(int $id, array $d): void {
        $d[':id'] = $id;
        \Database::query(
            "UPDATE bons_commande_client
             SET client_id             = :client_id,
                 date_livraison_prevue = :date_livraison_prevue,
                 remise_pct            = :remise_pct,
                 montant_ht            = :montant_ht,
                 montant_tva           = :montant_tva,
                 montant_ttc           = :montant_ttc,
                 notes                 = :notes
             WHERE id = :id AND statut = 'EN_ATTENTE'",
            $d
        );
    }

    public function supprimerLignes(int $bonCommandeId): void {
        \Database::query(
            "DELETE FROM lignes_commande_client WHERE bon_commande_id = :id",
            [':id' => $bonCommandeId]
        );
    }

    public function changerStatut(int $id, string $statut): void {
        \Database::query(
            "UPDATE bons_commande_client SET statut = :statut WHERE id = :id",
            [':statut' => $statut, ':id' => $id]
        );
    }

    public function mettreAJourQuantiteLivree(int $ligneId, float $qte): void {
        \Database::query(
            "UPDATE lignes_commande_client
             SET quantite_livree = quantite_livree + :qte
             WHERE id = :id",
            [':qte' => $qte, ':id' => $ligneId]
        );
    }

    // ----------------------------------------------------------------
    // RESTAURATION DEPUIS LA CORBEILLE
    // ----------------------------------------------------------------

    /**
     * Réinsère un enregistrement archivé en respectant son id d'origine,
     * puis réinsère ses lignes liées. Utilisé uniquement par la restauration
     * depuis la corbeille (Corbeille::charger()).
     *
     * @param array $principal  Champs de bons_commande_client (id inclus)
     * @param array $lignes     Lignes de lignes_commande_client (id inclus)
     */
    public function restaurer(array $principal, array $lignes): void {
        \Database::query(
            "INSERT INTO bons_commande_client
                (id, numero, client_id, date_commande, date_livraison_prevue,
                 statut, montant_ht, montant_tva, montant_ttc, remise_pct, notes, utilisateur_id)
             VALUES
                (:id, :numero, :client_id, :date_commande, :date_livraison_prevue,
                 :statut, :montant_ht, :montant_tva, :montant_ttc, :remise_pct, :notes, :utilisateur_id)",
            [
                ':id'                    => (int) $principal['id'],
                ':numero'                => $principal['numero'],
                ':client_id'             => (int) $principal['client_id'],
                ':date_commande'         => $principal['date_commande'],
                ':date_livraison_prevue' => $principal['date_livraison_prevue'] ?: null,
                ':statut'                => $principal['statut'],
                ':montant_ht'            => (float) $principal['montant_ht'],
                ':montant_tva'           => (float) $principal['montant_tva'],
                ':montant_ttc'           => (float) $principal['montant_ttc'],
                ':remise_pct'            => (float) $principal['remise_pct'],
                ':notes'                 => $principal['notes'] ?: null,
                ':utilisateur_id'        => $principal['utilisateur_id'] ? (int) $principal['utilisateur_id'] : null,
            ]
        );

        foreach ($lignes as $l) {
            \Database::query(
                "INSERT INTO lignes_commande_client
                    (id, bon_commande_id, produit_id, quantite_commandee, quantite_livree, prix_unitaire_ht, tva, remise_pct)
                 VALUES
                    (:id, :bon_commande_id, :produit_id, :quantite_commandee, :quantite_livree, :prix_unitaire_ht, :tva, :remise_pct)",
                [
                    ':id'                  => (int) $l['id'],
                    ':bon_commande_id'     => (int) $principal['id'],
                    ':produit_id'          => (int) $l['produit_id'],
                    ':quantite_commandee'  => (float) $l['quantite_commandee'],
                    ':quantite_livree'     => $l['quantite_livree'] !== '' ? (float) $l['quantite_livree'] : 0,
                    ':prix_unitaire_ht'    => (float) $l['prix_unitaire_ht'],
                    ':tva'                 => $l['tva'] !== '' ? (float) $l['tva'] : 0,
                    ':remise_pct'          => $l['remise_pct'] !== '' ? (float) $l['remise_pct'] : 0,
                ]
            );
        }

        // Resynchroniser la séquence PostgreSQL pour éviter les collisions d'id futures
        \Database::query("SELECT setval('bons_commande_client_id_seq', (SELECT MAX(id) FROM bons_commande_client))");
        \Database::query("SELECT setval('lignes_commande_client_id_seq', (SELECT MAX(id) FROM lignes_commande_client))");
    }
}
