<?php
// modules/approvisionnements/views/etats/annuel.php
// Variables : $annee, $mois, $debut, $fin, $mouvements
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">État mensuel — <?= date('F Y', strtotime($debut)) ?></h1>
        <a href="<?= BASE_URL ?>/appros/etats/annuel/pdf?annee=<?= $annee ?>&mois=<?= $mois ?>" target="_blank" class="btn btn-primary">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
    </div>

    <form method="GET" class="carte p-4 flex items-end gap-4">
        <div>
            <label class="label">Année</label>
            <input type="number" name="annee" class="input" value="<?= $annee ?>" min="2000" max="2100">
        </div>
        <div>
            <label class="label">Mois</label>
            <select name="mois" class="input">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $mois == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Afficher
        </button>
    </form>

    <!-- Résumé -->
    <?php
        $totalEntrees = array_sum(array_map(fn($m) => $m['type_mouvement'] === 'ENTRÉE' ? (float)$m['quantite'] : 0, $mouvements));
        $totalSorties = array_sum(array_map(fn($m) => $m['type_mouvement'] === 'SORTIE' ? (float)$m['quantite'] : 0, $mouvements));
    ?>
    <div class="grid grid-cols-3 gap-4">
        <div class="carte p-4 text-center bg-green-50">
            <p class="text-xs text-gray-500">Total entrées</p>
            <p class="text-xl font-bold text-green-700 font-mono"><?= number_format($totalEntrees, 2, ',', ' ') ?></p>
        </div>
        <div class="carte p-4 text-center bg-red-50">
            <p class="text-xs text-gray-500">Total sorties</p>
            <p class="text-xl font-bold text-red-700 font-mono"><?= number_format($totalSorties, 2, ',', ' ') ?></p>
        </div>
        <div class="carte p-4 text-center bg-blue-50">
            <p class="text-xs text-gray-500">Mouvements</p>
            <p class="text-xl font-bold text-blue-700 font-mono"><?= count($mouvements) ?></p>
        </div>
    </div>

    <div class="carte p-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Produit</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th class="text-right">Qté</th>
                    <th class="text-right">Stock après</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mouvements)): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-12">Aucun mouvement ce mois</td></tr>
                <?php else: ?>
                    <?php foreach ($mouvements as $m): ?>
                        <tr>
                            <td class="text-sm"><?= date('d/m/Y', strtotime($m['date_mouvement'])) ?></td>
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
                            <td class="text-right font-mono"><?= number_format((float)$m['stock_apres'], 2, ',', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>