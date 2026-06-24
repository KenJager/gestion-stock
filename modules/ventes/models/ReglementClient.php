<?php
// modules/Ventes/Models/ReglementClient.php

namespace ventes\models;

use Database;

class ReglementClient extends \Model {

    protected string $table = 'reglements_client';
    protected string $cle   = 'id';

    public function listerTout(): array {
        return \Database::query(
            "SELECT rc.*, fc.numero AS facture_numero,
                    c.raison_sociale AS client_nom, b.nom AS banque_nom
             FROM reglements_client rc
             JOIN factures_client fc ON fc.id = rc.facture_id
             JOIN clients c ON c.id = fc.client_id
             LEFT JOIN banques b ON b.id = rc.banque_id
             ORDER BY rc.date_reglement DESC, rc.id DESC"
        )->fetchAll();
    }

    public function creer(array $d): int {
        $row = \Database::query(
            "INSERT INTO reglements_client
                 (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, notes, utilisateur_id)
             VALUES (:facture_id, :date_reglement, :montant, :mode_paiement, :reference, :banque_id, :notes, :utilisateur_id)
             RETURNING id",
            $d
        )->fetch();
        return (int) $row['id'];
    }
}
