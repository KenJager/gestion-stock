<?php // modules/Ventes/Views/comptant/liste.php ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Ventes comptant</h1>
    <div style="display:flex;gap:8px">
        <a href="<?= BASE_URL ?>/ventes/comptant/creer"
           style="background:#111;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
            + Nouvelle vente
        </a>
    </div>
</div>

<?php if (empty($ventes)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">
    Aucune vente comptant enregistrée.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Vente</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Client</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">TTC</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Remise</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Perçu</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Monnaie rendue</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Mode</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Reçu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventes as $i => $v): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px;font-weight:500;color:#111"><?= htmlspecialchars($v['numero']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($v['client_nom']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($v['date_vente'])) ?></td>
                <td style="padding:12px 16px;text-align:right;font-weight:600;color:#111">
                    <?= number_format((float)$v['montant_ttc'], 0, ',', ' ') ?>
                </td>
                <td style="padding:12px 16px;text-align:right;color:#374151">
                    <?= number_format((float)$v['remise_montant'], 0, ',', ' ') ?>
                </td>
                <td style="padding:12px 16px;text-align:right;color:#16a34a;font-weight:600">
                    <?= number_format((float)$v['montant_percu'], 0, ',', ' ') ?>
                </td>
                <td style="padding:12px 16px;text-align:right;color:#374151">
                    <?= number_format((float)$v['monnaie_rendue'], 0, ',', ' ') ?>
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($v['mode_paiement'] ?? '—') ?></td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <a href="<?= BASE_URL ?>/ventes/comptant/<?= $v['id'] ?>/pdf" target="_blank"
                           style="background:#f1f5f9;color:#374151;padding:5px 12px;border-radius:5px;text-decoration:none;font-size:.8rem;font-weight:500">
                            🖨 Reçu
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/comptant/<?= $v['id'] ?>/supprimer"
                              onsubmit="return confirm('Supprimer cette vente ? Le stock sera restitué et elle sera placée dans la corbeille.')" style="display:inline">
                            <button style="background:#fee2e2;color:#991b1b;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:.8rem">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0">
                <td colspan="3" style="padding:12px 16px;font-weight:700;color:#374151">Total</td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;font-size:.95rem;color:#111">
                    <?= number_format(array_sum(array_column($ventes, 'montant_ttc')), 0, ',', ' ') ?> FCFA
                </td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>
