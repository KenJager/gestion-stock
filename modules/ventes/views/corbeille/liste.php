<?php
// modules/Ventes/Views/corbeille/liste.php
// Variables : $elements (array de résumés Corbeille::lister), $erreur?

$libellesTable = [
    'bons_commande_client'  => ['label' => 'Commande client',  'icone' => '📋', 'couleur' => '#2563eb'],
    'bons_livraison_client' => ['label' => 'Bon de livraison',  'icone' => '🚚', 'couleur' => '#16a34a'],
    'factures_client'       => ['label' => 'Facture client',    'icone' => '🧾', 'couleur' => '#d97706'],
    'ventes_comptant'       => ['label' => 'Vente comptant',    'icone' => '💵', 'couleur' => '#7c3aed'],
    'bons_sortie'           => ['label' => 'Bon de sortie',     'icone' => '📤', 'couleur' => '#dc2626'],
];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;margin:0 0 4px">🗑️ Corbeille — Ventes</h1>
        <p style="color:#6b7280;font-size:.875rem;margin:0">
            Les éléments supprimés restent ici et peuvent être restaurés à tout moment.
        </p>
    </div>
</div>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<?php if (empty($elements)): ?>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:60px 20px;text-align:center;color:#9ca3af">
    <div style="font-size:2.5rem;margin-bottom:12px">🗑️</div>
    La corbeille du module Ventes est vide.
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Type</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Libellé</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Supprimé le</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151">Supprimé par</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Éléments liés</th>
                <th style="padding:12px 16px;text-align:center;font-weight:600;color:#374151">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($elements as $i => $el):
                $info = $libellesTable[$el['table']] ?? ['label' => $el['table'], 'icone' => '📄', 'couleur' => '#6b7280'];
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;<?= $i % 2 ? 'background:#fafafa' : '' ?>">
                <td style="padding:12px 16px">
                    <span style="background:<?= $info['couleur'] ?>15;color:<?= $info['couleur'] ?>;padding:4px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                        <?= $info['icone'] ?> <?= htmlspecialchars($info['label']) ?>
                    </span>
                </td>
                <td style="padding:12px 16px;color:#111;font-weight:500">
                    <?= htmlspecialchars($el['libelle']) ?>
                </td>
                <td style="padding:12px 16px;color:#374151">
                    <?= date('d/m/Y à H:i', strtotime($el['date_suppression'])) ?>
                </td>
                <td style="padding:12px 16px;color:#374151">
                    <?= htmlspecialchars($el['supprime_par']) ?>
                </td>
                <td style="padding:12px 16px;text-align:center;color:#6b7280">
                    <?= $el['nb_liees'] > 0 ? $el['nb_liees'] . ' table(s)' : '—' ?>
                </td>
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                        <form method="POST" action="<?= BASE_URL ?>/ventes/corbeille/restaurer"
                              onsubmit="return confirm('Restaurer cet élément ? Il redeviendra actif dans le système.')"
                              style="display:inline">
                            <input type="hidden" name="fichier" value="<?= htmlspecialchars($el['fichier']) ?>">
                            <button type="submit"
                                    style="background:#dcfce7;color:#166534;border:none;padding:6px 14px;border-radius:5px;cursor:pointer;font-size:.8rem;font-weight:500">
                                ↩️ Restaurer
                            </button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/ventes/corbeille/purger"
                              onsubmit="return confirm('⚠️ Suppression définitive et irréversible. Continuer ?')"
                              style="display:inline">
                            <input type="hidden" name="fichier" value="<?= htmlspecialchars($el['fichier']) ?>">
                            <button type="submit"
                                    style="background:#fee2e2;color:#991b1b;border:none;padding:6px 14px;border-radius:5px;cursor:pointer;font-size:.8rem;font-weight:500">
                                🗑️ Purger
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p style="color:#9ca3af;font-size:.8rem;margin-top:16px">
    💡 La restauration réinjecte l'élément et toutes ses lignes liées dans la base avec leurs identifiants d'origine.
    La purge supprime définitivement le fichier archive — aucune restauration ne sera possible après.
</p>
<?php endif; ?>
