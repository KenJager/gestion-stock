<?php
// modules/approvisionnements/controllers/BonCommandeController.php

namespace approvisionnements\controllers;

use Controller;
use Auth;
use approvisionnements\services\BonCommandeService;
use approvisionnements\models\FournisseurModel;
use approvisionnements\models\ProduitModel;

class BonCommandeController extends Controller {

    private BonCommandeService $service;
    private FournisseurModel $fournisseurModel;
    private ProduitModel $produitModel;

    public function __construct() {
        parent::__construct();
        $this->service          = new BonCommandeService();
        $this->fournisseurModel = new FournisseurModel();
        $this->produitModel     = new ProduitModel();
    }

    /**
     * Liste tous les bons de commande fournisseur.
     */
    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $commandes = $this->service->listerTous();
        $this->rendrePage('approvisionnements', 'commandes/liste', [
            'titre'        => 'Bons de commande fournisseur',
            'commandes'    => $commandes,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    /**
     * Affiche le formulaire de création.
     */
    public function creer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $fournisseurs = $this->fournisseurModel->listerTout();
        $this->rendrePage('approvisionnements', 'commandes/formulaire', [
            'titre'        => 'Nouveau bon de commande',
            'commande'     => null,
            'lignes'       => [],
            'fournisseurs' => $fournisseurs,
            'mode'         => 'creation',
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    /**
     * Enregistre un nouveau bon de commande (entête + lignes).
     */
    public function enregistrer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost([
            'numero', 'fournisseur_id', 'date_commande',
            'date_livraison_prevue', 'notes'
        ]);

        if (empty($donnees['numero']) || empty($donnees['fournisseur_id']) || empty($donnees['date_commande'])) {
            $_SESSION['erreur'] = "Le numéro, le fournisseur et la date sont obligatoires.";
            $this->redirigerVers('/appros/commandes/creer');
            return;
        }

        $donnees['utilisateur_id'] = $this->utilisateurCourant['id'];

        \Database::debuterTransaction();
        try {
            $bonId = $this->service->creer($donnees);

            // Traitement des lignes (tableau indexé)
            $codes       = $_POST['produit_id']       ?? [];
            $quantites   = $_POST['quantite_commandee'] ?? [];
            $prixUnit    = $_POST['prix_unitaire_ht']  ?? [];
            $tvas        = $_POST['tva']               ?? [];

            if (!empty($codes)) {
                foreach ($codes as $i => $produitId) {
                    if (empty($produitId) || empty($quantites[$i])) continue;
                    $this->service->ajouterLigne([
                        'bon_commande_id'    => $bonId,
                        'produit_id'         => (int)$produitId,
                        'quantite_commandee' => (float)$quantites[$i],
                        'prix_unitaire_ht'   => (float)($prixUnit[$i] ?? 0),
                        'tva'                => (float)($tvas[$i] ?? 0),
                    ]);
                }
            }

            \Database::validerTransaction();
            $_SESSION['succes'] = "Bon de commande créé avec succès.";
            $this->redirigerVers('/appros/commandes/' . $bonId);
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
            $this->redirigerVers('/appros/commandes/creer');
        }
    }

    /**
     * Affiche le détail d'un bon de commande.
     */
    public function detail(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $commande = $this->service->trouverParId($id);
            $lignes   = $this->service->listerLignes($id);
            $this->rendrePage('approvisionnements', 'commandes/detail', [
                'titre'        => 'Bon de commande ' . $commande['numero'],
                'commande'     => $commande,
                'lignes'       => $lignes,
                'moduleActif'  => 'approvisionnements',
                'moduleAssets' => 'approvisionnements',
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/commandes');
        }
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function editer(string|int $id): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $id = (int)$id;
        try {
            $commande     = $this->service->trouverParId($id);
            $lignes       = $this->service->listerLignes($id);
            $fournisseurs = $this->fournisseurModel->listerTout();

            if ($commande['statut'] !== 'BROUILLON') {
                $_SESSION['erreur'] = "Seuls les bons en BROUILLON peuvent être modifiés.";
                $this->redirigerVers('/appros/commandes/' . $id);
                return;
            }

            $this->rendrePage('approvisionnements', 'commandes/formulaire', [
                'titre'        => 'Modifier le bon ' . $commande['numero'],
                'commande'     => $commande,
                'lignes'       => $lignes,
                'fournisseurs' => $fournisseurs,
                'mode'         => 'edition',
                'moduleActif'  => 'approvisionnements',
                'moduleAssets' => 'approvisionnements',
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/commandes');
        }
    }

    /**
     * Met à jour un bon de commande existant.
     */
    public function mettreAJour(int $id): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $this->verifierMethode('POST');

        try {
            $commande = $this->service->trouverParId($id);
            if ($commande['statut'] !== 'BROUILLON') {
                throw new \RuntimeException("Modification interdite sur un bon non brouillon.");
            }

            $donnees = $this->nettoyerPost([
                'fournisseur_id', 'date_commande',
                'date_livraison_prevue', 'notes'
            ]);

            // Mise à jour de l'entête (via requête directe simple)
            $sql = "UPDATE bons_commande_fournisseur
                    SET fournisseur_id = :fid, date_commande = :dc,
                        date_livraison_prevue = :dlp, notes = :notes
                    WHERE id = :id";
            \Database::query($sql, [
                ':fid'  => $donnees['fournisseur_id'],
                ':dc'   => $donnees['date_commande'],
                ':dlp'  => $donnees['date_livraison_prevue'] ?: null,
                ':notes'=> $donnees['notes'],
                ':id'   => $id,
            ]);

            // Supprimer les anciennes lignes et recréer
            $ancienLignes = $this->service->listerLignes($id);
            foreach ($ancienLignes as $l) {
                $this->service->supprimerLigneCommande($l['id']);
            }

            $codes     = $_POST['produit_id']         ?? [];
            $quantites = $_POST['quantite_commandee'] ?? [];
            $prixUnit  = $_POST['prix_unitaire_ht']   ?? [];
            $tvas      = $_POST['tva']                ?? [];

            foreach ($codes as $i => $produitId) {
                if (empty($produitId) || empty($quantites[$i])) continue;
                $this->service->ajouterLigne([
                    'bon_commande_id'    => $id,
                    'produit_id'         => (int)$produitId,
                    'quantite_commandee' => (float)$quantites[$i],
                    'prix_unitaire_ht'   => (float)($prixUnit[$i] ?? 0),
                    'tva'                => (float)($tvas[$i] ?? 0),
                ]);
            }

            $_SESSION['succes'] = "Bon de commande mis à jour.";
            $this->redirigerVers('/appros/commandes/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
            $this->redirigerVers('/appros/commandes/' . $id . '/modifier');
        }
    }

    /**
     * Supprime un bon de commande.
     */
    public function supprimer(string|int $id): void {
        Auth::verifierDroit('APPRO_SUPPRIMER');
        $id = (int)$id;
        try {
            $this->service->supprimerBonCommande($id);
            $_SESSION['succes'] = "Bon de commande supprimé.";
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
        }
        $this->redirigerVers('/appros/commandes');
    }

    /**
     * Génère le PDF du bon de commande.
     */
    public function pdf(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $commande = $this->service->trouverParId($id);
            $lignes   = $this->service->listerLignes($id);
            $this->rendreVueSeule('modules/approvisionnements/views/commandes/pdf.php', [
                'commande' => $commande,
                'lignes'   => $lignes,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/commandes');
        }
    }
}