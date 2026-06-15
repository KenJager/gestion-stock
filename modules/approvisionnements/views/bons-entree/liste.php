<?php
// modules/approvisionnements/views/bons-entree/liste.php
// Variables : $entrees
?>
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Bons d'entrée</h1>
        <p class="text-sm text-gray-500 mt-1"><?= count($entrees) ?> bon(s) d'entrée</p>
    </div>

    <div class="mt-4 table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Type source</th>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Notes</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entrees)): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-12">Aucun bon d'entrée</td></tr>
                <?php else: ?>
                    <?php foreach ($entrees as $e): ?>
                        <tr>
                            <td class="font-mono text-xs"><?= htmlspecialchars($e['numero']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($e['type_source']) ?></span></td>
                            <td class="text-sm"><?= date('d/m/Y', strtotime($e['date_entree'])) ?></td>
                            <td class="text-gray-500"><?= htmlspecialchars($e['utilisateur'] ?? '—') ?></td>
                            <td class="text-gray-500 text-sm"><?= htmlspecialchars(mb_strimwidth($e['notes'] ?? '', 0, 50, '…')) ?></td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/appros/bons-entree/<?= $e['id'] ?>/pdf" target="_blank" class="btn btn-secondary text-orange-600" title="PDF">
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