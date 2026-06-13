<?php
// modules/structure/controllers/FamilleController.php

namespace structure\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/models/FamilleModel.php';

use Controller;
use Auth;
use FamilleModel;

class FamilleController extends Controller
{
    private FamilleModel $modele;

    public function __construct() {
        Auth::verifierConnexion();
        $this->modele = new FamilleModel();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $familles = $this->modele->listerTout();
        $this->rendrePage('structure', 'familles/liste', [
            'titre'       => 'Familles de produits',
            'familles'    => $familles,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->rendrePage('structure', 'familles/formulaire', [
            'titre'       => 'Nouvelle famille',
            'famille'     => null,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['code', 'libelle', 'description']);
        if (empty($donnees['code']) || empty($donnees['libelle'])) {
            $_SESSION['erreur'] = "Le code et le libellé sont obligatoires.";
            $this->redirigerVers('/structure/familles/creer');
            return;
        }
        $this->modele->creer($donnees);
        $_SESSION['succes'] = "Famille créée avec succès.";
        $this->redirigerVers('/structure/familles');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $famille = $this->modele->trouverParId($id);
        if (!$famille) {
            $_SESSION['erreur'] = "Famille introuvable.";
            $this->redirigerVers('/structure/familles');
            return;
        }
        $this->rendrePage('structure', 'familles/formulaire', [
            'titre'       => 'Modifier la famille',
            'famille'     => $famille,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['code', 'libelle', 'description']);
        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Famille modifiée.";
        $this->redirigerVers('/structure/familles');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        $this->modele->supprimer($id);
        $_SESSION['succes'] = "Famille supprimée (ON DELETE CASCADE).";
        $this->redirigerVers('/structure/familles');
    }
}