<?php
// modules/structure/controllers/FournisseurController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/traitements/models/FournisseurModel.php';

use Controller;
use Auth;
use FournisseurModel;

class FournisseurController extends Controller
{
    private FournisseurModel $modele;

    public function __construct() {
        Auth::verifierConnexion();
        $this->modele = new FournisseurModel();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $fournisseurs = $this->modele->listerTout();
        $this->rendrePage('structure', 'fournisseurs/liste', [
            'titre'       => 'Fournisseurs',
            'fournisseurs'=> $fournisseurs,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->rendrePage('structure', 'fournisseurs/formulaire', [
            'titre'       => 'Nouveau fournisseur',
            'fournisseur' => null,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'raison_sociale', 'contact', 'telephone',
            'email', 'adresse', 'ville', 'pays', 'actif'
        ]);
        if (empty($donnees['code']) || empty($donnees['raison_sociale'])) {
            $_SESSION['erreur'] = "Code et raison sociale obligatoires.";
            $this->redirigerVers('/structure/fournisseurs/creer');
            return;
        }
        $this->modele->creer($donnees);
        $_SESSION['succes'] = "Fournisseur créé.";
        $this->redirigerVers('/structure/fournisseurs');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $fournisseur = $this->modele->trouverParId($id);
        if (!$fournisseur) {
            $_SESSION['erreur'] = "Fournisseur introuvable.";
            $this->redirigerVers('/structure/fournisseurs');
            return;
        }
        $this->rendrePage('structure', 'fournisseurs/formulaire', [
            'titre'       => 'Modifier le fournisseur',
            'fournisseur' => $fournisseur,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'raison_sociale', 'contact', 'telephone',
            'email', 'adresse', 'ville', 'pays', 'actif'
        ]);
        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Fournisseur modifié.";
        $this->redirigerVers('/structure/fournisseurs');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        // Récupérer l'enregistrement pour archivage
        $fournisseur = $this->modele->trouverParId($id);

        // Archiver (sans données liées par défaut)
        \Corbeille::archiver(
            'fournisseurs',
            $fournisseur,
            [],
            $fournisseur['raison_sociale'] ?? ($fournisseur['code'] ?? '')
        );

        $this->modele->supprimer($id);
        $_SESSION['succes'] = "Fournisseur supprimé et déplacé vers la corbeille.";
        $this->redirigerVers('/structure/fournisseurs');
    }

    public function detail(int $id): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $fournisseur = $this->modele->trouverParId($id);
        if (!$fournisseur) {
            $_SESSION['erreur'] = "Fournisseur introuvable.";
            $this->redirigerVers('/structure/fournisseurs');
            return;
        }
        $this->rendrePage('structure', 'fournisseurs/detail', [
            'titre'       => 'Détail fournisseur',
            'fournisseur' => $fournisseur,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }
}