<?php
// modules/approvisionnements/models/EntreeDon.php

namespace approvisionnements\models;

class EntreeDonModel extends \Model {

    protected string $table = 'entrees_dons';

    /**
     * Liste les entrées de dons sur une période.
     */
    public function listerParPeriode(string $dateDebut, string $dateFin): array {
        $sql = "SELECT id, numero, donateur, date_entree, notes
                FROM entrees_dons
                WHERE date_entree BETWEEN :debut AND :fin
                ORDER BY date_entree DESC";
        return \Database::query($sql, [':debut' => $dateDebut, ':fin' => $dateFin])->fetchAll();
    }

    /**
     * Crée une entrée de don.
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO entrees_dons
                (numero, donateur, notes, utilisateur_id)
                VALUES
                (:numero, :donateur, :notes, :utilisateur_id)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':numero'         => $donnees['numero'],
            ':donateur'       => $donnees['donateur'] ?? null,
            ':notes'          => $donnees['notes'] ?? null,
            ':utilisateur_id' => $donnees['utilisateur_id'] ?? null,
        ])->fetch();

        return (int) $resultat['id'];
    }
}