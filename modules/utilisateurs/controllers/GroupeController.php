<?php
// modules/Utilisateurs/Controllers/GroupeController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Models\GroupeUtilisateur;
use Utilisateurs\Models\Droit;

class GroupeController extends \Controller {

    private GroupeUtilisateur $modele;

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
        $this->modele = new GroupeUtilisateur();
    }

    // GET /groupes
    public function index(): void {
        $groupes = $this->modele->listerTout();
        $this->rendrePage('Utilisateurs', 'groupe_liste', [
            'titre'       => 'Groupes d\'utilisateurs',
            'moduleActif' => 'utilisateurs',
            'groupes'     => $groupes,
            'succes'      => $_SESSION['succes'] ?? null,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['succes'], $_SESSION['erreur']);
    }

    // GET /groupes/creer
    public function creer(): void {
        \Auth::verifierDroit('USERS_CREER');
        $this->rendrePage('Utilisateurs', 'groupe_formulaire', [
            'titre'       => 'Nouveau groupe',
            'moduleActif' => 'utilisateurs',
            'groupe'      => null,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['erreur']);
    }

    // POST /groupes/creer
    public function enregistrer(): void {
        \Auth::verifierDroit('USERS_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['nom', 'description']);

        if ($donnees['nom'] === '') {
            $_SESSION['erreur'] = 'Le nom du groupe est obligatoire.';
            $this->redirigerVers('/groupes/creer');
        }

        $id = $this->modele->creer($donnees);
        $_SESSION['succes'] = 'Groupe créé avec succès. Vous pouvez maintenant lui affecter des droits.';
        $this->redirigerVers("/groupes/{$id}/droits");
    }

    // GET /groupes/{id}/modifier
    public function editer(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');

        try {
            $groupe = $this->modele->trouverParId($id);
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
            $this->redirigerVers('/groupes');
        }

        $this->rendrePage('Utilisateurs', 'groupe_formulaire', [
            'titre'       => 'Modifier le groupe',
            'moduleActif' => 'utilisateurs',
            'groupe'      => $groupe,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['erreur']);
    }

    // POST /groupes/{id}/modifier
    public function mettreAJour(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['nom', 'description']);

        if ($donnees['nom'] === '') {
            $_SESSION['erreur'] = 'Le nom du groupe est obligatoire.';
            $this->redirigerVers("/groupes/{$id}/modifier");
        }

        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = 'Groupe mis à jour avec succès.';
        $this->redirigerVers('/groupes');
    }

    // POST /groupes/{id}/supprimer
    public function supprimer(int $id): void {
        \Auth::verifierDroit('USERS_SUPPRIMER');
        $this->verifierMethode('POST');

        if ($this->modele->aDesUtilisateurs($id)) {
            $_SESSION['erreur'] = 'Impossible de supprimer ce groupe : des utilisateurs y sont encore affectés.';
            $this->redirigerVers('/groupes');
        }

        $groupe = $this->modele->trouverParId($id);

        // Récupérer les droits liés (groupes_droits) pour archivage
        $sqlDroits = "SELECT groupe_id, droit_id FROM groupes_droits WHERE groupe_id = :id";
        $droitsLies = \Database::query($sqlDroits, [':id' => $id])->fetchAll();

        \Corbeille::archiver(
            'groupes_utilisateurs',
            $groupe,
            ['groupes_droits' => $droitsLies],
            $groupe['nom']
        );

        $this->modele->supprimer($id);
        $_SESSION['succes'] = 'Groupe supprimé et déplacé vers la corbeille.';
        $this->redirigerVers('/groupes');
    }

    // GET /groupes/{id}/droits
    public function gererDroits(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');

        try {
            $groupe = $this->modele->trouverParId($id);
        } catch (\RuntimeException $erreur) {
            $_SESSION['erreur'] = $erreur->getMessage();
            $this->redirigerVers('/groupes');
        }

        $droitsParModule = (new Droit())->listerParModule();
        $droitsAffectes  = $this->modele->droitsAffectes($id);

        $this->rendrePage('Utilisateurs', 'groupe_droits', [
            'titre'            => 'Droits du groupe : ' . $groupe['nom'],
            'moduleActif'      => 'utilisateurs',
            'groupe'           => $groupe,
            'droitsParModule'  => $droitsParModule,
            'droitsAffectes'   => $droitsAffectes,
            'succes'           => $_SESSION['succes'] ?? null,
        ]);
        unset($_SESSION['succes']);
    }

    // POST /groupes/{id}/droits
    public function enregistrerDroits(int $id): void {
        \Auth::verifierDroit('USERS_MODIFIER');
        $this->verifierMethode('POST');

        $droitIds = $_POST['droits'] ?? [];
        $this->modele->definirDroits($id, $droitIds);

        $_SESSION['succes'] = 'Droits mis à jour avec succès.';
        $this->redirigerVers("/groupes/{$id}/droits");
    }
}
