<?php
// modules/Ventes/Models/FactureClient.php

namespace ventes\traitements\models;

use Database;

class FactureClient extends \Model {

    protected string $table = 'factures_client';
    protected string $cle   = 'id';

    public function listerTout(): array {
        return \Database::query(
            "SELECT fc.*, c.raison_sociale AS client_nom,
                    blc.numero AS livraison_numero
             FROM factures_client fc
             JOIN clients c ON c.id = fc.client_id
             LEFT JOIN bons_livraison_client blc ON blc.id = fc.bon_livraison_id
             ORDER BY fc.date_facture DESC, fc.id DESC"
        )->fetchAll();
    }

    public function trouverParId(int $id): array {
        $f = \Database::query(
            "SELECT fc.*, c.raison_sociale AS client_nom,
                    c.adresse AS client_adresse, c.telephone AS client_telephone,
                    c.email AS client_email
             FROM factures_client fc
             JOIN clients c ON c.id = fc.client_id
             WHERE fc.id = :id",
            [':id' => $id]
        )->fetch();

        if (!$f) {
            throw new \RuntimeException("Facture client introuvable (id={$id}).");
        }

        $f['lignes']     = $this->lignes($id);
        $f['reglements'] = $this->reglements($id);
        return $f;
    }

    public function lignes(int $factureId): array {
        return \Database::query(
            "SELECT lfc.*, p.designation, p.unite, p.code AS produit_code
             FROM lignes_facture_client lfc
             JOIN produits p ON p.id = lfc.produit_id
             WHERE lfc.facture_id = :id",
            [':id' => $factureId]
        )->fetchAll();
    }

    public function reglements(int $factureId): array {
        return \Database::query(
            "SELECT rc.*, b.nom AS banque_nom
             FROM reglements_client rc
             LEFT JOIN banques b ON b.id = rc.banque_id
             WHERE rc.facture_id = :id
             ORDER BY rc.date_reglement",
            [':id' => $factureId]
        )->fetchAll();
    }

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO factures_client
                 (numero, bon_livraison_id, client_id, date_facture, date_echeance,
                  type_vente, montant_ht, montant_tva, montant_ttc, remise_montant, notes, utilisateur_id)
             VALUES (:numero, :bon_livraison_id, :client_id, :date_facture, :date_echeance,
                     :type_vente, :montant_ht, :montant_tva, :montant_ttc, :remise_montant, :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }

    public function ajouterLigne(array $d): void {
        \Database::query(
            "INSERT INTO lignes_facture_client
                 (facture_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct)
             VALUES (:facture_id, :produit_id, :quantite, :prix_unitaire_ht, :tva, :remise_pct)",
            $d
        );
    }

    public function modifier(int $id, array $d): void {
        $d[':id'] = $id;
        \Database::query(
            "UPDATE factures_client
             SET date_echeance  = :date_echeance,
                 remise_montant = :remise_montant,
                 notes          = :notes
             WHERE id = :id AND statut = 'NON_REGLÉE'",
            $d
        );
    }

    public function changerStatut(int $id, string $statut): void {
        \Database::query(
            "UPDATE factures_client SET statut = :statut WHERE id = :id",
            [':statut' => $statut, ':id' => $id]
        );
    }

    public function incrementerMontantRegle(int $id, float $montant): void {
        \Database::query(
            "UPDATE factures_client SET montant_regle = montant_regle + :m WHERE id = :id",
            [':m' => $montant, ':id' => $id]
        );
    }

    public function supprimer(int $id): void {
        \Database::query("DELETE FROM factures_client WHERE id = :id", [':id' => $id]);
    }

    // ----------------------------------------------------------------
    // RESTAURATION DEPUIS LA CORBEILLE
    // ----------------------------------------------------------------

    /**
     * @param array $principal   Champs de factures_client (id inclus)
     * @param array $lignes      Lignes de lignes_facture_client (id inclus)
     * @param array $reglements  Lignes de reglements_client (id inclus)
     */
    public function restaurer(array $principal, array $lignes, array $reglements = []): void {
        \Database::query(
            "INSERT INTO factures_client
                (id, numero, bon_livraison_id, client_id, date_facture, date_echeance,
                 type_vente, statut, montant_ht, montant_tva, montant_ttc,
                 remise_montant, montant_regle, notes, utilisateur_id)
             VALUES
                (:id, :numero, :bon_livraison_id, :client_id, :date_facture, :date_echeance,
                 :type_vente, :statut, :montant_ht, :montant_tva, :montant_ttc,
                 :remise_montant, :montant_regle, :notes, :utilisateur_id)",
            [
                ':id'               => (int) $principal['id'],
                ':numero'           => $principal['numero'],
                ':bon_livraison_id' => $principal['bon_livraison_id'] ? (int) $principal['bon_livraison_id'] : null,
                ':client_id'        => (int) $principal['client_id'],
                ':date_facture'     => $principal['date_facture'],
                ':date_echeance'    => $principal['date_echeance'] ?: null,
                ':type_vente'       => $principal['type_vente'],
                ':statut'           => $principal['statut'],
                ':montant_ht'       => (float) $principal['montant_ht'],
                ':montant_tva'      => (float) $principal['montant_tva'],
                ':montant_ttc'      => (float) $principal['montant_ttc'],
                ':remise_montant'   => $principal['remise_montant'] !== '' ? (float) $principal['remise_montant'] : 0,
                ':montant_regle'    => $principal['montant_regle']  !== '' ? (float) $principal['montant_regle']  : 0,
                ':notes'            => $principal['notes'] ?: null,
                ':utilisateur_id'   => $principal['utilisateur_id'] ? (int) $principal['utilisateur_id'] : null,
            ]
        );

        foreach ($lignes as $l) {
            \Database::query(
                "INSERT INTO lignes_facture_client
                    (id, facture_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct)
                 VALUES
                    (:id, :facture_id, :produit_id, :quantite, :prix_unitaire_ht, :tva, :remise_pct)",
                [
                    ':id'               => (int) $l['id'],
                    ':facture_id'       => (int) $principal['id'],
                    ':produit_id'       => (int) $l['produit_id'],
                    ':quantite'         => (float) $l['quantite'],
                    ':prix_unitaire_ht' => (float) $l['prix_unitaire_ht'],
                    ':tva'              => $l['tva'] !== '' ? (float) $l['tva'] : 0,
                    ':remise_pct'       => $l['remise_pct'] !== '' ? (float) $l['remise_pct'] : 0,
                ]
            );
        }

        foreach ($reglements as $r) {
            \Database::query(
                "INSERT INTO reglements_client
                    (id, facture_id, date_reglement, montant, mode_paiement, reference, banque_id, notes, utilisateur_id)
                 VALUES
                    (:id, :facture_id, :date_reglement, :montant, :mode_paiement, :reference, :banque_id, :notes, :utilisateur_id)",
                [
                    ':id'             => (int) $r['id'],
                    ':facture_id'     => (int) $principal['id'],
                    ':date_reglement' => $r['date_reglement'],
                    ':montant'        => (float) $r['montant'],
                    ':mode_paiement'  => $r['mode_paiement'] ?: null,
                    ':reference'      => $r['reference'] ?: null,
                    ':banque_id'      => $r['banque_id'] ? (int) $r['banque_id'] : null,
                    ':notes'          => $r['notes'] ?: null,
                    ':utilisateur_id' => $r['utilisateur_id'] ? (int) $r['utilisateur_id'] : null,
                ]
            );
        }

        \Database::query("SELECT setval('factures_client_id_seq', (SELECT MAX(id) FROM factures_client))");
        \Database::query("SELECT setval('lignes_facture_client_id_seq', (SELECT MAX(id) FROM lignes_facture_client))");
        if (!empty($reglements)) {
            \Database::query("SELECT setval('reglements_client_id_seq', (SELECT MAX(id) FROM reglements_client))");
        }
    }

    // ── ÉTATS ────────────────────────────────────────────────────────

    public function etatJournalier(string $date): array {
        return \Database::query(
            "SELECT fc.numero, c.raison_sociale AS client_nom,
                    fc.type_vente, fc.montant_ht, fc.montant_tva,
                    fc.montant_ttc, fc.remise_montant, fc.montant_regle,
                    fc.solde, fc.statut
             FROM factures_client fc
             JOIN clients c ON c.id = fc.client_id
             WHERE fc.date_facture = :date
             ORDER BY fc.id",
            [':date' => $date]
        )->fetchAll();
    }

    public function etatAnnuel(int $annee): array {
        return \Database::query(
            "SELECT EXTRACT(MONTH FROM date_facture)::INT AS mois,
                    COUNT(*)           AS nb_factures,
                    SUM(montant_ht)    AS total_ht,
                    SUM(montant_tva)   AS total_tva,
                    SUM(montant_ttc)   AS total_ttc,
                    SUM(montant_regle) AS total_regle,
                    SUM(solde)         AS total_solde
             FROM factures_client
             WHERE EXTRACT(YEAR FROM date_facture) = :annee
             GROUP BY EXTRACT(MONTH FROM date_facture)
             ORDER BY mois",
            [':annee' => $annee]
        )->fetchAll();
    }
}
