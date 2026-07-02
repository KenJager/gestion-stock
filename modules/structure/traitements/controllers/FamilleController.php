<?php
// modules/structure/controllers/FamilleController.php

namespace structure\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/structure/traitements/models/FamilleModel.php';

use Controller;
use Auth;
use Corbeille;
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

    /**
     * Supprime une famille et archive TOUS les produits liés
     */
    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        
        $famille = $this->modele->trouverParId($id);
        if (!$famille) {
            $_SESSION['erreur'] = "Famille introuvable.";
            $this->redirigerVers('/structure/familles');
            return;
        }

        // Requête adaptée : on passe par fractions_produit pour récupérer le code du père si le produit est un fils
        $sqlProduits = "SELECT p.*, pp.code AS produit_pere_code 
                        FROM produits p
                        LEFT JOIN fractions_produit fp ON fp.produit_fils_id = p.id
                        LEFT JOIN produits pp ON pp.id = fp.produit_pere_id
                        WHERE p.famille_id = :id";
        $produits = \Database::query($sqlProduits, [':id' => $id])->fetchAll();
        
        // Archiver la famille avec les produits
        Corbeille::archiver(
            'familles_produits',
            $famille,
            ['produits' => $produits],
            $famille['libelle']
        );
        
        // La suppression en base se fait avec ON DELETE CASCADE
        $this->modele->supprimer($id);
        
        $_SESSION['succes'] = "Famille et ses " . count($produits) . " produit(s) supprimés et déplacés vers la corbeille.";
        $this->redirigerVers('/structure/familles');
    }

    public function detail(int $id): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $famille = $this->modele->trouverParId($id);
        if (!$famille) {
            $_SESSION['erreur'] = "Famille introuvable.";
            $this->redirigerVers('/structure/familles');
            return;
        }
        $this->rendrePage('structure', 'familles/detail', [
            'titre'       => 'Détail famille',
            'famille'     => $famille,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }
}