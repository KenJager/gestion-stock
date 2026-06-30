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

    // GET /ventes/etats/journalier/pdf
    public function journalierPdf(): void {
        $date     = filter_input(INPUT_GET, 'date') ?? date('Y-m-d');
        $factures = $this->modeleFacture->etatJournalier($date);
        $comptant = $this->modeleComptant->etatJournalier($date);

        require_once ROOT . '/vendor/autoload.php';
        if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
            class_alias('Fpdf\\Fpdf', 'FPDF');
        }

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, $this->utf('ÉTAT DES VENTES — ' . date('d/m/Y', strtotime($date))), 0, 1, 'C');
        $pdf->Ln(4);

        // Factures
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, $this->utf('Factures clients'), 0, 1);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(40, 6, 'N°',            1, 0);
        $pdf->Cell(50, 6, 'Client',        1, 0);
        $pdf->Cell(25, 6, 'Total HT',      1, 0, 'R');
        $pdf->Cell(25, 6, 'TVA',           1, 0, 'R');
        $pdf->Cell(25, 6, 'Total TTC',     1, 0, 'R');
        $pdf->Cell(15, 6, $this->utf('Statut'), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 7);
        $totHT = 0; $totTVA = 0; $totTTC = 0;
        foreach ($factures as $f) {
            $totHT  += (float) $f['montant_ht'];
            $totTVA += (float) $f['montant_tva'];
            $totTTC += (float) $f['montant_ttc'];
            $pdf->Cell(40, 5, $this->utf($f['numero']),      1, 0);
            $pdf->Cell(50, 5, $this->utf($f['client_nom']),  1, 0);
            $pdf->Cell(25, 5, number_format((float)$f['montant_ht'],  0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(25, 5, number_format((float)$f['montant_tva'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(25, 5, number_format((float)$f['montant_ttc'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(15, 5, $this->utf($f['statut']),      1, 1, 'C');
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(90, 6, 'TOTAUX', 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($totHT,  0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($totTVA, 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($totTTC, 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(15, 6, '', 1, 1);

        // Ventes comptant
        if (!empty($comptant)) {
            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 6, $this->utf('Ventes comptant'), 0, 1);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(40, 6, 'N°',            1, 0);
            $pdf->Cell(55, 6, 'Client',        1, 0);
            $pdf->Cell(30, 6, 'Total TTC',     1, 0, 'R');
            $pdf->Cell(25, 6, $this->utf('Montant perçu'), 1, 0, 'R');
            $pdf->Cell(30, 6, 'Mode',          1, 1, 'C');

            $pdf->SetFont('Arial', '', 7);
            foreach ($comptant as $v) {
                $pdf->Cell(40, 5, $this->utf($v['numero']),         1, 0);
                $pdf->Cell(55, 5, $this->utf($v['client_nom']),     1, 0);
                $pdf->Cell(30, 5, number_format((float)$v['montant_ttc'],  0, ',', ' '), 1, 0, 'R');
                $pdf->Cell(25, 5, number_format((float)$v['montant_percu'],0, ',', ' '), 1, 0, 'R');
                $pdf->Cell(30, 5, $this->utf($v['mode_paiement'] ?? ''),   1, 1, 'C');
            }
        }

        $pdf->Output('I', 'etat_ventes_journalier_' . $date . '.pdf');
        exit;
    }

    // GET /ventes/etats/annuel/pdf
    public function annuelPdf(): void {
        $annee  = filter_input(INPUT_GET, 'annee', FILTER_VALIDATE_INT) ?? (int) date('Y');
        $lignes = $this->modeleFacture->etatAnnuel($annee);

        require_once ROOT . '/vendor/autoload.php';
        if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
            class_alias('Fpdf\\Fpdf', 'FPDF');
        }

        $nomsMois = ['', 'Janvier','Février','Mars','Avril','Mai','Juin',
                         'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, $this->utf("ÉTAT ANNUEL DES VENTES — {$annee}"), 0, 1, 'C');
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 7, 'Mois',          1, 0);
        $pdf->Cell(25, 7, 'Nb factures',   1, 0, 'C');
        $pdf->Cell(30, 7, 'Total HT',      1, 0, 'R');
        $pdf->Cell(30, 7, 'TVA',           1, 0, 'R');
        $pdf->Cell(30, 7, 'Total TTC',     1, 0, 'R');
        $pdf->Cell(25, 7, $this->utf('Réglé'), 1, 0, 'R');
        $pdf->Cell(25, 7, 'Solde',         1, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        $totNb = 0; $totHT = 0; $totTVA = 0; $totTTC = 0; $totRegle = 0; $totSolde = 0;
        foreach ($lignes as $l) {
            $mois = (int) $l['mois'];
            $totNb    += (int)   $l['nb_factures'];
            $totHT    += (float) $l['total_ht'];
            $totTVA   += (float) $l['total_tva'];
            $totTTC   += (float) $l['total_ttc'];
            $totRegle += (float) $l['total_regle'];
            $totSolde += (float) $l['total_solde'];
            $pdf->Cell(30, 7, $this->utf($nomsMois[$mois] ?? $mois), 1, 0);
            $pdf->Cell(25, 7, (int) $l['nb_factures'],                                     1, 0, 'C');
            $pdf->Cell(30, 7, number_format((float)$l['total_ht'],    0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(30, 7, number_format((float)$l['total_tva'],   0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(30, 7, number_format((float)$l['total_ttc'],   0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(25, 7, number_format((float)$l['total_regle'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(25, 7, number_format((float)$l['total_solde'], 0, ',', ' '), 1, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 7, 'TOTAUX', 1, 0, 'R');
        $pdf->Cell(25, 7, $totNb,                                     1, 0, 'C');
        $pdf->Cell(30, 7, number_format($totHT,    0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totTVA,   0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totTTC,   0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(25, 7, number_format($totRegle, 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(25, 7, number_format($totSolde, 0, ',', ' '), 1, 1, 'R');

        $pdf->Output('I', 'etat_ventes_annuel_' . $annee . '.pdf');
        exit;
    }

    private function utf(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
}
