<?php
// modules/Ventes/Views/etats/journalier.php
// Variables : $date (string), $factures (array), $ventesComptant (array)

$totFact  = array_sum(array_column($factures,       'montant_ttc'));
$totCpt   = array_sum(array_column($ventesComptant, 'montant_ttc'));
$totRegle = array_sum(array_column($factures,       'montant_regle'));
$totSolde = array_sum(array_column($factures,       'solde'));
?>

<!-- Titre + actions -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">État des ventes par jour</h1>
    <a href="<?= BASE_URL ?>/ventes/etats/journalier/pdf?date=<?= urlencode($date) ?>" target="_blank"
       style="background:#111;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
        🖨 Imprimer PDF
    </a>
</div>

<!-- Sélecteur date -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px">
    <form method="GET" action="<?= BASE_URL ?>/ventes/etats/journalier"
          style="display:flex;align-items:center;gap:12px">
        <label style="font-size:.875rem;font-weight:500;color:#374151">Date :</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"
               style="padding:9px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem">
        <button type="submit"
                style="padding:9px 20px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;cursor:pointer;font-weight:500">
            Afficher
        </button>
    </form>
</div>

<!-- Cartes résumé -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#6b7280;margin-bottom:6px">Factures crédit</div>
        <div style="font-size:1.25rem;font-weight:700;color:#111"><?= number_format($totFact, 0, ',', ' ') ?> FCFA</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#6b7280;margin-bottom:6px">Ventes comptant</div>
        <div style="font-size:1.25rem;font-weight:700;color:#111"><?= number_format($totCpt, 0, ',', ' ') ?> FCFA</div>
    </div>
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#166534;margin-bottom:6px">Total encaissé</div>
        <div style="font-size:1.25rem;font-weight:700;color:#166534"><?= number_format($totRegle + $totCpt, 0, ',', ' ') ?> FCFA</div>
    </div>
    <div style="background:#dbeafe;border:1px solid #bfdbfe;border-radius:12px;padding:20px">
        <div style="font-size:.8rem;color:#1e40af;margin-bottom:6px">Chiffre d'affaires</div>
        <div style="font-size:1.25rem;font-weight:700;color:#1e40af"><?= number_format($totFact + $totCpt, 0, ',', ' ') ?> FCFA</div>
    </div>
</div>

<!-- Factures du jour -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:20px">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:.95rem;font-weight:600;margin:0">Factures — <?= date('d/m/Y', strtotime($date)) ?></h3>
        <span style="font-size:.85rem;color:#6b7280"><?= count($factures) ?> facture(s)</span>
    </div>
    <?php if (empty($factures)): ?>
    <p style="padding:20px;color:#6b7280;margin:0">Aucune facture ce jour.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">N° Facture</th>
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Client</th>
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Type</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">HT</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TVA</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TTC</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Remise</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Réglé</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Solde</th>
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $i => $f): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:10px 16px;font-weight:500;color:#111"><?= htmlspecialchars($f['numero']) ?></td>
                <td style="padding:10px 16px;color:#374151"><?= htmlspecialchars($f['client_nom']) ?></td>
                <td style="padding:10px 16px;color:#374151"><?= htmlspecialchars($f['type_vente']) ?></td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$f['montant_ht'],    0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$f['montant_tva'],   0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#111"><?= number_format((float)$f['montant_ttc'],   0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$f['remise_montant'],0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#16a34a;font-weight:500"><?= number_format((float)$f['montant_regle'],0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;font-weight:600;color:<?= (float)$f['solde'] > 0 ? '#dc2626' : '#16a34a' ?>">
                    <?= number_format((float)$f['solde'],0,',', ' ') ?>
                </td>
                <td style="padding:10px 16px">
                    <?php $sc=['REGLÉE'=>'background:#dcfce7;color:#166534','PARTIELLEMENT_REGLÉE'=>'background:#dbeafe;color:#1e40af','NON_REGLÉE'=>'background:#fee2e2;color:#991b1b']; ?>
                    <span style="<?= $sc[$f['statut']] ?? '' ?>;padding:3px 8px;border-radius:20px;font-size:.75rem;font-weight:500">
                        <?= htmlspecialchars($f['statut']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:700">
                <td colspan="3" style="padding:12px 16px;color:#374151">TOTAUX FACTURES</td>
                <td style="padding:12px 16px;text-align:right"><?= number_format(array_sum(array_column($factures,'montant_ht')),    0,',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right"><?= number_format(array_sum(array_column($factures,'montant_tva')),   0,',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right;font-size:1rem"><?= number_format($totFact,  0,',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right"><?= number_format(array_sum(array_column($factures,'remise_montant')),0,',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right;color:#16a34a"><?= number_format($totRegle, 0,',', ' ') ?></td>
                <td style="padding:12px 16px;text-align:right;color:#dc2626"><?= number_format($totSolde, 0,',', ' ') ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<!-- Ventes comptant du jour -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:.95rem;font-weight:600;margin:0">Ventes comptant — <?= date('d/m/Y', strtotime($date)) ?></h3>
        <span style="font-size:.85rem;color:#6b7280"><?= count($ventesComptant) ?> vente(s)</span>
    </div>
    <?php if (empty($ventesComptant)): ?>
    <p style="padding:20px;color:#6b7280;margin:0">Aucune vente comptant ce jour.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">N° Vente</th>
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Client</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TTC</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Remise</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Perçu</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Monnaie rendue</th>
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventesComptant as $i => $v): ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:10px 16px;font-weight:500;color:#111"><?= htmlspecialchars($v['numero']) ?></td>
                <td style="padding:10px 16px;color:#374151"><?= htmlspecialchars($v['client_nom']) ?></td>
                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#111"><?= number_format((float)$v['montant_ttc'],    0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$v['remise_montant'], 0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#16a34a;font-weight:600"><?= number_format((float)$v['montant_percu'],  0,',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right;color:#374151"><?= number_format((float)$v['monnaie_rendue'], 0,',', ' ') ?></td>
                <td style="padding:10px 16px;color:#374151"><?= htmlspecialchars($v['mode_paiement'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:700">
                <td colspan="2" style="padding:12px 16px;color:#374151">TOTAL COMPTANT</td>
                <td style="padding:12px 16px;text-align:right;font-size:1rem"><?= number_format($totCpt, 0,',', ' ') ?></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>
