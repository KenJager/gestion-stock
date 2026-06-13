<?php
// modules/structure/controllers/ProduitController.php

namespace structure\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/structure/models/ProduitModel.php';
require_once ROOT . '/modules/structure/models/FamilleModel.php';
require_once ROOT . '/modules/structure/services/ProduitService.php';

use Controller;
use Auth;
use Corbeille;
use ProduitModel;
use FamilleModel;
use structure\services\ProduitService;

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
        $familles     = $this->familleModel->listerTout();
        $produitsPeres = $this->produitModel->listerProduitsPeres();
        $this->rendrePage('structure', 'produits/formulaire', [
            'titre'        => 'Nouveau produit',
            'produit'      => null,
            'familles'     => $familles,
            'produitsPeres'=> $produitsPeres,
            'etape'        => (int)($_GET['etape'] ?? 1),
            'moduleActif'  => 'structure',
            'moduleAssets' => 'structure',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('STRUCTURE_CREER');
        $this->verifierMethode('POST');

        if (isset($_POST['etape']) && (int)$_POST['etape'] === 1) {
            $donnees = $this->nettoyerPost([
                'code', 'designation', 'famille_id', 'unite', 'est_pere'
            ]);
            if (empty($donnees['code']) || empty($donnees['designation']) || empty($donnees['famille_id'])) {
                $_SESSION['erreur'] = "Code, désignation et famille sont obligatoires.";
                $this->redirigerVers('/structure/produits/creer');
                return;
            }
            if ($this->produitModel->existeParCode($donnees['code'])) {
                $_SESSION['erreur'] = "Ce code produit est déjà utilisé.";
                $this->redirigerVers('/structure/produits/creer');
                return;
            }
            $_SESSION['produit_etape1'] = $donnees;

            $familles      = $this->familleModel->listerTout();
            $produitsPeres = $this->produitModel->listerProduitsPeres();
            $this->rendrePage('structure', 'produits/formulaire', [
                'titre'        => 'Nouveau produit — Étape 2 / 2',
                'produit'      => null,
                'familles'     => $familles,
                'produitsPeres'=> $produitsPeres,
                'etape'        => 2,
                'etape1'       => $donnees,
                'moduleActif'  => 'structure',
                'moduleAssets' => 'structure',
            ]);
            return;
        }

        $etape1 = $_SESSION['produit_etape1'] ?? [];
        if (empty($etape1)) {
            $_SESSION['erreur'] = "Session expirée. Veuillez recommencer.";
            $this->redirigerVers('/structure/produits/creer');
            return;
        }

        $etape2 = $this->nettoyerPost([
            'prix_achat_ht', 'prix_vente_ht', 'tva',
            'stock_actuel', 'stock_minimum', 'date_peremption', 'actif',
        ]);

        $donnees = array_merge($etape1, $etape2);
        
        // ✅ Récupérer les données du formulaire (produit_pere_id et quantite_fils)
        $produitPereId = isset($_POST['produit_pere_id']) && !empty($_POST['produit_pere_id']) 
                        ? (int)$_POST['produit_pere_id'] 
                        : null;
        $quantiteFils = isset($_POST['quantite_fils']) && !empty($_POST['quantite_fils']) 
                    ? (float)$_POST['quantite_fils'] 
                    : 1;
        
        // Créer le produit
        $nouveauId = $this->produitModel->creer($donnees);
        
        // ✅ Si c'est un produit fils (produit_pere_id sélectionné et pas un père)
        $estFils = $produitPereId !== null && ($donnees['est_pere'] != 1);
        
        if ($estFils) {
            // Créer l'entrée dans fractions_produit
            $sqlFraction = "INSERT INTO fractions_produit (produit_pere_id, produit_fils_id, quantite_fils)
                            VALUES (:pere_id, :fils_id, :quantite)";
            \Database::query($sqlFraction, [
                ':pere_id' => $produitPereId,
                ':fils_id' => $nouveauId,
                ':quantite' => $quantiteFils,
            ]);
        }
        
        unset($_SESSION['produit_etape1']);
        $_SESSION['succes'] = "Produit créé avec succès.";
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
        $familles      = $this->familleModel->listerTout();
        $produitsPeres = $this->produitModel->listerProduitsPeres();
        $this->rendrePage('structure', 'produits/formulaire', [
            'titre'        => 'Modifier le produit',
            'produit'      => $produit,
            'familles'     => $familles,
            'produitsPeres'=> $produitsPeres,
            'etape'        => null,
            'moduleActif'  => 'structure',
            'moduleAssets' => 'structure',
        ]);
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('STRUCTURE_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost([
            'code', 'designation', 'famille_id', 'unite',
            'prix_achat_ht', 'prix_vente_ht', 'tva',
            'stock_actuel', 'stock_minimum', 'date_peremption', 'actif',
        ]);
        $this->produitModel->mettreAJour($id, $donnees);
        $_SESSION['succes'] = "Produit modifié.";
        $this->redirigerVers('/structure/produits');
    }

    // ── Suppression avec archivage en corbeille (père + fils) ───────────────
    public function supprimer(int $id): void {
        Auth::verifierDroit('STRUCTURE_SUPPRIMER');
        $this->verifierMethode('POST');

        $produit = $this->produitModel->trouverParId($id);
        if (!$produit) {
            $_SESSION['erreur'] = "Produit introuvable.";
            $this->redirigerVers('/structure/produits');
            return;
        }

        $liees = [];

        // Récupérer les produits fils (via fractions_produit)
        $fils = $this->produitModel->listerFilsParProduitPere($id);
        
        if (!empty($fils)) {
            $liees['produits_fils'] = [];
            
            foreach ($fils as $filsProduit) {
                // Archiver le produit fils
                Corbeille::archiver(
                    'produits',
                    $filsProduit,
                    [],
                    $filsProduit['code'] . ' — ' . $filsProduit['designation']
                );
                $liees['produits_fils'][] = $filsProduit;
            }
        }

        // Récupérer les fractions du produit père
        $sqlFractions = "SELECT * FROM fractions_produit WHERE produit_pere_id = :id";
        $fractions = \Database::query($sqlFractions, [':id' => $id])->fetchAll();
        if (!empty($fractions)) {
            $liees['fractions_produit'] = $fractions;
        }

        // Archiver le produit père
        Corbeille::archiver(
            'produits',
            $produit,
            $liees,
            $produit['code'] . ' — ' . $produit['designation']
        );

        // Supprimer les produits fils
        if (!empty($fils)) {
            foreach ($fils as $filsProduit) {
                $this->produitModel->supprimer($filsProduit['id']);
            }
        }

        // Supprimer le produit père
        $this->produitModel->supprimer($id);
        
        $_SESSION['succes'] = "Produit père et ses " . count($fils) . " fils supprimés et archivés.";
        $this->redirigerVers('/structure/produits');
    }

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
        } catch (\Exception $e) {
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
        }
        $this->redirigerVers('/structure/produits');
    }

    public function detail(string|int $id): void {
        Auth::verifierDroit('STRUCTURE_LIRE');
        $id = (int)$id;
        $produit = $this->produitModel->trouverParId($id);
        if (!$produit) {
            $_SESSION['erreur'] = "Produit introuvable.";
            $this->redirigerVers('/structure/produits');
            return;
        }
        $fractions = [];
        if ($produit['est_pere']) {
            $fractions = $this->produitModel->listerFilsParProduitPere($id);
        }
        $this->rendrePage('structure', 'produits/detail', [
            'titre'       => 'Détail produit',
            'produit'     => $produit,
            'fractions'   => $fractions,
            'moduleActif' => 'structure',
            'moduleAssets'=> 'structure',
        ]);
    }
}