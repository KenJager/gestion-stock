<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// public/index.php — Point d'entrée unique
// ============================================================================

// ── 1. Constantes globales ───────────────────────────────────────────────────
define('ROOT',     dirname(__DIR__));
define('APP_NOM',  'SGestion');

// BASE_URL : adapter selon l'environnement (ou surcharger via .env)
define('BASE_URL', rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'));

// ── 2. Rapport d'erreurs (masqué en production via .env APP_ENV=production) ──
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

// ── 3. Chargement du fichier .env (AVANT tout le reste) ─────────────────────
$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#')) continue;
        if (str_contains($ligne, '=')) {
            [$cle, $valeur] = explode('=', $ligne, 2);
            $cle    = trim($cle);
            $valeur = trim($valeur, " \t\"'"); // retirer guillemets éventuels
            if (!getenv($cle)) {               // ne pas écraser une var système
                putenv("{$cle}={$valeur}");
                $_ENV[$cle] = $valeur;
            }
        }
    }
}

// ── 4. Autoloader PSR-4 simplifié ────────────────────────────────────────────
spl_autoload_register(function (string $classe): void {

    // 🔥 Gérer les classes core (sans namespace ou avec namespace Core\)
    if (!str_contains($classe, '\\') || str_starts_with($classe, 'Core\\')) {
        $nomClasse = str_replace('Core\\', '', $classe);
        $fichier = ROOT . "/core/{$nomClasse}.php";
        if (file_exists($fichier)) {
            require_once $fichier;
            return;
        }
    }

    // 🔥 Enlever le préfixe "Modules\" si présent (pour compatibilité)
    if (str_starts_with($classe, 'Modules\\')) {
        $classe = substr($classe, 8);
    }

    // Classes de modules
    $chemin = str_replace('\\', DIRECTORY_SEPARATOR, $classe);
    $fichier = ROOT . '/modules/' . $chemin . '.php';
    if (file_exists($fichier)) {
        require_once $fichier;
        return;
    }
});
// ── 5. Chargement du core (ordre important) ──────────────────────────────────
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Model.php';
require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Router.php';

// ── 6. Configuration base de données ─────────────────────────────────────────
Database::chargerConfig(require ROOT . '/config/database.php');

// ── 7. Démarrage de la session ───────────────────────────────────────────────
Auth::demarrer();

// ── 8. Chargement des routes ─────────────────────────────────────────────────
require ROOT . '/routes/web.php';

// ── 9. Dispatch ──────────────────────────────────────────────────────────────
// ── 9. Dispatch ──────────────────────────────────────────────────────────────
Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);