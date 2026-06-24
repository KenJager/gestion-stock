<?php
// modules/Ventes/Pdf/VenteComptantPDF.php

namespace ventes\pdf;

// Prefer Composer autoload if available, otherwise fall back to the legacy path.
if (file_exists(ROOT . '/vendor/autoload.php')) {
    require_once ROOT . '/vendor/autoload.php';
} elseif (file_exists(ROOT . '/vendor/fpdf/fpdf.php')) {
    require_once ROOT . '/vendor/fpdf/fpdf.php';
} else {
    throw new \RuntimeException("FPDF library not found. Install it with: composer require fpdf/fpdf\nor place fpdf.php at vendor/fpdf/fpdf.php");
}

// Compatibility: some installations provide namespaced class `Fpdf\Fpdf`.
// Create the legacy global `FPDF` alias if it's not present.
if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
    class_alias('Fpdf\\Fpdf', 'FPDF');
}

/**
 * Reçu de vente comptant — format ticket de caisse A5.
 */
class VenteComptantPDF extends \FPDF {

    private array $vente;

    public function __construct(array $vente) {
        parent::__construct('P', 'mm', 'A5');
        $this->vente = $vente;
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 15);
        $this->AliasNbPages();
        $this->AddPage();
        $this->build();
    }

    public function Header(): void {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(17, 24, 39);
        $this->Cell(0, 8, iconv('UTF-8','ISO-8859-1//TRANSLIT', APP_NOM ?? 'VENTE COMPTANT'), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Reçu N° : ' . $this->vente['numero']), 0, 1, 'C');
        $this->Cell(0, 5, 'Date : ' . date('d/m/Y H:i', strtotime($this->vente['date_vente'])), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(226, 232, 240);
        $this->Line(10, $this->GetY() + 1, 138, $this->GetY() + 1);
        $this->Ln(4);
    }

    public function Footer(): void {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Merci de votre confiance !'), 0, 0, 'C');
    }

    private function build(): void {
        if ($this->vente['client_nom'] !== 'Client anonyme') {
            $this->SetFont('Arial', '', 9);
            $this->Cell(0, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Client : ' . $this->vente['client_nom']), 0, 1);
            $this->Ln(2);
        }

        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(17, 24, 39);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(65, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT','Article'), 0, 0, 'L', true);
        $this->Cell(20, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT','Qté'),     0, 0, 'C', true);
        $this->Cell(25, 6, 'PU HT',      0, 0, 'R', true);
        $this->Cell(28, 6, 'Montant HT', 0, 1, 'R', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);

        $pair = false; $totalHT = 0; $totalTVA = 0;
        foreach ($this->vente['lignes'] as $l) {
            $ht = (float) $l['quantite'] * (float) $l['prix_unitaire_ht'] * (1 - (float) ($l['remise_pct'] ?? 0) / 100);
            $totalHT  += $ht;
            $totalTVA += $ht * ((float) $l['tva'] / 100);

            $this->SetFillColor($pair ? 240 : 255, $pair ? 245 : 255, $pair ? 255 : 255);
            $this->Cell(65, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT', $l['designation']),      0, 0, 'L', $pair);
            $this->Cell(20, 6, number_format((float) $l['quantite'], 2, ',', ' '),            0, 0, 'C', $pair);
            $this->Cell(25, 6, number_format((float) $l['prix_unitaire_ht'], 0, ',', ' '),    0, 0, 'R', $pair);
            $this->Cell(28, 6, number_format($ht, 0, ',', ' '),                               0, 1, 'R', $pair);
            $pair = !$pair;
        }

        $this->Ln(2);
        $x = 80;
        $this->SetFont('Arial', '', 9);
        $this->SetX($x); $this->Cell(35, 5, 'Total HT',  0, 0); $this->Cell(23, 5, number_format($totalHT,  0,',', ' ') . ' FCFA', 0, 1, 'R');
        $this->SetX($x); $this->Cell(35, 5, 'Total TVA', 0, 0); $this->Cell(23, 5, number_format($totalTVA, 0,',', ' ') . ' FCFA', 0, 1, 'R');

        $remise = (float) $this->vente['remise_montant'];
        if ($remise > 0) {
            $this->SetX($x); $this->Cell(35, 5, 'Remise', 0, 0);
            $this->Cell(23, 5, '- ' . number_format($remise, 0,',', ' ') . ' FCFA', 0, 1, 'R');
        }

        $net = (float) $this->vente['montant_ttc'] - $remise;
        $this->SetFillColor(17, 24, 39); $this->SetTextColor(255,255,255); $this->SetFont('Arial','B',10);
        $this->SetX($x);
        $this->Cell(35, 8, iconv('UTF-8','ISO-8859-1//TRANSLIT','NET À PAYER'), 0, 0, 'L', true);
        $this->Cell(23, 8, number_format($net, 0,',', ' ') . ' FCFA', 0, 1, 'R', true);
        $this->SetTextColor(0,0,0); $this->SetFont('Arial','',9);

        $this->Ln(3);
        $this->SetX($x); $this->Cell(35, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Montant perçu'),  0, 0);
        $this->Cell(23, 5, number_format((float) $this->vente['montant_percu'], 0,',', ' ') . ' FCFA', 0, 1, 'R');
        $this->SetX($x); $this->Cell(35, 5, 'Monnaie rendue', 0, 0);
        $this->Cell(23, 5, number_format((float) $this->vente['monnaie_rendue'], 0,',', ' ') . ' FCFA', 0, 1, 'R');

        if (!empty($this->vente['mode_paiement'])) {
            $this->Ln(2);
            $this->SetX($x);
            $this->Cell(58, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Mode : ' . $this->vente['mode_paiement']), 0, 1);
        }
    }
}
