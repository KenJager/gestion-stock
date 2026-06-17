<?php
// modules/structure/views/categories_clients/detail.php
// Variables : $categorie (array)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Détail de la catégorie</h1>
            <p class="text-sm text-gray-500 mt-1">Informations complètes de la catégorie de clients</p>
        </div>
        <div class="flex gap-2">
            <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                <a href="<?= BASE_URL ?>/structure/categories-clients/editer/<?= $categorie['id'] ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/structure/categories-clients" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Détails -->
    <div class="carte p-6 mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="label">Code</label>
                <p class="font-mono text-gray-900"><?= htmlspecialchars($categorie['code']) ?></p>
            </div>
            <div class="form-group">
                <label class="label">Libellé</label>
                <p class="text-gray-900"><?= htmlspecialchars($categorie['libelle']) ?></p>
            </div>
            <div class="form-group">
                <label class="label">Remise</label>
                <p>
                    <?php if ($categorie['remise_pct'] > 0): ?>
                        <span class="badge badge-valide"><?= number_format($categorie['remise_pct'], 1, ',', ' ') ?>%</span>
                    <?php else: ?>
                        <span class="text-gray-400">Aucune remise</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="form-group">
                <label class="label">Date de création</label>
                <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($categorie['date_creation'] ?? 'now')) ?></p>
            </div>
            <div class="form-group col-span-2">
                <label class="label">Description</label>
                <p class="text-gray-700"><?= nl2br(htmlspecialchars($categorie['description'] ?? 'Aucune description')) ?></p>
            </div>
        </div>
    </div>

</div>