<?php
// config/database.php — Configuration de la base de données
//
// Ne JAMAIS commiter ce fichier avec de vraies credentials.
// Copier ce fichier en database.local.php pour l'environnement local
// et ajouter database.local.php dans .gitignore.
//
// Les valeurs sont lues depuis les variables d'environnement en priorité.
// Pour le développement local, créer un fichier .env à la racine (non commité).

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '5432',
    'dbname'   => getenv('DB_NAME') ?: 'geststock',
    'user'     => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASS') ?: '',       // ← vide par défaut, forcer via .env
    'charset'  => 'UTF8',
];