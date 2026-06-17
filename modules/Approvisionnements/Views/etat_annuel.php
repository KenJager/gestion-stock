<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre) ?></h1>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/appros/etats/annuel/pdf?annee=<?= $annee ?>" class="btn btn-primary btn-sm" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/annuel?annee=<?= $annee - 1 ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-chevron-left"></i> <?= $annee - 1 ?>
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/annuel?annee=<?= $annee + 1 ?>" class="btn btn-secondary btn-sm">
                <?= $annee + 1 ?> <i class="fa-solid fa-chevron-right"></i>
            </a>
            <a href="<?= BASE_URL ?>/appros/etats/annuel?annee=<?= date('Y') ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-calendar-year"></i> <?= date('Y') ?>
            </a>
        </div>
    </div>

    <div class="carte p-4">
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

        <?php if (empty($mois)): ?>
            <p class="text-center text-gray-500 py-4">Aucune commande réceptionnée cette année.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th class="text-center">Nombre d'achats</th>
                            <th class="text-right">Total HT</th>
                            <th class="text-right">TVA</th>
                            <th class="text-right">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mois as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['libelle']) ?></td>
                            <td class="text-center"><?= $m['nb_commandes'] ?></td>
                            <td class="text-right"><?= number_format($m['total_ht'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($m['total_tva'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($m['total_ttc'], 0, ',', ' ') ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>