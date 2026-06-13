<?php
// views/layouts/sidebar.php
// Variable attendue : $moduleActif (string)

$navItems = [
    [
        'module'  => 'tableau-de-bord',
        'libelle' => 'Tableau de bord',
        'icone'   => 'fa-solid fa-chart-pie',
        'chemin'  => '/tableau-de-bord',
        'droit'   => null,
        'sousmenu' => []
    ],
    [
        'separateur' => 'Opérations',
    ],
    [
        'module'  => 'approvisionnements',
        'libelle' => 'Approvisionnements',
        'icone'   => 'fa-solid fa-boxes-stacked',
        'chemin'  => '#',
        'droit'   => 'APPRO_LIRE',
        'sousmenu' => [
            ['libelle' => 'Commandes fournisseurs', 'chemin' => '/approvisionnements/commandes'],
            ['libelle' => 'Bons de réception',      'chemin' => '/approvisionnements/receptions'],
            ['libelle' => 'Factures fournisseurs',  'chemin' => '/approvisionnements/factures'],
            ['libelle' => 'Entrées (dons)',         'chemin' => '/approvisionnements/dons'],
        ],
    ],
    [
        'module'  => 'ventes',
        'libelle' => 'Ventes',
        'icone'   => 'fa-solid fa-arrow-trend-up',
        'chemin'  => '#',
        'droit'   => 'VENTE_LIRE',
        'sousmenu' => [
            ['libelle' => 'Commandes clients',    'chemin' => '/ventes/commandes'],
            ['libelle' => 'Livraisons',            'chemin' => '/ventes/livraisons'],
            ['libelle' => 'Factures clients',      'chemin' => '/ventes/factures'],
            ['libelle' => 'Ventes comptant',       'chemin' => '/ventes/comptant'],
            ['libelle' => 'Sorties produits',      'chemin' => '/ventes/sorties'],
        ],
    ],
    [
        'separateur' => 'Référentiels',
    ],
    [
        'module'  => 'structure',
        'libelle' => 'Structure',
        'icone'   => 'fa-solid fa-layer-group',
        'chemin'  => '#',
        'droit'   => 'STRUCTURE_LIRE',
        'sousmenu' => [
            ['libelle' => 'Familles',         'chemin' => '/structure/familles'],
            ['libelle' => 'Produits',         'chemin' => '/structure/produits'],
            ['libelle' => 'Fournisseurs',     'chemin' => '/structure/fournisseurs'],
            ['libelle' => 'Clients',          'chemin' => '/structure/clients'],
            ['libelle' => 'Catégories clients','chemin' => '/structure/categories'],
            ['libelle' => 'Banques',          'chemin' => '/structure/banques'],
        ],
    ],
    [
        'separateur' => 'Administration',
    ],
    [
        'module'  => 'utilisateurs',
        'libelle' => 'Utilisateurs',
        'icone'   => 'fa-solid fa-users',
        'chemin'  => '#',
        'droit'   => 'USERS_LIRE',
        'sousmenu' => [
            ['libelle' => 'Liste des utilisateurs', 'chemin' => '/utilisateurs',  'droit' => 'USERS_LIRE'],
            ['libelle' => 'Groupes',                'chemin' => '/groupes',       'droit' => 'USERS_MODIFIER'],
            ['libelle' => 'Droits d\'accès',        'chemin' => '/droits',        'droit' => 'USERS_MODIFIER'],
            ['libelle' => 'Paramètres de connexion','chemin' => '/parametres',    'droit' => null],
            ['libelle' => 'Journal d\'audit',         'chemin' => '/audit',         'droit' => 'USERS_MODIFIER'],
            ['libelle' => 'Corbeille',               'chemin' => '/utilisateurs/corbeille',     'droit' => 'USERS_MODIFIER'],
        ],
    ],
];

$moduleActif = $moduleActif ?? '';

// Déterminer quel module a ses sous-menus ouverts (celui actif ou celui cliqué)
$ouvert = $_COOKIE['sidebar_ouvert'] ?? '';
$openModules = !empty($ouvert) ? explode(',', $ouvert) : [];
if (!empty($moduleActif) && !in_array($moduleActif, $openModules)) {
    $openModules[] = $moduleActif;
}
?>

<aside id="sidebar" class="sidebar">
    <!-- Logo / En-tête avec bouton de fermeture sur mobile -->
    <div class="sidebar-header">
        <div class="logo-icon">
            <i class="fa-solid fa-cube"></i>
        </div>
        <div class="logo-text">
            <p class="logo-title">SGestion</p>
            <p class="logo-subtitle">Système de gestion</p>
        </div>
        <button id="sidebar-close" class="sidebar-close mobile-only" aria-label="Fermer le menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation principale avec dropdowns -->
    <nav class="sidebar-nav" id="sidebar-nav">
        <?php foreach ($navItems as $item): ?>

            <?php if (isset($item['separateur'])): ?>
                <p class="nav-separateur">
                    <?= htmlspecialchars($item['separateur']) ?>
                </p>

            <?php elseif ($item['droit'] === null || Auth::peut($item['droit'] ?? '')):?>
                <?php
                    $estActif     = $moduleActif === $item['module'];
                    $aSousmenu    = !empty($item['sousmenu']);
                    $estOuvert    = in_array($item['module'], $openModules) || $estActif;
                ?>

                <div class="nav-item-wrapper" data-module="<?= $item['module'] ?>">
                    <!-- Lien principal (si pas de sous-menu, c'est un lien simple) -->
                    <?php if (!$aSousmenu): ?>
                        <a href="<?= BASE_URL . $item['chemin'] ?>"
                           class="nav-link <?= $estActif ? 'active' : '' ?>">
                            <span class="nav-link-content">
                                <i class="<?= $item['icone'] ?> nav-icon"></i>
                                <span class="nav-label"><?= htmlspecialchars($item['libelle']) ?></span>
                            </span>
                        </a>
                    <?php else: ?>
                        <!-- Lien avec dropdown (toggle) -->
                        <div class="nav-dropdown-trigger <?= $estActif ? 'active' : '' ?> <?= $estOuvert ? 'open' : '' ?>"
                             data-module="<?= $item['module'] ?>">
                            <div class="nav-link">
                                <span class="nav-link-content">
                                    <i class="<?= $item['icone'] ?> nav-icon"></i>
                                    <span class="nav-label"><?= htmlspecialchars($item['libelle']) ?></span>
                                </span>
                                <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
                            </div>
                        </div>

                        <!-- Sous-menu déroulant -->
                        <div class="submenu-container" data-module="<?= $item['module'] ?>"
                             style="<?= $estOuvert ? '' : 'display: none;' ?>">
                            <div class="submenu">
                                <?php foreach ($item['sousmenu'] as $sous): ?>
                                    <?php if (!isset($sous['droit']) || $sous['droit'] === null || \Auth::peut($sous['droit'])): ?>
                                    <a href="<?= BASE_URL . $sous['chemin'] ?>"
                                       class="submenu-link"
                                       data-nav-close>
                                        <?= htmlspecialchars($sous['libelle']) ?>
                                    </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Pied de sidebar -->
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar-small">
                <?= mb_strtoupper(mb_substr($utilisateur['nom'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-details">
                <p class="user-name"><?= htmlspecialchars($utilisateur['nom'] ?? '') ?></p>
                <p class="user-group"><?= htmlspecialchars($utilisateur['groupe_nom'] ?? '') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/deconnexion" class="logout-btn" title="Se déconnecter">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>
</aside>