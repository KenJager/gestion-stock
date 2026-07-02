<?php
// modules/structure/views/familles/liste.php
// Variables : $familles (array), $titre (string)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($titre ?? 'Familles') ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($familles) ?> famille(s) enregistrée(s)</p>
        </div>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/familles/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle famille
            </a>
        <?php endif; ?>
    </div>

    <!-- Recherche -->
    <div class="mt-4 carte p-3">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
            <input type="search"
                   id="recherche-famille"
                   placeholder="Rechercher par code ou libellé…"
                   class="input border-0 shadow-none focus:ring-0 py-1 px-0 text-sm flex-1"
                   oninput="filtrerTableau(this.value)">
        </div>
    </div>

    <!-- Tableau -->
    <div class="mt-4 table-container">
        <table class="table" id="tableau-familles">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Description</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($familles)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-gray-400 py-12">
                            <i class="fa-solid fa-sitemap text-2xl mb-2 block"></i>
                            Aucune famille enregistrée
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($familles as $f): ?>
                        <?php $searchString = strtolower((($f['code'] ?? '') . ' ' . ($f['libelle'] ?? '') . ' ' . ($f['description'] ?? ''))); ?>
                        <tr data-recherche="<?= htmlspecialchars($searchString) ?>">
                            <td class="font-mono text-xs text-gray-500"><?= htmlspecialchars($f['code'] ?? '') ?></td>
                            <td class="font-medium text-gray-900"><?= htmlspecialchars($f['libelle'] ?? '') ?></td>
                            <td class="text-gray-600"><?= htmlspecialchars($f['description'] ?? '—') ?></td>

                            <!-- Actions -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">

                                    <!-- Détail -->
                                    <a href="<?= BASE_URL ?>/structure/familles/detail/<?= $f['id'] ?>"
                                       class="btn btn-secondary text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                                       title="Voir le détail">
                                        <i class="fa-regular fa-eye text-sm"></i>
                                    </a>

                                    <!-- Modifier -->
                                    <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                        <a href="<?= BASE_URL ?>/structure/familles/editer/<?= $f['id'] ?>"
                                           class="btn btn-secondary text-green-600 hover:text-green-800 hover:bg-green-50"
                                           title="Modifier">
                                            <i class="fa-regular fa-pen-to-square text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Supprimer -->
                                    <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/structure/familles/supprimer/<?= $f['id'] ?>"
                                              class="inline"
                                              data-confirmer="Supprimer la famille « <?= htmlspecialchars($f['libelle'] ?? '') ?> » ? Les produits liés seront archivés.">
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