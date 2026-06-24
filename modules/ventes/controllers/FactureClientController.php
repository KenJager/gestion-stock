<?php
// modules/ventes/Controllers/FactureClientController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/FactureClient.php';
require_once ROOT . '/modules/ventes/models/LivraisonClient.php';
require_once ROOT . '/modules/ventes/services/VenteService.php';
require_once ROOT . '/modules/ventes/pdf/FactureClientPDF.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\FactureClient;
use ventes\models\LivraisonClient;
use ventes\services\VenteService;
use ventes\pdf\FactureClientPDF;

class FactureClientController extends Controller {

    private FactureClient   $modele;
    private LivraisonClient $modeleLivraison;
    private VenteService    $service;

    public function __construct() {
        parent::__construct();
        $this->modele          = new FactureClient();
        $this->modeleLivraison = new LivraisonClient();
        $this->service         = new VenteService();
    }

    // GET /ventes/factures
    public function index(): void {
        $this->rendrePage('ventes', 'factures/liste', [
            'titre'      => 'Factures clients',
            'moduleActif'=> 'ventes',
            'factures'   => $this->modele->listerTout(),
        ]);
    }

    // GET /ventes/factures/{id}
    public function detail(int $id): void {
        try {
            $this->rendrePage('ventes', 'factures/detail', [
                'titre'      => 'Facture client',
                'moduleActif'=> 'ventes',
                'facture'    => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // GET /ventes/factures/{id}/modifier
    public function editer(int $id): void {
        try {
            $this->rendrePage('ventes', 'factures/edit_form', [
                'titre'      => 'Modifier la facture',
                'moduleActif'=> 'ventes',
                'facture'    => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // POST /ventes/factures/{id}/modifier
    public function mettreAJour(int $id): void {
        $d = $this->nettoyerPost(['date_echeance', 'remise_montant', 'notes']);
        $this->modele->modifier($id, [
            ':date_echeance'  => $d['date_echeance'] ?: null,
            ':remise_montant' => (float) $d['remise_montant'],
            ':notes'          => $d['notes'],
        ]);
        $this->redirigerVers("/ventes/factures/{$id}");
    }

    // POST /ventes/factures/generer  (appelé depuis livraison/detail)
    public function generer(): void {
        $livraisonId = (int) ($_POST['livraison_id'] ?? 0);
        try {
            if (!$livraisonId) {
                throw new \RuntimeException("Bon de livraison manquant.");
            }
            $post = $this->nettoyerPost(['type_vente', 'date_echeance', 'remise_montant', 'notes']);
            $id   = $this->service->facturer($livraisonId, $post);
            $this->redirigerVers("/ventes/factures/{$id}");
        } catch (\RuntimeException $e) {
            $this->redirigerVers("/ventes/livraisons/{$livraisonId}");
        }
    }

    // GET /ventes/factures/{id}/pdf
    public function pdf(int $id): void {
        $facture = $this->modele->trouverParId($id);
        $pdf = new FactureClientPDF($facture);
        $pdf->Output('I', 'Facture_' . $facture['numero'] . '.pdf');
        exit;
    }

    // POST /ventes/factures/{id}/supprimer
    public function supprimer(int $id): void {
        $facture = $this->modele->trouverParId($id);

        // On n'autorise la suppression que si aucun règlement n'a été encaissé,
        // pour éviter de perdre la trace d'argent réellement reçu.
        if ((float) $facture['montant_regle'] > 0) {
            $this->redirigerVers("/ventes/factures/{$id}");
            return;
        }

        \Corbeille::archiver(
            'factures_client',
            $facture,
            [
                'lignes_facture_client' => $facture['lignes'],
                'reglements_client'     => $facture['reglements'],
            ],
            $facture['numero'] . ' — ' . $facture['client_nom']
        );

        $this->modele->supprimer($id);
        $this->redirigerVers('/ventes/factures');
    }
}
