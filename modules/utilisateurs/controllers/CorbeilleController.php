<?php
// modules/Utilisateurs/Controllers/CorbeilleController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Services\CorbeilleService;

class CorbeilleController extends \Controller {

    // Tables gérées par CE module (Groupe 4)
    private const TABLES_MODULE = ['utilisateurs', 'groupes_utilisateurs'];

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
    }

    // GET /utilisateurs/corbeille
    public function index(): void {
        $elements = [];
        foreach (self::TABLES_MODULE as $table) {
            $elements = array_merge($elements, \Corbeille::lister($table));
        }
        usort($elements, fn($a, $b) => strcmp($b['date_suppression'], $a['date_suppression']));

        $this->rendrePage('Utilisateurs', 'corbeille_liste', [
            'titre'       => 'Corbeille — Utilisateurs',
            'moduleActif' => 'utilisateurs',
            'elements'    => $elements,
            'succes'      => $_SESSION['succes'] ?? null,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['succes'], $_SESSION['erreur']);
    }

    // POST /utilisateurs/corbeille/restaurer
    public function restaurer(): void {
        \Auth::verifierDroit('USERS_CREER');
        $this->verifierMethode('POST');

        $fichier = $_POST['fichier'] ?? '';
        $donnees = \Corbeille::charger($fichier);

        if (!in_array($donnees['table'], self::TABLES_MODULE, true)) {
            $_SESSION['erreur'] = "Ce fichier n'appartient pas au module Utilisateurs.";
            $this->redirigerVers('/utilisateurs/corbeille');
        }

        $service = new CorbeilleService();

        try {
            match ($donnees['table']) {
                'utilisateurs'         => $service->restaurerUtilisateur($fichier),
                'groupes_utilisateurs' => $service->restaurerGroupe($fichier),
            };
            $_SESSION['succes'] = 'Élément restauré avec succès.';
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
        }

        $this->redirigerVers('/utilisateurs/corbeille');
    }

    // POST /utilisateurs/corbeille/purger
    public function purger(): void {
        \Auth::verifierDroit('USERS_SUPPRIMER');
        $this->verifierMethode('POST');

        $fichier = $_POST['fichier'] ?? '';
        $donnees = \Corbeille::charger($fichier);

        if (!in_array($donnees['table'], self::TABLES_MODULE, true)) {
            $_SESSION['erreur'] = "Ce fichier n'appartient pas au module Utilisateurs.";
            $this->redirigerVers('/utilisateurs/corbeille');
        }

        \Corbeille::supprimerFichier($fichier);
        $_SESSION['succes'] = 'Élément supprimé définitivement.';
        $this->redirigerVers('/utilisateurs/corbeille');
    }
}
