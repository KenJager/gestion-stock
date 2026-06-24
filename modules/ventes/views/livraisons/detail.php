<?php
// modules/Ventes/Views/livraisons/detail.php
// Variables : $livraison (array avec lignes)
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;margin:0 0 6px"><?= htmlspecialchars($livraison['numero']) ?></h1>
        <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:500">LIVRÉ</span>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/ventes/livraisons"
           style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">← Retour</a>
        <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $livraison['id'] ?>/pdf" target="_blank"
           style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">🖨 Imprimer BL</a>
        <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $livraison['id'] ?>/modifier"
           style="padding:9px 16px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;color:#92400e;text-decoration:none;font-size:.875rem;font-weight:500">✏️ Éditer</a>
    </div>
</div>

<!-- Infos -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div><span style="font-size:.8rem;color:#6b7280">Client</span><br><strong><?= htmlspecialchars($livraison['client_nom']) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Téléphone</span><br><strong><?= htmlspecialchars($livraison['client_telephone'] ?? '—') ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Date livraison</span><br><strong><?= date('d/m/Y', strtotime($livraison['date_livraison'])) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Réf commande</span><br><strong><?= htmlspecialchars($livraison['commande_numero'] ?? '—') ?></strong></div>
    </div>
</div>

<!-- Lignes livrées -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:20px">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
        <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0">Produits livrés</h3>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Qté livrée</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">PU HT</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TVA</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">HT ligne</th>
            </tr>
        </thead>
        <tbody>
            <?php $totalHT = 0; foreach ($livraison['lignes'] as $i => $l):
                $ht = (float)$l['quantite_livree'] * (float)$l['prix_unitaire_ht'] * (1 - (float)($l['remise_pct'] ?? 0) / 100);
                $totalHT += $ht;
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:10px 16px;color:#111;font-weight:500"><?= htmlspecialchars($l['designation']) ?> <small style="color:#6b7280">(<?= $l['unite'] ?? '' ?>)</small></td>
                <td style="padding:10px 16px;text-align:center"><?= number_format((float)$l['quantite_livree'], 3, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:center"><?= $l['tva'] ?> %</td>
                <td style="padding:10px 16px;text-align:right;font-weight:600"><?= number_format($ht, 0, ',', ' ') ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc">
                <td colspan="4" style="padding:12px 16px;font-weight:700;text-align:right">Total HT</td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;font-size:1rem"><?= number_format($totalHT, 0, ',', ' ') ?> FCFA</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Bouton générer facture -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px">
    <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 16px">Générer la facture client</h3>
    <form method="POST" action="<?= BASE_URL ?>/ventes/factures/generer">
        <input type="hidden" name="livraison_id" value="<?= $livraison['id'] ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Type de vente</label>
                <select name="type_vente" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="CREDIT">Crédit</option>
                    <option value="COMPTANT">Comptant</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Date d'échéance</label>
                <input type="date" name="date_echeance"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Remise (FCFA)</label>
                <input type="number" name="remise_montant" min="0" step="1" value="0"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Notes</label>
                <input type="text" name="notes"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button type="submit"
                    style="padding:10px 24px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:500;cursor:pointer">
                📄 Créer la facture
            </button>
        </div>
    </form>
</div>
