<?php
// modules/ventes/Controllers/VenteComptantController.php

namespace ventes\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/traitements/models/VenteComptant.php';
require_once ROOT . '/modules/ventes/traitements/services/VenteService.php';
require_once ROOT . '/modules/ventes/traitements/services/StockService.php';
require_once ROOT . '/modules/ventes/traitements/pdf/VenteComptantPDF.php';

use Controller;
use Auth;
use Corbeille;
use ventes\traitements\models\VenteComptant;
use ventes\traitements\services\VenteService;
use ventes\traitements\services\StockService;
use ventes\traitements\pdf\VenteComptantPDF;

class VenteComptantController extends Controller {

    private VenteComptant $modele;
    private VenteService  $service;
    private StockService  $stock;

    public function __construct() {
        parent::__construct();
        $this->modele  = new VenteComptant();
        $this->service = new VenteService();
        $this->stock   = new StockService();
    }

    // GET /ventes/comptant
    public function index(): void {
        Auth::verifierDroit('VENTE_LIRE');
        $this->rendrePage('ventes', 'comptant/liste', [
            'titre'      => 'Ventes comptant',
            'moduleActif'=> 'ventes',
            'ventes'     => $this->modele->listerTout(),
        ]);
    }

    // GET /ventes/comptant/creer
    public function creer(): void {
        Auth::verifierDroit('VENTE_CREER');
        $clients  = $this->service->listerClientsActifs();
        $produits = $this->service->listerProduitsActifs();
        
        $this->rendrePage('ventes', 'comptant/form', [
            'titre'      => 'Nouvelle vente comptant',
            'moduleActif'=> 'ventes',
            'clients'    => $clients,
            'produits'   => $produits,
        ]);
    }

    // POST /ventes/comptant/creer
    public function enregistrer(): void {
        Auth::verifierDroit('VENTE_CREER');
        try {
            $post           = $this->nettoyerPost(['client_id', 'remise_montant', 'montant_percu', 'mode_paiement', 'notes']);
            $post['lignes'] = $this->extraireLignes();

            if (empty($post['lignes'])) {
                throw new \RuntimeException("La vente doit contenir au moins un produit.");
            }
            if ((float) ($post['montant_percu'] ?? 0) <= 0) {
                throw new \RuntimeException("Le montant perçu doit être renseigné.");
            }

            $id = $this->service->venteComptant($post);
            $this->redirigerVers("/ventes/comptant/{$id}/pdf");
        } catch (\RuntimeException $e) {
            // 🔥 Recharger les données en cas d'erreur
            $clients  = $this->service->listerClientsActifs();
            $produits = $this->service->listerProduitsActifs();
            
            $this->rendrePage('ventes', 'comptant/form', [
                'titre'      => 'Nouvelle vente comptant',
                'moduleActif'=> 'ventes',
                'clients'    => $clients,
                'produits'   => $produits,
                'erreur'     => $e->getMessage(),
            ]);
        }
    }

    // GET /ventes/comptant/{id}/pdf
    public function pdf(int $id): void {
        $vente = $this->modele->trouverParId($id);
        $pdf   = new VenteComptantPDF($vente);
        $pdf->Output('I', 'VenteComptant_' . $vente['numero'] . '.pdf');
        exit;
    }

    // POST /ventes/comptant/{id}/supprimer
    public function supprimer(int $id): void {
        Auth::verifierDroit('VENTE_SUPPRIMER');
        $vente = $this->modele->trouverParId($id);

        try {
            \Database::debuterTransaction();

            \Corbeille::archiver(
                'ventes_comptant',
                $vente,
                ['lignes_vente_comptant' => $vente['lignes']],
                $vente['numero'] . ' — ' . $vente['client_nom']
            );

            // Restituer le stock vendu
            foreach ($vente['lignes'] as $l) {
                $this->stock->entrer(
                    (int) $l['produit_id'],
                    (float) $l['quantite'],
                    'ANNULATION_VENTE_COMPTANT',
                    $id
                );
            }

            $this->modele->supprimer($id);

            \Database::validerTransaction();
            $this->redirigerVers('/ventes/comptant');
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $this->redirigerVers('/ventes/comptant');
        }
    }

    private function extraireLignes(): array {
        $lignes = [];
        $ids    = $_POST['produit_id']       ?? [];
        $qtes   = $_POST['quantite']          ?? [];
        $prix   = $_POST['prix_unitaire_ht']  ?? [];
        $tvas   = $_POST['tva']               ?? [];
        $rems   = $_POST['remise_pct']        ?? [];

        foreach ($ids as $i => $pid) {
            $pid = (int) $pid;
            $qte = (float) ($qtes[$i] ?? 0);
            $pu  = (float) ($prix[$i] ?? 0);
            if (!$pid || $qte <= 0 || $pu <= 0) continue;
            $lignes[] = [
                'produit_id'       => $pid,
                'quantite'         => $qte,
                'prix_unitaire_ht' => $pu,
                'tva'              => (float) ($tvas[$i] ?? 0),
                'remise_pct'       => (float) ($rems[$i] ?? 0),
            ];
        }
        return $lignes;
    }
}