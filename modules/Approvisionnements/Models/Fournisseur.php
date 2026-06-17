<?php
// modules/Approvisionnements/Models/Fournisseur.php

namespace Approvisionnements\Models;

use \Model;

class Fournisseur extends Model {
    
    protected string $table = 'fournisseurs';
    
    /**
     * Liste tous les fournisseurs actifs
     */
    public function listerTout(): array {
        $sql = "SELECT * FROM " . $this->table . " 
                WHERE actif = true 
                ORDER BY raison_sociale";
        
        $resultat = $this->requete($sql);
        return $resultat->toutRecuperer();
    }
}