<?php // modules/Ventes/Views/reglements/liste.php ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Règlements clients</h1>
</div>

<?php if (empty($reglements)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">
    Aucun règlement enregistré.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Facture</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Client</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Montant</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Mode</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Référence</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Banque</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reglements as $i => $r): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($r['date_reglement'])) ?></td>
                <td style="padding:12px 16px">
                    <a href="<?= BASE_URL ?>/ventes/factures/<?= $r['facture_id'] ?>"
                       style="color:#2563eb;text-decoration:none;font-weight:500">
                        <?= htmlspecialchars($r['facture_numero']) ?>
                    </a>
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($r['client_nom']) ?></td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;color:#16a34a">
                    <?= number_format((float)$r['montant'], 0, ',', ' ') ?> FCFA
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($r['mode_paiement'] ?? '—') ?></td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($r['reference'] ?? '—') ?></td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($r['banque_nom'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0">
                <td colspan="3" style="padding:12px 16px;font-weight:700;color:#374151">Total</td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;font-size:1rem;color:#16a34a">
                    <?= number_format(array_sum(array_column($reglements, 'montant')), 0, ',', ' ') ?> FCFA
                </td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>
