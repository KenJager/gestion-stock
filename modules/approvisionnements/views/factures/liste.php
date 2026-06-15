<?php
// modules/approvisionnements/views/factures/liste.php
// Variables : $factures
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Factures fournisseurs</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($factures) ?> facture(s)</p>
        </div>
    </div>

    <div class="mt-4 table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Fournisseur</th>
                    <th>Date</th>
                    <th>Échéance</th>
                    <th class="text-right">Montant TTC</th>
                    <th class="text-right">Réglé</th>
                    <th class="text-right">Solde</th>
                    <th class="text-center">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($factures)): ?>
                    <tr><td colspan="9" class="text-center text-gray-400 py-12">Aucune facture</td></tr>
                <?php else: ?>
                    <?php foreach ($factures as $f):
                        $badge = match($f['statut']) {
                            'REGLÉE'                 => 'badge-success',
                            'PARTIELLEMENT_REGLÉE'   => 'badge-warning',
                            default                  => 'badge-danger',
                        };
                        $solde = (float)$f['solde'];
                    ?>
                        <tr>
                            <td class="font-mono text-xs">
                                <a href="<?= BASE_URL ?>/appros/factures/<?= $f['id'] ?>" class="text-blue-600 hover:underline">
                                    <?= htmlspecialchars($f['numero']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($f['fournisseur_nom']) ?></td>
                            <td class="text-sm"><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                            <td class="text-sm"><?= $f['date_echeance'] ? date('d/m/Y', strtotime($f['date_echeance'])) : '—' ?></td>
                            <td class="text-right font-mono"><?= number_format((float)$f['montant_ttc'], 0, ',', ' ') ?></td>
                            <td class="text-right font-mono text-green-600"><?= number_format((float)$f['montant_regle'], 0, ',', ' ') ?></td>
                            <td class="text-right font-mono <?= $solde > 0 ? 'text-red-600 font-semibold' : '' ?>">
                                <?= number_format($solde, 0, ',', ' ') ?>
                            </td>
                            <td class="text-center"><span class="badge <?= $badge ?>"><?= htmlspecialchars($f['statut']) ?></span></td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/appros/factures/<?= $f['id'] ?>" class="btn btn-secondary text-blue-600" title="Détail">
                                    <i class="fa-regular fa-eye text-sm"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/appros/factures/<?= $f['id'] ?>/pdf" target="_blank" class="btn btn-secondary text-orange-600" title="PDF">
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