<?php
// modules/approvisionnements/models/LigneEntreeDon.php

namespace approvisionnements\models;

class LigneEntreeDonModel extends \Model {

    protected string $table = 'lignes_entree_don';

    /**
     * Crée une ligne d'entrée de don.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO lignes_entree_don
                (entree_don_id, produit_id, quantite, valeur_estimee, date_peremption)
                VALUES
                (:entree_don_id, :produit_id, :quantite, :valeur_estimee, :date_peremption)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':entree_don_id'   => $donnees['entree_don_id'],
            ':produit_id'      => $donnees['produit_id'],
            ':quantite'        => $donnees['quantite'],
            ':valeur_estimee'  => $donnees['valeur_estimee'] ?? null,
            ':date_peremption' => $donnees['date_peremption'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }
}