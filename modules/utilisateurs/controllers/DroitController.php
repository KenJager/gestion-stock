<?php
// modules/Utilisateurs/Controllers/DroitController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Models\Droit;

class DroitController extends \Controller {

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
    }

    // GET /droits
    public function index(): void {
        $droitsParModule = (new Droit())->listerParModule();

        $this->rendrePage('Utilisateurs', 'droit_liste', [
            'titre'           => 'Droits d\'accès',
            'moduleActif'     => 'utilisateurs',
            'droitsParModule' => $droitsParModule,
        ]);
    }
}
