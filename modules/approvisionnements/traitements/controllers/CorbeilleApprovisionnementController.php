<?php
// modules/approvisionnements/controllers/CorbeilleApprovisionnementController.php

namespace approvisionnements\traitements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/approvisionnements/traitements/models/BonCommande.php';
require_once ROOT . '/modules/approvisionnements/traitements/models/BonReception.php';
require_once ROOT . '/modules/approvisionnements/traitements/models/FactureFournisseur.php';
require_once ROOT . '/modules/approvisionnements/traitements/models/Don.php';
require_once ROOT . '/modules/approvisionnements/traitements/models/ReglementFournisseur.php';

use Controller;
use approvisionnements\traitements\models\BonCommande;
use approvisionnements\traitements\models\BonReception;
use approvisionnements\traitements\models\FactureFournisseur;
use approvisionnements\traitements\models\Don;
use approvisionnements\traitements\models\ReglementFournisseur;

class CorbeilleApprovisionnementController extends Controller {

    private const TABLES = [
        'bons_commande_fournisseur',
        'bons_reception',
        'factures_fournisseur',
        'entrees_dons',
        'reglements_fournisseur',
    ];

    private BonCommande       $modCommande;
    private BonReception      $modReception;
    private FactureFournisseur $modFacture;
    private Don               $modDon;
    private ReglementFournisseur $modReglement;

    public function __construct() {
        parent::__construct();
        $this->modCommande  = new BonCommande();
        $this->modReception = new BonReception();
        $this->modFacture   = new FactureFournisseur();
        $this->modDon       = new Don();
        $this->modReglement = new ReglementFournisseur();
    }

    // GET /appros/corbeille
    public function index(): void {
        \Auth::verifierDroit('APPRO_LIRE');
        $elements = $this->listerTousElements();

        $this->rendrePage('approvisionnements', 'corbeille_liste', [
            'titre'      => 'Corbeille — Approvisionnements',
            'moduleActif'=> 'approvisionnements',
            'elements'   => $elements,
        ]);
    }

    // POST /appros/corbeille/restaurer
    public function restaurer(): void {
        \Auth::verifierDroit('APPRO_CREER');
        $fichier = $_POST['fichier'] ?? '';
        try {
            if (!$fichier) {
                throw new \RuntimeException('Fichier corbeille manquant.');
            }

            $contenu = \Corbeille::charger($fichier);
            $table   = $contenu['table'];

            if (!in_array($table, self::TABLES, true)) {
                throw new \RuntimeException("Cet élément n'appartient pas au module approvisionnements.");
            }

            \Database::debuterTransaction();

            switch ($table) {
                case 'bons_commande_fournisseur':
                    $this->modCommande->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_commande_fournisseur'] ?? []
                    );
                    break;

                case 'bons_reception':
                    $this->modReception->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_reception'] ?? []
                    );
                    break;

                case 'factures_fournisseur':
                    $this->modFacture->restaurer($contenu['principal']);
                    break;

                case 'entrees_dons':
                    $this->modDon->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_entree_don'] ?? []
                    );
                    break;

                case 'reglements_fournisseur':
                    $this->modReglement->restaurer($contenu['principal']);
                    $this->modFacture->mettreAJourMontantRegle((int) $contenu['principal']['facture_id']);
                    break;
            }

            \Database::validerTransaction();
            \Corbeille::supprimerFichier($fichier);

            $_SESSION['success'] = 'Élément restauré avec succès.';
            $this->redirigerVers('/appros/corbeille');

        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $_SESSION['error'] = 'Restauration impossible : ' . $e->getMessage();
            $this->redirigerVers('/appros/corbeille');
        }
    }

    // POST /appros/corbeille/purger
    public function purger(): void {
        \Auth::verifierDroit('APPRO_SUPPRIMER');
        $fichier = $_POST['fichier'] ?? '';
        if ($fichier) {
            \Corbeille::supprimerFichier($fichier);
        }
        $this->redirigerVers('/appros/corbeille');
    }

    private function listerTousElements(): array {
        $elements = [];
        foreach (self::TABLES as $table) {
            foreach (\Corbeille::lister($table) as $item) {
                $elements[] = $item;
            }
        }
        usort($elements, fn($a, $b) => strcmp($b['date_suppression'], $a['date_suppression']));
        return $elements;
    }
}
