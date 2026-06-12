<?php
// modules/Utilisateurs/Controllers/AuditController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Models\AuditLog;

class AuditController extends \Controller {

    private AuditLog $modele;

    public function __construct() {
        parent::__construct();
        \Auth::verifierDroit('USERS_LIRE');
        $this->modele = new AuditLog();
    }

    // GET /audit
    public function index(): void {
        $filtres = [
            'table_name'        => $_GET['table_name'] ?? '',
            'operation'         => $_GET['operation'] ?? '',
            'login_utilisateur' => $_GET['login'] ?? '',
        ];
        // Retirer les filtres vides
        $filtres = array_filter($filtres, fn($v) => $v !== '');

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $parPage = 25;

        $entrees    = $this->modele->lister($filtres, $page, $parPage);
        $total      = $this->modele->compter($filtres);
        $totalPages = max(1, (int) ceil($total / $parPage));
        $tables     = $this->modele->listerTablesDistinctes();

        $this->rendrePage('Utilisateurs', 'audit_liste', [
            'titre'       => 'Journal d\'audit',
            'moduleActif' => 'audit',
            'entrees'     => $entrees,
            'tables'      => $tables,
            'filtres'     => [
                'table_name' => $_GET['table_name'] ?? '',
                'operation'  => $_GET['operation'] ?? '',
                'login'      => $_GET['login'] ?? '',
            ],
            'page'        => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }
}
