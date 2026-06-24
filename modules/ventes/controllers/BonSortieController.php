<?php
// modules/ventes/controllers/BonSortieController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/BonSortie.php';
require_once ROOT . '/modules/ventes/services/VenteService.php';
require_once ROOT . '/modules/ventes/services/StockService.php';
require_once ROOT . '/modules/ventes/pdf/BonSortiePDF.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\BonSortie;
use ventes\services\VenteService;
use ventes\services\StockService;
use ventes\pdf\BonSortiePDF;

class BonSortieController extends Controller {

    private BonSortie    $modele;
    private VenteService $service;
    private StockService $stock;

    public function __construct() {
        parent::__construct();
        $this->modele  = new BonSortie();
        $this->service = new VenteService();
        $this->stock   = new StockService();
    }

    // GET /ventes/sorties
    public function index(): void {
        $this->rendrePage('ventes', 'sorties/liste', [
            'titre'      => 'Bons de sortie',
            'moduleActif'=> 'ventes',
            'sorties'    => $this->modele->listerTout(),
        ]);
    }

    // GET /ventes/sorties/creer
    public function creer(): void {
        $this->rendrePage('ventes', 'sorties/form', [
            'titre'      => 'Nouveau bon de sortie',
            'moduleActif'=> 'ventes',
            'sortie'     => null,
        ]);
    }

    // POST /ventes/sorties/creer
    public function enregistrer(): void {
        try {
            $post           = $this->nettoyerPost(['motif', 'notes']);
            $post['lignes'] = $this->extraireLignes();

            if (empty($post['lignes'])) {
                throw new \RuntimeException("Le bon de sortie doit contenir au moins une ligne.");
            }

            $id = $this->service->sortirProduits($post);
            $this->redirigerVers("/ventes/sorties/{$id}/pdf");
        } catch (\RuntimeException $e) {
            $this->rendrePage('ventes', 'sorties/form', [
                'titre'      => 'Nouveau bon de sortie',
                'moduleActif'=> 'ventes',
                'sortie'     => null,
                'erreur'     => $e->getMessage(),
            ]);
        }
    }

    // GET /ventes/sorties/{id}/modifier
    public function editer(int $id): void {
        try {
            $this->rendrePage('ventes', 'sorties/form', [
                'titre'      => 'Modifier le bon de sortie',
                'moduleActif'=> 'ventes',
                'sortie'     => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // POST /ventes/sorties/{id}/modifier
    public function mettreAJour(int $id): void {
        $d = $this->nettoyerPost(['motif', 'notes']);
        $this->modele->modifier($id, [':motif' => $d['motif'], ':notes' => $d['notes']]);
        $this->redirigerVers("/ventes/sorties/{$id}/pdf");
    }

    // GET /ventes/sorties/{id}/pdf
    public function pdf(int $id): void {
        $sortie = $this->modele->trouverParId($id);
        $pdf    = new BonSortiePDF($sortie);
        $pdf->Output('I', 'BonSortie_' . $sortie['numero'] . '.pdf');
        exit;
    }

    // POST /ventes/sorties/{id}/supprimer
    public function supprimer(int $id): void {
        $sortie = $this->modele->trouverParId($id);

        try {
            \Database::debuterTransaction();

            \Corbeille::archiver(
                'bons_sortie',
                $sortie,
                ['lignes_bon_sortie' => $sortie['lignes']],
                $sortie['numero'] . ' — ' . $sortie['motif']
            );

            // Annuler la sortie = remettre les produits en stock
            foreach ($sortie['lignes'] as $l) {
                $this->stock->entrer(
                    (int) $l['produit_id'],
                    (float) $l['quantite'],
                    'ANNULATION_SORTIE',
                    $id
                );
            }

            $this->modele->supprimer($id);

            \Database::validerTransaction();
            $this->redirigerVers('/ventes/sorties');
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $this->redirigerVers('/ventes/sorties');
        }
    }

    private function extraireLignes(): array {
        $lignes = [];
        $ids    = $_POST['produit_id']   ?? [];
        $qtes   = $_POST['quantite']      ?? [];
        $prix   = $_POST['prix_unitaire'] ?? [];
        $obs    = $_POST['observations']  ?? [];

        foreach ($ids as $i => $pid) {
            $pid = (int) $pid;
            $qte = (float) ($qtes[$i] ?? 0);
            if (!$pid || $qte <= 0) continue;
            $lignes[] = [
                'produit_id'    => $pid,
                'quantite'      => $qte,
                'prix_unitaire' => (float) ($prix[$i] ?? 0),
                'observations'  => $obs[$i] ?? null,
            ];
        }
        return $lignes;
    }
}
