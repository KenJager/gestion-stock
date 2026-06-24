<?php
// modules/ventes/Controllers/CommandeClientController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/CommandeClient.php';
require_once ROOT . '/modules/ventes/services/VenteService.php';
require_once ROOT . '/modules/ventes/pdf/BonCommandeClientPDF.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\CommandeClient;
use ventes\services\VenteService;
use ventes\pdf\BonCommandeClientPDF;

class CommandeClientController extends Controller {

    private CommandeClient $modele;
    private VenteService   $service;

    public function __construct() {
        parent::__construct(); // → Auth::verifierConnexion() déjà appelé ici
        $this->modele  = new CommandeClient();
        $this->service = new VenteService();
    }

    // GET /ventes/commandes
    public function index(): void {
        $this->rendrePage('ventes', 'commandes/liste', [
            'titre'      => 'Commandes clients',
            'moduleActif'=> 'ventes',
            'commandes'  => $this->modele->listerTout(),
        ]);
    }

    // GET /ventes/commandes/creer
    public function creer(): void {
        // 🔥 Récupérer les vrais clients et produits de la base de données
        $clients  = $this->service->listerClientsActifs();
        $produits = $this->service->listerProduitsActifs();
        
        $this->rendrePage('ventes', 'commandes/form', [
            'titre'      => 'Nouvelle commande client',
            'moduleActif'=> 'ventes',
            'commande'   => null,
            'clients'    => $clients,
            'produits'   => $produits,
        ]);
    }

    // POST /ventes/commandes/creer
    public function enregistrer(): void {
        try {
            $post           = $this->nettoyerPost(['client_id', 'date_livraison_prevue', 'remise_pct', 'notes']);
            $post['lignes'] = $this->extraireLignes();

            if (empty($post['lignes'])) {
                throw new \RuntimeException("La commande doit avoir au moins une ligne.");
            }

            $id = $this->service->enregistrerCommande($post);
            $this->redirigerVers("/ventes/commandes/{$id}");
        } catch (\RuntimeException $e) {
            // 🔥 Recharger les données en cas d'erreur
            $clients  = $this->service->listerClientsActifs();
            $produits = $this->service->listerProduitsActifs();
            
            $this->rendrePage('ventes', 'commandes/form', [
                'titre'      => 'Nouvelle commande client',
                'moduleActif'=> 'ventes',
                'commande'   => null,
                'clients'    => $clients,
                'produits'   => $produits,
                'erreur'     => $e->getMessage(),
            ]);
        }
    }

    // GET /ventes/commandes/{id}
    public function detail(int $id): void {
        try {
            $this->rendrePage('ventes', 'commandes/detail', [
                'titre'      => 'Commande client',
                'moduleActif'=> 'ventes',
                'commande'   => $this->modele->trouverParId($id),
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // GET /ventes/commandes/{id}/modifier
    public function editer(int $id): void {
        try {
            // 🔥 Récupérer les vrais clients et produits de la base de données
            $clients  = $this->service->listerClientsActifs();
            $produits = $this->service->listerProduitsActifs();
            
            $this->rendrePage('ventes', 'commandes/form', [
                'titre'      => 'Modifier la commande',
                'moduleActif'=> 'ventes',
                'commande'   => $this->modele->trouverParId($id),
                'clients'    => $clients,
                'produits'   => $produits,
            ]);
        } catch (\RuntimeException $e) {
            $this->erreur404($e->getMessage());
        }
    }

    // POST /ventes/commandes/{id}/modifier
    public function mettreAJour(int $id): void {
        try {
            $post           = $this->nettoyerPost(['client_id', 'date_livraison_prevue', 'remise_pct', 'notes']);
            $post['lignes'] = $this->extraireLignes();
            $this->service->modifierCommande($id, $post);
            $this->redirigerVers("/ventes/commandes/{$id}");
        } catch (\RuntimeException $e) {
            // 🔥 Recharger les données en cas d'erreur
            $clients  = $this->service->listerClientsActifs();
            $produits = $this->service->listerProduitsActifs();
            
            $this->rendrePage('ventes', 'commandes/form', [
                'titre'      => 'Modifier la commande',
                'moduleActif'=> 'ventes',
                'commande'   => $this->modele->trouverParId($id),
                'clients'    => $clients,
                'produits'   => $produits,
                'erreur'     => $e->getMessage(),
            ]);
        }
    }

    // POST /ventes/commandes/{id}/supprimer
    public function supprimer(int $id): void {
        $commande = $this->modele->trouverParId($id);

        if ($commande['statut'] !== 'EN_ATTENTE') {
            $this->redirigerVers("/ventes/commandes/{$id}");
            return;
        }

        // 1. Archiver en corbeille (fichier XML créé AVANT toute suppression BD)
        \Corbeille::archiver(
            'bons_commande_client',
            $commande,
            ['lignes_commande_client' => $commande['lignes']],
            $commande['numero'] . ' — ' . $commande['client_nom']
        );

        // 2. Supprimer en base (ON DELETE CASCADE supprime les lignes liées)
        $this->modele->supprimer($id);

        $this->redirigerVers('/ventes/commandes');
    }

    // GET /ventes/commandes/{id}/pdf
    public function pdf(int $id): void {
        $commande = $this->modele->trouverParId($id);
        $pdf = new BonCommandeClientPDF($commande);
        $pdf->Output('I', 'BonCommande_' . $commande['numero'] . '.pdf');
        exit;
    }

    // ── Helpers ──────────────────────────────────────────────────────

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