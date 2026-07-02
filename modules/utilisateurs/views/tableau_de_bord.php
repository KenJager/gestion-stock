<?php
// modules/utilisateurs/views/tableau_de_bord.php
$joursAbrev = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
?>

<style>
/* ── KPI Cards ─────────────────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .kpi-grid { grid-template-columns: 1fr; } }

.kpi-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
    position: relative;
    overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: var(--radius) 0 0 var(--radius);
}
.kpi-card.kpi-blue::before   { background: #3b82f6; }
.kpi-card.kpi-orange::before { background: #f59e0b; }
.kpi-card.kpi-red::before    { background: #ef4444; }
.kpi-card.kpi-green::before  { background: #22c55e; }

.kpi-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.kpi-blue   .kpi-icon { background: #eff6ff; color: #3b82f6; }
.kpi-orange .kpi-icon { background: #fffbeb; color: #f59e0b; }
.kpi-red    .kpi-icon { background: #fef2f2; color: #ef4444; }
.kpi-green  .kpi-icon { background: #f0fdf4; color: #22c55e; }

.kpi-body { flex: 1; min-width: 0; }
.kpi-label { font-size: 12px; color: #71717a; font-weight: 500; margin-bottom: 4px; }
.kpi-value { font-size: 22px; font-weight: 700; color: var(--foreground); line-height: 1.2; }
.kpi-sub   { font-size: 11px; color: #a1a1aa; margin-top: 4px; }

/* ── Graphique barres ─────────────────────────────────────────────────── */
.chart-section {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 900px) { .chart-section { grid-template-columns: 1fr; } }

.section-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.section-header {
    padding: 16px 20px 12px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.section-title {
    font-size: 14px; font-weight: 600; color: var(--foreground);
    display: flex; align-items: center; gap: 8px;
}
.section-title i { color: #71717a; font-size: 13px; }
.section-subtitle { font-size: 11px; color: #a1a1aa; }

.bars-container {
    padding: 16px 20px 12px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 6px;
    height: 180px;
}
.bar-col { display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; }
.bar-wrap {
    width: 100%; flex: 1;
    display: flex; align-items: flex-end;
    cursor: default;
    position: relative;
}
.bar {
    width: 100%;
    background: var(--primary);
    border-radius: 4px 4px 0 0;
    min-height: 4px;
    transition: opacity .15s;
    position: relative;
}
.bar:hover { opacity: .75; }
.bar-tooltip {
    display: none;
    position: absolute;
    bottom: calc(100% + 6px); left: 50%;
    transform: translateX(-50%);
    background: var(--foreground); color: white;
    font-size: 10px; padding: 4px 8px;
    border-radius: 4px; white-space: nowrap;
    pointer-events: none; z-index: 10;
}
.bar:hover .bar-tooltip { display: block; }
.bar-zero {
    width: 100%; height: 4px;
    background: var(--border);
    border-radius: 4px;
}
.bar-label { font-size: 10px; color: #a1a1aa; font-weight: 500; }
.bars-baseline {
    height: 1px; background: var(--border);
    margin: 0 20px 8px;
}

/* ── Tables dans les sections ─────────────────────────────────────────── */
.mini-table { width: 100%; border-collapse: collapse; }
.mini-table th {
    padding: 8px 12px;
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    color: #a1a1aa; letter-spacing: .04em;
    text-align: left; white-space: nowrap;
}
.mini-table td {
    padding: 9px 12px;
    font-size: 13px; color: var(--foreground);
    border-top: 1px solid var(--border);
}
.mini-table tr:hover td { background: var(--secondary); }

/* ── Bottom grid ─────────────────────────────────────────────────────── */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

/* ── Stock bar ─────────────────────────────────────────────────────── */
.stock-bar-wrap { display: flex; align-items: center; gap: 8px; }
.stock-bar-bg {
    flex: 1; height: 6px; background: var(--secondary);
    border-radius: 999px; overflow: hidden;
}
.stock-bar-fill { height: 100%; border-radius: 999px; }
.stock-ok     { background: #22c55e; }
.stock-warn   { background: #f59e0b; }
.stock-danger { background: #ef4444; }

/* ── Vide ─────────────────────────────────────────────────────────── */
.empty-state { padding: 32px 20px; text-align: center; color: #a1a1aa; font-size: 13px; }
.empty-state i { display: block; font-size: 28px; margin-bottom: 8px; }
</style>

<!-- En-tête page -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div>
        <h1 style="font-size:20px; font-weight:700; color:var(--foreground);">Tableau de bord</h1>
        <p style="font-size:12px; color:#71717a; margin-top:2px;">
            <?= date('l d F Y', strtotime($aujourd_hui)) ?>
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <?php if (Auth::peut('APPRO_LIRE')): ?>
        <a href="<?= BASE_URL ?>/appros/etats/journalier" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-chart-bar"></i> État journalier
        </a>
        <?php endif; ?>
        <?php if (Auth::peut('VENTE_LIRE')): ?>
        <a href="<?= BASE_URL ?>/ventes/comptant/creer" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Nouvelle vente
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════ KPI CARDS ═══════════════════════════ -->
<div class="kpi-grid">

    <!-- Ventes du jour -->
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Ventes du jour</div>
            <div class="kpi-value"><?= number_format((float)($ventesJour['total'] ?? 0), 0, ',', ' ') ?> <span style="font-size:13px;font-weight:500;">FCFA</span></div>
            <div class="kpi-sub"><?= (int)($ventesJour['nb'] ?? 0) ?> transaction<?= (int)($ventesJour['nb'] ?? 0) > 1 ? 's' : '' ?></div>
        </div>
    </div>

    <!-- Commandes en cours -->
    <div class="kpi-card kpi-orange">
        <div class="kpi-icon"><i class="fa-solid fa-file-invoice"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Commandes fournisseurs</div>
            <div class="kpi-value"><?= $commandesEnCours ?></div>
            <div class="kpi-sub">en attente de réception</div>
        </div>
    </div>

    <!-- Produits critiques -->
    <div class="kpi-card <?= $produitsCritiques > 0 ? 'kpi-red' : 'kpi-green' ?>">
        <div class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Alertes de stock</div>
            <div class="kpi-value"><?= $produitsCritiques ?></div>
            <div class="kpi-sub"><?= $produitsCritiques > 0 ? 'produit(s) sous le seuil minimum' : 'stock en bon état' ?></div>
        </div>
    </div>

    <!-- Clients actifs -->
    <div class="kpi-card kpi-green">
        <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Clients actifs</div>
            <div class="kpi-value"><?= $totalClients ?></div>
            <div class="kpi-sub">réceptions ce mois : <?= (int)($receptionsTotal['nb'] ?? 0) ?></div>
        </div>
    </div>

</div>

<!-- ═══════════════════════ GRAPHIQUE + DERNIÈRES VENTES ════════════════════ -->
<div class="chart-section">

    <!-- Graphique barres -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-chart-column"></i>
                Ventes des 7 derniers jours
            </div>
            <span class="section-subtitle">en FCFA TTC</span>
        </div>

        <div class="bars-container">
            <?php foreach ($graphique as $jour): ?>
            <?php
                $pct   = $maxGraphique > 0 ? ($jour['total'] / $maxGraphique) : 0;
                $h     = max(4, (int) round($pct * 130));
                $isToday = ($jour['jour'] === $aujourd_hui);
                $dow   = (int) date('w', strtotime($jour['jour']));
                $label = $joursAbrev[$dow];
                $montantFmt = number_format((float)$jour['total'], 0, ',', ' ');
            ?>
            <div class="bar-col">
                <div class="bar-wrap">
                    <?php if ($jour['total'] > 0): ?>
                    <div class="bar" style="height:<?= $h ?>px; <?= $isToday ? 'background:#3b82f6;' : '' ?>">
                        <div class="bar-tooltip"><?= $montantFmt ?> FCFA<br><?= $jour['nb'] ?> vente<?= $jour['nb'] > 1 ? 's' : '' ?></div>
                    </div>
                    <?php else: ?>
                    <div class="bar-zero"></div>
                    <?php endif; ?>
                </div>
                <span class="bar-label" style="<?= $isToday ? 'color:var(--foreground);font-weight:700;' : '' ?>">
                    <?= $label ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="bars-baseline"></div>
        <div style="display:flex; justify-content:flex-end; gap:12px; padding:0 20px 12px; font-size:11px; color:#a1a1aa;">
            <span><span style="display:inline-block;width:10px;height:10px;background:#18181b;border-radius:2px;margin-right:4px;"></span>Autres jours</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#3b82f6;border-radius:2px;margin-right:4px;"></span>Aujourd'hui</span>
        </div>
    </div>

    <!-- Dernières ventes -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-receipt"></i>
                Dernières ventes
            </div>
            <?php if (Auth::peut('VENTE_LIRE')): ?>
            <a href="<?= BASE_URL ?>/ventes/comptant" style="font-size:12px;color:#71717a;text-decoration:none;">Voir tout →</a>
            <?php endif; ?>
        </div>

        <?php if (empty($dernieresVentes)): ?>
        <div class="empty-state"><i class="fa-solid fa-inbox"></i>Aucune vente enregistrée</div>
        <?php else: ?>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Client</th>
                    <th style="text-align:right;">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dernieresVentes as $v): ?>
                <tr>
                    <td>
                        <span style="font-size:11px;font-weight:600;color:var(--foreground);"><?= htmlspecialchars($v['numero']) ?></span>
                        <br><span style="font-size:10px;color:#a1a1aa;"><?= date('d/m', strtotime($v['date_vente'])) ?></span>
                    </td>
                    <td>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:100px;">
                            <?= htmlspecialchars($v['client_nom']) ?>
                        </span>
                        <?php if ($v['mode_paiement']): ?>
                        <span style="font-size:10px;color:#71717a;"><?= htmlspecialchars($v['mode_paiement']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;font-weight:600;font-size:13px;white-space:nowrap;">
                        <?= number_format((float)$v['montant_ttc'], 0, ',', ' ') ?> F
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════════════════════ ALERTES STOCK + COMMANDES ══════════════════════ -->
<div class="bottom-grid">

    <!-- Produits en alerte -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <?php if ($produitsCritiques > 0): ?>
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i>
                <?php else: ?>
                <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                <?php endif; ?>
                Alertes de stock
            </div>
            <?php if (Auth::peut('STRUCTURE_LIRE')): ?>
            <a href="<?= BASE_URL ?>/structure/produits" style="font-size:12px;color:#71717a;text-decoration:none;">Voir tout →</a>
            <?php endif; ?>
        </div>

        <?php if (empty($produitsAlerte)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
            Tous les stocks sont à niveau
        </div>
        <?php else: ?>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th style="text-align:center;">Stock</th>
                    <th style="text-align:center;">Seuil</th>
                    <th>Niveau</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produitsAlerte as $p): ?>
                <?php
                    $stockActuel  = (float) $p['stock_actuel'];
                    $stockMin     = (float) $p['stock_minimum'];
                    $pctStock     = $stockMin > 0 ? min(100, ($stockActuel / $stockMin) * 100) : 100;
                    $barClass     = $stockActuel <= 0 ? 'stock-danger' : ($pctStock < 50 ? 'stock-warn' : 'stock-ok');
                ?>
                <tr>
                    <td>
                        <span style="font-size:12px;font-weight:600;"><?= htmlspecialchars($p['designation']) ?></span>
                        <br><span style="font-size:10px;color:#a1a1aa;"><?= htmlspecialchars($p['code']) ?></span>
                    </td>
                    <td style="text-align:center;font-weight:700;color:<?= $stockActuel <= 0 ? '#ef4444' : '#f59e0b' ?>;">
                        <?= number_format($stockActuel, 0, ',', ' ') ?>
                        <?php if ($p['unite']): ?><span style="font-size:10px;font-weight:400;color:#a1a1aa;"><?= htmlspecialchars($p['unite']) ?></span><?php endif; ?>
                    </td>
                    <td style="text-align:center;font-size:12px;color:#71717a;"><?= number_format($stockMin, 0, ',', ' ') ?></td>
                    <td style="min-width:80px;">
                        <div class="stock-bar-wrap">
                            <div class="stock-bar-bg">
                                <div class="stock-bar-fill <?= $barClass ?>" style="width:<?= round($pctStock) ?>%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Dernières commandes fournisseurs -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-boxes-stacked"></i>
                Commandes fournisseurs
            </div>
            <?php if (Auth::peut('APPRO_LIRE')): ?>
            <a href="<?= BASE_URL ?>/appros/commandes" style="font-size:12px;color:#71717a;text-decoration:none;">Voir tout →</a>
            <?php endif; ?>
        </div>

        <?php if (empty($dernieresCommandes)): ?>
        <div class="empty-state"><i class="fa-solid fa-inbox"></i>Aucune commande</div>
        <?php else: ?>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>N° / Fournisseur</th>
                    <th style="text-align:center;">Statut</th>
                    <th style="text-align:right;">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dernieresCommandes as $c): ?>
                <?php
                    $badgeClass = match($c['statut']) {
                        'BROUILLON'    => 'badge-brouillon',
                        'VALIDÉ'       => 'badge-valide',
                        'REÇU_PARTIEL' => 'badge-partiel',
                        'REÇU_TOTAL'   => 'badge-complet',
                        'ANNULÉ'       => 'badge-annule',
                        default        => 'badge-brouillon',
                    };
                ?>
                <tr>
                    <td>
                        <span style="font-size:11px;font-weight:600;"><?= htmlspecialchars($c['numero']) ?></span>
                        <br><span style="font-size:11px;color:#71717a;"><?= htmlspecialchars($c['fournisseur_nom']) ?></span>
                        <br><span style="font-size:10px;color:#a1a1aa;"><?= date('d/m/Y', strtotime($c['date_commande'])) ?></span>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge <?= $badgeClass ?>" style="font-size:10px;"><?= htmlspecialchars($c['statut']) ?></span>
                    </td>
                    <td style="text-align:right;font-weight:600;font-size:13px;white-space:nowrap;">
                        <?= number_format((float)$c['montant_ttc'], 0, ',', ' ') ?> F
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
