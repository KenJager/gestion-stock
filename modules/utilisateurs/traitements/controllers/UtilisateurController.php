<?php
// modules/utilisateurs/Controllers/UtilisateurController.php

namespace utilisateurs\traitements\controllers;

use utilisateurs\traitements\models\Utilisateur;
use utilisateurs\traitements\models\GroupeUtilisateur;
use utilisateurs\traitements\services\UtilisateurService;

class UtilisateurController extends \Controller {

    private Utilisateur $modele;
    private UtilisateurService $service;

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
        $this->modele  = new Utilisateur();
        $this->service = new UtilisateurService();
    }

    // GET /utilisateurs
    public function index(): void {
        $utilisateurs = $this->modele->listerTout();
        $this->rendrePage('utilisateurs', 'utilisateur_liste', [
            'titre'        => 'Utilisateurs',
            'moduleActif'  => 'utilisateurs',
            'utilisateurs' => $utilisateurs,
            'succes'       => $_SESSION['succes'] ?? null,
            'erreur'       => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['succes'], $_SESSION['erreur']);
    }

    // GET /utilisateurs/creer
    public function creer(): void {
        \Auth::verifierDroit('USERS_CREER');
        $groupes = (new GroupeUtilisateur())->listerTout();

        $this->rendrePage('utilisateurs', 'utilisateur_formulaire', [
            'titre'       => 'Nouvel utilisateur',
            'moduleActif' => 'utilisateurs',
            'utilisateur' => null,
            'groupes'     => $groupes,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['erreur']);
    }

    // POST /utilisateurs/creer
    public function enregistrer(): void {
        \Auth::verifierDroit('USERS_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['nom', 'prenom', 'login', 'email', 'groupe_id']);
        $donnees['mot_de_passe'] = $_POST['mot_de_passe'] ?? '';
        $donnees['groupe_id']    = (int) $donnees['groupe_id'];

        if ($donnees['nom'] === '' || $donnees['login'] === '' || $donnees['mot_de_passe'] === '') {
            $_SESSION['erreur'] = 'Le nom, le login et le mot de passe sont obligatoires.';
            $this->redirigerVers('/utilisateurs/creer');
        }

        try {
            $this->service->creerUtilisateur($donnees);
            $_SESSION['succes'] = 'Utilisateur créé avec succès.';
            $this->redirigerVers('/utilisateurs');
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
            $this->redirigerVers('/utilisateurs/creer');
        }
    }

    // GET /utilisateurs/{id}/modifier
    public function editer(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');

        try {
            $utilisateur = $this->modele->trouverParId($id);
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
            $this->redirigerVers('/utilisateurs');
        }

        $groupes = (new GroupeUtilisateur())->listerTout();

        $this->rendrePage('utilisateurs', 'utilisateur_formulaire', [
            'titre'       => 'Modifier l\'utilisateur',
            'moduleActif' => 'utilisateurs',
            'utilisateur' => $utilisateur,
            'groupes'     => $groupes,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['erreur']);
    }

    // POST /utilisateurs/{id}/modifier
    public function mettreAJour(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['nom', 'prenom', 'login', 'email', 'groupe_id']);
        $donnees['mot_de_passe'] = $_POST['mot_de_passe'] ?? '';
        $donnees['groupe_id']    = (int) $donnees['groupe_id'];
        $donnees['actif']        = isset($_POST['actif']) ? true : false;

        if ($donnees['nom'] === '' || $donnees['login'] === '') {
            $_SESSION['erreur'] = 'Le nom et le login sont obligatoires.';
            $this->redirigerVers("/utilisateurs/{$id}/modifier");
        }

        try {
            $this->service->mettreAJourUtilisateur($id, $donnees);
            $_SESSION['succes'] = 'Utilisateur mis à jour avec succès.';
            $this->redirigerVers('/utilisateurs');
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
            $this->redirigerVers("/utilisateurs/{$id}/modifier");
        }
    }

    // POST /utilisateurs/{id}/supprimer
    public function supprimer(int $id): void {
        \Auth::verifierDroit('USERS_SUPPRIMER');
        $this->verifierMethode('POST');

        // Empêcher l'auto-suppression
        if ($id === (int) (\Auth::utilisateurCourant()['id'] ?? 0)) {
            $_SESSION['erreur'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            $this->redirigerVers('/utilisateurs');
        }

        $utilisateur = $this->modele->trouverParId($id);

        // Archiver dans la corbeille XML avant suppression
        \Corbeille::archiver(
            'utilisateurs',
            $utilisateur,
            [],
            trim($utilisateur['nom'] . ' ' . ($utilisateur['prenom'] ?? ''))
        );

        $this->modele->supprimer($id);
        $_SESSION['succes'] = 'Utilisateur supprimé et déplacé vers la corbeille.';
        $this->redirigerVers('/utilisateurs');
    }
}
