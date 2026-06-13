<?php
// modules/structure/controllers/CorbeilleController.php

namespace structure\controllers;

use structure\services\CorbeilleService;

class CorbeilleController extends \Controller {

    // Tables gérées par ce module
    private const TABLES_MODULE = [
        'banques', 'clients', 'categories_clients', 'familles_produits',
        'fournisseurs', 'produits', 'versements'
    ];

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('STRUCTURE_LIRE');
    }

    // GET /structure/corbeille
    public function index(): void {
        $elements = [];
        foreach (self::TABLES_MODULE as $table) {
            $elements = array_merge($elements, \Corbeille::lister($table));
        }
        usort($elements, fn($a, $b) => strcmp($b['date_suppression'], $a['date_suppression']));

        $this->rendrePage('structure', 'corbeille_liste', [
            'titre'       => 'Corbeille — Structure',
            'moduleActif' => 'structure',
            'elements'    => $elements,
            'succes'      => $_SESSION['succes'] ?? null,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['succes'], $_SESSION['erreur']);
    }

    // POST /structure/corbeille/restaurer
    public function restaurer(): void {
        \Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');

        $fichier = $_POST['fichier'] ?? '';
        try {
            $donnees = \Corbeille::charger($fichier);
        } catch (\RuntimeException $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/structure/corbeille');
        }

        if (!in_array($donnees['table'], self::TABLES_MODULE, true)) {
            $_SESSION['erreur'] = "Ce fichier n'appartient pas au module Structure.";
            $this->redirigerVers('/structure/corbeille');
        }

        $service = new CorbeilleService();

        try {
            match ($donnees['table']) {
                'banques'            => $service->restaurerBanque($fichier),
                'clients'            => $service->restaurerClient($fichier),
                'categories_clients' => $service->restaurerCategorieClient($fichier),
                'familles_produits'  => $service->restaurerFamille($fichier),
                'fournisseurs'       => $service->restaurerFournisseur($fichier),
                'produits'           => $service->restaurerProduit($fichier),
                'versements'         => $service->restaurerVersement($fichier),
            };
            $_SESSION['succes'] = 'Élément restauré avec succès.';
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
        }

        $this->redirigerVers('/structure/corbeille');
    }

    // POST /structure/corbeille/purger
    public function purger(): void {
        \Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');

        $fichier = $_POST['fichier'] ?? '';
        try {
            $donnees = \Corbeille::charger($fichier);
        } catch (\RuntimeException $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/structure/corbeille');
        }

        if (!in_array($donnees['table'], self::TABLES_MODULE, true)) {
            $_SESSION['erreur'] = "Ce fichier n'appartient pas au module Structure.";
            $this->redirigerVers('/structure/corbeille');
        }

        \Corbeille::supprimerFichier($fichier);
        $_SESSION['succes'] = 'Élément supprimé définitivement.';
        $this->redirigerVers('/structure/corbeille');
    }
}
