<?php
// modules/ventes/Views/sorties/form.php
// Variables : $sortie (null=création | array=édition), $erreur?

$edition  = $sortie !== null;
$action   = $edition
    ? BASE_URL . '/ventes/sorties/' . $sortie['id'] . '/modifier'
    : BASE_URL . '/ventes/sorties/creer';


?>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<h1 style="font-size:1.6rem;font-weight:700;margin:0 0 24px">
    <?= $edition ? 'Éditer le bon de sortie ' . htmlspecialchars($sortie['numero']) : 'Sortir un produit non vendu ou périmé' ?>
</h1>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px">
    <form method="POST" action="<?= $action ?>">

        <!-- EN-TÊTE -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Motif de sortie <span style="color:#ef4444">*</span>
                </label>
                <select name="motif" required
                        style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="NON_VENDU"  <?= ($sortie['motif'] ?? '') === 'NON_VENDU'  ? 'selected' : '' ?>>Produit non vendu</option>
                    <option value="PÉRIMÉ"     <?= ($sortie['motif'] ?? '') === 'PÉRIMÉ'     ? 'selected' : '' ?>>Produit périmé</option>
                    <option value="CASSE"      <?= ($sortie['motif'] ?? '') === 'CASSE'      ? 'selected' : '' ?>>Cassé / Endommagé</option>
                    <option value="AJUSTEMENT" <?= ($sortie['motif'] ?? '') === 'AJUSTEMENT' ? 'selected' : '' ?>>Ajustement inventaire</option>
                </select>
            </div>

            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Observations générales
                </label>
                <input type="text" name="notes"
                       value="<?= htmlspecialchars($sortie['notes'] ?? '') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
        </div>

        <!-- LIGNES PRODUITS -->
        <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e2e8f0">
            Produits à sortir du stock
        </h3>

        <div style="overflow-x:auto;margin-bottom:12px">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:110px">Quantité</th>
                        <th style="padding:10px 12px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:150px">PU estimé (FCFA)</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Observations ligne</th>
                        <th style="padding:10px 12px;width:40px;border-bottom:1px solid #e2e8f0"></th>
                    </tr>
                </thead>
                <tbody id="tbody-sortie">
                    <?php $lignesInit = $edition ? $sortie['lignes'] : [[]]; ?>
                    <?php foreach ($lignesInit as $lg): ?>
                    <tr class="ligne-sortie" style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px">
                            <select name="produit_id[]" required
                                    style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;color:#111">
                                <option value="">— Produit —</option>
                                <?php foreach ($produits as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= isset($lg['produit_id']) && $lg['produit_id'] == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['designation']) ?>
                                    (<?= $p['unite'] ?? '' ?>) — stock: <?= $p['stock_actuel'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="quantite[]"
                                   min="0.001" step="0.001" required
                                   value="<?= htmlspecialchars($lg['quantite'] ?? '') ?>"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="prix_unitaire[]"
                                   min="0" step="1"
                                   value="<?= htmlspecialchars($lg['prix_unitaire'] ?? '0') ?>"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:right;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px">
                            <input type="text" name="observations[]"
                                   value="<?= htmlspecialchars($lg['observations'] ?? '') ?>"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px;text-align:center">
                            <button type="button" class="btn-suppr-s"
                                    style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">✕</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="button" id="btn-add-s"
                style="background:#f8fafc;border:1px dashed #cbd5e1;color:#475569;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:.875rem;margin-bottom:24px">
            + Ajouter une ligne
        </button>

        <!-- BOUTONS -->
        <div style="display:flex;justify-content:flex-end;gap:12px;padding-top:20px;border-top:1px solid #e2e8f0">
            <a href="<?= BASE_URL ?>/ventes/sorties"
               style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.9rem">
                Annuler
            </a>
            <button type="submit"
                    style="padding:10px 24px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:600;cursor:pointer">
                <?= $edition ? 'Enregistrer les modifications' : '📤 Valider la sortie de stock' ?>
            </button>
        </div>

    </form>
</div>

<?php
$iS = 'width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box';
$optsS = '';
foreach ($produits as $p) {
    $optsS .= sprintf(
        '<option value="%d">%s (%s) — stock: %s</option>',
        $p['id'], htmlspecialchars($p['designation']), $p['unite'] ?? '', $p['stock_actuel']
    );
}
?>
<script>
const tplS = `<tr class="ligne-sortie" style="border-bottom:1px solid #f1f5f9">
    <td style="padding:8px 12px"><select name="produit_id[]" required style="<?= addslashes($iS) ?>;color:#111"><option value="">— Produit —</option><?= addslashes($optsS) ?></select></td>
    <td style="padding:8px 12px"><input type="number" name="quantite[]"      min="0.001" step="0.001" required style="<?= addslashes($iS) ?>;text-align:center"></td>
    <td style="padding:8px 12px"><input type="number" name="prix_unitaire[]" min="0"     step="1"             style="<?= addslashes($iS) ?>;text-align:right" value="0"></td>
    <td style="padding:8px 12px"><input type="text"   name="observations[]"                                   style="<?= addslashes($iS) ?>"></td>
    <td style="padding:8px 12px;text-align:center"><button type="button" class="btn-suppr-s" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">✕</button></td>
</tr>`;

document.getElementById('btn-add-s').addEventListener('click', () => {
    document.getElementById('tbody-sortie').insertAdjacentHTML('beforeend', tplS);
    bindS();
});

function bindS() {
    document.querySelectorAll('.btn-suppr-s').forEach(b => b.onclick = function() {
        this.closest('tr').remove();
    });
}
bindS();
</script>
