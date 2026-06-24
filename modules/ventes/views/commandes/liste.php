<?php
// modules/Ventes/Views/commandes/liste.php
// Variables : $commandes (array)
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">Commandes clients</h1>
    <div style="display:flex;gap:8px">
        <a href="<?= BASE_URL ?>/ventes/commandes/creer"
           style="background:#111;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
            + Nouvelle commande
        </a>
    </div>
</div>

<?php if (!empty($erreur ?? '')): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<?php if (empty($commandes)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:40px;text-align:center;color:#64748b">
    Aucune commande client enregistrée.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.9rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">N° Commande</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Client</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Date</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Livraison prévue</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151">Montant TTC</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Statut</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes as $i => $c): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i % 2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px">
                    <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>"
                       style="color:#2563eb;text-decoration:none;font-weight:500">
                        <?= htmlspecialchars($c['numero']) ?>
                    </a>
                </td>
                <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($c['client_nom']) ?></td>
                <td style="padding:12px 16px;color:#374151"><?= date('d/m/Y', strtotime($c['date_commande'])) ?></td>
                <td style="padding:12px 16px;color:#374151">
                    <?= $c['date_livraison_prevue'] ? date('d/m/Y', strtotime($c['date_livraison_prevue'])) : '—' ?>
                </td>
                <td style="padding:12px 16px;text-align:right;font-weight:600;color:#111">
                    <?= number_format((float)$c['montant_ttc'], 0, ',', ' ') ?> FCFA
                </td>
                <td style="padding:12px 16px">
                    <?php
                    $couleurs = [
                        'EN_ATTENTE'    => 'background:#fef3c7;color:#92400e',
                        'LIVRÉ_PARTIEL' => 'background:#dbeafe;color:#1e40af',
                        'LIVRÉ_TOTAL'   => 'background:#dcfce7;color:#166534',
                        'ANNULÉ'        => 'background:#fee2e2;color:#991b1b',
                    ];
                    $style = $couleurs[$c['statut']] ?? 'background:#f1f5f9;color:#475569';
                    ?>
                    <span style="<?= $style ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                        <?= htmlspecialchars($c['statut']) ?>
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>"
                           style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem" title="Voir">
                            👁
                        </a>
                        <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/pdf"
                           style="background:#f1f5f9;color:#374151;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem" title="Imprimer" target="_blank">
                            🖨
                        </a>
                        <?php if ($c['statut'] === 'EN_ATTENTE'): ?>
                        <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/modifier"
                           style="background:#fef3c7;color:#92400e;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem" title="Modifier">
                            ✏️
                        </a>
                        <a href="<?= BASE_URL ?>/ventes/livraisons/creer?commande_id=<?= $c['id'] ?>"
                           style="background:#dcfce7;color:#166534;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem" title="Livrer">
                            🚚
                        </a>
                        <?php endif; ?>
                        <?php if (in_array($c['statut'], ['EN_ATTENTE', 'LIVRÉ_PARTIEL'])): ?>
                        <a href="<?= BASE_URL ?>/ventes/livraisons/creer?commande_id=<?= $c['id'] ?>"
                           style="background:#dbeafe;color:#1e40af;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:.8rem" title="Livraison partielle">
                            📦
                        </a>
                        <?php endif; ?>
                        <?php if ($c['statut'] === 'EN_ATTENTE'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/supprimer"
                              onsubmit="return confirm('Supprimer cette commande ?')" style="display:inline">
                            <button style="background:#fee2e2;color:#991b1b;padding:5px 10px;border-radius:5px;border:none;cursor:pointer;font-size:.8rem" title="Supprimer">
                                🗑
                            </button>
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
