<?php
// modules/approvisionnements/views/etats/journalier.php
// Variables : $date, $mouvements
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">État journalier</h1>
        <a href="<?= BASE_URL ?>/appros/etats/journalier/pdf?date=<?= $date ?>" target="_blank" class="btn btn-primary">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
    </div>

    <form method="GET" class="carte p-4 flex items-end gap-4">
        <div>
            <label class="label">Date</label>
            <input type="date" name="date" class="input" value="<?= htmlspecialchars($date) ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Afficher
        </button>
    </form>

    <div class="carte p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Mouvements du <?= date('d/m/Y', strtotime($date)) ?></h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Produit</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th class="text-right">Qté</th>
                    <th class="text-right">Avant</th>
                    <th class="text-right">Après</th>
                    <th>Utilisateur</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mouvements)): ?>
                    <tr><td colspan="8" class="text-center text-gray-400 py-12">Aucun mouvement ce jour</td></tr>
                <?php else: ?>
                    <?php foreach ($mouvements as $m): ?>
                        <tr>
                            <td class="text-sm"><?= date('H:i', strtotime($m['date_mouvement'])) ?></td>
                            <td><?= htmlspecialchars($m['code']) ?> - <?= htmlspecialchars($m['designation']) ?></td>
                            <td>
                                <?php if ($m['type_mouvement'] === 'ENTRÉE'): ?>
                                    <span class="badge badge-success">↑ Entrée</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">↓ Sortie</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-gray-500"><?= htmlspecialchars($m['source']) ?></td>
                            <td class="text-right font-mono"><?= number_format((float)$m['quantite'], 2, ',', ' ') ?> <?= $m['unite'] ?></td>
                            <td class="text-right font-mono text-gray-500"><?= number_format((float)$m['stock_avant'], 2, ',', ' ') ?></td>
                            <td class="text-right font-mono font-semibold"><?= number_format((float)$m['stock_apres'], 2, ',', ' ') ?></td>
                            <td class="text-gray-500 text-sm"><?= htmlspecialchars($m['utilisateur'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>