<?php
// modules/Approvisionnements/Controllers/BonCommandeController.php

namespace Approvisionnements\Controllers;

use \Controller; 
use Approvisionnements\Models\BonCommande;
use Approvisionnements\Models\Fournisseur;
use Approvisionnements\Models\Produit;

class BonCommandeController extends Controller {
    
    private BonCommande $modele;
    
    public function __construct() {
        // parent::__construct();
        $this->modele = new BonCommande();
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Liste des commandes
     */
    public function index(): void {
        $commandes = $this->modele->listerTout();
        
        $this->rendrePage('Approvisionnements', 'commande_liste', [
            'titre' => 'Bons de commande fournisseur',
            'commandes' => $commandes
        ]);
    }
    
    /**
     * Formulaire de création
     */
    public function creer(): void {
        $fournisseurs = (new Fournisseur())->listerTout();
        $produits = (new Produit())->listerTout();
        
        $this->rendrePage('Approvisionnements', 'commande_formulaire', [
            'titre' => 'Nouvelle commande fournisseur',
            'fournisseurs' => $fournisseurs,
            'produits' => $produits
        ]);
    }
    
    /**
     * Enregistre une nouvelle commande
     */
    public function enregistrer(): void {
        $this->verifierMethode('POST');
        
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $date_commande = $_POST['date_commande'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (!$fournisseur_id) {
            $_SESSION['error'] = 'Veuillez sélectionner un fournisseur.';
            $this->redirigerVers('/appros/commandes/creer');
            return;
        }
        
        if (empty($lignes)) {
            $_SESSION['error'] = 'Ajoutez au moins un produit à la commande.';
            $this->redirigerVers('/appros/commandes/creer');
            return;
        }
        
        $total_ht = 0;
        foreach ($lignes as $ligne) {
            $total_ht += floatval($ligne['quantite']) * floatval($ligne['prix_unitaire']);
        }
        $tva_taux = 19.25;
        $montant_tva = $total_ht * ($tva_taux / 100);
        $montant_ttc = $total_ht + $montant_tva;
        $numero = 'BCF-' . date('Ymd') . '-' . rand(100, 999);
        
        try {
            $modele = new BonCommande();
            $commandeId = $modele->creerAvecLignes([
                ':numero' => $numero,
                ':fournisseur_id' => $fournisseur_id,
                ':date_commande' => $date_commande,
                ':montant_ht' => $total_ht,
                ':montant_tva' => $montant_tva,
                ':montant_ttc' => $montant_ttc,
                ':notes' => $notes
            ], $lignes);
            
            $_SESSION['success'] = 'Commande créée avec succès.';
            $this->redirigerVers('/appros/commandes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/commandes/creer');
        }
    }
    
    /**
     * Affiche le détail d'une commande
     */
    public function detail(int $id): void {
        $modele = new BonCommande();
        $commande = $modele->trouverComplet($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        $this->rendrePage('Approvisionnements', 'commande_detail', [
            'titre' => 'Détail commande ' . $commande['numero'],
            'commande' => $commande
        ]);
    }
    
    /**
     * Génère le PDF du bon de commande
     */
    public function pdf(int $id): void {
        $modele = new BonCommande();
        $commande = $modele->trouverComplet($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        require_once ROOT . '/vendor/fpdf/fpdf.php';
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->convertir('BON DE COMMANDE FOURNISSEUR'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir('N° ' . $commande['numero']), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Fournisseur :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $this->convertir($commande['fournisseur_nom']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, $this->convertir('Date :'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('d/m/Y', strtotime($commande['date_commande'])), 0, 1);
        
        $pdf->Ln(10);
        
        // En-tête du tableau
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, $this->convertir('Désignation'), 1, 0, 'L');
        $pdf->Cell(25, 8, $this->convertir('Qté'), 1, 0, 'C');
        $pdf->Cell(30, 8, $this->convertir('PU HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, $this->convertir('Montant HT'), 1, 1, 'R');
        
        // Lignes
        $pdf->SetFont('Arial', '', 10);
        $total_ht = 0;
        foreach ($commande['lignes'] as $ligne) {
            $montant = $ligne['quantite_commandee'] * $ligne['prix_unitaire_ht'];
            $total_ht += $montant;
            $pdf->Cell(80, 8, $this->convertir($ligne['designation']), 1);
            $pdf->Cell(25, 8, $ligne['quantite_commandee'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($ligne['prix_unitaire_ht'], 0, ',', ' '), 1, 0, 'R');
            $pdf->Cell(35, 8, number_format($montant, 0, ',', ' '), 1, 1, 'R');
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(135, 8, $this->convertir('Total HT'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($total_ht, 0, ',', ' '), 1, 1, 'R');
        
        $tva = $total_ht * 0.1925;
        $ttc = $total_ht + $tva;
        $pdf->Cell(135, 8, $this->convertir('TVA (19.25%)'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($tva, 0, ',', ' '), 1, 1, 'R');
        
        $pdf->SetFillColor(200, 220, 240);
        $pdf->Cell(135, 10, $this->convertir('Total TTC'), 1, 0, 'R', true);
        $pdf->Cell(35, 10, number_format($ttc, 0, ',', ' ') . ' FCFA', 1, 1, 'R', true);
        
        $pdf->Output('I', 'bon_commande_' . $commande['numero'] . '.pdf');
    }
    
    /**
     * Valide une commande (passe le statut à VALIDÉ)
     */
    public function valider(int $id): void {
        $this->verifierMethode('POST');
        
        $modele = new BonCommande();
        $commande = $modele->trouverParId($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        if ($commande['statut'] !== 'BROUILLON') {
            $_SESSION['error'] = 'Seules les commandes en brouillon peuvent être validées.';
            $this->redirigerVers('/appros/commandes/' . $id);
            return;
        }
        
        $modele->changerStatut($id, 'VALIDÉ');
        $_SESSION['success'] = 'Commande validée avec succès.';
        $this->redirigerVers('/appros/commandes/' . $id);
    }
    
    /**
     * Formulaire de modification d'une commande
     */
    public function editer(int $id): void {
        $modele = new BonCommande();
        $commande = $modele->trouverParId($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        if ($commande['statut'] !== 'BROUILLON') {
            $_SESSION['error'] = 'Seules les commandes en brouillon peuvent être modifiées.';
            $this->redirigerVers('/appros/commandes/' . $id);
            return;
        }
        
        $fournisseurs = (new \Approvisionnements\Models\Fournisseur())->listerTout();
        $produits = (new \Approvisionnements\Models\Produit())->listerTout();
        
        $this->rendrePage('Approvisionnements', 'commande_formulaire', [
            'titre' => 'Modifier la commande ' . $commande['numero'],
            'commande' => $commande,
            'fournisseurs' => $fournisseurs,
            'produits' => $produits,
            'isEdit' => true
        ]);
    }
    
    /**
     * Met à jour une commande existante
     */
    public function mettreAJour(int $id): void {
        $this->verifierMethode('POST');
        
        $modele = new BonCommande();
        $commande = $modele->trouverParId($id);
        
        if (!$commande || $commande['statut'] !== 'BROUILLON') {
            $_SESSION['error'] = 'Commande non modifiable.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $date_commande = $_POST['date_commande'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $lignes = $_POST['lignes'] ?? [];
        
        if (!$fournisseur_id || empty($lignes)) {
            $_SESSION['error'] = 'Veuillez sélectionner un fournisseur et ajouter au moins une ligne.';
            $this->redirigerVers('/appros/commandes/' . $id . '/modifier');
            return;
        }
        
        $total_ht = 0;
        foreach ($lignes as $ligne) {
            $total_ht += floatval($ligne['quantite']) * floatval($ligne['prix_unitaire']);
        }
        $tva_taux = 19.25;
        $montant_tva = $total_ht * ($tva_taux / 100);
        $montant_ttc = $total_ht + $montant_tva;
        
        try {
            $modele->mettreAJour($id, [
                'fournisseur_id' => $fournisseur_id,
                'date_commande' => $date_commande,
                'notes' => $notes,
                'montant_ht' => $total_ht,
                'montant_tva' => $montant_tva,
                'montant_ttc' => $montant_ttc
            ]);
            
            $modele->supprimerLignes($id);
            foreach ($lignes as $ligne) {
                $modele->ajouterLigne($id, $ligne);
            }
            
            $_SESSION['success'] = 'Commande mise à jour avec succès.';
            $this->redirigerVers('/appros/commandes/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
            $this->redirigerVers('/appros/commandes/' . $id . '/modifier');
        }
    }
    
    /**
     * Annule une commande
     */
    public function annuler(int $id): void {
        $this->verifierMethode('POST');
        
        $modele = new BonCommande();
        $commande = $modele->trouverParId($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        if ($commande['statut'] === 'REÇU_TOTAL') {
            $_SESSION['error'] = 'Commande déjà réceptionnée, impossible d\'annuler.';
            $this->redirigerVers('/appros/commandes/' . $id);
            return;
        }
        
        $modele->changerStatut($id, 'ANNULÉ');
        $_SESSION['success'] = 'Commande annulée avec succès.';
        $this->redirigerVers('/appros/commandes');
    }
    
    /**
     * Supprime une commande avec sauvegarde XML
     */
    public function supprimer(int $id): void {
        $this->verifierMethode('POST');
        
        $modele = new BonCommande();
        $commande = $modele->trouverComplet($id);
        
        if (!$commande) {
            $_SESSION['error'] = 'Commande non trouvée.';
            $this->redirigerVers('/appros/commandes');
            return;
        }
        
        $this->sauvegarderXML($commande);
        $modele->supprimer($id);
        
        $_SESSION['success'] = 'Commande supprimée avec succès. Une sauvegarde XML a été créée.';
        $this->redirigerVers('/appros/commandes');
    }
    
    /**
     * Sauvegarde une commande en XML
     */
    private function sauvegarderXML(array $commande): void {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><archive></archive>');
        $xml->addChild('type', 'commande_fournisseur');
        $xml->addChild('date_suppression', date('Y-m-d H:i:s'));
        $xml->addChild('utilisateur', $_SESSION['utilisateur_nom'] ?? 'Inconnu');
        
        $commandeNode = $xml->addChild('commande');
        foreach ($commande as $key => $value) {
            if ($key === 'lignes') continue;
            $commandeNode->addChild($key, htmlspecialchars((string)$value));
        }
        
        $lignesNode = $xml->addChild('lignes');
        foreach ($commande['lignes'] as $ligne) {
            $ligneNode = $lignesNode->addChild('ligne');
            foreach ($ligne as $key => $value) {
                $ligneNode->addChild($key, htmlspecialchars((string)$value));
            }
        }
        
        $dir = ROOT . '/storage/archives/commandes/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $filename = $dir . $commande['numero'] . '_' . date('Y-m-d_H-i-s') . '.xml';
        $xml->asXML($filename);
    }
}