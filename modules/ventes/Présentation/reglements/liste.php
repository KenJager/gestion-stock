<?php // modules/ventes/views/reglements/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Règlements clients</h1>
    </div>

    <?php if (empty($reglements)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucun règlement enregistré.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th class="text-right">Montant</th>
                        <th>Mode</th>
                        <th>Référence</th>
                        <th>Banque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reglements as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['date_reglement'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $r['facture_id'] ?>" class="text-blue-600 font-medium hover:underline">
                                <?= htmlspecialchars($r['facture_numero']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($r['client_nom']) ?></td>
                        <td class="text-right font-bold" style="color:#16a34a"><?= number_format((float)$r['montant'], 0, ',', ' ') ?> FCFA</td>
                        <td><?= htmlspecialchars($r['mode_paiement'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['reference'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['banque_nom'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold">
                        <td colspan="3">Total</td>
                        <td class="text-right" style="color:#16a34a">
                            <?= number_format(array_sum(array_column($reglements, 'montant')), 0, ',', ' ') ?> FCFA
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
