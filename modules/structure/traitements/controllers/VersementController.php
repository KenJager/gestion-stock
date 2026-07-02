<?php
// modules/structure/controllers/VersementController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/traitements/models/VersementModel.php';
require_once ROOT . '/modules/structure/traitements/models/BanqueModel.php';

use Controller;
use Auth;
use VersementModel;
use BanqueModel;

class VersementController extends Controller
{
    private VersementModel $modele;
    private BanqueModel $banqueModel;

    public function __construct() {
        Auth::verifierConnexion();
        $this->modele = new VersementModel();
        $this->banqueModel = new BanqueModel();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $versements = $this->modele->listerAvecBanque();
        $this->rendrePage('structure', 'versements/liste', [
            'titre'       => 'Versements en banque',
            'versements'  => $versements,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $banques = $this->banqueModel->listerTout();
        $this->rendrePage('structure', 'versements/formulaire', [
            'titre'       => 'Nouveau versement',
            'versement'   => null,
            'banques'     => $banques,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'banque_id', 'montant', 'date_versement', 'reference', 'motif'
        ]);
        if (empty($donnees['banque_id']) || empty($donnees['montant'])) {
            $_SESSION['erreur'] = "Banque et montant obligatoires.";
            $this->redirigerVers('/structure/versements/creer');
            return;
        }
        $donnees['utilisateur_id'] = $_SESSION['utilisateur_id'];
        $this->modele->creer($donnees);
        $_SESSION['succes'] = "Versement enregistré.";
        $this->redirigerVers('/structure/versements');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $versement = $this->modele->trouverParId($id);
        if (!$versement) {
            $_SESSION['erreur'] = "Versement introuvable.";
            $this->redirigerVers('/structure/versements');
            return;
        }
        $banques = $this->banqueModel->listerTout();
        $this->rendrePage('structure', 'versements/formulaire', [
            'titre'       => 'Modifier le versement',
            'versement'   => $versement,
            'banques'     => $banques,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'banque_id', 'montant', 'date_versement', 'reference', 'motif'
        ]);
        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Versement modifié.";
        $this->redirigerVers('/structure/versements');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        $this->modele->supprimer($id);
        $_SESSION['succes'] = "Versement supprimé.";
        $this->redirigerVers('/structure/versements');
    }
}