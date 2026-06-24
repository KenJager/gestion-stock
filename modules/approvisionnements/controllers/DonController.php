<?php
// modules/approvisionnements/controllers/DonController.php

namespace approvisionnements\controllers;

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/modules/approvisionnements/models/Don.php';
require_once ROOT . '/modules/approvisionnements/models/Produit.php';

use Controller;
use approvisionnements\models\Don;
use approvisionnements\models\Produit;

class DonController extends Controller {
    
    private Don $modele;
    
    public function __construct() {
        // parent::__construct(); // Désactivé pour les tests
        $this->modele = new Don();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Liste des dons
     */
    public function index(): void {
        $dons = $this->modele->listerTout();
        
        $this->rendrePage('approvisionnements', 'don_liste', [
            'titre' => 'Entrées de dons',
            'dons' => $dons
        ]);
    }
    
    /**
     * Formulaire de création
     */
    public function creer(): void {
        $produits = (new Produit())->listerTout();
        
        $this->rendrePage('approvisionnements', 'don_formulaire', [
            'titre' => 'Nouveau don',
            'produits' => $produits,
            'don' => null,
            'isEdit' => false
        ]);
    }
    
    /**
     * Enregistre un don
     */
    public function enregistrer(): void {
        $this->verifierMethode('POST');
        
        $donateur = $_POST['donateur'] ?? '';
        $date_entree = $_POST['date_entree'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (empty($lignes)) {
            $_SESSION['error'] = 'Veuillez ajouter au moins un produit.';
            $this->redirigerVers('/appros/dons/creer');
            return;
        }
        
        $numero = 'DON-' . date('Ymd') . '-' . rand(100, 999);
        
        try {
            $donId = $this->modele->creerAvecLignes([
                ':numero' => $numero,
                ':donateur' => $donateur,
                ':date_entree' => $date_entree,
                ':notes' => $notes
            ], $lignes);
            
            $_SESSION['success'] = 'Don enregistré avec succès. Stock mis à jour.';
            $this->redirigerVers('/appros/dons');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/dons/creer');
        }
    }
    
    /**
     * Formulaire de modification
     */
    public function editer(int $id): void {
        $don = $this->modele->trouverComplet($id);
        
        if (!$don) {
            $_SESSION['error'] = 'Don non trouvé.';
            $this->redirigerVers('/appros/dons');
            return;
        }
        
        $produits = (new Produit())->listerTout();
        
        $this->rendrePage('approvisionnements', 'don_formulaire', [
            'titre' => 'Modifier le don ' . $don['numero'],
            'don' => $don,
            'produits' => $produits,
            'isEdit' => true
        ]);
    }
    
    /**
     * Met à jour un don
     */
    public function mettreAJour(int $id): void {
        $this->verifierMethode('POST');
        
        $donateur = $_POST['donateur'] ?? '';
        $date_entree = $_POST['date_entree'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (empty($lignes)) {
            $_SESSION['error'] = 'Veuillez ajouter au moins un produit.';
            $this->redirigerVers('/appros/dons/' . $id . '/modifier');
            return;
        }
        
        try {
            $this->modele->mettreAJour($id, [
                ':donateur' => $donateur,
                ':date_entree' => $date_entree,
                ':notes' => $notes
            ], $lignes);
            
            $_SESSION['success'] = 'Don mis à jour avec succès. Stock corrigé.';
            $this->redirigerVers('/appros/dons/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/dons/' . $id . '/modifier');
        }
    }
    
    /**
     * Supprime un don
     */
    public function supprimer(int $id): void {
        $this->verifierMethode('POST');
        
        try {
            $this->modele->supprimer($id);
            $_SESSION['success'] = 'Don supprimé avec succès. Stock restauré.';
            $this->redirigerVers('/appros/dons');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/dons');
        }
    }
    
    /**
     * Détail d'un don
     */
    public function detail(int $id): void {
        $don = $this->modele->trouverComplet($id);
        
        if (!$don) {
            $_SESSION['error'] = 'Don non trouvé.';
            $this->redirigerVers('/appros/dons');
            return;
        }
        
        $this->rendrePage('approvisionnements', 'don_detail', [
            'titre' => 'Détail don ' . $don['numero'],
            'don' => $don
        ]);
    }
    
    /**
     * PDF du bon d'entrée pour don (version corrigée)
     */
    public function pdf(int $id): void {
        $don = $this->modele->trouverComplet($id);
        
        if (!$don) {
            $_SESSION['error'] = 'Don non trouvé.';
            $this->redirigerVers('/appros/dons');
            return;
        }
        
        require_once ROOT . '/vendor/fpdf/fpdf.php';
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('BON D\'ENTRÉE - DON'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('N° ' . $don['numero']), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Donateur :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($don['donateur'] ?? 'Anonyme'), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Date :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('d/m/Y', strtotime($don['date_entree'])), 0, 1);
        
        if (!empty($don['notes'])) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, $this->convertir('Notes :'), 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 10, $this->convertir($don['notes']));
        }
        
        $pdf->Ln(10);
        
        // Tableau
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, $this->convertir('Désignation'), 1, 0, 'L');
        $pdf->Cell(25, 8, $this->convertir('Quantité'), 1, 0, 'C');
        $pdf->Cell(35, 8, $this->convertir('Valeur estimée'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('Total estimé'), 1, 1, 'R');
        
        $pdf->SetFont('Arial', '', 10);
        $totalEstime = 0;
        foreach ($don['lignes'] as $ligne) {
            $total = $ligne['quantite'] * ($ligne['valeur_estimee'] ?? 0);
            $totalEstime += $total;
            $pdf->Cell(80, 8, $this->convertir($ligne['designation']), 1);
            $pdf->Cell(25, 8, $ligne['quantite'], 1, 0, 'C');
            $pdf->Cell(35, 8, number_format($ligne['valeur_estimee'] ?? 0, 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(35, 8, number_format($total, 0, ',', ' '), 1, 1, 'R');
        }
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(140, 8, $this->convertir('Total estimé'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($totalEstime, 0, ',', ' '), 1, 1, 'R');
        
        $pdf->Output('I', 'bon_entree_don_' . $don['numero'] . '.pdf');
    }
}