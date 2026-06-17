<?php
// router.php — Routeur pour le serveur PHP intégré

// Si la requête demande un fichier dans public/assets/
$uri = $_SERVER['REQUEST_URI'];

// Extensions des fichiers statiques à servir directement
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    // Construire le chemin physique
    $file = __DIR__ . '/public' . $uri;
    
    // Vérifier si le fichier existe
    if (file_exists($file)) {
        // Déterminer le type MIME
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2'
        ];
        
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        return true; // Serveur a servi le fichier
    }
}

// Sinon, on passe la main à public/index.php
require __DIR__ . '/public/index.php';