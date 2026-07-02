<?php
// modules/Ventes/Views/etats/annuel.php
// Variables : $annee (int), $lignes (array)

$mois = ['','Janvier','Février','Mars','Avril','Mai','Juin',
         'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

$parMois = [];
foreach ($lignes as $l) { $parMois[(int)$l['mois']] = $l; }

$tHT=$tTVA=$tTTC=$tRegle=$tSolde=$tFact = 0;
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:1.6rem;font-weight:700;margin:0">État des ventes annuel</h1>
    <a href="<?= BASE_URL ?>/ventes/etats/annuel/pdf?annee=<?= $annee ?>" target="_blank"
       style="background:#111;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:500">
        🖨 Imprimer PDF
    </a>
</div>

<!-- Sélecteur année -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px">
    <form method="GET" action="<?= BASE_URL ?>/ventes/etats/annuel"
          style="display:flex;align-items:center;gap:12px">
        <label style="font-size:.875rem;font-weight:500;color:#374151">Année :</label>
        <input type="number" name="annee" value="<?= (int)$annee ?>" min="2000" max="2099"
               style="padding:9px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;width:120px">
        <button type="submit"
                style="padding:9px 20px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;cursor:pointer;font-weight:500">
            Afficher
        </button>
    </form>
</div>

<!-- Tableau annuel -->
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
        <h3 style="font-size:.95rem;font-weight:600;margin:0">Récapitulatif mensuel — <?= (int)$annee ?></h3>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc">
                <th style="padding:11px 16px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Mois</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Nb factures</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Total HT</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Total TVA</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Total TTC</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Total réglé</th>
                <th style="padding:11px 16px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Solde restant</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($m = 1; $m <= 12; $m++):
                $l     = $parMois[$m] ?? null;
                $ht    = $l ? (float)$l['total_ht']    : 0;
                $tva   = $l ? (float)$l['total_tva']   : 0;
                $ttc   = $l ? (float)$l['total_ttc']   : 0;
                $regle = $l ? (float)$l['total_regle'] : 0;
                $solde = $l ? (float)$l['total_solde'] : 0;
                $nf    = $l ? (int)$l['nb_factures']   : 0;
                $tHT+=$ht; $tTVA+=$tva; $tTTC+=$ttc; $tRegle+=$regle; $tSolde+=$solde; $tFact+=$nf;

                // Mois courant : surligner
                $estCourant = ((int)$annee === (int)date('Y') && $m === (int)date('n'));
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $estCourant ? 'background:#eff6ff;' : ($m%2===0 ? 'background:#fafafa;' : '') ?>">
                <td style="padding:10px 16px;font-weight:<?= $l ? '600' : '400' ?>;color:<?= $l ? '#111' : '#9ca3af' ?>">
                    <?= $mois[$m] ?>
                    <?php if ($estCourant): ?><span style="font-size:.75rem;background:#2563eb;color:#fff;padding:1px 6px;border-radius:10px;margin-left:6px">en cours</span><?php endif; ?>
                </td>
                <td style="padding:10px 16px;text-align:right;color:<?= $l ? '#374151' : '#9ca3af' ?>">
                    <?= $nf ?: '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;color:<?= $l ? '#374151' : '#9ca3af' ?>">
                    <?= $ht ? number_format($ht, 0, ',', ' ') : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;color:<?= $l ? '#374151' : '#9ca3af' ?>">
                    <?= $tva ? number_format($tva, 0, ',', ' ') : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;font-weight:<?= $l ? '600' : '400' ?>;color:<?= $l ? '#111' : '#9ca3af' ?>">
                    <?= $ttc ? number_format($ttc, 0, ',', ' ') : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;color:<?= $l ? '#16a34a' : '#9ca3af' ?>;font-weight:<?= $l ? '500' : '400' ?>">
                    <?= $regle ? number_format($regle, 0, ',', ' ') : '—' ?>
                </td>
                <td style="padding:10px 16px;text-align:right;font-weight:<?= $solde > 0 ? '700' : '400' ?>;color:<?= $solde > 0 ? '#dc2626' : ($l ? '#16a34a' : '#9ca3af') ?>">
                    <?= $l ? number_format($solde, 0, ',', ' ') : '—' ?>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr style="background:#111;color:#fff;font-weight:700">
                <td style="padding:14px 16px">TOTAL <?= (int)$annee ?></td>
                <td style="padding:14px 16px;text-align:right"><?= $tFact ?></td>
                <td style="padding:14px 16px;text-align:right"><?= number_format($tHT,    0,',', ' ') ?></td>
                <td style="padding:14px 16px;text-align:right"><?= number_format($tTVA,   0,',', ' ') ?></td>
                <td style="padding:14px 16px;text-align:right;font-size:1rem"><?= number_format($tTTC,   0,',', ' ') ?> FCFA</td>
                <td style="padding:14px 16px;text-align:right;color:#86efac"><?= number_format($tRegle, 0,',', ' ') ?></td>
                <td style="padding:14px 16px;text-align:right;color:<?= $tSolde > 0 ? '#fca5a5' : '#86efac' ?>"><?= number_format($tSolde, 0,',', ' ') ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Graphique simple par mois -->
<?php if ($tTTC > 0): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-top:20px">
    <h3 style="font-size:.95rem;font-weight:600;margin:0 0 16px">Évolution mensuelle du CA (TTC)</h3>
    <div style="display:flex;align-items:flex-end;gap:6px;height:120px">
        <?php for ($m = 1; $m <= 12; $m++):
            $l   = $parMois[$m] ?? null;
            $ttc = $l ? (float)$l['total_ttc'] : 0;
            $pct = $tTTC > 0 ? ($ttc / $tTTC * 100) : 0;
            $h   = max(2, (int)($pct * 1.2));
            $estCourant = ((int)$annee === (int)date('Y') && $m === (int)date('n'));
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
            <div title="<?= $mois[$m] ?> : <?= number_format($ttc,0,',', ' ') ?> FCFA"
                 style="width:100%;height:<?= $h ?>px;background:<?= $estCourant ? '#2563eb' : ($ttc > 0 ? '#93c5fd' : '#f1f5f9') ?>;border-radius:3px 3px 0 0;transition:height .3s"></div>
            <span style="font-size:.65rem;color:#9ca3af"><?= substr($mois[$m], 0, 3) ?></span>
        </div>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>
