<?php
// core/Model.php

abstract class Model {
    
    protected ?\PDOStatement $dernierResultat = null;
    
    /**
     * Exécute une requête SQL préparée
     */
    protected function requete(string $sql, array $params = []): self {
        $this->dernierResultat = Database::query($sql, $params);
        return $this;
    }
    
    /**
     * Récupère tous les résultats
     */
    protected function toutRecuperer(): array {
        return $this->dernierResultat->fetchAll();
    }
    
    /**
     * Récupère un seul résultat
     */
    protected function unRecuperer(): array|false {
        return $this->dernierResultat->fetch();
    }
}