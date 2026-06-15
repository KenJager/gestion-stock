<?php
// modules/approvisionnements/controllers/BonReceptionController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/approvisionnements/services/BonReceptionService.php';

use Controller;
use Auth;
use approvisionnements\services\BonReceptionService;

class BonReceptionController extends Controller {

    private BonReceptionService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new BonReceptionService();
    }

    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $receptions = $this->service->listerToutes();
        $this->rendrePage('approvisionnements', 'receptions/liste', [
            'titre'        => 'Bons de réception',
            'receptions'   => $receptions,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function creer(): void {
        Auth::verifierDroit('APPRO_CREER');
        // Lister les bons de commande VALIDÉ ou REÇU_PARTIEL
        $commandesEligibles = $this->service->listerCommandesEligibles();
        $this->rendrePage('approvisionnements', 'receptions/formulaire', [
            'titre'              => 'Nouveau bon de réception',
            'commandesEligibles' => $commandesEligibles,
            'moduleActif'        => 'approvisionnements',
            'moduleAssets'       => 'approvisionnements',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['bon_commande_id', 'date_reception', 'notes']);
        $donnees['utilisateur_id'] = $this->utilisateurCourant['id'];

        // Récupérer les quantités reçues par ligne
        $lignesRecues = [];
        foreach (($_POST['ligne_id'] ?? []) as $i => $ligneId) {
            $qte = (float)($_POST['quantite_recue'][$i] ?? 0);
            if ($qte > 0) {
                $lignesRecues[] = [
                    'ligne_commande_id' => (int)$ligneId,
                    'quantite_recue'    => $qte,
                ];
            }
        }

        if (empty($donnees['bon_commande_id']) || empty($lignesRecues)) {
            $_SESSION['erreur'] = "Veuillez sélectionner une commande et saisir au moins une quantité reçue.";
            $this->redirigerVers('/appros/receptions/creer');
            return;
        }

        try {
            $receptionId = $this->service->enregistrer($donnees, $lignesRecues);
            $_SESSION['succes'] = "Bon de réception enregistré.";
            $this->redirigerVers('/appros/receptions/' . $receptionId);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = "Erreur : " . $e->getMessage();
            $this->redirigerVers('/appros/receptions/creer');
        }
    }

    public function detail(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $reception = $this->service->trouverParId($id);
            $lignes    = $this->service->listerLignes($id);
            $this->rendrePage('approvisionnements', 'receptions/detail', [
                'titre'       => 'Bon de réception ' . $reception['numero'],
                'reception'   => $reception,
                'lignes'      => $lignes,
                'moduleActif' => 'approvisionnements',
                'moduleAssets'=> 'approvisionnements',
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/receptions');
        }
    }

    public function pdf(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $reception = $this->service->trouverParId($id);
            $lignes    = $this->service->listerLignes($id);
            $this->rendreVueSeule('modules/approvisionnements/views/receptions/pdf.php', [
                'reception' => $reception,
                'lignes'    => $lignes,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/receptions');
        }
    }
}