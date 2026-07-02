<?php
// modules/ventes/Controllers/CorbeilleVentesController.php

namespace ventes\traitements\controllers;
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/traitements/models/CommandeClient.php';
require_once ROOT . '/modules/ventes/traitements/models/LivraisonClient.php';
require_once ROOT . '/modules/ventes/traitements/models/FactureClient.php';
require_once ROOT . '/modules/ventes/traitements/models/VenteComptant.php';
require_once ROOT . '/modules/ventes/traitements/models/BonSortie.php';

use Controller;
use Auth;
use Corbeille;
use ventes\traitements\models\CommandeClient;
use ventes\traitements\models\LivraisonClient;
use ventes\traitements\models\FactureClient;
use ventes\traitements\models\VenteComptant;
use ventes\traitements\models\BonSortie;

/**
 * Gère la corbeille pour les 5 types de documents du module ventes :
 *   bons_commande_client, bons_livraison_client, factures_client,
 *   ventes_comptant, bons_sortie.
 *
 * Repose sur \Corbeille (core/Corbeille.php) qui archive/lit les fichiers XML.
 * Ce controller ajoute la logique de RESTAURATION propre à chaque table,
 * que Corbeille.php ne connaît pas (il est générique).
 */
class CorbeilleVentesController extends Controller {

    /** Tables du module ventes éligibles à la corbeille */
    private const TABLES = [
        'bons_commande_client',
        'bons_livraison_client',
        'factures_client',
        'ventes_comptant',
        'bons_sortie',
    ];

    private CommandeClient  $modCommande;
    private LivraisonClient $modLivraison;
    private FactureClient   $modFacture;
    private VenteComptant   $modComptant;
    private BonSortie       $modSortie;

    public function __construct() {
        parent::__construct();
        $this->modCommande  = new CommandeClient();
        $this->modLivraison = new LivraisonClient();
        $this->modFacture   = new FactureClient();
        $this->modComptant  = new VenteComptant();
        $this->modSortie    = new BonSortie();
    }

    // GET /ventes/corbeille
    public function index(): void {
        Auth::verifierDroit('VENTE_LIRE');
        $elements = [];
        foreach (self::TABLES as $table) {
            foreach (\Corbeille::lister($table) as $item) {
                $elements[] = $item;
            }
        }
        usort($elements, fn($a, $b) => strcmp($b['date_suppression'], $a['date_suppression']));

        $this->rendrePage('ventes', 'corbeille/liste', [
            'titre'      => 'Corbeille — ventes',
            'moduleActif'=> 'ventes',
            'elements'   => $elements,
        ]);
    }

    // POST /ventes/corbeille/restaurer
    public function restaurer(): void {
        Auth::verifierDroit('VENTE_CREER');
        $fichier = $_POST['fichier'] ?? '';
        try {
            if (!$fichier) {
                throw new \RuntimeException("Fichier corbeille manquant.");
            }

            $contenu = \Corbeille::charger($fichier);
            $table   = $contenu['table'];

            if (!in_array($table, self::TABLES, true)) {
                throw new \RuntimeException("Cet élément n'appartient pas au module ventes.");
            }

            \Database::debuterTransaction();

            switch ($table) {
                case 'bons_commande_client':
                    $this->modCommande->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_commande_client'] ?? []
                    );
                    break;

                case 'bons_livraison_client':
                    $this->modLivraison->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_livraison_client'] ?? []
                    );
                    break;

                case 'factures_client':
                    $this->modFacture->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_facture_client'] ?? [],
                        $contenu['liees']['reglements_client'] ?? []
                    );
                    break;

                case 'ventes_comptant':
                    $this->modComptant->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_vente_comptant'] ?? []
                    );
                    break;

                case 'bons_sortie':
                    $this->modSortie->restaurer(
                        $contenu['principal'],
                        $contenu['liees']['lignes_bon_sortie'] ?? []
                    );
                    break;
            }

            \Database::validerTransaction();

            // Une fois restauré en base, le fichier XML n'a plus lieu d'être
            \Corbeille::supprimerFichier($fichier);

            $this->redirigerVers('/ventes/corbeille');
        } catch (\Exception $e) {
            \Database::annulerTransaction();
            $this->rendrePage('ventes', 'corbeille/liste', [
                'titre'      => 'Corbeille — ventes',
                'moduleActif'=> 'ventes',
                'elements'   => $this->listerTousElements(),
                'erreur'     => "Restauration impossible : " . $e->getMessage(),
            ]);
        }
    }

    // POST /ventes/corbeille/purger
    public function purger(): void {
        Auth::verifierDroit('VENTE_SUPPRIMER');
        $fichier = $_POST['fichier'] ?? '';
        if ($fichier) {
            \Corbeille::supprimerFichier($fichier);
        }
        $this->redirigerVers('/ventes/corbeille');
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
