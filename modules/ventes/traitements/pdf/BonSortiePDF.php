<?php
// modules/Ventes/Pdf/BonSortiePDF.php

namespace ventes\traitements\pdf;

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

class BonSortiePDF extends \FPDF {

    private array $sortie;

    public function __construct(array $sortie) {
        parent::__construct('P', 'mm', 'A4');
        $this->sortie = $sortie;
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
        $this->AddPage();
        $this->build();
    }

    public function Header(): void {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(153, 27, 27);
        $this->Cell(0, 10, 'BON DE SORTIE DE STOCK', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 5,
            'N° : ' . $this->sortie['numero'] .
            '   |   Date : ' . date('d/m/Y', strtotime($this->sortie['date_sortie'])) .
            '   |   Motif : ' . iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->sortie['motif']),
            0, 1, 'C'
        );
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
        if (!empty($this->sortie['notes'])) {
            $this->SetFont('Arial', 'I', 9);
            $this->SetTextColor(100, 100, 100);
            $this->MultiCell(0, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', 'Observations : ' . $this->sortie['notes']), 0, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->Ln(3);
        }

        $this->lignesTable();
        $this->Ln(10);
        $this->signatures();
    }

    private function lignesTable(): void {
        $this->SetFillColor(153, 27, 27);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(80, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Désignation'), 'B', 0, 'L', true);
        $this->Cell(20, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Unité'),       'B', 0, 'C', true);
        $this->Cell(25, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Qté sortie'),  'B', 0, 'C', true);
        $this->Cell(30, 7, 'PU estime (FCFA)', 'B', 0, 'R', true);
        $this->Cell(35, 7, 'Valeur estimee',   'B', 1, 'R', true);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
        $pair = false;
        $valTotale = 0;

        foreach ($this->sortie['lignes'] as $l) {
            $valeur = (float) $l['quantite'] * (float) ($l['prix_unitaire'] ?? 0);
            $valTotale += $valeur;

            $this->SetFillColor($pair ? 255 : 255, $pair ? 241 : 255, $pair ? 242 : 255);
            $this->Cell(80, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT', $l['designation']),       'B', 0, 'L', $pair);
            $this->Cell(20, 6, $l['unite'] ?? '',                                              'B', 0, 'C', $pair);
            $this->Cell(25, 6, number_format((float) $l['quantite'], 3, ',', ' '),             'B', 0, 'C', $pair);
            $this->Cell(30, 6, number_format((float)($l['prix_unitaire'] ?? 0), 0, ',', ' '),  'B', 0, 'R', $pair);
            $this->Cell(35, 6, number_format($valeur, 0, ',', ' '),                            'B', 1, 'R', $pair);

            if (!empty($l['observations'])) {
                $this->SetFont('Arial', 'I', 7);
                $this->SetTextColor(100, 100, 100);
                $this->Cell(10);
                $this->Cell(0, 5, '-> ' . iconv('UTF-8','ISO-8859-1//TRANSLIT', $l['observations']), 0, 1);
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(0, 0, 0);
            }

            $pair = !$pair;
        }

        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(153, 27, 27);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(155, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','VALEUR TOTALE ESTIMÉE'), 'B', 0, 'R', true);
        $this->Cell(35,  7, number_format($valTotale, 0, ',', ' ') . ' FCFA', 'B', 1, 'R', true);
        $this->SetTextColor(0, 0, 0);
    }

    private function signatures(): void {
        $this->SetFont('Arial', '', 9);
        $this->Cell(90, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Établi par :'), 0, 0);
        $this->Cell(0,  5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Approuvé par le responsable :'), 0, 1);
        $this->Ln(18);
        $this->SetDrawColor(156, 163, 175);
        $this->Line(15,  $this->GetY(), 85,  $this->GetY());
        $this->SetX(110);
        $this->Line(110, $this->GetY(), 195, $this->GetY());
    }
}
