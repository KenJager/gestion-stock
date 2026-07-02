<?php
// modules/approvisionnements/controllers/EtatController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Database.php';

use Controller;
use \Database;

class EtatController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * État achat par jour
     */
    public function journalier(): void {
        $date = $_GET['date'] ?? date('Y-m-d');
        $commandes = $this->getCommandesParJour($date);
        
        $this->rendrePage('approvisionnements', 'etat_journalier', [
            'titre' => 'État achat par jour',
            'date' => $date,
            'commandes' => $commandes,
            'total_ht' => array_sum(array_column($commandes, 'montant_ht')),
            'total_tva' => array_sum(array_column($commandes, 'montant_tva')),
            'total_ttc' => array_sum(array_column($commandes, 'montant_ttc'))
        ]);
    }
    
    /**
     * État achat annuel
     */
    public function annuel(): void {
        $annee = $_GET['annee'] ?? date('Y');
        $mois = $this->getMoisAchats($annee);
        
        $this->rendrePage('approvisionnements', 'etat_annuel', [
            'titre' => 'État achat annuel',
            'annee' => $annee,
            'mois' => $mois,
            'total_ht' => array_sum(array_column($mois, 'total_ht')),
            'total_tva' => array_sum(array_column($mois, 'total_tva')),
            'total_ttc' => array_sum(array_column($mois, 'total_ttc'))
        ]);
    }
    
    /**
     * PDF de l'état journalier
     */
  public function journalierPdf(): void {
    $date = $_GET['date'] ?? date('Y-m-d');
    $commandes = $this->getCommandesParJour($date);

    require_once ROOT . '/vendor/autoload.php';
    if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
        class_alias('Fpdf\\Fpdf', 'FPDF');
    }

    $pdf = new \FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, $this->convertir('ETAT ACHAT PAR JOUR'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $this->convertir('Date : ' . date('d/m/Y', strtotime($date))), 0, 1, 'C');
    $pdf->Ln(6);
    
    // Tableau avec largeurs ajustées pour tenir dans une page A4
    $pdf->SetFont('Arial', 'B', 8);
    $largeur1 = 35;  // N° Commande
    $largeur2 = 50;  // Fournisseur
    $largeur3 = 30;  // Montant HT
    $largeur4 = 30;  // TVA
    $largeur5 = 30;  // Montant TTC
    // Total : 35+50+30+30+30 = 175 mm (marge 15 mm de chaque côté → 190 mm utilisables)
    
    $pdf->Cell($largeur1, 7, $this->convertir('N° Commande'), 1, 0, 'L');
    $pdf->Cell($largeur2, 7, $this->convertir('Fournisseur'), 1, 0, 'L');
    $pdf->Cell($largeur3, 7, $this->convertir('Montant HT'), 1, 0, 'R');
    $pdf->Cell($largeur4, 7, $this->convertir('TVA'), 1, 0, 'R');
    $pdf->Cell($largeur5, 7, $this->convertir('Montant TTC'), 1, 1, 'R');
    
    $total_ht = 0;
    $total_tva = 0;
    $total_ttc = 0;
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($commandes as $c) {
        $total_ht += $c['montant_ht'];
        $total_tva += $c['montant_tva'];
        $total_ttc += $c['montant_ttc'];
        $pdf->Cell($largeur1, 6, $this->convertir($c['numero']), 1);
        $pdf->Cell($largeur2, 6, $this->convertir($c['fournisseur_nom']), 1);
        $pdf->Cell($largeur3, 6, number_format($c['montant_ht'], 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell($largeur4, 6, number_format($c['montant_tva'], 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell($largeur5, 6, number_format($c['montant_ttc'], 0, ',', ' '), 1, 1, 'R');
    }
    
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($largeur1 + $largeur2, 7, $this->convertir('TOTAUX'), 1, 0, 'R');
    $pdf->Cell($largeur3, 7, number_format($total_ht, 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($largeur4, 7, number_format($total_tva, 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($largeur5, 7, number_format($total_ttc, 0, ',', ' '), 1, 1, 'R');
    
    $pdf->Output('I', 'etat_achat_journalier_' . $date . '.pdf');
}
    
    /**
     * PDF de l'état annuel
     */
    public function annuelPdf(): void {
        $annee = $_GET['annee'] ?? date('Y');
        $mois = $this->getMoisAchats($annee);

        require_once ROOT . '/vendor/autoload.php';
        if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
            class_alias('Fpdf\\Fpdf', 'FPDF');
        }

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('ETAT ACHAT ANNUEL'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('Année : ' . $annee), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 8, $this->convertir('Mois'), 1, 0, 'L');
        $pdf->Cell(35, 8, $this->convertir('Nombre achats'), 1, 0, 'C');
        $pdf->Cell(35, 8, $this->convertir('Total HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('TVA'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('Total TTC'), 1, 1, 'R');
        
        $total_ht = 0;
        $total_tva = 0;
        $total_ttc = 0;
        $total_nb = 0;
        
        $pdf->SetFont('Arial', '', 10);
        foreach ($mois as $m) {
            $total_ht += $m['total_ht'];
            $total_tva += $m['total_tva'];
            $total_ttc += $m['total_ttc'];
            $total_nb += $m['nb_commandes'];
            $pdf->Cell(40, 8, $this->convertir($m['libelle']), 1);
            $pdf->Cell(35, 8, $m['nb_commandes'], 1, 0, 'C');
            $pdf->Cell(35, 8, number_format($m['total_ht'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(35, 8, number_format($m['total_tva'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(35, 8, number_format($m['total_ttc'], 0, ',', ' '), 1, 1, 'R');
        }
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 8, $this->convertir('TOTAUX'), 1, 0, 'R');
        $pdf->Cell(35, 8, $total_nb, 1, 0, 'C');
        $pdf->Cell(35, 8, number_format($total_ht, 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($total_tva, 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($total_ttc, 0, ',', ' '), 1, 1, 'R');
        
        $pdf->Output('I', 'etat_achat_annuel_' . $annee . '.pdf');
    }
    
    // ─── Méthodes privées ────────────────────────────────────────────────────
    
    private function getCommandesParJour(string $date): array {
        $sql = "SELECT bcf.numero, bcf.montant_ht, bcf.montant_tva, bcf.montant_ttc,
                       f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.statut IN ('REÇU_TOTAL', 'REÇU_PARTIEL')
                  AND bcf.date_commande = :date
                ORDER BY bcf.date_commande DESC";
        
        $resultat = $this->requete($sql, [':date' => $date]);
        return $resultat->toutRecuperer();
    }
    
    private function getMoisAchats(string $annee): array {
        $sql = "SELECT EXTRACT(MONTH FROM bcf.date_commande) AS mois,
                       COUNT(*) AS nb_commandes,
                       SUM(bcf.montant_ht) AS total_ht,
                       SUM(bcf.montant_tva) AS total_tva,
                       SUM(bcf.montant_ttc) AS total_ttc
                FROM bons_commande_fournisseur bcf
                WHERE bcf.statut IN ('REÇU_TOTAL', 'REÇU_PARTIEL')
                  AND EXTRACT(YEAR FROM bcf.date_commande) = :annee
                GROUP BY EXTRACT(MONTH FROM bcf.date_commande)
                ORDER BY mois";
        
        $resultat = $this->requete($sql, [':annee' => $annee]);
        $mois = $resultat->toutRecuperer();
        
        // Ajouter les libellés des mois (avec accents convertis plus tard)
        $nomsMois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        foreach ($mois as &$m) {
            $m['libelle'] = $nomsMois[(int)$m['mois']] ?? $m['mois'];
        }
        
        return $mois;
    }
    
    private function requete(string $sql, array $params = []) {
        $stmt = Database::connexion()->prepare($sql);
        $stmt->execute($params);
        return new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function toutRecuperer(): array {
                return $this->stmt->fetchAll();
            }
            public function unRecuperer(): array|false {
                return $this->stmt->fetch();
            }
        };
    }
}