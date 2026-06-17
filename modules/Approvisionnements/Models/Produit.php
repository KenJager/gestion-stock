<?php
// modules/Approvisionnements/Models/Produit.php

namespace Approvisionnements\Models;

use \Model;

class Produit extends Model {
    
    protected string $table = 'produits';
    
    /**
     * Liste tous les produits actifs
     */
    public function listerTout(): array {
        $sql = "SELECT p.*, f.libelle as famille_nom
                FROM " . $this->table . " p
                JOIN familles_produits f ON f.id = p.famille_id
                WHERE p.actif = true 
                ORDER BY p.designation";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
}