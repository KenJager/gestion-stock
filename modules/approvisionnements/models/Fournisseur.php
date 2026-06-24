<?php

// modules/approvisionnements/models/Fournisseur.php

namespace approvisionnements\models;

require_once ROOT . '/core/Model.php';

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