<?php
// modules/approvisionnements/views/receptions/liste.php
// Variables : $receptions
?>
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Bons de réception</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($receptions) ?> réception(s)</p>
        </div>
        <?php if (Auth::peut('APPRO_CREER')): ?>
            <a href="<?= BASE_URL ?>/appros/receptions/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle réception
            </a>
        <?php endif; ?>
    </div>

    <div class="mt-4 table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Commande liée</th>
                    <th>Fournisseur</th>
                    <th>Date réception</th>
                    <th class="text-center">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receptions)): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-12">
                        <i class="fa-solid fa-truck-arrow-right text-2xl mb-2 block"></i>
                        Aucune réception enregistrée
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($receptions as $r): ?>
                        <tr>
                            <td class="font-mono text-xs">
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $r['id'] ?>" class="text-blue-600 hover:underline">
                                    <?= htmlspecialchars($r['numero']) ?>
                                </a>
                            </td>
                            <td class="text-gray-600"><?= htmlspecialchars($r['commande_numero'] ?? '—') ?></td>
                            <td class="text-gray-700"><?= htmlspecialchars($r['fournisseur_nom']) ?></td>
                            <td class="text-gray-500 text-sm"><?= date('d/m/Y', strtotime($r['date_reception'])) ?></td>
                            <td class="text-center"><span class="badge badge-success"><?= htmlspecialchars($r['statut']) ?></span></td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $r['id'] ?>" class="btn btn-secondary text-blue-600" title="Détail">
                                    <i class="fa-regular fa-eye text-sm"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $r['id'] ?>/pdf" target="_blank" class="btn btn-secondary text-orange-600" title="PDF">
                                    <i class="fa-solid fa-file-pdf text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>