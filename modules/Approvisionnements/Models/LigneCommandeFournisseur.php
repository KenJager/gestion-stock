<?php
// modules/Approvisionnements/Models/LigneCommandeFournisseur.php

namespace Approvisionnements\Models;

use Core\Model;

class LigneCommandeFournisseur extends Model {
    
    protected string $table = 'lignes_commande_fournisseur';
    
    /**
     * Crée une ligne de commande
     */
    public function creer(array $donnees): int {
        $sql = "INSERT INTO " . $this->table . " 
                (bon_commande_id, produit_id, quantite_commandee, prix_unitaire_ht, tva)
                VALUES (:bon_commande_id, :produit_id, :quantite_commandee, :prix_unitaire_ht, :tva)
                RETURNING id";
        
        $resultat = $this->requete($sql, $donnees);
        $row = $resultat->unRecuperer();
        return (int) $row['id'];
    }
    
    /**
     * Récupère les lignes d'une commande
     */
    public function trouverParCommande(int $commandeId): array {
        $sql = "SELECT lcf.*, p.designation, p.code, p.unite
                FROM " . $this->table . " lcf
                JOIN produits p ON p.id = lcf.produit_id
                WHERE lcf.bon_commande_id = :commande_id
                ORDER BY lcf.id";
        
        $resultat = $this->requete($sql, [':commande_id' => $commandeId]);
        return $resultat->toutRecuperer();
    }
}