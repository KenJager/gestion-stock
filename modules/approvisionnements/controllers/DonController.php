<?php
// modules/approvisionnements/controllers/DonController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/approvisionnements/services/DonService.php';
require_once ROOT . '/modules/structure/models/ProduitModel.php';

use Controller;
use Auth;
use approvisionnements\services\DonService;
use ProduitModel;

class DonController extends Controller {

    private DonService $service;
    private ProduitModel $produitModel;

    public function __construct() {
        parent::__construct();
        $this->service        = new DonService();
        $this->produitModel   = new ProduitModel();
    }

    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $debut = $_GET['debut'] ?? date('Y-m-01');
        $fin   = $_GET['fin']   ?? date('Y-m-d');
        $dons  = $this->service->listerParPeriode($debut, $fin);
        $this->rendrePage('approvisionnements', 'dons/liste', [
            'titre'        => 'Entrées de dons',
            'dons'         => $dons,
            'debut'        => $debut,
            'fin'          => $fin,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $produits = $this->produitModel->listerTout();
        $this->rendrePage('approvisionnements', 'dons/formulaire', [
            'titre'        => 'Nouvelle entrée de don',
            'produits'     => $produits,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['numero', 'donateur', 'notes']);
        $donnees['utilisateur_id'] = $this->utilisateurCourant['id'];

        $lignes = [];
        foreach (($_POST['produit_id'] ?? []) as $i => $produitId) {
            $qte = (float)($_POST['quantite'][$i] ?? 0);
            if (empty($produitId) || $qte <= 0) continue;
            $lignes[] = [
                'produit_id'      => (int)$produitId,
                'quantite'        => $qte,
                'valeur_estimee'  => (float)($_POST['valeur_estimee'][$i] ?? 0) ?: null,
                'date_peremption' => $_POST['date_peremption'][$i] ?: null,
            ];
        }

        if (empty($donnees['numero']) || empty($lignes)) {
            $_SESSION['erreur'] = "Numéro et au moins une ligne sont obligatoires.";
            $this->redirigerVers('/appros/dons/creer');
            return;
        }

        $donnees['lignes'] = $lignes;

        try {
            $donId = $this->service->enregistrer($donnees);
            $_SESSION['succes'] = "Don enregistré avec succès.";
            $this->redirigerVers('/appros/dons');
        } catch (\Exception $e) {
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
            $this->redirigerVers('/appros/dons/creer');
        }
    }

    public function pdf(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $sql = "SELECT * FROM entrees_dons WHERE id = :id";
            $don = \Database::query($sql, [':id' => $id])->fetch();
            if (!$don) throw new \RuntimeException("Don introuvable.");

            $sqlL = "SELECT le.*, p.code, p.designation, p.unite
                     FROM lignes_entree_don le
                     JOIN produits p ON p.id = le.produit_id
                     WHERE le.entree_don_id = :id";
            $lignes = \Database::query($sqlL, [':id' => $id])->fetchAll();

            $this->rendreVueSeule('modules/approvisionnements/views/dons/pdf.php', [
                'don'    => $don,
                'lignes' => $lignes,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/dons');
        }
    }
}