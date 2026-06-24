<?php
// modules/ventes/Controllers/EtatController.php

namespace ventes\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/ventes/models/FactureClient.php';
require_once ROOT . '/modules/ventes/models/VenteComptant.php';

use Controller;
use Auth;
use Corbeille;
use ventes\models\FactureClient;
use ventes\models\VenteComptant;

class EtatController extends Controller {

    private FactureClient $modeleFacture;
    private VenteComptant $modeleComptant;

    public function __construct() {
        parent::__construct();
        $this->modeleFacture  = new FactureClient();
        $this->modeleComptant = new VenteComptant();
    }

    // GET /ventes/etats/journalier
    public function journalier(): void {
        $date = filter_input(INPUT_GET, 'date') ?? date('Y-m-d');
        $this->rendrePage('ventes', 'etats/journalier', [
            'titre'          => 'État des ventes — ' . date('d/m/Y', strtotime($date)),
            'moduleActif'    => 'ventes',
            'date'           => $date,
            'factures'       => $this->modeleFacture->etatJournalier($date),
            'ventesComptant' => $this->modeleComptant->etatJournalier($date),
        ]);
    }

    // GET /ventes/etats/annuel
    public function annuel(): void {
        $annee = filter_input(INPUT_GET, 'annee', FILTER_VALIDATE_INT) ?? (int) date('Y');
        $this->rendrePage('ventes', 'etats/annuel', [
            'titre'      => "État annuel des ventes — {$annee}",
            'moduleActif'=> 'ventes',
            'annee'      => $annee,
            'lignes'     => $this->modeleFacture->etatAnnuel($annee),
        ]);
    }
}
