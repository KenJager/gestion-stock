<?php
// modules/ventes/Controllers/LivraisonController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/LivraisonClient.php';
require_once ROOT . '/modules/ventes/models/CommandeClient.php';
require_once ROOT . '/modules/ventes/services/VenteService.php';
require_once ROOT . '/modules/ventes/services/StockService.php';
require_once ROOT . '/modules/ventes/pdf/BonLivraisonClientPDF.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\LivraisonClient;
use ventes\models\CommandeClient;
use ventes\services\VenteService;
use ventes\services\StockService;
use ventes\pdf\BonLivraisonClientPDF;

class LivraisonController extends Controller {

    private LivraisonClient $modele;
    private CommandeClient  $modeleCommande;
    private VenteService    $service;
    private StockService    $stock;

    public function __construct() {
        parent::__construct();
        $this->modele         = new LivraisonClient();
        $this->modeleCommande = new CommandeClient();
        $this->service        = new VenteService();
        $this->stock          = new StockService();
    }

    // GET /ventes/livraisons
    public function index(): void {
        $this->rendrePage('ventes', 'livraisons/liste', [
            'titre'      => 'Bons de livraison',
            'moduleActif'=> 'ventes',
            'livraisons' => $this->modele->listerTout(),
        ]);
    }

    // GET /ventes/livraisons/creer  (?commande_id=X)
    public function creer(): void {
        $commandeId = filter_input(INPUT_GET, 'commande_id', FILTER_VALIDATE_INT);
        $commande   = $commandeId ? $this->modeleCommande->trouverParId($commandeId) : null;
        $this->rendrePage('ventes', 'livraisons/form', [
            'titre'      => 'Nouvelle livraison',
            'moduleActif'=> 'ventes',
            'commande'   => $commande,
        ]);
    }

    // POST /ventes/livraisons/creer
    public function enregistrer(): void {
        $commandeId = (int) ($_POST['commande_id'] ?? 0);
        try {
            if (!$commandeId) {
                throw new \RuntimeException("Commande manquante.");
            }
            $lignes = $this->extraireLignesLivraison();
            if (empty($lignes)) {
                throw new \RuntimeException("Aucune quantité saisie.");
            }
            $id = $this->service->livrerProduits($commandeId, $lignes);
            $this->redirigerVers("/ventes/livraisons/{$id}");
        } catch (\RuntimeException $e) {
            $commande = $commandeId ? $this->modeleCommande->trouverParId($commandeId) : null;
            $this->rendrePage('ventes', 'livraisons/form', [
                'titre'      => 'Nouvelle livraison',
                'moduleActif'=> 'ventes',
                'commande'   => $commande,
                'erreur'     => $e->getMessage(),
            ]);
        }
    }

    // GET /ventes/livraisons/{id}
    public function detail(int $id): void {
        try {
            $this->rendrePage('ventes', 'livraisons/detail', [
                'titre'      => 'Bon de livraison',
                'moduleActif'=> 'ventes',
                'livraison'  => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // GET /ventes/livraisons/{id}/modifier
    public function editer(int $id): void {
        try {
            $this->rendrePage('ventes', 'livraisons/edit_form', [
                'titre'      => 'Modifier la livraison',
                'moduleActif'=> 'ventes',
                'livraison'  => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // POST /ventes/livraisons/{id}/modifier
    public function mettreAJour(int $id): void {
        $donnees = $this->nettoyerPost(['date_livraison', 'notes']);
        $this->modele->modifier($id, [
            ':date_livraison' => $donnees['date_livraison'],
            ':notes'          => $donnees['notes'],
        ]);
        $this->redirigerVers("/ventes/livraisons/{$id}");
    }

    // GET /ventes/livraisons/{id}/pdf
    public function pdf(int $id): void {
        $livraison = $this->modele->trouverParId($id);
        $pdf = new BonLivraisonClientPDF($livraison);
        $pdf->Output('I', 'BonLivraison_' . $livraison['numero'] . '.pdf');
        exit;
    }

    // POST /ventes/livraisons/{id}/supprimer
    public function supprimer(int $id): void {
        $livraison = $this->modele->trouverParId($id);

        try {
            \Database::debuterTransaction();

            // 1. Archiver en corbeille avant toute modification
            \Corbeille::archiver(
                'bons_livraison_client',
                $livraison,
                ['lignes_livraison_client' => $livraison['lignes']],
                $livraison['numero'] . ' — ' . $livraison['client_nom']
            );

            // 2. Restituer le stock sorti lors de cette livraison
            foreach ($livraison['lignes'] as $l) {
                $this->stock->entrer(
                    (int) $l['produit_id'],
                    (float) $l['quantite_livree'],
                    'ANNULATION_LIVRAISON',
                    $id
                );
            }

            // 3. Réajuster la quantité livrée sur la commande d'origine, si elle existe encore
            if (!empty($livraison['bon_commande_id'])) {
                $sqlLignesCmd = \Database::query(
                    "SELECT id, produit_id FROM lignes_commande_client WHERE bon_commande_id = :id",
                    [':id' => $livraison['bon_commande_id']]
                )->fetchAll();

                foreach ($livraison['lignes'] as $l) {
                    foreach ($sqlLignesCmd as $lc) {
                        if ((int) $lc['produit_id'] === (int) $l['produit_id']) {
                            $this->modeleCommande->mettreAJourQuantiteLivree(
                                (int) $lc['id'],
                                -(float) $l['quantite_livree']
                            );
                            break;
                        }
                    }
                }
            }

            // 3. Supprimer la livraison (cascade sur ses lignes)
            $this->modele->supprimer($id);

            \Database::validerTransaction();
            $this->redirigerVers('/ventes/livraisons');
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $this->redirigerVers("/ventes/livraisons/{$id}");
        }
    }

    private function extraireLignesLivraison(): array {
        $lignes   = [];
        $ligneIds = $_POST['ligne_id']        ?? [];
        $prodIds  = $_POST['produit_id']       ?? [];
        $qtes     = $_POST['quantite_livree']  ?? [];
        $prix     = $_POST['prix_unitaire_ht'] ?? [];
        $tvas     = $_POST['tva']              ?? [];
        $rems     = $_POST['remise_pct']       ?? [];

        foreach ($ligneIds as $i => $lid) {
            $qte = (float) ($qtes[$i] ?? 0);
            if ($qte <= 0) continue;
            $lignes[] = [
                'ligne_id'         => (int) $lid,
                'produit_id'       => (int) ($prodIds[$i] ?? 0),
                'quantite_livree'  => $qte,
                'prix_unitaire_ht' => (float) ($prix[$i] ?? 0),
                'tva'              => (float) ($tvas[$i] ?? 0),
                'remise_pct'       => (float) ($rems[$i] ?? 0),
            ];
        }
        return $lignes;
    }
}
