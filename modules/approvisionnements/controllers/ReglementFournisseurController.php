<?php
// modules/approvisionnements/controllers/ReglementFournisseurController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/approvisionnements/models/ReglementFournisseur.php';
require_once ROOT . '/modules/approvisionnements/models/FactureFournisseur.php';
// Banque model may live in structure; require if exists
if (file_exists(ROOT . '/modules/structure/models/BanqueModel.php')) {
    require_once ROOT . '/modules/structure/models/BanqueModel.php';
}

use Controller;
use approvisionnements\models\ReglementFournisseur;
use approvisionnements\models\FactureFournisseur;
use \BanqueModel as Banque;

class ReglementFournisseurController extends Controller {
    
    private ReglementFournisseur $modele;
    
    public function __construct() {
        parent::__construct();
        $this->modele = new ReglementFournisseur();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Liste des règlements
     */
    public function index(): void {
        $reglements = $this->modele->listerTout();
        
        $this->rendrePage('approvisionnements', 'reglement_liste', [
            'titre' => 'Règlements fournisseurs',
            'reglements' => $reglements
        ]);
    }
    
    /**
     * Formulaire de création
     */
    public function creer(): void {
        $facture_id = $_GET['facture_id'] ?? null;
        $facture = null;
        
        if ($facture_id) {
            $factureModel = new FactureFournisseur();
            $facture = $factureModel->trouverParId((int)$facture_id);
            if (!$facture) {
                $_SESSION['error'] = 'Facture non trouvée.';
                $this->redirigerVers('/appros/reglements');
                return;
            }
        }
        
        $this->rendrePage('approvisionnements', 'reglement_formulaire', [
            'titre' => 'Nouveau règlement',
            'facture' => $facture,
            'facture_id' => $facture_id
        ]);
    }
    
    /**
     * Enregistre un règlement
     */
    public function enregistrer(): void {
        $this->verifierMethode('POST');
        
        $facture_id = $_POST['facture_id'] ?? null;
        $date_reglement = $_POST['date_reglement'] ?? date('Y-m-d');
        $montant_saisi = str_replace(',', '.', $_POST['montant'] ?? 0);
        $montant = floatval($montant_saisi);
        $mode_paiement = $_POST['mode_paiement'] ?? null;
        $reference = $_POST['reference'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!$facture_id || $montant <= 0) {
            $_SESSION['error'] = 'Veuillez renseigner une facture et un montant valide.';
            $this->redirigerVers('/appros/reglements/creer');
            return;
        }
        
        $factureModel = new FactureFournisseur();
        $facture = $factureModel->trouverParId((int)$facture_id);
        if (!$facture) {
            $_SESSION['error'] = 'Facture non trouvée.';
            $this->redirigerVers('/appros/reglements/creer');
            return;
        }
        
        // Convertir en centimes pour éviter les erreurs de flottants
        $montant_ttc_centimes = intval(round(floatval($facture['montant_ttc']) * 100));
        $montant_regle_centimes = intval(round(floatval($facture['montant_regle'] ?? 0) * 100));
        $solde_centimes = $montant_ttc_centimes - $montant_regle_centimes;
        $montant_centimes = intval(round($montant * 100));
        
        if ($montant_centimes > $solde_centimes) {
            $solde = $solde_centimes / 100;
            $_SESSION['error'] = 'Le montant ne peut pas dépasser le solde restant (' . number_format($solde, 2, ',', ' ') . ' FCFA).';
            $this->redirigerVers('/appros/reglements/creer?facture_id=' . $facture_id);
            return;
        }
        
        try {
            $reglementId = $this->modele->creer([
                ':facture_id' => $facture_id,
                ':date_reglement' => $date_reglement,
                ':montant' => $montant,
                ':mode_paiement' => $mode_paiement,
                ':reference' => $reference,
                ':banque_id' => null,
                ':notes' => $notes
            ]);
            
            $factureModel->mettreAJourMontantRegle((int)$facture_id);
            
            $_SESSION['success'] = 'Règlement enregistré avec succès.';
            $this->redirigerVers('/appros/factures/' . $facture_id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/reglements/creer?facture_id=' . $facture_id);
        }
    }
    
    /**
     * Reçu PDF d'un règlement (version corrigée avec accents)
     */
    public function recu(int $id): void {
        $reglement = $this->modele->trouverParId($id);
        
        if (!$reglement) {
            $_SESSION['error'] = 'Règlement non trouvé.';
            $this->redirigerVers('/appros/reglements');
            return;
        }
        
        require_once ROOT . '/vendor/autoload.php';
        if (!class_exists('FPDF') && class_exists('Fpdf\\Fpdf')) {
            class_alias('Fpdf\\Fpdf', 'FPDF');
        }

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('REÇU DE RÈGLEMENT'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('N° ' . $reglement['id']), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Fournisseur :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($reglement['fournisseur_nom']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Facture :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($reglement['facture_numero']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Date :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('d/m/Y', strtotime($reglement['date_reglement'])), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Montant :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, number_format($reglement['montant'], 0, ',', ' ') . ' FCFA', 0, 1);
        
        if ($reglement['mode_paiement']) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, $this->convertir('Mode :'), 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, $this->convertir($reglement['mode_paiement']), 0, 1);
        }
        
        $pdf->Output('I', 'recu_reglement_' . $reglement['id'] . '.pdf');
    }
    
    /**
     * Supprime un règlement
     */
    public function supprimer(int $id): void {
        $this->verifierMethode('POST');

        $reglement = $this->modele->trouverParId($id);
        if (!$reglement) {
            $_SESSION['error'] = 'Règlement non trouvé.';
            $this->redirigerVers('/appros/reglements');
            return;
        }

        $facture_id = $reglement['facture_id'];

        \Corbeille::archiver(
            'reglements_fournisseur',
            $reglement,
            [],
            'Règlement #' . $id . ' — ' . ($reglement['facture_numero'] ?? '')
        );

        try {
            $this->modele->supprimer($id);
            
            $factureModel = new FactureFournisseur();
            $factureModel->mettreAJourMontantRegle((int)$facture_id);
            
            $_SESSION['success'] = 'Règlement supprimé.';
            $this->redirigerVers('/appros/factures/' . $facture_id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/reglements');
        }
    }
}