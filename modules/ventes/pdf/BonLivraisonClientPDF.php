<?php
// modules/Ventes/Pdf/BonLivraisonClientPDF.php

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

class BonLivraisonClientPDF extends \FPDF {

    private array $liv;

    public function __construct(array $livraison) {
        parent::__construct('P', 'mm', 'A4');
        $this->liv = $livraison;
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
        $this->AddPage();
        $this->build();
    }

    public function Header(): void {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(22, 101, 52);
        $this->Cell(0, 10, 'BON DE LIVRAISON CLIENT', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 5, 'N° : ' . $this->liv['numero'] . '   |   Date : ' . date('d/m/Y', strtotime($this->liv['date_livraison'])), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
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
        // Infos
        $y = $this->GetY();
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->liv['client_nom']), 0, 1);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107,114,128);
        $this->Cell(85, 4, 'Tel : '.($this->liv['client_telephone'] ?? ''), 0, 1);
        if ($this->liv['commande_numero']) {
            $this->Cell(85, 4, iconv('UTF-8','ISO-8859-1//TRANSLIT','Réf commande : '.$this->liv['commande_numero']), 0, 1);
        }
        $this->SetTextColor(0,0,0);
        $this->Ln(3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);

        // Table
        $this->SetFillColor(22, 101, 52); $this->SetTextColor(255,255,255); $this->SetFont('Arial','B',8);
        $this->Cell(85, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Désignation'), 'B', 0, 'L', true);
        $this->Cell(20, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Unité'),       'B', 0, 'C', true);
        $this->Cell(25, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Qté livrée'),  'B', 0, 'C', true);
        $this->Cell(30, 7, 'PU HT (FCFA)', 'B', 0, 'R', true);
        $this->Cell(30, 7, 'Montant HT',   'B', 1, 'R', true);

        $this->SetTextColor(0,0,0); $this->SetFont('Arial','',8);
        $pair = false; $totalHT = 0;

        foreach ($this->liv['lignes'] as $l) {
            $ht = (float)$l['quantite_livree'] * (float)$l['prix_unitaire_ht'] * (1 - (float)($l['remise_pct'] ?? 0)/100);
            $totalHT += $ht;
            $this->SetFillColor($pair ? 235:255, $pair ? 252:255, $pair ? 243:255);
            $this->Cell(85, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT',$l['designation']), 'B', 0, 'L', $pair);
            $this->Cell(20, 6, $l['unite'] ?? '',                                        'B', 0, 'C', $pair);
            $this->Cell(25, 6, number_format((float)$l['quantite_livree'], 3, ',', ' '), 'B', 0, 'C', $pair);
            $this->Cell(30, 6, number_format((float)$l['prix_unitaire_ht'],0, ',', ' '), 'B', 0, 'R', $pair);
            $this->Cell(30, 6, number_format($ht,0,',', ' '),                            'B', 1, 'R', $pair);
            $pair = !$pair;
        }

        // Total
        $this->SetFillColor(22,101,52); $this->SetTextColor(255,255,255); $this->SetFont('Arial','B',9);
        $this->Cell(160, 7, 'TOTAL HT', 'B', 0, 'R', true);
        $this->Cell(30,  7, number_format($totalHT,0,',', ' ').' FCFA', 'B', 1, 'R', true);
        $this->SetTextColor(0,0,0);

        // Signature
        $this->Ln(10);
        $this->SetFont('Arial','',9);
        $this->Cell(0, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Je soussigné(e) certifie avoir reçu les marchandises ci-dessus en bon état.'), 0, 1);
        $this->Ln(5);
        $this->Cell(90, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Signature et cachet du client :'), 0, 0);
        $this->Cell(0,  5, 'Signature du livreur :', 0, 1);
        $this->Ln(18);
        $this->Line(15, $this->GetY(), 85, $this->GetY());
        $this->SetX(110); $this->Line(110, $this->GetY(), 195, $this->GetY());
    }
}
