<?php
// modules/Utilisateurs/views/login.php
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= APP_NOM ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background);
            padding: 16px;
        }
        .login-card { width: 100%; max-width: 380px; }
        .login-logo {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px; margin-bottom: 24px;
        }
        .login-logo .logo-icon { width: 48px; height: 48px; }
        .login-logo .logo-icon i { font-size: 20px; }
        .login-title { font-size: 18px; font-weight: 600; }
        .login-subtitle { font-size: 13px; color: #71717a; }
    </style>
</head>
<body class="h-full">
<div class="login-page">
    <div class="login-card">

        <div class="login-logo">
            <div class="logo-icon">
                <i class="fa-solid fa-cube"></i>
            </div>
            <p class="login-title"><?= APP_NOM ?></p>
            <p class="login-subtitle">Connectez-vous à votre compte</p>
        </div>

        <div class="carte p-6">
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($erreur) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/connexion" method="POST">
                <div class="form-group mb-4">
                    <label for="login" class="label label-required">Identifiant</label>
                    <input type="text" id="login" name="login" class="input"
                           placeholder="ex: jdupont" required autofocus
                           value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                </div>

                <div class="form-group mb-4">
                    <label for="mot_de_passe" class="label label-required">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="input"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-right-to-bracket"></i> Se connecter
                </button>
            </form>
        </div>

    </div>
</div>
</body>
</html>
