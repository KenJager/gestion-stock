<?php
// modules/ventes/Pdf/BonCommandeClientPDF.php

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

class BonCommandeClientPDF extends \FPDF {

    private array $cmd;

    public function __construct(array $commande) {
        parent::__construct('P', 'mm', 'A4');
        $this->cmd = $commande;
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
        $this->AddPage();
        $this->build();
    }

    public function Header(): void {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(17, 24, 39);
        $this->Cell(0, 10, 'BON DE COMMANDE CLIENT', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 5, 'N° : ' . $this->cmd['numero'] . '   |   Date : ' . date('d/m/Y', strtotime($this->cmd['date_commande'])), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(226, 232, 240);
        $this->Line(15, $this->GetY() + 2, 195, $this->GetY() + 2);
        $this->Ln(5);
    }

    public function Footer(): void {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    private function build(): void {
        $this->infoBloc();
        $this->Ln(4);
        $this->lignesTable();
        $this->Ln(4);
        $this->totauxBloc();
        $this->Ln(8);
        $this->signatures();
    }

    private function infoBloc(): void {
        $y = $this->GetY();
        // Gauche — émetteur
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', APP_NOM ?? 'Système de gestion'), 0, 1);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(85, 4, 'Yaoundé, Cameroun', 0, 1);
        $this->SetTextColor(0, 0, 0);

        // Droite — client
        $this->SetXY(110, $y);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(85, 5, 'CLIENT', 0, 1);
        $this->SetXY(110, $this->GetY());
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->cmd['client_nom']), 0, 1);
        $this->SetFont('Arial', '', 8);
        $this->SetXY(110, $this->GetY());
        $this->Cell(85, 4, iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->cmd['client_adresse'] ?? ''), 0, 1);
        $this->SetXY(110, $this->GetY());
        $this->Cell(85, 4, 'Tel : ' . ($this->cmd['client_telephone'] ?? ''), 0, 1);

        if (!empty($this->cmd['date_livraison_prevue'])) {
            $this->SetXY(110, $this->GetY());
            $this->Cell(85, 4, iconv('UTF-8','ISO-8859-1//TRANSLIT','Livraison prévue : ' . date('d/m/Y', strtotime($this->cmd['date_livraison_prevue']))), 0, 1);
        }

        $this->SetY(max($this->GetY(), $y + 22) + 3);
        $this->SetDrawColor(226, 232, 240);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
    }

    private function lignesTable(): void {
        // En-tête
        $this->SetFillColor(17, 24, 39);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(80, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Désignation'), 'B', 0, 'L', true);
        $this->Cell(25, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Qté'), 'B', 0, 'C', true);
        $this->Cell(30, 7, 'PU HT (FCFA)', 'B', 0, 'R', true);
        $this->Cell(20, 7, 'Remise %', 'B', 0, 'C', true);
        $this->Cell(25, 7, 'Montant HT', 'B', 1, 'R', true);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
        $pair = false;

        foreach ($this->cmd['lignes'] as $l) {
            $ht = (float)$l['quantite_commandee'] * (float)$l['prix_unitaire_ht'] * (1 - (float)($l['remise_pct'] ?? 0) / 100);
            $this->SetFillColor($pair ? 250 : 255, $pair ? 250 : 255, $pair ? 250 : 255);
            $this->Cell(80, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT', $l['designation']), 'B', 0, 'L', $pair);
            $this->Cell(25, 6, number_format((float)$l['quantite_commandee'], 3, ',', ' '), 'B', 0, 'C', $pair);
            $this->Cell(30, 6, number_format((float)$l['prix_unitaire_ht'], 0, ',', ' '), 'B', 0, 'R', $pair);
            $this->Cell(20, 6, ($l['remise_pct'] ?? 0) > 0 ? $l['remise_pct'].'%' : '—', 'B', 0, 'C', $pair);
            $this->Cell(25, 6, number_format($ht, 0, ',', ' '), 'B', 1, 'R', $pair);
            $pair = !$pair;
        }
    }

    private function totauxBloc(): void {
        $x = 130;
        $this->SetFont('Arial', '', 9);
        $this->SetX($x); $this->Cell(35, 6, 'Total HT',  0, 0, 'L'); $this->Cell(30, 6, number_format((float)$this->cmd['montant_ht'],  0,',', ' ').' FCFA', 0, 1, 'R');
        $this->SetX($x); $this->Cell(35, 6, 'Total TVA', 0, 0, 'L'); $this->Cell(30, 6, number_format((float)$this->cmd['montant_tva'], 0,',', ' ').' FCFA', 0, 1, 'R');
        if ((float)($this->cmd['remise_pct'] ?? 0) > 0) {
            $this->SetX($x); $this->Cell(35, 6, 'Remise '.$this->cmd['remise_pct'].'%', 0, 0, 'L'); $this->Cell(30, 6, '—', 0, 1, 'R');
        }
        $this->SetFillColor(17, 24, 39); $this->SetTextColor(255,255,255); $this->SetFont('Arial','B',10);
        $this->SetX($x);
        $this->Cell(35, 8, 'Total TTC', 0, 0, 'L', true);
        $this->Cell(30, 8, number_format((float)$this->cmd['montant_ttc'], 0,',', ' ').' FCFA', 0, 1, 'R', true);
        $this->SetTextColor(0,0,0);
    }

    private function signatures(): void {
        $this->SetFont('Arial', '', 9);
        $this->Cell(90, 5, 'Signature et cachet du client :', 0, 0);
        $this->Cell(0,  5, 'Signature du commercial :', 0, 1);
        $this->Ln(18);
        $this->SetDrawColor(156, 163, 175);
        $this->Line(15,  $this->GetY(), 85,  $this->GetY());
        $this->SetX(110);
        $this->Line(110, $this->GetY(), 195, $this->GetY());
    }
}
