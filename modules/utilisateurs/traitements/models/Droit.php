<?php
// modules/Utilisateurs/Models/Droit.php

namespace utilisateurs\traitements\models;

class Droit extends \Model {

    protected string $table = 'droits';

    /**
     * Liste tous les droits, regroupés par module.
     * Retourne un tableau associatif : ['MODULE' => [droits...]]
     */
    public function listerParModule(): array {
        $sql = "SELECT * FROM droits ORDER BY module ASC, code ASC";
        $droits = \Database::query($sql)->fetchAll();

        $groupes = [];
        foreach ($droits as $droit) {
            $groupes[$droit['module']][] = $droit;
        }
        return $groupes;
    }

    public function listerTout(): array {
        $sql = "SELECT * FROM droits ORDER BY module ASC, code ASC";
        return \Database::query($sql)->fetchAll();
    }
}
