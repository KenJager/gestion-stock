<?php
// modules/utilisateurs/Controllers/DashboardController.php

namespace utilisateurs\controllers;

class DashboardController extends \Controller {

    public function index(): void {
        $this->rendrePage('utilisateurs', 'tableau_de_bord', [
            'titre'       => 'Tableau de bord',
            'moduleActif' => 'tableau-de-bord',
        ]);
    }
}
