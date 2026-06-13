<?php
// modules/structure/controllers/ProduitController.php

namespace structure\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/structure/models/ProduitModel.php';
require_once ROOT . '/modules/structure/models/FamilleModel.php';
require_once ROOT . '/modules/structure/services/ProduitService.php';

use Controller;
use Auth;
use ProduitModel;
use FamilleModel;
use ProduitService;

class ProduitController extends Controller
{
    private ProduitModel $produitModel;
    private FamilleModel $familleModel;
    private ProduitService $produitService;

    public function __construct() {
        Auth::verifierConnexion();
        $this->produitModel   = new ProduitModel();
        $this->familleModel   = new FamilleModel();
        $this->produitService = new ProduitService();
    }

    public function index(): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $produits = $this->produitModel->listerAvecFamille();
        $this->rendrePage('structure', 'produits/liste', [
            'titre'       => 'Produits',
            'produits'    => $produits,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $familles = $this->familleModel->listerTout();
        $this->rendrePage('structure', 'produits/formulaire', [
            'titre'       => 'Nouveau produit',
            'produit'     => null,
            'familles'    => $familles,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'designation', 'famille_id', 'unite',
            'prix_achat_ht', 'prix_vente_ht', 'tva',
            'stock_actuel', 'stock_minimum', 'date_peremption', 'actif'
        ]);
        if (empty($donnees['code']) || empty($donnees['designation']) || empty($donnees['famille_id'])) {
            $_SESSION['erreur'] = "Code, désignation et famille sont obligatoires.";
            $this->redirigerVers('/structure/produits/creer');
            return;
        }
        $this->produitModel->creer($donnees);
        $_SESSION['succes'] = "Produit créé.";
        $this->redirigerVers('/structure/produits');
    }

    public function editer(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $produit = $this->produitModel->trouverParId($id);
        if (!$produit) {
            $_SESSION['erreur'] = "Produit introuvable.";
            $this->redirigerVers('/structure/produits');
            return;
        }
        $familles = $this->familleModel->listerTout();
        $this->rendrePage('structure', 'produits/formulaire', [
            'titre'       => 'Modifier le produit',
            'produit'     => $produit,
            'familles'    => $familles,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'designation', 'famille_id', 'unite',
            'prix_achat_ht', 'prix_vente_ht', 'tva',
            'stock_actuel', 'stock_minimum', 'date_peremption', 'actif'
        ]);
        $this->produitModel->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Produit modifié.";
        $this->redirigerVers('/structure/produits');
    }

    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');
        $this->produitModel->supprimer($id);
        $_SESSION['succes'] = "Produit supprimé.";
        $this->redirigerVers('/structure/produits');
    }

    // Afficher le formulaire de fractionnement d'un produit père
    public function fractionner(int $id): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $produitPere = $this->produitModel->trouverParId($id);
        if (!$produitPere || !$produitPere['est_pere']) {
            $_SESSION['erreur'] = "Ce produit n'est pas un produit père fractionnable.";
            $this->redirigerVers('/structure/produits');
            return;
        }
        $familles = $this->familleModel->listerTout();
        $this->rendrePage('structure', 'produits/fractionnement', [
            'titre'       => 'Fractionner le produit : ' . htmlspecialchars($produitPere['designation']),
            'produitPere' => $produitPere,
            'familles'    => $familles,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }

    public function enregistrerFractionnement(int $id): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');
        $produitPere = $this->produitModel->trouverParId($id);
        if (!$produitPere || !$produitPere['est_pere']) {
            $_SESSION['erreur'] = "Produit père invalide.";
            $this->redirigerVers('/structure/produits');
            return;
        }

        // Récupérer les lignes de fils depuis POST (tableau dynamique)
        $fils = [];
        $index = 0;
        while (isset($_POST["fils_code_{$index}"])) {
            $fils[] = [
                'code'          => $_POST["fils_code_{$index}"],
                'designation'   => $_POST["fils_designation_{$index}"],
                'unite'         => $_POST["fils_unite_{$index}"],
                'prix_vente_ht' => $_POST["fils_prix_vente_ht_{$index}"] ?? 0,
                'quantite_fils' => $_POST["fils_quantite_{$index}"] ?? 1,
                'famille_id'    => $produitPere['famille_id'],
            ];
            $index++;
        }

        if (empty($fils)) {
            $_SESSION['erreur'] = "Vous devez ajouter au moins un produit fils.";
            $this->redirigerVers("/structure/produits/fractionner/{$id}");
            return;
        }

        try {
            $this->produitService->creerProduitAvecFractions([
                'code'        => $produitPere['code'],
                'designation' => $produitPere['designation'],
                'famille_id'  => $produitPere['famille_id'],
                'est_pere'    => true,
                'unite'       => $produitPere['unite'],
                'tva'         => $produitPere['tva'],
            ], $fils);
            $_SESSION['succes'] = "Fractionnement enregistré avec succès.";
        } catch (Exception $e) {
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
        }
        $this->redirigerVers('/structure/produits');
    }
}