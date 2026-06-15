<?php
// modules/approvisionnements/controllers/EtatController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';

use Controller;
use Auth;

class EtatController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function journalier(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $date = $_GET['date'] ?? date('Y-m-d');

        $mouvements = $this->recupererMouvements($date, $date);
        $this->rendrePage('approvisionnements', 'etats/journalier', [
            'titre'        => 'État journalier du ' . $date,
            'date'         => $date,
            'mouvements'   => $mouvements,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function annuel(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $annee = (int)($_GET['annee'] ?? date('Y'));
        $mois  = (int)($_GET['mois']  ?? date('m'));

        $debut = sprintf('%04d-%02d-01', $annee, $mois);
        $fin   = date('Y-m-t', strtotime($debut));

        $mouvements = $this->recupererMouvements($debut, $fin);
        $this->rendrePage('approvisionnements', 'etats/annuel', [
            'titre'        => "État mensuel — " . date('F Y', strtotime($debut)),
            'annee'        => $annee,
            'mois'         => $mois,
            'debut'        => $debut,
            'fin'          => $fin,
            'mouvements'   => $mouvements,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function journalierPdf(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $date = $_GET['date'] ?? date('Y-m-d');
        $mouvements = $this->recupererMouvements($date, $date);
        $this->rendreVueSeule('modules/approvisionnements/views/etats/journalier_pdf.php', [
            'titre'      => 'État journalier du ' . $date,
            'date'       => $date,
            'mouvements' => $mouvements,
        ]);
    }

    public function annuelPdf(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $annee = (int)($_GET['annee'] ?? date('Y'));
        $mois  = (int)($_GET['mois']  ?? date('m'));
        $debut = sprintf('%04d-%02d-01', $annee, $mois);
        $fin   = date('Y-m-t', strtotime($debut));
        $mouvements = $this->recupererMouvements($debut, $fin);
        $this->rendreVueSeule('modules/approvisionnements/views/etats/annuel_pdf.php', [
            'titre'      => "État mensuel — " . date('F Y', strtotime($debut)),
            'debut'      => $debut,
            'fin'        => $fin,
            'mouvements' => $mouvements,
        ]);
    }

    /**
     * Récupère les mouvements de stock sur une période.
     */
    private function recupererMouvements(string $debut, string $fin): array {
        $sql = "SELECT m.*, p.code, p.designation, p.unite,
                       u.nom || ' ' || u.prenom AS utilisateur
                FROM mouvements_stock m
                JOIN produits p ON p.id = m.produit_id
                LEFT JOIN utilisateurs u ON u.id = m.utilisateur_id
                WHERE m.date_mouvement::date BETWEEN :debut AND :fin
                ORDER BY m.date_mouvement DESC, m.id DESC";
        return \Database::query($sql, [':debut' => $debut, ':fin' => $fin])->fetchAll();
    }
}