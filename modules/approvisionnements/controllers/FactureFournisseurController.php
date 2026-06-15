<?php
// modules/approvisionnements/controllers/FactureFournisseurController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/approvisionnements/services/FactureFournisseurService.php';

use Controller;
use Auth;
use approvisionnements\services\FactureFournisseurService;

class FactureFournisseurController extends Controller {

    private FactureFournisseurService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new FactureFournisseurService();
    }

    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $factures = $this->service->listerToutes();
        $this->rendrePage('approvisionnements', 'factures/liste', [
            'titre'        => 'Factures fournisseurs',
            'factures'     => $factures,
            'moduleActif'  => 'approvisionnements',
            'moduleAssets' => 'approvisionnements',
        ]);
    }

    public function detail(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $sql = "SELECT ff.*, f.raison_sociale AS fournisseur_nom
                    FROM factures_fournisseur ff
                    JOIN fournisseurs f ON f.id = ff.fournisseur_id
                    WHERE ff.id = :id";
            $facture = \Database::query($sql, [':id' => $id])->fetch();
            if (!$facture) throw new \RuntimeException("Facture introuvable.");

            $sqlReg = "SELECT * FROM reglements_fournisseur WHERE facture_id = :id ORDER BY date_reglement DESC";
            $reglements = \Database::query($sqlReg, [':id' => $id])->fetchAll();

            $this->rendrePage('approvisionnements', 'factures/detail', [
                'titre'        => 'Facture ' . $facture['numero'],
                'facture'      => $facture,
                'reglements'   => $reglements,
                'moduleActif'  => 'approvisionnements',
                'moduleAssets' => 'approvisionnements',
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/factures');
        }
    }

    public function editer(string|int $id): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $id = (int)$id;
        try {
            $sql = "SELECT * FROM factures_fournisseur WHERE id = :id";
            $facture = \Database::query($sql, [':id' => $id])->fetch();
            if (!$facture) throw new \RuntimeException("Facture introuvable.");

            $this->rendrePage('approvisionnements', 'factures/formulaire', [
                'titre'        => 'Modifier la facture ' . $facture['numero'],
                'facture'      => $facture,
                'moduleActif'  => 'approvisionnements',
                'moduleAssets' => 'approvisionnements',
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/factures');
        }
    }

    public function mettreAJour(int $id): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $this->verifierMethode('POST');

        $donnees = $this->nettoyerPost(['date_echeance', 'notes']);
        try {
            $sql = "UPDATE factures_fournisseur
                    SET date_echeance = :de, notes = :notes
                    WHERE id = :id";
            \Database::query($sql, [
                ':de'    => $donnees['date_echeance'] ?: null,
                ':notes' => $donnees['notes'],
                ':id'    => $id,
            ]);
            $_SESSION['succes'] = "Facture mise à jour.";
            $this->redirigerVers('/appros/factures/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/factures/' . $id . '/modifier');
        }
    }

    public function pdf(string|int $id): void {
        Auth::verifierDroit('APPRO_LIRE');
        $id = (int)$id;
        try {
            $sql = "SELECT ff.*, f.raison_sociale AS fournisseur_nom,
                           f.adresse AS fournisseur_adresse, f.telephone AS fournisseur_tel
                    FROM factures_fournisseur ff
                    JOIN fournisseurs f ON f.id = ff.fournisseur_id
                    WHERE ff.id = :id";
            $facture = \Database::query($sql, [':id' => $id])->fetch();
            if (!$facture) throw new \RuntimeException("Facture introuvable.");

            $this->rendreVueSeule('modules/approvisionnements/views/factures/pdf.php', [
                'facture' => $facture,
            ]);
        } catch (\Exception $e) {
            $_SESSION['erreur'] = $e->getMessage();
            $this->redirigerVers('/appros/factures');
        }
    }
}