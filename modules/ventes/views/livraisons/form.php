<?php
// modules/Ventes/Views/livraisons/form.php
// Variables : $commande (array|null), $erreur?
?>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<?php if (!$commande): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:40px;text-align:center;color:#64748b">
    <p>Accédez d'abord à la commande depuis la liste, puis cliquez sur <strong>Livrer</strong>.</p>
    <a href="<?= BASE_URL ?>/ventes/commandes"
       style="display:inline-block;margin-top:12px;background:#111;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-size:.9rem">
        Voir les commandes
    </a>
</div>
<?php else: ?>

<h1 style="font-size:1.6rem;font-weight:700;margin:0 0 6px">Livrer des produits</h1>
<p style="color:#6b7280;margin:0 0 24px;font-size:.9rem">
    Commande <?= htmlspecialchars($commande['numero']) ?> — <?= htmlspecialchars($commande['client_nom']) ?>
</p>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px">

    <!-- Rappel commande -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;padding:16px;background:#f8fafc;border-radius:8px;margin-bottom:24px">
        <div><span style="font-size:.8rem;color:#6b7280">Client</span><br><strong><?= htmlspecialchars($commande['client_nom']) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">N° Commande</span><br><strong><?= htmlspecialchars($commande['numero']) ?></strong></div>
        <div><span style="font-size:.8rem;color:#6b7280">Statut</span><br>
            <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                <?= htmlspecialchars($commande['statut']) ?>
            </span>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/ventes/livraisons/creer">
        <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">

        <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e2e8f0">
            Quantités à livrer maintenant
        </h3>

        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Commandée</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Déjà livrée</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#d97706;border-bottom:1px solid #e2e8f0">Reste</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#111;border-bottom:1px solid #e2e8f0">Qté à livrer</th>
                        <th style="padding:10px 12px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">PU HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $toutLivre = true;
                    foreach ($commande['lignes'] as $i => $lg):
                        $reste = (float)$lg['quantite_commandee'] - (float)$lg['quantite_livree'];
                        if ($reste <= 0) continue;
                        $toutLivre = false;
                    ?>
                    <tr style="border-bottom:1px solid #f1f5f9;<?= $i%2 ? 'background:#fafafa' : '' ?>">
                        <td style="padding:10px 12px;color:#111;font-weight:500">
                            <?= htmlspecialchars($lg['designation']) ?>
                            <small style="color:#6b7280">(<?= $lg['unite'] ?? '' ?>)</small>
                            <input type="hidden" name="ligne_id[]"         value="<?= $lg['id'] ?>">
                            <input type="hidden" name="produit_id[]"       value="<?= $lg['produit_id'] ?>">
                            <input type="hidden" name="prix_unitaire_ht[]" value="<?= $lg['prix_unitaire_ht'] ?>">
                            <input type="hidden" name="tva[]"              value="<?= $lg['tva'] ?>">
                            <input type="hidden" name="remise_pct[]"       value="<?= $lg['remise_pct'] ?? 0 ?>">
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#374151"><?= number_format((float)$lg['quantite_commandee'], 3, ',', ' ') ?></td>
                        <td style="padding:10px 12px;text-align:center;color:#16a34a"><?= number_format((float)$lg['quantite_livree'], 3, ',', ' ') ?></td>
                        <td style="padding:10px 12px;text-align:center;font-weight:700;color:#d97706"><?= number_format($reste, 3, ',', ' ') ?></td>
                        <td style="padding:10px 12px">
                            <input type="number" name="quantite_livree[]"
                                   min="0" max="<?= $reste ?>" step="0.001" value="<?= $reste ?>"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;text-align:center;box-sizing:border-box">
                        </td>
                        <td style="padding:10px 12px;text-align:right;color:#374151">
                            <?= number_format((float)$lg['prix_unitaire_ht'], 0, ',', ' ') ?> FCFA
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if ($toutLivre): ?>
                    <tr>
                        <td colspan="6" style="padding:20px;text-align:center;color:#16a34a;font-weight:500">
                            ✅ Toutes les lignes de cette commande ont été livrées.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!$toutLivre): ?>
        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0">
            <a href="<?= BASE_URL ?>/ventes/commandes/<?= $commande['id'] ?>"
               style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.9rem">
                Annuler
            </a>
            <button type="submit"
                    style="padding:10px 24px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:500;cursor:pointer">
                🚚 Valider la livraison
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>
