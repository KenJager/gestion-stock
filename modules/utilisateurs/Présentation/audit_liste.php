<?php
// modules/Utilisateurs/views/audit_liste.php
// Variables : $entrees, $tables, $filtres, $page, $totalPages, $total

function badgeOperation(string $op): string {
    return match ($op) {
        'INSERT' => 'badge-complet',
        'UPDATE' => 'badge-partiel',
        'DELETE' => 'badge-annule',
        default  => 'badge-brouillon',
    };
}
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Journal d'audit</h1>
    <span style="color:#71717a; font-size:13px;"><?= $total ?> entrée(s)</span>
</div>

<!-- Filtres -->
<div class="carte p-4 mb-4">
    <form action="<?= BASE_URL ?>/audit" method="GET" class="grid grid-cols-3" style="align-items:end;">
        <div class="form-group">
            <label for="table_name" class="label">Table</label>
            <select id="table_name" name="table_name" class="input">
                <option value="">Toutes</option>
                <?php foreach ($tables as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $filtres['table_name'] === $t ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="operation" class="label">Opération</label>
            <select id="operation" name="operation" class="input">
                <option value="">Toutes</option>
                <?php foreach (['INSERT', 'UPDATE', 'DELETE'] as $op): ?>
                    <option value="<?= $op ?>" <?= $filtres['operation'] === $op ? 'selected' : '' ?>>
                        <?= $op ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="login" class="label">Login utilisateur</label>
            <div style="display:flex; gap:8px;">
                <input type="text" id="login" name="login" class="input" placeholder="ex: admin"
                       value="<?= htmlspecialchars($filtres['login']) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <?php if (!empty($filtres['table_name']) || !empty($filtres['operation']) || !empty($filtres['login'])): ?>
                    <a href="<?= BASE_URL ?>/audit" class="btn btn-secondary" title="Réinitialiser">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="carte">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Table</th>
                    <th>Opération</th>
                    <th>Utilisateur</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entrees)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="color:#71717a; padding:24px;">
                            Aucune entrée trouvée.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($entrees as $i => $e): ?>
                    <tr>
                        <td style="white-space:nowrap; font-size:12px; color:#71717a;">
                            <?= date('d/m/Y H:i:s', strtotime($e['date_operation'])) ?>
                        </td>
                        <td><code><?= htmlspecialchars($e['table_name']) ?></code></td>
                        <td>
                            <span class="badge <?= badgeOperation($e['operation']) ?>">
                                <?= htmlspecialchars($e['operation']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($e['login_utilisateur'] ?? '—') ?>
                            <?php if ($e['utilisateur_id']): ?>
                                <span style="color:#a1a1aa; font-size:11px;">(#<?= $e['utilisateur_id'] ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline"
                                    onclick="const e=document.getElementById('detail-<?= $i ?>'); e.style.display = e.style.display === 'none' ? 'block' : 'none';">
                                <i class="fa-solid fa-eye"></i> Voir
                            </button>
                            <div id="detail-<?= $i ?>" class="hidden-detail" style="display:none; margin-top:8px;">
                                <?php if ($e['operation'] === 'UPDATE'): ?>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p style="font-size:11px; font-weight:600; color:#71717a; margin-bottom:4px;">AVANT</p>
                                            <pre style="background:#f9fafb; border:1px solid var(--border); border-radius:var(--radius); padding:8px; font-size:11px; overflow-x:auto; max-width:400px;"><?= htmlspecialchars(json_encode(json_decode($e['ancien_valeur'] ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        </div>
                                        <div>
                                            <p style="font-size:11px; font-weight:600; color:#71717a; margin-bottom:4px;">APRÈS</p>
                                            <pre style="background:#f9fafb; border:1px solid var(--border); border-radius:var(--radius); padding:8px; font-size:11px; overflow-x:auto; max-width:400px;"><?= htmlspecialchars(json_encode(json_decode($e['nouvelle_valeur'] ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        </div>
                                    </div>
                                <?php elseif ($e['operation'] === 'INSERT'): ?>
                                    <pre style="background:#f9fafb; border:1px solid var(--border); border-radius:var(--radius); padding:8px; font-size:11px; overflow-x:auto; max-width:600px;"><?= htmlspecialchars(json_encode(json_decode($e['nouvelle_valeur'] ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                <?php else: ?>
                                    <pre style="background:#f9fafb; border:1px solid var(--border); border-radius:var(--radius); padding:8px; font-size:11px; overflow-x:auto; max-width:600px;"><?= htmlspecialchars(json_encode(json_decode($e['ancien_valeur'] ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="flex justify-between items-center mt-4">
        <span style="font-size:13px; color:#71717a;">Page <?= $page ?> / <?= $totalPages ?></span>
        <div class="flex gap-2">
            <?php
                $query = $_GET;
                $query['page'] = max(1, $page - 1);
                $hrefPrec = BASE_URL . '/audit?' . http_build_query($query);

                $query['page'] = min($totalPages, $page + 1);
                $hrefSuiv = BASE_URL . '/audit?' . http_build_query($query);
            ?>
            <a href="<?= $hrefPrec ?>" class="btn btn-secondary <?= $page <= 1 ? 'disabled' : '' ?>"
               <?= $page <= 1 ? 'style="pointer-events:none; opacity:0.5;"' : '' ?>>
                <i class="fa-solid fa-chevron-left"></i> Précédent
            </a>
            <a href="<?= $hrefSuiv ?>" class="btn btn-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>"
               <?= $page >= $totalPages ? 'style="pointer-events:none; opacity:0.5;"' : '' ?>>
                Suivant <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
<?php endif; ?>

