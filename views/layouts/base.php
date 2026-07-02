<?php
// views/layouts/base.php
// Variables attendues : $titrePage, $moduleActif, $contenuPage, $moduleAssets (optionnel)
$utilisateur = Auth::utilisateurCourant();
$titrePage   = htmlspecialchars($titrePage ?? APP_NOM ?? 'SGestion');
$moduleAssets = $moduleAssets ?? '';  // ← NOUVEAU : pour les assets spécifiques
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $titrePage ?> — SGestion</title>
    
    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    
    <!-- CSS SPÉCIFIQUE AU MODULE (si défini) -->
    <?php if (!empty($moduleAssets)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modules/<?= $moduleAssets ?>.css">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full">

<!-- Overlay pour fermer la sidebar sur mobile -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="app-container">

    <!-- SIDEBAR -->
    <?php require ROOT . '/views/layouts/sidebar.php'; ?>

    <!-- ZONE PRINCIPALE -->
    <div class="main-area">

        <!-- HEADER -->
        <header class="app-header">
            <div class="header-left">
                <button id="menu-toggle" class="menu-toggle" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="breadcrumb desktop-only">
                    <span class="header-breadcrumb">SGestion</span>
                    <span class="header-separator">/</span>
                    <span class="header-title"><?= $titrePage ?></span>
                </div>
            </div>

            <div class="header-right">
                <span class="header-date hidden-sm"><?= date('d/m/Y') ?></span>
                <div class="header-divider hidden-sm"></div>
                <span class="badge-group">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="badge-text"><?= htmlspecialchars($utilisateur['groupe_nom'] ?? '') ?></span>
                </span>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= mb_strtoupper(mb_substr($utilisateur['nom'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="user-name hidden-md"><?= htmlspecialchars($utilisateur['nom'] ?? '') ?></span>
                </div>
            </div>
        </header>

        <!-- CONTENU PRINCIPAL -->
        <main class="app-content">
            <div class="content-container">
                <?= $contenuPage ?>
            </div>
        </main>

    </div>

</div>

<!-- JS GLOBAL -->
<script>
    // Exposer BASE_URL en JS pour les scripts front-end
    window.BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/app.js" defer></script>

<!-- JS SPÉCIFIQUE AU MODULE (si défini) -->
<?php if (!empty($moduleAssets)): ?>
    <script src="<?= BASE_URL ?>/assets/js/modules/<?= $moduleAssets ?>.js" defer></script>
<?php endif; ?>

</body>
</html>