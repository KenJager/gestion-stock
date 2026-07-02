<?php
// modules/approvisionnements/controllers/FactureFournisseurController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/approvisionnements/models/FactureFournisseur.php';
require_once ROOT . '/modules/approvisionnements/models/BonReception.php';

use Controller;
use approvisionnements\models\FactureFournisseur;
use approvisionnements\models\BonReception;

class FactureFournisseurController extends Controller {
    
    private FactureFournisseur $modele;
    
    public function __construct() {
        parent::__construct();
        $this->modele = new FactureFournisseur();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Valide un ID et le convertit en int
     */
    private function validerId($id): int {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID invalide.';
            $this->redirigerVers('/appros/factures');
            exit;
        }
        return $id;
    }
    
    /**
     * Liste des factures
     */
    public function index(): void {
        $factures = $this->modele->listerTout();
        
        $this->rendrePage('approvisionnements', 'facture_liste', [
            'titre' => 'Factures fournisseurs',
            'factures' => $factures
        ]);
    }
    
    /**
     * Formulaire de création
     */
    public function creer(): void {
        $receptions = $this->modele->getReceptionsSansFacture();
        
        $this->rendrePage('approvisionnements', 'facture_formulaire', [
            'titre' => 'Nouvelle facture fournisseur',
            'receptions' => $receptions,
            'facture' => null,
            'isEdit' => false
        ]);
    }
    
    /**
     * Enregistre une nouvelle facture (à partir d'une réception)
     */
    public function enregistrer(): void {
        $this->verifierMethode('POST');
        
        $bon_reception_id = $_POST['bon_reception_id'] ?? null;
        $date_facture = $_POST['date_facture'] ?? date('Y-m-d');
        $date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
        $notes = $_POST['notes'] ?? '';
        $montant_ht = floatval($_POST['montant_ht'] ?? 0);
        $montant_tva = floatval($_POST['montant_tva'] ?? 0);
        $montant_ttc = floatval($_POST['montant_ttc'] ?? 0);
        
        if (!$bon_reception_id) {
            $_SESSION['error'] = 'Veuillez sélectionner une réception.';
            $this->redirigerVers('/appros/factures/creer');
            return;
        }
        
        $receptionModel = new BonReception();
        $reception = $receptionModel->trouverComplet((int)$bon_reception_id);
        if (!$reception) {
            $_SESSION['error'] = 'Réception non trouvée.';
            $this->redirigerVers('/appros/factures/creer');
            return;
        }
        
        $fournisseur_id = $reception['fournisseur_id'];
        $numero = 'FAC-' . date('Ymd') . '-' . rand(100, 999);
        
        try {
            $factureId = $this->modele->creerDepuisReception([
                ':numero' => $numero,
                ':bon_reception_id' => $bon_reception_id,
                ':fournisseur_id' => $fournisseur_id,
                ':date_facture' => $date_facture,
                ':date_echeance' => $date_echeance,
                ':montant_ht' => $montant_ht,
                ':montant_tva' => $montant_tva,
                ':montant_ttc' => $montant_ttc,
                ':notes' => $notes
            ]);
            
            $_SESSION['success'] = 'Facture créée avec succès.';
            $this->redirigerVers('/appros/factures/' . $factureId);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/factures/creer');
        }
    }
    
    /**
     * Détail d'une facture
     */
    public function detail($id): void {
        // Validation de l'ID
        $id = $this->validerId($id);
        
        $facture = $this->modele->trouverComplet($id);
        
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/factures');
            return;
        }
        
        $this->rendrePage('approvisionnements', 'facture_detail', [
            'titre' => 'Facture ' . $facture['numero'],
            'facture' => $facture
        ]);
    }
    
    /**
     * Formulaire de modification
     */
    public function editer($id): void {
        // Validation de l'ID
        $id = $this->validerId($id);
        
        $facture = $this->modele->trouverAvecJointure($id);
        
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/factures');
            return;
        }
        
        if ($facture['statut'] === 'REGLÉE') {
            $_SESSION['error'] = 'Impossible de modifier une facture réglée.';
            $this->redirigerVers('/appros/factures/' . $id);
            return;
        }
        
        $receptions = $this->modele->getReceptionsSansFacture();
        if ($facture['bon_reception_id']) {
            $receptionModel = new BonReception();
            $reception = $receptionModel->trouverComplet((int)$facture['bon_reception_id']);
            if ($reception) {
                $receptions[] = $reception;
            }
        }
        
        $this->rendrePage('approvisionnements', 'facture_formulaire', [
            'titre' => 'Modifier la facture ' . $facture['numero'],
            'facture' => $facture,
            'receptions' => $receptions,
            'isEdit' => true
        ]);
    }
    
    /**
     * Met à jour une facture existante
     */
    public function mettreAJour($id): void {
        // Validation de l'ID
        $id = $this->validerId($id);
        
        $this->verifierMethode('POST');
        
        $facture = $this->modele->trouverAvecJointure($id);
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/factures');
            return;
        }
        
        if ($facture['statut'] === 'REGLÉE') {
            $_SESSION['error'] = 'Impossible de modifier une facture réglée.';
            $this->redirigerVers('/appros/factures/' . $id);
            return;
        }
        
        $bon_reception_id = $_POST['bon_reception_id'] ?? $facture['bon_reception_id'];
        $date_facture = $_POST['date_facture'] ?? date('Y-m-d');
        $date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
        $notes = $_POST['notes'] ?? '';
        $montant_ht = floatval($_POST['montant_ht'] ?? 0);
        $montant_tva = floatval($_POST['montant_tva'] ?? 0);
        $montant_ttc = floatval($_POST['montant_ttc'] ?? 0);
        
        $db = \Database::connexion();
        $sql = "UPDATE factures_fournisseur 
                SET bon_reception_id = :bon_reception_id,
                    date_facture = :date_facture,
                    date_echeance = :date_echeance,
                    notes = :notes,
                    montant_ht = :montant_ht,
                    montant_tva = :montant_tva,
                    montant_ttc = :montant_ttc
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':bon_reception_id' => $bon_reception_id,
            ':date_facture' => $date_facture,
            ':date_echeance' => $date_echeance,
            ':notes' => $notes,
            ':montant_ht' => $montant_ht,
            ':montant_tva' => $montant_tva,
            ':montant_ttc' => $montant_ttc,
            ':id' => $id
        ]);
        
        $this->modele->mettreAJourMontantRegle($id);
        
        $_SESSION['success'] = 'Facture mise à jour avec succès.';
        $this->redirigerVers('/appros/factures/' . $id);
    }
    
    /**
     * Supprime une facture
     */
    public function supprimer($id): void {
        // Validation de l'ID
        $id = $this->validerId($id);
        
        $this->verifierMethode('POST');
        
        $facture = $this->modele->trouverAvecJointure($id);
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/factures');
            return;
        }
        
        if ($facture['statut'] === 'REGLÉE') {
            $_SESSION['error'] = 'Impossible de supprimer une facture réglée.';
            $this->redirigerVers('/appros/factures/' . $id);
            return;
        }
        
        \Corbeille::archiver(
            'factures_fournisseur',
            $facture,
            [],
            $facture['numero'] . ' — ' . ($facture['fournisseur_nom'] ?? '')
        );
        $this->modele->supprimer($id);
        $_SESSION['success'] = 'Facture supprimée. Elle est disponible dans la corbeille.';
        $this->redirigerVers('/appros/factures');
    }
    
    /**
     * PDF de la facture
     */
    public function pdf($id): void {
        // Validation de l'ID
        $id = $this->validerId($id);
        
        $facture = $this->modele->trouverComplet($id);
        
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/factures');
            return;
        }
        
        require_once ROOT . '/vendor/autoload.php';
        if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
            class_alias('Fpdf\\Fpdf', 'FPDF');
        }

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('FACTURE FOURNISSEUR'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('N° ' . $facture['numero']), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Fournisseur :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($facture['fournisseur_nom']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Date :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('d/m/Y', strtotime($facture['date_facture'])), 0, 1);
        
        if ($facture['date_echeance']) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, $this->convertir('Échéance :'), 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, date('d/m/Y', strtotime($facture['date_echeance'])), 0, 1);
        }
        
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, $this->convertir('Désignation'), 1, 0, 'L');
        $pdf->Cell(35, 8, $this->convertir('Montant HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('TVA'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('Montant TTC'), 1, 1, 'R');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(80, 8, $this->convertir('Bon de réception N° ' . ($facture['reception_numero'] ?? '-')), 1);
        $pdf->Cell(35, 8, number_format($facture['montant_ht'], 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($facture['montant_tva'], 0, ',', ' '), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($facture['montant_ttc'], 0, ',', ' '), 1, 1, 'R');
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 8, $this->convertir('Total TTC'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($facture['montant_ttc'], 0, ',', ' '), 1, 1, 'R');
        
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Ln(5);
        $pdf->Cell(0, 10, $this->convertir('Statut : ' . $facture['statut']), 0, 1);
        $pdf->Cell(0, 10, $this->convertir('Montant réglé : ') . number_format($facture['montant_regle'] ?? 0, 0, ',', ' '), 0, 1);
        $pdf->Cell(0, 10, $this->convertir('Solde : ') . number_format($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0), 0, ',', ' '), 0, 1);
        
        $pdf->Output('I', 'facture_' . $facture['numero'] . '.pdf');
    }
}