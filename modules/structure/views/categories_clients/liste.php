<?php
// modules/structure/views/categories_clients/liste.php
// Variables : $categories (array)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Catégories de clients</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($categories) ?> catégorie(s) enregistrée(s)</p>
        </div>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/categories-clients/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle catégorie
            </a>
        <?php endif; ?>
    </div>

    <!-- Tableau -->
    <div class="table-container mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Remise</th>
                    <th>Description</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-12">
                            <i class="fa-solid fa-tags text-2xl mb-2 block"></i>
                            Aucune catégorie enregistrée
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td class="font-mono text-xs text-gray-500"><?= htmlspecialchars($c['code']) ?></td>
                            <td>
                                <span class="font-medium text-gray-900"><?= htmlspecialchars($c['libelle']) ?></span>
                            </td>
                            <td>
                                <?php if ($c['remise_pct'] > 0): ?>
                                    <span class="badge badge-valide">
                                        <i class="fa-solid fa-percent text-[9px]"></i> <?= number_format($c['remise_pct'], 0, ',', ' ') ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-gray-500 text-sm"><?= htmlspecialchars($c['description'] ?? '—') ?></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Détail -->
                                    <a href="<?= BASE_URL ?>/structure/categories-clients/<?= $c['id'] ?>"
                                       class="btn btn-secondary text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                                       title="Voir le détail">
                                        <i class="fa-regular fa-eye text-sm"></i>
                                    </a>

                                    <!-- Modifier -->
                                    <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                        <a href="<?= BASE_URL ?>/structure/categories-clients/editer/<?= $c['id'] ?>"
                                           class="btn btn-secondary text-green-600 hover:text-green-800 hover:bg-green-50"
                                           title="Modifier">
                                            <i class="fa-regular fa-pen-to-square text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Supprimer -->
                                    <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/structure/categories-clients/supprimer/<?= $c['id'] ?>"
                                              class="inline"
                                              data-confirmer="Supprimer la catégorie « <?= htmlspecialchars($c['libelle']) ?> » ? Les clients seront réaffectés à « Aucune catégorie ».">
                                            <button type="submit" class="btn btn-danger text-red-500 hover:text-red-700 hover:bg-red-50"
                                                    title="Supprimer">
                                                <i class="fa-regular fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>