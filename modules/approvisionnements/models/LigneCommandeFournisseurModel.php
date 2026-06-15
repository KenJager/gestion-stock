<?php
// modules/approvisionnements/models/LigneCommandeFournisseur.php

namespace approvisionnements\models;

class LigneCommandeFournisseurModel extends \Model {

    protected string $table = 'lignes_commande_fournisseur';

    /**
     * Liste les lignes d'un bon de commande avec les infos produit.
     */
    public function listerParBonCommande(int $bonCommandeId): array {
        $sql = "SELECT lc.*, p.code, p.designation, p.unite
                FROM lignes_commande_fournisseur lc
                JOIN produits p ON p.id = lc.produit_id
                WHERE lc.bon_commande_id = :id";
        return \Database::query($sql, [':id' => $bonCommandeId])->fetchAll();
    }

    /**
     * Crée une ligne de commande.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO lignes_commande_fournisseur
                (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
                VALUES
                (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':bon_commande_id'    => $donnees['bon_commande_id'],
            ':produit_id'         => $donnees['produit_id'],
            ':quantite_commandee' => $donnees['quantite_commandee'],
            ':prix_unitaire_ht'   => $donnees['prix_unitaire_ht'],
            ':tva'                => $donnees['tva'],
        ])->fetch();

        return (int) $resultat['id'];
    }

    /**
     * Met à jour la quantité reçue d'une ligne.
     */
    public function mettreAJourQuantiteRecue(int $id, float $quantiteRecue): void {
        $sql = "UPDATE lignes_commande_fournisseur SET quantite_recue = :recue WHERE id = :id";
        \Database::query($sql, [':recue' => $quantiteRecue, ':id' => $id]);
    }

    /**
     * Récupère une ligne avec les infos du bon parent (pour vérification statut).
     */
    public function trouverAvecBon(int $id): array {
        $sql = "SELECT lc.*, bc.statut AS bon_statut, bc.id AS bon_id
                FROM lignes_commande_fournisseur lc
                JOIN bons_commande_fournisseur bc ON bc.id = lc.bon_commande_id
                WHERE lc.id = :id";
        $resultat = \Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            throw new \RuntimeException("Ligne de commande ID {$id} introuvable.");
        }
        return $resultat;
    }
}