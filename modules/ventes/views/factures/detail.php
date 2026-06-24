<?php
// modules/Ventes/Views/factures/detail.php
// Variables : $facture (array avec lignes + reglements), $erreur?

$sc = ['REGLÉE'=>'background:#dcfce7;color:#166534','PARTIELLEMENT_REGLÉE'=>'background:#dbeafe;color:#1e40af','NON_REGLÉE'=>'background:#fee2e2;color:#991b1b'];
$ss = $sc[$facture['statut']] ?? 'background:#f1f5f9;color:#475569';
?>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<!-- En-tête -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;margin:0 0 6px"><?= htmlspecialchars($facture['numero']) ?></h1>
        <span style="<?= $ss ?>;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:500"><?= htmlspecialchars($facture['statut']) ?></span>
    </div>
    <div style="display:flex;gap:8px">
        <a href="<?= BASE_URL ?>/ventes/factures" style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">← Retour</a>
        <a href="<?= BASE_URL ?>/ventes/factures/<?= $facture['id'] ?>/pdf" target="_blank" style="padding:9px 16px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.875rem">🖨 Imprimer</a>
        <?php if ($facture['statut'] === 'NON_REGLÉE'): ?>
        <a href="<?= BASE_URL ?>/ventes/factures/<?= $facture['id'] ?>/modifier" style="padding:9px 16px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;color:#92400e;text-decoration:none;font-size:.875rem;font-weight:500">✏️ Éditer</a>
        <?php endif; ?>
    </div>
</div>

<!-- Infos facture -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div><span style="font-size:.8rem;color:#6b7280">Client</span><br><strong><?= htmlspecialchars($facture['client_nom']) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Téléphone</span><br><strong><?= htmlspecialchars($facture['client_telephone'] ?? '—') ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Date facture</span><br><strong><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Date échéance</span><br><strong><?= $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : '—' ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Type vente</span><br><strong><?= htmlspecialchars($facture['type_vente']) ?></strong></div>
    </div>
</div>

<!-- Lignes -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:20px">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
        <h3 style="font-size:.95rem;font-weight:600;margin:0">Détail de la facture</h3>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Qté</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">PU HT</th>
                <th style="padding:10px 16px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">TVA</th>
                <th style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">HT ligne</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($facture['lignes'] as $i => $l):
                $ht = (float)$l['quantite'] * (float)$l['prix_unitaire_ht'] * (1 - (float)($l['remise_pct'] ?? 0) / 100);
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:10px 16px;font-weight:500"><?= htmlspecialchars($l['designation']) ?> <small style="color:#6b7280">(<?= $l['unite'] ?? '' ?>)</small></td>
                <td style="padding:10px 16px;text-align:center"><?= number_format((float)$l['quantite'], 3, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:right"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                <td style="padding:10px 16px;text-align:center"><?= $l['tva'] ?> %</td>
                <td style="padding:10px 16px;text-align:right;font-weight:600"><?= number_format($ht, 0, ',', ' ') ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Totaux + Règlements côte à côte -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <!-- Totaux -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <h3 style="font-size:.95rem;font-weight:600;margin:0 0 12px">Récapitulatif</h3>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#374151;border-bottom:1px solid #f1f5f9">
            <span>Total HT</span> <strong><?= number_format((float)$facture['montant_ht'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#374151;border-bottom:1px solid #f1f5f9">
            <span>Total TVA</span> <strong><?= number_format((float)$facture['montant_tva'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <?php if ((float)$facture['remise_montant'] > 0): ?>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#374151;border-bottom:1px solid #f1f5f9">
            <span>Remise</span> <strong>- <?= number_format((float)$facture['remise_montant'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;border-bottom:1px solid #f1f5f9">
            <span>Total TTC</span> <strong><?= number_format((float)$facture['montant_ttc'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.9rem;color:#16a34a;border-bottom:1px solid #f1f5f9">
            <span>Réglé</span> <strong><?= number_format((float)$facture['montant_regle'], 0, ',', ' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:1rem">
            <span style="font-weight:700">Solde restant</span>
            <strong style="color:<?= (float)$facture['solde'] > 0 ? '#dc2626' : '#16a34a' ?>;font-size:1.1rem">
                <?= number_format((float)$facture['solde'], 0, ',', ' ') ?> FCFA
            </strong>
        </div>
    </div>

    <!-- Historique règlements -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">
        <h3 style="font-size:.95rem;font-weight:600;margin:0 0 12px">Historique des règlements</h3>
        <?php if (empty($facture['reglements'])): ?>
        <p style="color:#6b7280;font-size:.875rem">Aucun règlement enregistré.</p>
        <?php else: ?>
        <?php foreach ($facture['reglements'] as $r): ?>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.875rem">
            <div>
                <strong style="color:#111"><?= number_format((float)$r['montant'], 0, ',', ' ') ?> FCFA</strong><br>
                <small style="color:#6b7280"><?= date('d/m/Y', strtotime($r['date_reglement'])) ?> — <?= htmlspecialchars($r['mode_paiement'] ?? '') ?></small>
            </div>
            <small style="color:#6b7280"><?= htmlspecialchars($r['reference'] ?? '') ?></small>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Formulaire règlement -->
<?php if ((float)$facture['solde'] > 0): ?>
<div style="background:#fff;border:2px solid #2563eb;border-radius:12px;padding:28px" id="regler">
    <h3 style="font-size:1rem;font-weight:700;color:#1e40af;margin:0 0 20px">💰 Régler la facture</h3>
    <form method="POST" action="<?= BASE_URL ?>/ventes/reglements/creer">
        <input type="hidden" name="facture_id" value="<?= $facture['id'] ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Montant à régler (FCFA) <span style="color:#ef4444">*</span>
                </label>
                <input type="number" name="montant" min="1" max="<?= $facture['solde'] ?>" step="1"
                       value="<?= $facture['solde'] ?>" required
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Mode de paiement</label>
                <select name="mode_paiement" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="ESPÈCES">Espèces</option>
                    <option value="CHÈQUE">Chèque</option>
                    <option value="VIREMENT">Virement</option>
                    <option value="MOBILE_MONEY">Mobile Money</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Référence / N° chèque</label>
                <input type="text" name="reference" maxlength="100"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Banque (si applicable)</label>
                <select name="banque_id" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="">— Aucune —</option>
                    <?php
                    try {
                        $banques = \Database::query("SELECT id, nom FROM banques ORDER BY nom")->fetchAll();
                        foreach ($banques as $b) {
                            echo '<option value="' . $b['id'] . '">' . htmlspecialchars($b['nom']) . '</option>';
                        }
                    } catch (\Exception $e) {}
                    ?>
                </select>
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Notes</label>
                <input type="text" name="notes"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button type="submit"
                    style="padding:10px 28px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:600;cursor:pointer">
                ✅ Valider le règlement
            </button>
        </div>
    </form>
</div>
<?php else: ?>
<div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:16px 20px;border-radius:8px;font-weight:500">
    ✅ Cette facture est entièrement réglée.
</div>
<?php endif; ?>
