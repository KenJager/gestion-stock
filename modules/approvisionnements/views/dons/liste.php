<?php
// modules/approvisionnements/views/dons/liste.php
// Variables : $dons, $debut, $fin
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Entrées de dons</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($dons) ?> don(s) enregistré(s)</p>
        </div>
        <?php if (Auth::peut('APPRO_CREER')): ?>
            <a href="<?= BASE_URL ?>/appros/dons/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouveau don
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtre période -->
    <form method="GET" action="<?= BASE_URL ?>/appros/dons" class="carte p-4 flex items-end gap-4">
        <div>
            <label class="label">Du</label>
            <input type="date" name="debut" class="input" value="<?= htmlspecialchars($debut) ?>">
        </div>
        <div>
            <label class="label">Au</label>
            <input type="date" name="fin" class="input" value="<?= htmlspecialchars($fin) ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
    </form>

    <div class="mt-4 table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Donateur</th>
                    <th>Date</th>
                    <th>Notes</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dons)): ?>
                    <tr><td colspan="5" class="text-center text-gray-400 py-12">Aucun don sur cette période</td></tr>
                <?php else: ?>
                    <?php foreach ($dons as $d): ?>
                        <tr>
                            <td class="font-mono text-xs"><?= htmlspecialchars($d['numero']) ?></td>
                            <td><?= htmlspecialchars($d['donateur'] ?? '—') ?></td>
                            <td class="text-sm"><?= date('d/m/Y', strtotime($d['date_entree'])) ?></td>
                            <td class="text-gray-500 text-sm"><?= htmlspecialchars(mb_strimwidth($d['notes'] ?? '', 0, 50, '…')) ?></td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $d['id'] ?>/pdf" target="_blank" class="btn btn-secondary text-orange-600" title="PDF">
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