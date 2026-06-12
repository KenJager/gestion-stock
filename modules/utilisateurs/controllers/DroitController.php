<?php
// modules/utilisateurs/Controllers/DroitController.php

namespace utilisateurs\controllers;

use utilisateurs\models\Droit;

class DroitController extends \Controller {

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
    }

    // GET /droits
    public function index(): void {
        $droitsParModule = (new Droit())->listerParModule();

        $this->rendrePage('utilisateurs', 'droit_liste', [
            'titre'           => 'Droits d\'accès',
            'moduleActif'     => 'utilisateurs',
            'droitsParModule' => $droitsParModule,
        ]);
    }
}
