<?php
// modules/Ventes/Pdf/FactureClientPDF.php

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

/**
 * Génère une facture client en PDF avec FPDF.
 * Usage : (new FactureClientPDF($facture))->Output('I', 'Facture_xxx.pdf');
 */
class FactureClientPDF extends \FPDF {

    private array $facture;

    public function __construct(array $facture) {
        parent::__construct('P', 'mm', 'A4');
        $this->facture = $facture;
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
        $this->AddPage();
        $this->build();
    }

    public function Header(): void {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(17, 24, 39);
        $this->Cell(0, 10, 'FACTURE CLIENT', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 5,
            'N° : ' . $this->facture['numero'] . '   |   Date : ' . date('d/m/Y', strtotime($this->facture['date_facture'])),
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
        $this->infoBloc();
        $this->Ln(4);
        $this->lignesTable();
        $this->Ln(4);
        $this->totauxBloc();
        $this->Ln(6);
        $this->reglementsBloc();
        $this->Ln(8);
        $this->mentions();
    }

    private function infoBloc(): void {
        $y = $this->GetY();

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', 'ÉMIS PAR'), 0, 1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', APP_NOM ?? 'Système de gestion'), 0, 1);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(85, 4, iconv('UTF-8','ISO-8859-1//TRANSLIT','Yaoundé, Cameroun'), 0, 1);
        $this->SetTextColor(0, 0, 0);

        $this->SetXY(110, $y);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','FACTURÉ À'), 0, 1);
        $this->SetXY(110, $this->GetY());
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(85, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->facture['client_nom']), 0, 1);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->SetXY(110, $this->GetY());
        $this->Cell(85, 4, iconv('UTF-8','ISO-8859-1//TRANSLIT', $this->facture['client_adresse'] ?? ''), 0, 1);
        $this->SetXY(110, $this->GetY());
        $this->Cell(85, 4, 'Tel : ' . ($this->facture['client_telephone'] ?? ''), 0, 1);
        $this->SetTextColor(0, 0, 0);

        $this->SetY(max($this->GetY(), $y + 22) + 3);
        $this->SetDrawColor(226, 232, 240);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);

        $this->SetFont('Arial', '', 9);
        $echeance = $this->facture['date_echeance']
            ? date('d/m/Y', strtotime($this->facture['date_echeance'])) : '-';
        $this->Cell(60, 5, 'Type : ' . $this->facture['type_vente'], 0, 0);
        $this->Cell(60, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Échéance : ' . $echeance), 0, 0);
        $this->Cell(0,  5, iconv('UTF-8','ISO-8859-1//TRANSLIT','Statut : ' . $this->facture['statut']), 0, 1);
        $this->Ln(2);
    }

    private function lignesTable(): void {
        $this->SetFillColor(17, 24, 39);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(75, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Désignation'), 'B', 0, 'L', true);
        $this->Cell(20, 7, iconv('UTF-8','ISO-8859-1//TRANSLIT','Qté'),         'B', 0, 'C', true);
        $this->Cell(30, 7, 'PU HT (FCFA)', 'B', 0, 'R', true);
        $this->Cell(20, 7, 'Remise%',      'B', 0, 'C', true);
        $this->Cell(18, 7, 'TVA%',         'B', 0, 'C', true);
        $this->Cell(27, 7, 'Montant HT',   'B', 1, 'R', true);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
        $pair = false;

        foreach ($this->facture['lignes'] as $l) {
            $qte    = (float) $l['quantite'];
            $pu     = (float) $l['prix_unitaire_ht'];
            $remise = (float) ($l['remise_pct'] ?? 0);
            $htLig  = $qte * $pu * (1 - $remise / 100);

            $this->SetFillColor($pair ? 240 : 255, $pair ? 245 : 255, $pair ? 255 : 255);
            $this->Cell(75, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT', $l['designation']), 'B', 0, 'L', $pair);
            $this->Cell(20, 6, number_format($qte, 3, ',', ' ') . ' ' . ($l['unite'] ?? ''), 'B', 0, 'C', $pair);
            $this->Cell(30, 6, number_format($pu, 0, ',', ' '),                           'B', 0, 'R', $pair);
            $this->Cell(20, 6, $remise > 0 ? number_format($remise, 2) . '%' : '-',       'B', 0, 'C', $pair);
            $this->Cell(18, 6, number_format((float) $l['tva'], 2) . '%',                 'B', 0, 'C', $pair);
            $this->Cell(27, 6, number_format($htLig, 0, ',', ' '),                        'B', 1, 'R', $pair);
            $pair = !$pair;
        }
    }

    private function totauxBloc(): void {
        $x = 130;
        $this->SetFont('Arial', '', 9);
        $this->SetX($x); $this->Cell(40, 6, 'Total HT',  0, 0, 'L'); $this->Cell(30, 6, number_format((float)$this->facture['montant_ht'],  0,',', ' ') . ' FCFA', 0, 1, 'R');
        $this->SetX($x); $this->Cell(40, 6, 'Total TVA', 0, 0, 'L'); $this->Cell(30, 6, number_format((float)$this->facture['montant_tva'], 0,',', ' ') . ' FCFA', 0, 1, 'R');

        if ((float)$this->facture['remise_montant'] > 0) {
            $this->SetX($x); $this->Cell(40, 6, 'Remise', 0, 0, 'L');
            $this->Cell(30, 6, '- ' . number_format((float)$this->facture['remise_montant'], 0,',', ' ') . ' FCFA', 0, 1, 'R');
        }

        $this->SetFillColor(17, 24, 39); $this->SetTextColor(255,255,255); $this->SetFont('Arial','B',10);
        $this->SetX($x);
        $this->Cell(40, 8, iconv('UTF-8','ISO-8859-1//TRANSLIT','NET À PAYER'), 0, 0, 'L', true);
        $this->Cell(30, 8, number_format((float)$this->facture['solde'], 0,',', ' ') . ' FCFA', 0, 1, 'R', true);
        $this->SetTextColor(0,0,0);
    }

    private function reglementsBloc(): void {
        if (empty($this->facture['reglements'])) return;

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 6, iconv('UTF-8','ISO-8859-1//TRANSLIT','Règlements enregistrés :'), 0, 1);
        $this->SetFont('Arial', '', 8);

        foreach ($this->facture['reglements'] as $r) {
            $this->Cell(40, 5, date('d/m/Y', strtotime($r['date_reglement'])), 0, 0);
            $this->Cell(50, 5, iconv('UTF-8','ISO-8859-1//TRANSLIT', $r['mode_paiement'] ?? ''), 0, 0);
            $this->Cell(0,  5, number_format((float)$r['montant'], 0,',', ' ') . ' FCFA', 0, 1, 'R');
        }
    }

    private function mentions(): void {
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->MultiCell(0, 4,
            iconv('UTF-8','ISO-8859-1//TRANSLIT',
                'Facture générée électroniquement. Tout règlement après échéance peut entraîner des pénalités de retard. Merci de votre confiance.'
            ),
            0, 'C'
        );
    }
}
