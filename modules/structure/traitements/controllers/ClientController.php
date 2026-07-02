<?php
// modules/structure/controllers/ClientController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/traitements/models/ClientModel.php';
require_once ROOT . '/modules/structure/traitements/models/CategorieClientModel.php';

use Controller;
use Auth;
use ClientModel;
use CategorieClientModel;

class ClientController extends Controller
{
    private ClientModel $clientModel;
    private CategorieClientModel $categorieModel;

    public function __construct() {
        Auth::verifierConnexion();
        $this->clientModel = new ClientModel();
        $this->categorieModel = new CategorieClientModel();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $clients = $this->clientModel->listerAvecCategorie();
        $this->rendrePage('structure', 'clients/liste', [
            'titre'       => 'Clients',
            'clients'     => $clients,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $categories = $this->categorieModel->listerTout();
        $this->rendrePage('structure', 'clients/formulaire', [
            'titre'       => 'Nouveau client',
            'client'      => null,
            'categories'  => $categories,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'raison_sociale', 'contact', 'telephone',
            'email', 'adresse', 'ville', 'pays', 'categorie_id', 'actif'
        ]);
        if (empty($donnees['code']) || empty($donnees['raison_sociale'])) {
            $_SESSION['erreur'] = "Code et raison sociale obligatoires.";
            $this->redirigerVers('/structure/clients/creer');
            return;
        }
        $this->clientModel->creer($donnees);
        $_SESSION['succes'] = "Client créé.";
        $this->redirigerVers('/structure/clients');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $client = $this->clientModel->trouverParId($id);
        if (!$client) {
            $_SESSION['erreur'] = "Client introuvable.";
            $this->redirigerVers('/structure/clients');
            return;
        }
        $categories = $this->categorieModel->listerTout();
        $this->rendrePage('structure', 'clients/formulaire', [
            'titre'       => 'Modifier le client',
            'client'      => $client,
            'categories'  => $categories,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'raison_sociale', 'contact', 'telephone',
            'email', 'adresse', 'ville', 'pays', 'categorie_id', 'actif'
        ]);
        $this->clientModel->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Client modifié.";
        $this->redirigerVers('/structure/clients');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        // Récupérer l'enregistrement pour archivage
        $client = $this->clientModel->trouverParId($id);

        // Archiver (sans données liées par défaut)
        \Corbeille::archiver(
            'clients',
            $client,
            [],
            $client['raison_sociale'] ?? ($client['code'] ?? '')
        );

        $this->clientModel->supprimer($id);
        $_SESSION['succes'] = "Client supprimé et déplacé vers la corbeille.";
        $this->redirigerVers('/structure/clients');
    }

    public function detail(int $id): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $client = $this->clientModel->trouverAvecCategorie($id);
        if (!$client) {
            $_SESSION['erreur'] = "Client introuvable.";
            $this->redirigerVers('/structure/clients');
            return;
        }
        $this->rendrePage('structure', 'clients/detail', [
            'titre'       => 'Détail client',
            'client'      => $client,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }
}