<?php
// modules/Approvisionnements/Controllers/ArchiveController.php

namespace Approvisionnements\Controllers;

use \Controller;
use \Database;

class ArchiveController extends Controller {
    
    private string $archiveDir;
    
    public function __construct() {
        // parent::__construct(); // Désactivé pour les tests
        $this->archiveDir = ROOT . '/storage/archives/commandes/';
    }
    
    /**
     * Convertit une chaîne UTF-8 en ISO-8859-1 pour FPDF
     */
    private function convertir(string $texte): string {
        return mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8');
    }
    
    /**
     * Liste des commandes archivées
     */
    public function index(): void {
        $archives = $this->getArchives();
        
        $this->rendrePage('Approvisionnements', 'archive_liste', [
            'titre' => 'Commandes archivées',
            'archives' => $archives
        ]);
    }
    
    /**
     * Détail d'une archive
     */
    public function detail(string $filename): void {
        $data = $this->getArchiveData($filename);
        
        if (!$data) {
            $_SESSION['error'] = 'Archive non trouvée.';
            $this->redirigerVers('/appros/archives');
            return;
        }
        
        $this->rendrePage('Approvisionnements', 'archive_detail', [
            'titre' => 'Détail archive',
            'filename' => $filename,
            'archive' => $data
        ]);
    }
    
    /**
     * Restaure une commande depuis une archive
     */
    public function restaurer(string $filename): void {
        $this->verifierMethode('POST');
        
        $data = $this->getArchiveData($filename);
        
        if (!$data) {
            $_SESSION['error'] = 'Archive non trouvée.';
            $this->redirigerVers('/appros/archives');
            return;
        }
        
        try {
            $this->restaurerCommande($data);
            $_SESSION['success'] = 'Commande restaurée avec succès.';
            $this->redirigerVers('/appros/archives');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la restauration : ' . $e->getMessage();
            $this->redirigerVers('/appros/archives');
        }
    }
    
    /**
     * Supprime un fichier archive
     */
    public function supprimerArchive(string $filename): void {
        $this->verifierMethode('POST');
        
        $filepath = $this->archiveDir . $filename;
        
        if (!file_exists($filepath)) {
            $_SESSION['error'] = 'Archive non trouvée.';
            $this->redirigerVers('/appros/archives');
            return;
        }
        
        unlink($filepath);
        $_SESSION['success'] = 'Archive supprimée.';
        $this->redirigerVers('/appros/archives');
    }
    
    // ─── Méthodes privées ────────────────────────────────────────────────────
    
    /**
     * Récupère la liste des fichiers XML dans le dossier d'archives
     */
    private function getArchives(): array {
        $archives = [];
        
        if (!is_dir($this->archiveDir)) {
            return $archives;
        }
        
        $files = glob($this->archiveDir . '*.xml');
        rsort($files); // trier par date décroissante
        
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            
            // Extraire les infos du nom du fichier
            $parts = explode('_', $filename);
            $numero = $parts[0] ?? '';
            $dateSuppression = '';
            
            if (isset($parts[1])) {
                $dateSuppression = str_replace('.xml', '', $parts[1]);
                $dateSuppression = str_replace('-', ':', $dateSuppression);
            }
            
            $archives[] = [
                'filename' => $filename,
                'numero' => $numero,
                'date_suppression' => $dateSuppression,
                'taille' => filesize($filepath)
            ];
        }
        
        return $archives;
    }
    
    /**
     * Récupère et parse le contenu d'un fichier archive XML
     */
    private function getArchiveData(string $filename): ?array {
        $filepath = $this->archiveDir . $filename;
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        $xml = simplexml_load_file($filepath);
        if (!$xml) {
            return null;
        }
        
        $commande = [];
        
        // Infos générales
        $commande['type'] = (string) $xml->type;
        $commande['date_suppression'] = (string) $xml->date_suppression;
        $commande['utilisateur'] = (string) $xml->utilisateur;
        
        // Commande
        $commande['commande'] = [];
        foreach ($xml->commande->children() as $key => $value) {
            $commande['commande'][$key] = (string) $value;
        }
        
        // Lignes
        $commande['lignes'] = [];
        if (isset($xml->lignes)) {
            foreach ($xml->lignes->ligne as $ligne) {
                $ligneData = [];
                foreach ($ligne->children() as $key => $value) {
                    $ligneData[$key] = (string) $value;
                }
                $commande['lignes'][] = $ligneData;
            }
        }
        
        return $commande;
    }
    
    /**
     * Restaure une commande dans la base de données
     */
    private function restaurerCommande(array $data): void {
        $commande = $data['commande'];
        $lignes = $data['lignes'];
        
        $db = Database::connexion();
        
        try {
            $db->beginTransaction();
            
            // Vérifier si le numéro existe déjà
            $check = $db->prepare("SELECT id FROM bons_commande_fournisseur WHERE numero = ?");
            $check->execute([$commande['numero']]);
            if ($check->fetch()) {
                throw new \Exception('Une commande avec ce numéro existe déjà.');
            }
            
            // Restaurer la commande
            $sql = "INSERT INTO bons_commande_fournisseur 
                    (numero, fournisseur_id, date_commande, statut, montant_ht, montant_tva, montant_ttc, notes)
                    VALUES (:numero, :fournisseur_id, :date_commande, :statut, :montant_ht, :montant_tva, :montant_ttc, :notes)
                    RETURNING id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':numero' => $commande['numero'],
                ':fournisseur_id' => $commande['fournisseur_id'],
                ':date_commande' => $commande['date_commande'],
                ':statut' => $commande['statut'] ?? 'BROUILLON',
                ':montant_ht' => $commande['montant_ht'] ?? 0,
                ':montant_tva' => $commande['montant_tva'] ?? 0,
                ':montant_ttc' => $commande['montant_ttc'] ?? 0,
                ':notes' => $commande['notes'] ?? ''
            ]);
            
            $commandeId = $stmt->fetch()['id'];
            
            // Restaurer les lignes
            if (!empty($lignes)) {
                $sqlLigne = "INSERT INTO lignes_commande_fournisseur 
                             (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
                             VALUES (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)";
                
                $stmtLigne = $db->prepare($sqlLigne);
                foreach ($lignes as $ligne) {
                    $stmtLigne->execute([
                        ':bon_commande_id' => $commandeId,
                        ':produit_id' => $ligne['produit_id'],
                        ':quantite_commandee' => $ligne['quantite_commandee'] ?? 0,
                        ':prix_unitaire_ht' => $ligne['prix_unitaire_ht'] ?? 0,
                        ':tva' => $ligne['tva'] ?? 19.25
                    ]);
                }
            }
            
            $db->commit();
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}