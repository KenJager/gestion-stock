<?php
// modules/Utilisateurs/Controllers/DashboardController.php

namespace Utilisateurs\Controllers;

class DashboardController extends \Controller {

    public function index(): void {
        $this->rendrePage('Utilisateurs', 'tableau_de_bord', [
            'titre'       => 'Tableau de bord',
            'moduleActif' => 'tableau-de-bord',
        ]);
    }
}
