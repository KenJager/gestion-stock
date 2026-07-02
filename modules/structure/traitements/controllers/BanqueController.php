<?php
// modules/structure/controllers/BanqueController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/traitements/models/BanqueModel.php';

use Controller;
use Auth;
use BanqueModel;

class BanqueController extends Controller
{
    private BanqueModel $modele;

    public function __construct() {
        Auth::verifierConnexion();
        $this->modele = new BanqueModel();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $banques = $this->modele->listerTout();
        $this->rendrePage('structure', 'banques/liste', [
            'titre'       => 'Banques',
            'banques'     => $banques,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->rendrePage('structure', 'banques/formulaire', [
            'titre'       => 'Nouvelle banque',
            'banque'      => null,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'nom', 'adresse', 'telephone', 'numero_compte'
        ]);
        if (empty($donnees['code']) || empty($donnees['nom'])) {
            $_SESSION['erreur'] = "Code et nom obligatoires.";
            $this->redirigerVers('/structure/banques/creer');
            return;
        }
        $this->modele->creer($donnees);
        $_SESSION['succes'] = "Banque créée.";
        $this->redirigerVers('/structure/banques');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $banque = $this->modele->trouverParId($id);
        if (!$banque) {
            $_SESSION['erreur'] = "Banque introuvable.";
            $this->redirigerVers('/structure/banques');
            return;
        }
        $this->rendrePage('structure', 'banques/formulaire', [
            'titre'       => 'Modifier la banque',
            'banque'      => $banque,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'nom', 'adresse', 'telephone', 'numero_compte'
        ]);
        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Banque modifiée.";
        $this->redirigerVers('/structure/banques');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        // Récupérer l'enregistrement pour archivage
        $banque = $this->modele->trouverParId($id);

        // Récupérer les versements liés pour archivage
        $sql = "SELECT * FROM versements_banque WHERE banque_id = :id";
        $versements = \Database::query($sql, [':id' => $id])->fetchAll();

        \Corbeille::archiver(
            'banques',
            $banque,
            ['versements' => $versements],
            $banque['nom']
        );

        $this->modele->supprimer($id);
        $_SESSION['succes'] = "Banque supprimée et déplacée vers la corbeille.";
        $this->redirigerVers('/structure/banques');
    }
}