<?php
// modules/approvisionnements/views/commandes/formulaire.php
// Variables : $commande, $lignes, $fournisseurs, $mode
$isEdit = ($mode === 'edition');
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">
            <?= $isEdit ? 'Modifier le bon ' . htmlspecialchars($commande['numero']) : 'Nouveau bon de commande' ?>
        </h1>
        <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="POST"
          action="<?= BASE_URL ?>/appros/commandes<?= $isEdit ? '/' . $commande['id'] . '/modifier' : '/creer' ?>"
          class="carte p-6 space-y-6" id="form-commande">

        <!-- Entête -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="label">Numéro *</label>
                <input type="text" name="numero" class="input" required
                       value="<?= htmlspecialchars($commande['numero'] ?? 'BC-' . date('Ymd') . '-') ?>"
                       <?= $isEdit ? 'readonly' : '' ?>>
            </div>
            <div>
                <label class="label">Fournisseur *</label>
                <select name="fournisseur_id" class="input" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id'] ?>"
                            <?= ($commande['fournisseur_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['raison_sociale']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label">Date commande *</label>
                <input type="date" name="date_commande" class="input" required
                       value="<?= htmlspecialchars($commande['date_commande'] ?? date('Y-m-d')) ?>">
            </div>
            <div>
                <label class="label">Livraison prévue</label>
                <input type="date" name="date_livraison_prevue" class="input"
                       value="<?= htmlspecialchars($commande['date_livraison_prevue'] ?? '') ?>">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" class="input" rows="2"><?= htmlspecialchars($commande['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Lignes -->
        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-800">Articles commandés</h3>
                <button type="button" onclick="ajouterLigne()" class="btn btn-secondary text-sm">
                    <i class="fa-solid fa-plus"></i> Ajouter une ligne
                </button>
            </div>

            <table class="table" id="table-lignes">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">Prix unitaire HT</th>
                        <th class="text-right">TVA %</th>
                        <th class="text-right">Total HT</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-lignes">
                    <?php if (!empty($lignes)): ?>
                        <?php foreach ($lignes as $i => $l): ?>
                            <?= renderLigneCommande($i, $l) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-50">
                        <td colspan="4" class="text-right">Total HT :</td>
                        <td class="text-right font-mono" id="total-ht">0 FCFA</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 border-t pt-4">
            <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
            </button>
        </div>
    </form>
</div>

<?php
// Liste des produits (JSON pour JS)
$produitsJson = json_encode(
    \Database::query("SELECT id, code, designation, prix_achat_ht, tva FROM produits WHERE actif = true ORDER BY designation")->fetchAll()
);
?>

<script>
const PRODUITS = <?= $produitsJson ?>;
let compteurLigne = <?= count($lignes) ?>;

function renderLigne(index, data = {}) {
    const options = PRODUITS.map(p =>
        `<option value="${p.id}" data-prix="${p.prix_achat_ht}" data-tva="${p.tva}"
            ${data.produit_id == p.id ? 'selected' : ''}>
            ${p.code} - ${p.designation}
        </option>`
    ).join('');

    return `
    <tr data-index="${index}">
        <td>
            <select name="produit_id[]" class="input" onchange="majPrixLigne(this)" required>
                <option value="">-- Produit --</option>
                ${options}
            </select>
        </td>
        <td><input type="number" step="0.001" name="quantite_commandee[]" class="input text-right"
                   value="${data.quantite_commandee ?? 1}" min="0.001" oninput="recalculer()" required></td>
        <td><input type="number" step="0.01" name="prix_unitaire_ht[]" class="input text-right"
                   value="${data.prix_unitaire_ht ?? 0}" oninput="recalculer()" required></td>
        <td><input type="number" step="0.01" name="tva[]" class="input text-right"
                   value="${data.tva ?? 0}" oninput="recalculer()"></td>
        <td class="text-right font-mono ligne-total">0</td>
        <td>
            <button type="button" onclick="supprimerLigne(this)" class="btn btn-danger text-red-500 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </td>
    </tr>`;
}

function ajouterLigne() {
    document.getElementById('tbody-lignes').insertAdjacentHTML('beforeend', renderLigne(compteurLigne++));
    recalculer();
}

function supprimerLigne(btn) {
    btn.closest('tr').remove();
    recalculer();
}

function majPrixLigne(select) {
    const opt = select.options[select.selectedIndex];
    const tr  = select.closest('tr');
    tr.querySelector('[name="prix_unitaire_ht[]"]').value = opt.dataset.prix || 0;
    tr.querySelector('[name="tva[]"]').value = opt.dataset.tva || 0;
    recalculer();
}

function recalculer() {
    let total = 0;
    document.querySelectorAll('#tbody-lignes tr').forEach(tr => {
        const qte  = parseFloat(tr.querySelector('[name="quantite_commandee[]"]').value) || 0;
        const prix = parseFloat(tr.querySelector('[name="prix_unitaire_ht[]"]').value) || 0;
        const ht   = qte * prix;
        tr.querySelector('.ligne-total').textContent = ht.toLocaleString('fr-FR') + ' FCFA';
        total += ht;
    });
    document.getElementById('total-ht').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('#tbody-lignes tr').length === 0) ajouterLigne();
    recalculer();
});
</script>

<?php
function renderLigneCommande(int $i, array $l): string {
    $produits = \Database::query("SELECT id, code, designation, prix_achat_ht, tva FROM produits WHERE actif = true ORDER BY designation")->fetchAll();
    $options = '';
    foreach ($produits as $p) {
        $sel = ($p['id'] == $l['produit_id']) ? 'selected' : '';
        $options .= "<option value=\"{$p['id']}\" data-prix=\"{$p['prix_achat_ht']}\" data-tva=\"{$p['tva']}\" $sel>{$p['code']} - {$p['designation']}</option>";
    }
    $total = (float)$l['quantite_commandee'] * (float)$l['prix_unitaire_ht'];
    return <<<HTML
    <tr data-index="{$i}">
        <td><select name="produit_id[]" class="input" onchange="majPrixLigne(this)" required>
            <option value="">-- Produit --</option>{$options}
        </select></td>
        <td><input type="number" step="0.001" name="quantite_commandee[]" class="input text-right" value="{$l['quantite_commandee']}" min="0.001" oninput="recalculer()" required></td>
        <td><input type="number" step="0.01" name="prix_unitaire_ht[]" class="input text-right" value="{$l['prix_unitaire_ht']}" oninput="recalculer()" required></td>
        <td><input type="number" step="0.01" name="tva[]" class="input text-right" value="{$l['tva']}" oninput="recalculer()"></td>
        <td class="text-right font-mono ligne-total">{$total}</td>
        <td><button type="button" onclick="supprimerLigne(this)" class="btn btn-danger text-red-500 text-sm"><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
    HTML;
}
?>