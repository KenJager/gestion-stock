<?php // modules/Ventes/Views/factures/liste.php ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Factures clients</h1>
</div>

<?php if (empty($factures)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">Aucune facture enregistrée.</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Facture</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Client</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Échéance</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">TTC</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Réglé</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Solde</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Statut</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $i => $f): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px">
                    <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>" style="color:#2563eb;text-decoration:none;font-weight:500">
                        <?= htmlspecialchars($f['numero']) ?>
                    </a>
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($f['client_nom']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                <td style="padding:12px 16px;color:<?= ($f['date_echeance'] && strtotime($f['date_echeance']) < time() && (float)$f['solde'] > 0) ? '#dc2626' : '#374151' ?>">
                    <?= $f['date_echeance'] ? date('d/m/Y', strtotime($f['date_echeance'])) : '—' ?>
                </td>
                <td style="padding:12px 16px;text-align:right;font-weight:600"><?= number_format((float)$f['montant_ttc'], 0, ',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right;color:#16a34a"><?= number_format((float)$f['montant_regle'], 0, ',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;color:<?= (float)$f['solde'] > 0 ? '#dc2626' : '#16a34a' ?>">
                    <?= number_format((float)$f['solde'], 0, ',', ' ') ?>
                </td>
                <td style="padding:12px 16px">
                    <?php
                    $sc = ['REGLÉE'=>'background:#dcfce7;color:#166534','PARTIELLEMENT_REGLÉE'=>'background:#dbeafe;color:#1e40af','NON_REGLÉE'=>'background:#fee2e2;color:#991b1b'];
                    $ss = $sc[$f['statut']] ?? 'background:#f1f5f9;color:#475569';
                    ?>
                    <span style="<?= $ss ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500"><?= htmlspecialchars($f['statut']) ?></span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>" style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">👁</a>
                        <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/pdf" target="_blank" style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">🖨</a>
                        <?php if ($f['statut'] === 'NON_REGLÉE'): ?>
                        <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/modifier" style="background:#fef3c7;color:#92400e;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">✏️</a>
                        <?php endif; ?>
                        <?php if ((float)$f['solde'] > 0): ?>
                        <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>#regler" style="background:#dcfce7;color:#166534;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">💰</a>
                        <?php endif; ?>
                        <?php if ((float)$f['montant_regle'] == 0): ?>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/supprimer"
                              onsubmit="return confirm('Supprimer cette facture ? Elle sera placée dans la corbeille.')" style="display:inline">
                            <button style="background:#fee2e2;color:#991b1b;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:.8rem">🗑</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
