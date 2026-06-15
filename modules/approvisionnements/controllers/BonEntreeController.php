<?php
// modules/approvisionnements/controllers/BonEntreeController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';

use Controller;
use Auth;

class BonEntreeController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $sql = "SELECT be.*, u.nom || ' ' || u.prenom AS utilisateur
                FROM bons_entree be
                LEFT JOIN utilisateurs u ON u.id = be.utilisateur_id
                ORDER BY be.date_entree DESC";
        $entrees = \Database::query($sql)->fetchAll();

        $this->rendrePage('approvisionnements', 'bons-entree/liste', [
            'titre'        => 'Bons d\'entrée',
            'entrees'      => $entrees,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function pdf(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $sql = "SELECT be.*, u.nom || ' ' || u.prenom AS utilisateur
                    FROM bons_entree be
                    LEFT JOIN utilisateurs u ON u.id = be.utilisateur_id
                    WHERE be.id = :id";
            $entree = \Database::query($sql, [':id' => $id])->fetch();
            if (!$entree) throw new \RuntimeException("Bon d'entrée introuvable.");

            $sqlL = "SELECT lbe.*, p.code, p.designation, p.unite
                     FROM lignes_bon_entree lbe
                     JOIN produits p ON p.id = lbe.produit_id
                     WHERE lbe.bon_entree_id = :id";
            $lignes = \Database::query($sqlL, [':id' => $id])->fetchAll();

            $this->rendreVueSeule('modules/approvisionnements/views/bons-entree/pdf.php', [
                'entree' => $entree,
                'lignes' => $lignes,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/bons-entree');
        }
    }
}