<?php
// modules/structure/views/produits/liste.php
// Variables : $produits (array)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Produits</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($produits) ?> produit(s) enregistré(s)</p>
        </div>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/produits/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouveau produit
            </a>
        <?php endif; ?>
    </div>

    <!-- Barre de recherche rapide -->
    <div class="mt-4 carte p-3">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
            <input type="search"
                   id="recherche-produit"
                   placeholder="Rechercher par code, désignation ou famille…"
                   class="input border-0 shadow-none focus:ring-0 py-1 px-0 text-sm flex-1"
                   oninput="filtrerTableau(this.value)">
        </div>
    </div>

    <!-- Tableau -->
    <div class="mt-4 table-container">
        <table class="table" id="tableau-produits">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th>Famille</th>
                    <th>Unité</th>
                    <th class="text-right">Prix vente HT</th>
                    <th class="text-right">Stock</th>
                    <th class="text-right">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produits)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-12">
                            <i class="fa-solid fa-box-open text-2xl mb-2 block"></i>
                            Aucun produit enregistré
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produits as $p): ?>
                        <?php 
                            $stockBas = (float)($p['stock_actuel'] ?? 0) < (float)($p['stock_minimum'] ?? 0);
                            $searchString = strtolower(
                                ($p['code'] ?? '') . ' ' . 
                                ($p['designation'] ?? '') . ' ' . 
                                ($p['famille_libelle'] ?? '')
                            );
                        ?>
                        <tr data-recherche="<?= htmlspecialchars($searchString) ?>">

                            <!-- Code -->
                            <td class="font-mono text-xs text-gray-500">
                                <?= htmlspecialchars($p['code'] ?? '') ?>
                            </td>

                            <!-- Désignation + badge père -->
                            <td>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($p['designation'] ?? '') ?>
                                </span>
                                <?php if (!empty($p['est_pere']) && $p['est_pere']): ?>
                                    <span class="badge badge-info ml-1">
                                        <i class="fa-solid fa-code-branch text-[9px]"></i> Père
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Famille -->
                            <td class="text-gray-600">
                                <?= htmlspecialchars($p['famille_libelle'] ?? '—') ?>
                            </td>

                            <!-- Unité -->
                            <td class="text-gray-500 text-xs">
                                <?= htmlspecialchars($p['unite'] ?? '—') ?>
                            </td>

                            <!-- Prix vente HT -->
                            <td class="text-right font-mono text-sm">
                                <?= number_format((float)($p['prix_vente_ht'] ?? 0), 0, ',', ' ') ?> 
                                <span class="text-gray-400 text-xs">FCFA</span>
                            </td>

                            <!-- Stock -->
                            <td class="text-right">
                                <span class="font-mono text-sm <?= $stockBas ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                                    <?= number_format((float)($p['stock_actuel'] ?? 0), 2, ',', ' ') ?>
                                </span>
                                <?php if ($stockBas): ?>
                                    <i class="fa-solid fa-triangle-exclamation text-red-400 ml-1 text-xs"
                                       title="Sous le stock minimum (<?= number_format((float)($p['stock_minimum'] ?? 0), 2, ',', ' ') ?>)"></i>
                                <?php endif; ?>
                            </td>

                            <!-- Statut actif -->
                            <td class="text-right">
                                <?php 
                                    $estActif = ($p['actif'] ?? true);
                                    if ($estActif === true || $estActif === 't' || $estActif === 1 || $estActif === 'true'):
                                ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactif</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">

                                    <!-- Détail -->
                                    <a href="<?= BASE_URL ?>/structure/produits/<?= $p['id'] ?>"
                                       class="btn btn-secondary text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                                       title="Voir le détail">
                                        <i class="fa-regular fa-eye text-sm"></i>
                                    </a>

                                    <!-- Modifier -->
                                    <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                        <a href="<?= BASE_URL ?>/structure/produits/editer/<?= $p['id'] ?>"
                                           class="btn btn-secondary text-green-600 hover:text-green-800 hover:bg-green-50"
                                           title="Modifier">
                                            <i class="fa-regular fa-pen-to-square text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Fractionner -->
                                    <?php if (Auth::peut('STRUCTURE_CREER') && !empty($p['est_pere']) && $p['est_pere']): ?>
                                        <a href="<?= BASE_URL ?>/structure/produits/fractionner/<?= $p['id'] ?>"
                                           class="btn btn-secondary text-purple-600 hover:text-purple-800 hover:bg-purple-50"
                                           title="Fractionner">
                                            <i class="fa-solid fa-code-branch text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Supprimer -->
                                    <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/structure/produits/supprimer/<?= $p['id'] ?>"
                                              class="inline"
                                              data-confirmer="Supprimer le produit « <?= htmlspecialchars($p['designation'] ?? '') ?> » ? Cette action est irréversible.">
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