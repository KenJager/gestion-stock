<?php
// modules/structure/controllers/CategorieClientController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/structure/traitements/models/CategorieClientModel.php';
require_once ROOT . '/modules/structure/traitements/models/ClientModel.php';

use Controller;
use Auth;
use CategorieClientModel;
use ClientModel;
use Corbeille;

class CategorieClientController extends Controller
{
    private CategorieClientModel $modele;
    private ClientModel $clientModel;

    public function __construct() {
        Auth::verifierConnexion();
        $this->modele = new CategorieClientModel();
        $this->clientModel = new ClientModel();
    }

    // Liste des catégories
    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $categories = $this->modele->listerTout();
        $this->rendrePage('structure', 'categories_clients/liste', [
            'titre'       => 'Catégories de clients',
            'categories'  => $categories,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    // Formulaire de création
    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->rendrePage('structure', 'categories_clients/formulaire', [
            'titre'       => 'Nouvelle catégorie de clients',
            'categorie'   => null,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    // Enregistrement (POST)
    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['code', 'libelle', 'remise_pct', 'description']);
        
        if (empty($donnees['code']) || empty($donnees['libelle'])) {
            $_SESSION['erreur'] = "Le code et le libellé sont obligatoires.";
            $this->redirigerVers('/structure/categories-clients/creer');
            return;
        }
        
        $donnees['remise_pct'] = !empty($donnees['remise_pct']) ? (float)$donnees['remise_pct'] : 0;
        
        $this->modele->creer($donnees);
        $_SESSION['succes'] = "Catégorie créée avec succès.";
        $this->redirigerVers('/structure/categories-clients');
    }

    // Formulaire d'édition
    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $categorie = $this->modele->trouverParId($id);
        if (empty($categorie)) {
            $_SESSION['erreur'] = "Catégorie introuvable.";
            $this->redirigerVers('/structure/categories-clients');
            return;
        }
        $this->rendrePage('structure', 'categories_clients/formulaire', [
            'titre'       => 'Modifier la catégorie',
            'categorie'   => $categorie,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    // Mise à jour (POST)
    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['code', 'libelle', 'remise_pct', 'description']);
        $donnees['remise_pct'] = !empty($donnees['remise_pct']) ? (float)$donnees['remise_pct'] : 0;
        
        $this->modele->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Catégorie modifiée.";
        $this->redirigerVers('/structure/categories-clients');
    }

    /**
     * Supprime une catégorie et archive TOUS ses clients
     */
    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        
        // Récupérer la catégorie
        $categorie = $this->modele->trouverParId($id);
        
        // Récupérer TOUS les clients de cette catégorie
        $sqlClients = "SELECT * FROM clients WHERE categorie_id = :id";
        $clients = \Database::query($sqlClients, [':id' => $id])->fetchAll();
        
        // Récupérer juste les IDs des clients
        $clientsIds = array_column($clients, 'id');
        
        // Formater les IDs pour l'archivage (chaque ID dans son propre tableau)
        $clientsIdsFormatted = [];
        foreach ($clientsIds as $clientId) {
            $clientsIdsFormatted[] = ['id' => $clientId];
        }
        
        // Archiver la catégorie avec les IDs des clients
        Corbeille::archiver(
            'categories_clients',
            $categorie,
            ['clients_ids' => $clientsIdsFormatted],
            $categorie['libelle']
        );
        
        // Supprimer la catégorie (les clients auront categorie_id = NULL)
        $this->modele->supprimer($id);
        
        $_SESSION['succes'] = "Catégorie et ses " . count($clients) . " client(s) supprimés et déplacés vers la corbeille.";
        $this->redirigerVers('/structure/categories-clients');
    }

    // Détail d'une catégorie
    public function detail(int $id): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $categorie = $this->modele->trouverParId($id);
        if (empty($categorie)) {
            $_SESSION['erreur'] = "Catégorie introuvable.";
            $this->redirigerVers('/structure/categories-clients');
            return;
        }
        $this->rendrePage('structure', 'categories_clients/detail', [
            'titre'       => 'Détail catégorie',
            'categorie'   => $categorie,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }
}