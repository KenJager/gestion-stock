<?php
// public/index.php — Point d'entrée unique
// ============================================================================

// ── 1. Constante ROOT (nécessaire pour localiser .env) ───────────────────────
define('ROOT', dirname(__DIR__));

// ── 2. Chargement du fichier .env (AVANT toute autre constante) ─────────────
$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#')) continue;
        if (str_contains($ligne, '=')) {
            [$cle, $valeur] = explode('=', $ligne, 2);
            $cle    = trim($cle);
            $valeur = trim($valeur, " \t\"'");
            if (!getenv($cle)) {
                putenv("{$cle}={$valeur}");
                $_ENV[$cle] = $valeur;
            }
        }
    }
}

// ── 3. Constantes globales (dépendent du .env) ───────────────────────────────
define('APP_NOM',  'SGestion');
define('BASE_URL', rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'));

// ── 4. Rapport d'erreurs ──────────────────────────────────────────────────────
$env = getenv('APP_ENV') ?: 'development';
if ($env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT . '/storage/logs/php_errors.log');
}

// ── 5. Autoloader PSR-4 simplifié ─────────────────────────────────────────────
spl_autoload_register(function (string $classe): void {
    if (!str_contains($classe, '\\')) {
        $fichier = ROOT . "/core/{$classe}.php";
        if (file_exists($fichier)) {
            require_once $fichier;
            return;
        }
    }

    $chemin  = str_replace('\\', DIRECTORY_SEPARATOR, $classe);
    $fichier = ROOT . '/modules/' . $chemin . '.php';
    if (file_exists($fichier)) {
        require_once $fichier;
        return;
    }
});

// ── 6. Chargement du core (ordre important) ──────────────────────────────────
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Router.php';

// ── 7. Configuration base de données ──────────────────────────────────────────
$dbConfig = require ROOT . '/config/database.php';
$dbLocalFile = ROOT . '/config/database.local.php';
if (file_exists($dbLocalFile)) {
    $dbConfig = array_merge($dbConfig, require $dbLocalFile);
}
Database::chargerConfig($dbConfig);

// ── 8. Démarrage de la session ────────────────────────────────────────────────
Auth::demarrer();

// Définir les infos de l'utilisateur connecté pour le journal d'audit (triggers SQL)
if (Auth::estConnecte()) {
    $utilisateur = Auth::utilisateurCourant();
    Database::definirUtilisateurAudit((int) $utilisateur['id'], $utilisateur['login']);
}

// ── 9. Chargement des routes ──────────────────────────────────────────────────
require ROOT . '/routes/web.php';

// ── 10. Dispatch ────────────────────────────────────────────────────────────────
Router::dispatcher();
