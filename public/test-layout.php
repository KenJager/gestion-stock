<?php
// public/test-layout.php
session_start();

// Simuler un utilisateur connecté pour le test
$_SESSION['user_id'] = 1;
$_SESSION['user_nom'] = 'Jean Dupont';
$_SESSION['user_login'] = 'jdupont';
$_SESSION['groupe_id'] = 1;
$_SESSION['groupe_nom'] = 'Administrateur';
$_SESSION['logged_in'] = true;

define('ROOT', dirname(__DIR__));
define('BASE_URL', 'http://localhost/projet/public');
define('APP_NOM', 'SGestion');

// Classes minimales pour le test
if (!class_exists('Auth')) {
    class Auth {
        public static function utilisateurCourant() {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'nom' => $_SESSION['user_nom'] ?? '',
                'login' => $_SESSION['user_login'] ?? '',
                'groupe_id' => $_SESSION['groupe_id'] ?? null,
                'groupe_nom' => $_SESSION['groupe_nom'] ?? ''
            ];
        }
        public static function demarrer() { if (session_status() === PHP_SESSION_NONE) session_start(); }
        public static function peut($droit) { return true; }
        public static function estConnecte() { return true; }
    }
}

Auth::demarrer();

$titrePage = 'Tableau de bord';
$moduleActif = 'tableau-de-bord';

// Contenu de test
ob_start();
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Tableau de bord</h1>
    <a href="#" class="btn btn-primary">Nouvelle vente</a>
</div>

<div class="grid grid-cols-1" style="grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="carte p-4">
        <p class="text-sm text-gray-500">Chiffre d'affaires</p>
        <p class="text-2xl font-bold">2 450 000 FCFA</p>
    </div>
    <div class="carte p-4">
        <p class="text-sm text-gray-500">Commandes en cours</p>
        <p class="text-2xl font-bold">12</p>
    </div>
    <div class="carte p-4">
        <p class="text-sm text-gray-500">Produits en stock</p>
        <p class="text-2xl font-bold">1 234</p>
    </div>
    <div class="carte p-4">
        <p class="text-sm text-gray-500">Alertes stock</p>
        <p class="text-2xl font-bold text-destructive">3</p>
    </div>
</div>

<div class="carte">
    <div class="p-4 border-b border-border">
        <h2 class="font-semibold">Dernières commandes</h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° commande</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CMD-001</td>
                    <td>Société ABC</td>
                    <td>01/06/2026</td>
                    <td>150 000 FCFA</td>
                    <td><span class="badge badge-valide">Validée</span></td>
                </tr>
                <tr>
                    <td>CMD-002</td>
                    <td>Entreprise XYZ</td>
                    <td>02/06/2026</td>
                    <td>89 000 FCFA</td>
                    <td><span class="badge badge-partiel">En attente</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php
$contenuPage = ob_get_clean();

require ROOT . '/views/layouts/base.php';
?>