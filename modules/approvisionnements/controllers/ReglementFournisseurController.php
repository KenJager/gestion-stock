<?php
// modules/approvisionnements/controllers/ReglementFournisseurController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/approvisionnements/services/ReglementFournisseurService.php';


use Controller;
use Auth;
use approvisionnements\services\ReglementFournisseurService;
use approvisionnements\models\BanqueModel;

class ReglementFournisseurController extends Controller {

    private ReglementFournisseurService $service;
    private BanqueModel $banqueModel;

    public function __construct() {
        parent::__construct();
        $this->service     = new ReglementFournisseurService();
        $this->banqueModel = new BanqueModel();
    }

    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $fournisseurId = (int)($_GET['fournisseur_id'] ?? 0);
        $reglements = $fournisseurId
            ? $this->service->listerHistoriqueParFournisseur($fournisseurId)
            : [];

        $sql = "SELECT * FROM fournisseurs WHERE actif = true ORDER BY raison_sociale";
        $fournisseurs = \Database::query($sql)->fetchAll();

        $this->rendrePage('approvisionnements', 'reglements/liste', [
            'titre'            => 'Règlements fournisseurs',
            'reglements'       => $reglements,
            'fournisseurs'     => $fournisseurs,
            'fournisseurId'    => $fournisseurId,
            'moduleActif'      => 'approvisionnements',
            'moduleAssets'     => 'approvisionnements',
        ]);
    }

    public function enregistrer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost([
            'facture_id', 'date_reglement', 'montant',
            'mode_paiement', 'reference', 'notes'
        ]);
        $donnees['banque_id']      = !empty($_POST['banque_id']) ? (int)$_POST['banque_id'] : null;
        $donnees['utilisateur_id'] = $this->utilisateurCourant['id'];

        try {
            $reglementId = $this->service->enregistrer($donnees);
            $_SESSION['succes'] = "Règlement enregistré.";
            $this->redirigerVers('/appros/reglements/' . $reglementId . '/recu');
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $factureId = (int)$donnees['facture_id'];
            $this->redirigerVers('/appros/factures/' . $factureId);
        }
    }

    public function recu(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $sql = "SELECT rf.*, ff.numero AS facture_numero,
                           f.raison_sociale AS fournisseur_nom,
                           b.nom AS banque_nom,
                           u.nom || ' ' || u.prenom AS utilisateur
                    FROM reglements_fournisseur rf
                    JOIN factures_fournisseur ff ON ff.id = rf.facture_id
                    JOIN fournisseurs f ON f.id = ff.fournisseur_id
                    LEFT JOIN banques b ON b.id = rf.banque_id
                    LEFT JOIN utilisateurs u ON u.id = rf.utilisateur_id
                    WHERE rf.id = :id";
            $recu = \Database::query($sql, [':id' => $id])->fetch();
            if (!$recu) throw new \RuntimeException("Règlement introuvable.");

            $this->rendreVueSeule('modules/approvisionnements/views/reglements/recu.php', [
                'recu' => $recu,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/reglements');
        }
    }
}