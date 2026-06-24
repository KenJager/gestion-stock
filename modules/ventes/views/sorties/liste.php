<?php // modules/Ventes/Views/sorties/liste.php ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Bons de sortie</h1>
    <div style="display:flex;gap:8px">
        <a href="<?= BASE_URL ?>/ventes/sorties/creer"
           style="background:#dc2626;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
            + Nouveau bon de sortie
        </a>
    </div>
</div>

<?php if (empty($sorties)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">
    Aucun bon de sortie enregistré.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Bon</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Motif</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Notes</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sorties as $i => $s): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px;font-weight:500;color:#111"><?= htmlspecialchars($s['numero']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($s['date_sortie'])) ?></td>
                <td style="padding:12px 16px">
                    <?php
                    $mc = ['NON_VENDU'=>'background:#fef3c7;color:#92400e','PÉRIMÉ'=>'background:#fee2e2;color:#991b1b','CASSE'=>'background:#fed7aa;color:#9a3412','AJUSTEMENT'=>'background:#dbeafe;color:#1e40af'];
                    $ms = $mc[$s['motif']] ?? 'background:#f1f5f9;color:#475569';
                    ?>
                    <span style="<?= $ms ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                        <?= htmlspecialchars($s['motif']) ?>
                    </span>
                </td>
                <td style="padding:12px 16px;color:#6b7280;font-size:.85rem"><?= htmlspecialchars($s['notes'] ?? '—') ?></td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <a href="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/pdf" target="_blank"
                           style="background:#f1f5f9;color:#374151;padding:5px 12px;border-radius:5px;text-decoration:none;font-size:.8rem">
                            🖨 PDF
                        </a>
                        <a href="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/modifier"
                           style="background:#fef3c7;color:#92400e;padding:5px 12px;border-radius:5px;text-decoration:none;font-size:.8rem;font-weight:500">
                            ✏️ Éditer
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/supprimer"
                              onsubmit="return confirm('Supprimer ce bon de sortie ? Le stock sera restitué et il sera placé dans la corbeille.')" style="display:inline">
                            <button style="background:#fee2e2;color:#991b1b;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:.8rem">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
