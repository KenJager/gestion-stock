<?php
// modules/Ventes/Views/livraisons/liste.php
// Variables : $livraisons (array)
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Bons de livraison clients</h1>
    <div style="display:flex;gap:8px">
        <a href="<?= BASE_URL ?>/ventes/commandes"
           style="background:#f1f5f9;border:1px solid #d1d5db;color:#374151;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
            Voir les commandes
        </a>
    </div>
</div>

<?php if (empty($livraisons)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">
    Aucun bon de livraison enregistré.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.9rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Livraison</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Commande</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Client</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date livraison</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Statut</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($livraisons as $i => $l): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px">
                    <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>"
                       style="color:#2563eb;text-decoration:none;font-weight:500">
                        <?= htmlspecialchars($l['numero']) ?>
                    </a>
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($l['commande_numero'] ?? '—') ?></td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($l['client_nom']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($l['date_livraison'])) ?></td>
                <td style="padding:12px 16px">
                    <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                        <?= htmlspecialchars($l['statut']) ?>
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>"
                           style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">👁</a>
                        <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/pdf" target="_blank"
                           style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">🖨</a>
                        <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/modifier"
                           style="background:#fef3c7;color:#92400e;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem">✏️</a>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/supprimer"
                              onsubmit="return confirm('Supprimer ce bon de livraison ? Le stock sera restitué et il sera placé dans la corbeille.')"
                              style="display:inline">
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
