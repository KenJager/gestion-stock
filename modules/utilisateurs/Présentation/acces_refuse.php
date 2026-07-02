<?php
// modules/Utilisateurs/views/acces_refuse.php
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — <?= APP_NOM ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full">
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:16px; padding:16px; text-align:center;">
    <i class="fa-solid fa-shield-halved" style="font-size:48px; color: var(--destructive);"></i>
    <h1 style="font-size:20px; font-weight:600;">Accès refusé</h1>
    <p style="color:#71717a; font-size:14px;">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-primary">
        <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
    </a>
</div>
</body>
</html>
