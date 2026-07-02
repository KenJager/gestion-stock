<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre) ?></h1>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/appros/etats/journalier/pdf?date=<?= $date ?>" class="btn btn-primary" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/journalier?date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>" class="btn btn-secondary">
                <i class="fa-solid fa-chevron-left"></i> Jour précédent
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/journalier?date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>" class="btn btn-secondary">
                Jour suivant <i class="fa-solid fa-chevron-right"></i>
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/journalier?date=<?= date('Y-m-d') ?>" class="btn btn-secondary">
                <i class="fa-solid fa-calendar-day"></i> Aujourd'hui
            </a>
        </div>
    </div>

    <div class="carte p-4 mt-4">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="text-center">
                <span class="text-sm text-gray-500">Total HT</span>
                <p class="text-xl font-semibold"><?= number_format($total_ht, 0, ',', ' ') ?> FCFA</p>
            </div>
            <div class="text-center">
                <span class="text-sm text-gray-500">Total TVA</span>
                <p class="text-xl font-semibold"><?= number_format($total_tva, 0, ',', ' ') ?> FCFA</p>
            </div>
            <div class="text-center">
                <span class="text-sm text-gray-500">Total TTC</span>
                <p class="text-xl font-semibold"><?= number_format($total_ttc, 0, ',', ' ') ?> FCFA</p>
            </div>
        </div>

        <?php if (empty($commandes)): ?>
            <p class="text-center text-gray-500 py-4">Aucune commande réceptionnée ce jour.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Fournisseur</th>
                            <th class="text-right">Montant HT</th>
                            <th class="text-right">TVA</th>
                            <th class="text-right">Montant TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['numero']) ?></td>
                            <td><?= htmlspecialchars($c['fournisseur_nom']) ?></td>
                            <td class="text-right"><?= number_format($c['montant_ht'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($c['montant_tva'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($c['montant_ttc'], 0, ',', ' ') ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>