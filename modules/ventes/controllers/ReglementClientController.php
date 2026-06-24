<?php
// modules/ventes/Controllers/ReglementClientController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/ReglementClient.php';
require_once ROOT . '/modules/ventes/models/FactureClient.php';
require_once ROOT . '/modules/ventes/services/VenteService.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\ReglementClient;
use ventes\models\FactureClient;
use ventes\services\VenteService;

class ReglementClientController extends Controller {

    private ReglementClient $modele;
    private FactureClient   $modeleFacture;
    private VenteService    $service;

    public function __construct() {
        parent::__construct();
        $this->modele        = new ReglementClient();
        $this->modeleFacture = new FactureClient();
        $this->service       = new VenteService();
    }

    // GET /ventes/reglements
    public function index(): void {
        $this->rendrePage('ventes', 'reglements/liste', [
            'titre'      => 'Règlements clients',
            'moduleActif'=> 'ventes',
            'reglements' => $this->modele->listerTout(),
        ]);
    }

    // POST /ventes/reglements/creer
    public function enregistrer(): void {
        $factureId = (int) ($_POST['facture_id'] ?? 0);
        try {
            if (!$factureId) {
                throw new \RuntimeException("Facture manquante.");
            }
            $post = $this->nettoyerPost(['montant', 'mode_paiement', 'reference', 'banque_id', 'notes']);
            $this->service->reglerFacture($factureId, $post);
            $this->redirigerVers("/ventes/factures/{$factureId}");
        } catch (\RuntimeException $e) {
            try {
                $facture = $this->modeleFacture->trouverParId($factureId);
                $this->rendrePage('ventes', 'factures/detail', [
                    'titre'      => 'Facture client',
                    'moduleActif'=> 'ventes',
                    'facture'    => $facture,
                    'erreur'     => $e->getMessage(),
                ]);
            } catch (\RuntimeException $e2) {
                $this->erreur404($e2->getMessage());
            }
        }
    }
}
