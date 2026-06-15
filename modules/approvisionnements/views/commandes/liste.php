<?php
// modules/approvisionnements/views/commandes/liste.php
// Variables : $commandes (array)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Bons de commande fournisseur</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($commandes) ?> bon(s) de commande</p>
        </div>
        <?php if (Auth::peut('APPRO_CREER')): ?>
            <a href="<?= BASE_URL ?>/appros/commandes/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle commande
            </a>
        <?php endif; ?>
    </div>

    <!-- Recherche -->
    <div class="mt-4 carte p-3">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
            <input type="search" id="recherche-cmd"
                   placeholder="Rechercher par numéro, fournisseur…"
                   class="input border-0 shadow-none focus:ring-0 py-1 px-0 text-sm flex-1"
                   oninput="filtrerTableau(this.value)">
        </div>
    </div>

    <!-- Tableau -->
    <div class="mt-4 table-container">
        <table class="table" id="tableau-commandes">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Fournisseur</th>
                    <th>Date commande</th>
                    <th>Livraison prévue</th>
                    <th class="text-right">Montant TTC</th>
                    <th class="text-center">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commandes)): ?>
                    <tr><td colspan="7" class="text-center text-gray-400 py-12">
                        <i class="fa-solid fa-file-invoice text-2xl mb-2 block"></i>
                        Aucun bon de commande enregistré
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($commandes as $c): ?>
                        <?php
                            $search = strtolower(($c['numero'] ?? '') . ' ' . ($c['fournisseur_nom'] ?? ''));
                            $badgeStatut = [
                                'BROUILLON'      => 'badge-secondary',
                                'VALIDÉ'         => 'badge-info',
                                'REÇU_PARTIEL'   => 'badge-warning',
                                'REÇU_TOTAL'     => 'badge-success',
                                'ANNULÉ'         => 'badge-danger',
                            ][$c['statut']] ?? 'badge-secondary';
                        ?>
                        <tr data-recherche="<?= htmlspecialchars($search) ?>">
                            <td class="font-mono text-xs">
                                <a href="<?= BASE_URL ?>/appros/commandes/<?= $c['id'] ?>"
                                   class="text-blue-600 hover:underline">
                                    <?= htmlspecialchars($c['numero']) ?>
                                </a>
                            </td>
                            <td class="text-gray-700"><?= htmlspecialchars($c['fournisseur_nom'] ?? '—') ?></td>
                            <td class="text-gray-500 text-sm"><?= date('d/m/Y', strtotime($c['date_commande'])) ?></td>
                            <td class="text-gray-500 text-sm">
                                <?= $c['date_livraison_prevue'] ? date('d/m/Y', strtotime($c['date_livraison_prevue'])) : '—' ?>
                            </td>
                            <td class="text-right font-mono text-sm">
                                <?= number_format((float)$c['montant_ttc'], 0, ',', ' ') ?>
                                <span class="text-gray-400 text-xs">FCFA</span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badgeStatut ?>"><?= htmlspecialchars($c['statut']) ?></span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= BASE_URL ?>/appros/commandes/<?= $c['id'] ?>"
                                       class="btn btn-secondary text-blue-600" title="Détail">
                                        <i class="fa-regular fa-eye text-sm"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/appros/commandes/<?= $c['id'] ?>/pdf"
                                       class="btn btn-secondary text-orange-600" title="PDF" target="_blank">
                                        <i class="fa-solid fa-file-pdf text-sm"></i>
                                    </a>
                                    <?php if (Auth::peut('APPRO_MODIFIER') && $c['statut'] === 'BROUILLON'): ?>
                                        <a href="<?= BASE_URL ?>/appros/commandes/<?= $c['id'] ?>/modifier"
                                           class="btn btn-secondary text-green-600" title="Modifier">
                                            <i class="fa-regular fa-pen-to-square text-sm"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (Auth::peut('APPRO_SUPPRIMER') && in_array($c['statut'], ['BROUILLON', 'ANNULÉ'])): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/appros/commandes/<?= $c['id'] ?>/supprimer"
                                              class="inline"
                                              data-confirmer="Supprimer le bon <?= htmlspecialchars($c['numero']) ?> ?">
                                            <button type="submit" class="btn btn-danger text-red-500" title="Supprimer">
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