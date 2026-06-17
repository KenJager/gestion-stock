<?php
// modules/Approvisionnements/Controllers/BonReceptionController.php

namespace Approvisionnements\Controllers;

use \Controller;
use Approvisionnements\Models\BonReception;
use Approvisionnements\Models\Produit;

class BonReceptionController extends Controller {
    
    private BonReception $modele;
    
    public function __construct() {
        //parent::__construct();
        $this->modele = new BonReception();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Liste des réceptions
     */
    public function index(): void {
        $receptions = $this->modele->listerTout();
        
        $this->rendrePage('Approvisionnements', 'reception_liste', [
            'titre' => 'Bons de réception',
            'receptions' => $receptions
        ]);
    }
    
    /**
     * Formulaire de création de réception
     */
    public function creer(): void {
        $commandes = $this->modele->getCommandesValidees();
        
        $this->rendrePage('Approvisionnements', 'reception_formulaire', [
            'titre' => 'Nouvelle réception',
            'commandes' => $commandes
        ]);
    }
    
    /**
     * Récupère les détails d'une commande pour la réception (AJAX)
     */
    public function getCommande(int $id): void {
        $commande = $this->modele->getCommandeDetails($id);
        
        if (!$commande) {
            http_response_code(404);
            echo json_encode(['error' => 'Commande non trouvée']);
            return;
        }
        
        header('Content-Type: application/json');
        echo json_encode($commande);
    }
    
    /**
     * Enregistre une réception
     */
    public function enregistrer(): void {
        $this->verifierMethode('POST');
        
        $bon_commande_id = $_POST['bon_commande_id'] ?? null;
        $date_reception = $_POST['date_reception'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (!$bon_commande_id || empty($lignes)) {
            $_SESSION['error'] = 'Veuillez sélectionner une commande et ajouter au moins une ligne.';
            $this->redirigerVers('/appros/receptions/creer');
            return;
        }
        
        $commande = $this->modele->getCommandeDetails((int)$bon_commande_id);
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/receptions/creer');
            return;
        }
        
        $fournisseur_id = $commande['fournisseur_id'];
        $numero = 'BR-' . date('Ymd') . '-' . rand(100, 999);
        
        try {
            $receptionId = $this->modele->creerAvecLignes([
                ':numero' => $numero,
                ':bon_commande_id' => $bon_commande_id,
                ':fournisseur_id' => $fournisseur_id,
                ':date_reception' => $date_reception,
                ':notes' => $notes
            ], $lignes);
            
            $_SESSION['success'] = 'Réception créée avec succès. Stock mis à jour.';
            $this->redirigerVers('/appros/receptions');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/receptions/creer');
        }
    }
    
    /**
     * Détail d'une réception
     */
    public function detail(int $id): void {
        $reception = $this->modele->trouverComplet($id);
        
        if (!$reception) {
            $_SESSION['error'] = 'Réception non trouvée.';
            $this->redirigerVers('/appros/receptions');
            return;
        }
        
        $this->rendrePage('Approvisionnements', 'reception_detail', [
            'titre' => 'Détail réception ' . $reception['numero'],
            'reception' => $reception
        ]);
    }
    
    /**
     * PDF de la réception (version corrigée)
     */
    public function pdf(int $id): void {
        $reception = $this->modele->trouverComplet($id);
        
        if (!$reception) {
            $_SESSION['error'] = 'Réception non trouvée.';
            $this->redirigerVers('/appros/receptions');
            return;
        }
        
        require_once ROOT . '/vendor/fpdf/fpdf.php';
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('BON DE RECEPTION'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('N° ' . $reception['numero']), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Fournisseur :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($reception['fournisseur_nom']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Date :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('d/m/Y', strtotime($reception['date_reception'])), 0, 1);
        
        $pdf->Ln(10);
        
        // En-tête du tableau
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, $this->convertir('Désignation'), 1, 0, 'L');
        $pdf->Cell(25, 8, $this->convertir('Qté reçue'), 1, 0, 'C');
        $pdf->Cell(30, 8, $this->convertir('PU HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('Montant HT'), 1, 1, 'R');
        
        // Lignes
        $pdf->SetFont('Arial', '', 10);
        $total_ht = 0;
        foreach ($reception['lignes'] as $ligne) {
            $montant = $ligne['quantite_recue'] * $ligne['prix_unitaire_ht'];
            $total_ht += $montant;
            $pdf->Cell(80, 8, $this->convertir($ligne['designation']), 1);
            $pdf->Cell(25, 8, $ligne['quantite_recue'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($ligne['prix_unitaire_ht'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(35, 8, number_format($montant, 0, ',', ' '), 1, 1, 'R');
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(135, 8, $this->convertir('Total HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($total_ht, 0, ',', ' '), 1, 1, 'R');
        
        $pdf->Output('I', 'bon_reception_' . $reception['numero'] . '.pdf');
    }
    
    /**
     * Formulaire de modification d'une réception
     */
    public function editer(int $id): void {
        $reception = $this->modele->trouverComplet($id);
        
        if (!$reception) {
            $_SESSION['error'] = 'Réception non trouvée.';
            $this->redirigerVers('/appros/receptions');
            return;
        }
        
        $commandes = $this->modele->getCommandesValidees();
        $produits = (new Produit())->listerTout();
        
        $this->rendrePage('Approvisionnements', 'reception_formulaire', [
            'titre' => 'Modifier la réception ' . $reception['numero'],
            'reception' => $reception,
            'commandes' => $commandes,
            'produits' => $produits,
            'isEdit' => true
        ]);
    }
    
    /**
     * Met à jour une réception
     */
    public function mettreAJour(int $id): void {
        $this->verifierMethode('POST');
        
        $bon_commande_id = $_POST['bon_commande_id'] ?? null;
        $date_reception = $_POST['date_reception'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (!$bon_commande_id || empty($lignes)) {
            $_SESSION['error'] = 'Veuillez sélectionner une commande et ajouter au moins une ligne.';
            $this->redirigerVers('/appros/receptions/' . $id . '/modifier');
            return;
        }
        
        try {
            $this->modele->mettreAJour($id, [
                ':bon_commande_id' => $bon_commande_id,
                ':date_reception' => $date_reception,
                ':notes' => $notes
            ], $lignes);
            
            $_SESSION['success'] = 'Réception mise à jour avec succès.';
            $this->redirigerVers('/appros/receptions/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/receptions/' . $id . '/modifier');
        }
    }
    
    /**
     * Supprime une réception (annule la mise à jour du stock)
     */
    public function supprimer(int $id): void {
        $this->verifierMethode('POST');
        
        try {
            $this->modele->supprimer($id);
            $_SESSION['success'] = 'Réception supprimée avec succès. Le stock a été restauré.';
            $this->redirigerVers('/appros/receptions');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/receptions');
        }
    }
    
    /**
     * Récupère les montants d'une réception (HT, TVA, TTC) pour pré-remplir la facture
     */
    public function getMontants(int $id): void {
        $reception = $this->modele->trouverComplet($id);
        
        if (!$reception) {
            http_response_code(404);
            echo json_encode(['error' => 'Réception non trouvée']);
            return;
        }
        
        $total_ht = 0;
        foreach ($reception['lignes'] as $ligne) {
            $total_ht += $ligne['quantite_recue'] * $ligne['prix_unitaire_ht'];
        }
        
        $tva_taux = 19.25;
        $montant_tva = $total_ht * ($tva_taux / 100);
        $montant_ttc = $total_ht + $montant_tva;
        
        header('Content-Type: application/json');
        echo json_encode([
            'montant_ht' => round($total_ht, 2),
            'montant_tva' => round($montant_tva, 2),
            'montant_ttc' => round($montant_ttc, 2),
            'fournisseur_id' => $reception['fournisseur_id'],
            'fournisseur_nom' => $reception['fournisseur_nom']
        ]);
    }
}