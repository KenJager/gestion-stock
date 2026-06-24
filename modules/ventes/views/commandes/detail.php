<?php
// modules/Ventes/Views/commandes/detail.php
// Variables : $commande (array avec lignes)

$couleurs = [
    'EN_ATTENTE'    => 'background:#fef3c7;color:#92400e',
    'LIVRÉ_PARTIEL' => 'background:#dbeafe;color:#1e40af',
    'LIVRÉ_TOTAL'   => 'background:#dcfce7;color:#166534',
    'ANNULÉ'        => 'background:#fee2e2;color:#991b1b',
];
$styleBadge = $couleurs[$commande['statut']] ?? 'background:#f1f5f9;color:#475569';
?>

<!-- En-tête page -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;margin:0 0 6px"><?= htmlspecialchars($commande['numero']) ?></h1>
        <span style="<?= $styleBadge ?>;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:500">
            <?= htmlspecialchars($commande['statut']) ?>
        </span>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/ventes/commandes"
           style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">
            ← Retour
        </a>
        <a href="<?= BASE_URL ?>/ventes/commandes/<?= $commande['id'] ?>/pdf" target="_blank"
           style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">
            🖨 Imprimer
        </a>
        <?php if (in_array($commande['statut'], ['EN_ATTENTE'])): ?>
        <a href="<?= BASE_URL ?>/ventes/commandes/<?= $commande['id'] ?>/modifier"
           style="padding:9px 16px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;color:#92400e;text-decoration:none;font-size:.875rem;font-weight:500">
            ✏️ Éditer
        </a>
        <?php endif; ?>
        <?php if (in_array($commande['statut'], ['EN_ATTENTE', 'LIVRÉ_PARTIEL'])): ?>
        <a href="<?= BASE_URL ?>/ventes/livraisons/creer?commande_id=<?= $commande['id'] ?>"
           style="padding:9px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;text-decoration:none;font-size:.875rem;font-weight:500">
            🚚 Livrer
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Infos client -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px">
    <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9">
        Informations
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div><span style="font-size:.8rem;color:#6b7280">Client</span><br><strong style="color:#111"><?= htmlspecialchars($commande['client_nom']) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Téléphone</span><br><strong style="color:#111"><?= htmlspecialchars($commande['client_telephone'] ?? '—') ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Date commande</span><br><strong style="color:#111"><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Livraison prévue</span><br><strong style="color:#111"><?= $commande['date_livraison_prevue'] ? date('d/m/Y', strtotime($commande['date_livraison_prevue'])) : '—' ?></strong></div>
        <?php if ($commande['remise_pct'] > 0): ?>
        <div><span style="font-size:.8rem;color:#6b7280">Remise globale</span><br><strong style="color:#111"><?= $commande['remise_pct'] ?> %</strong></div>
        <?php endif; ?>
        <?php if ($commande['notes']): ?>
        <div style="grid-column:1/-1"><span style="font-size:.8rem;color:#6b7280">Notes</span><br><span style="color:#374151"><?= htmlspecialchars($commande['notes']) ?></span></div>
        <?php endif; ?>
    </div>
</div>

<!-- Lignes -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:20px">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
        <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0">Produits commandés</h3>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Qté commandée</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Livrée</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Reste</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">PU HT</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TVA</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">HT ligne</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commande['lignes'] as $i => $l):
                $ht    = (float)$l['quantite_commandee'] * (float)$l['prix_unitaire_ht'] * (1 - (float)($l['remise_pct'] ?? 0) / 100);
                $reste = (float)$l['quantite_commandee'] - (float)$l['quantite_livree'];
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:10px 16px;color:#111;font-weight:500">
                    <?= htmlspecialchars($l['designation']) ?>
                    <small style="color:#6b7280;font-weight:400">(<?= $l['unite'] ?? '' ?>)</small>
                </td>
                <td style="padding:10px 16px;text-align:center;color:#374151"><?= number_format((float)$l['quantite_commandee'], 3, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:center;color:#16a34a;font-weight:500"><?= number_format((float)$l['quantite_livree'], 3, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:center;font-weight:600;color:<?= $reste > 0 ? '#d97706' : '#6b7280' ?>">
                    <?= number_format($reste, 3, ',', ' ') ?>
                </td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:center;color:#374151"><?= $l['tva'] ?> %</td>
                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#111"><?= number_format($ht, 0, ',', ' ') ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Totaux -->
<div style="display:flex;justify-content:flex-end">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 28px;min-width:280px">
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#374151">
            <span>Total HT</span> <strong><?= number_format((float)$commande['montant_ht'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#374151">
            <span>Total TVA</span> <strong><?= number_format((float)$commande['montant_tva'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:10px 0 0;border-top:1px solid #e2e8f0;margin-top:5px">
            <span style="font-weight:700;font-size:1rem">Total TTC</span>
            <strong style="font-size:1.1rem;color:#111"><?= number_format((float)$commande['montant_ttc'], 0, ',', ' ') ?> FCFA</strong>
        </div>
    </div>
</div>
