<?php
// views/erreurs/404.php
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — <?= defined('APP_NOM') ? APP_NOM : 'SGestion' ?></title>
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full">
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:16px; padding:16px; text-align:center;">
    <i class="fa-solid fa-compass" style="font-size:48px; color:#a1a1aa;"></i>
    <h1 style="font-size:48px; font-weight:700; margin:0;">404</h1>
    <p style="color:#71717a; font-size:14px;">La page que vous recherchez n'existe pas.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/tableau-de-bord" class="btn btn-primary">
        <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
    </a>
</div>
</body>
</html>
