<?php
// modules/ventes/Views/commandes/form.php
// Variables : $commande (null=création | array=édition), $erreur?

$edition = $commande !== null;
$action  = $edition
    ? BASE_URL . '/ventes/commandes/' . $commande['id'] . '/modifier'
    : BASE_URL . '/ventes/commandes/creer';
?>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<!-- Titre -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="font-size:1.6rem; font-weight:700; margin:0;">
        <?= $edition ? 'Éditer la commande ' . htmlspecialchars($commande['numero']) : 'Enregistrer une commande client' ?>
    </h1>
    
    <a href="<?= BASE_URL ?>/ventes/commandes" class="btn" style="background-color: black; color: white; border: 1px solid black;">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px">
    <form method="POST" action="<?= $action ?>">

        <!-- ── EN-TÊTE : grille 2 colonnes comme l'image ── -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

            <!-- Client -->
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Client <span style="color:#ef4444">*</span>
                </label>
                <select name="client_id" required
                        style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;color:#111;background:#fff;outline:none">
                    <option value="">— Choisir un client —</option>
                    <?php foreach ($clients as $cl): ?>
                    <option value="<?= $cl['id'] ?>"
                        <?= $edition && $commande['client_id'] == $cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['raison_sociale'] ?? $cl['nom'] ?? 'Client #' . $cl['id']) ?>
                        <?php if (!empty($cl['code'])): ?>
                            (<?= htmlspecialchars($cl['code']) ?>)
                        <?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date livraison prévue -->
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Date de livraison prévue
                </label>
                <input type="date" name="date_livraison_prevue"
                       value="<?= $edition && $commande['date_livraison_prevue'] ? date('Y-m-d', strtotime($commande['date_livraison_prevue'])) : '' ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;color:#111;box-sizing:border-box">
            </div>

            <!-- Remise globale -->
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Remise globale (%)
                </label>
                <input type="number" name="remise_pct" id="remise_pct"
                       min="0" max="100" step="0.01"
                       value="<?= htmlspecialchars($commande['remise_pct'] ?? '0') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;color:#111;box-sizing:border-box">
            </div>

            <!-- Notes -->
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Notes
                </label>
                <input type="text" name="notes"
                       value="<?= htmlspecialchars($commande['notes'] ?? '') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;color:#111;box-sizing:border-box">
            </div>
        </div>

        <!-- ── LIGNES PRODUITS ── -->
        <div style="margin-bottom:16px">
            <h3 style="font-size:1rem;font-weight:600;color:#111;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e2e8f0">
                Produits commandés
            </h3>

            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem" id="tbl-lignes">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="padding:10px 12px;text-align:left;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0">Produit</th>
                            <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:100px">Quantité</th>
                            <th style="padding:10px 12px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:130px">PU HT (FCFA)</th>
                            <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:80px">TVA %</th>
                            <th style="padding:10px 12px;text-align:center;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:80px">Rem %</th>
                            <th style="padding:10px 12px;text-align:right;font-weight:600;color:#374151;border-bottom:1px solid #e2e8f0;width:120px">HT ligne</th>
                            <th style="padding:10px 12px;width:40px;border-bottom:1px solid #e2e8f0"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-lignes">
                        <?php 
                        $lignesInit = [];
                        if ($edition && isset($commande['lignes']) && !empty($commande['lignes'])) {
                            $lignesInit = $commande['lignes'];
                        } else {
                            // Une ligne vide par défaut
                            $lignesInit = [['produit_id' => '', 'quantite_commandee' => '', 'prix_unitaire_ht' => '', 'tva' => 0, 'remise_pct' => 0]];
                        }
                        ?>
                        <?php foreach ($lignesInit as $lg): ?>
                        <tr class="ligne-cmd" style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:8px 12px">
                                <select name="produit_id[]" class="sel-produit" required
                                        style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;color:#111">
                                    <option value="">— Produit —</option>
                                    <?php foreach ($produits as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                            data-prix="<?= $p['prix_vente_ht'] ?? 0 ?>"
                                            data-tva="<?= $p['tva'] ?? 0 ?>"
                                        <?= isset($lg['produit_id']) && $lg['produit_id'] == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['designation']) ?>
                                        <?php if (!empty($p['code'])): ?>
                                            (<?= htmlspecialchars($p['code']) ?>)
                                        <?php endif; ?>
                                        — stock: <?= number_format($p['stock_actuel'] ?? 0, 0, ',', ' ') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding:8px 12px">
                                <input type="number" name="quantite[]" class="inp-qte"
                                       min="0.001" step="0.001" required
                                       value="<?= htmlspecialchars($lg['quantite_commandee'] ?? $lg['quantite'] ?? '') ?>"
                                       style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                            </td>
                            <td style="padding:8px 12px">
                                <input type="number" name="prix_unitaire_ht[]" class="inp-prix"
                                       min="0" step="1" required
                                       value="<?= htmlspecialchars($lg['prix_unitaire_ht'] ?? '') ?>"
                                       style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:right;box-sizing:border-box">
                            </td>
                            <td style="padding:8px 12px">
                                <input type="number" name="tva[]" class="inp-tva"
                                       min="0" max="100" step="0.01"
                                       value="<?= htmlspecialchars($lg['tva'] ?? '19.25') ?>"
                                       style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                            </td>
                            <td style="padding:8px 12px">
                                <input type="number" name="remise_pct[]" class="inp-rem"
                                       min="0" max="100" step="0.01"
                                       value="<?= htmlspecialchars($lg['remise_pct'] ?? '0') ?>"
                                       style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                            </td>
                            <td style="padding:8px 12px;text-align:right;font-weight:600;color:#111" class="ht-ligne">—</td>
                            <td style="padding:8px 12px;text-align:center">
                                <button type="button" class="btn-suppr"
                                        style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">
                                    ✕
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="button" id="btn-add"
                    style="margin-top:10px;background:#f8fafc;border:1px dashed #cbd5e1;color:#475569;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:.875rem">
                + Ajouter une ligne
            </button>
        </div>

        <!-- ── TOTAUX ── -->
        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 24px;min-width:260px">
                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem;color:#374151">
                    <span>Total HT</span> <strong id="tot-ht">0 FCFA</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.9rem;color:#374151">
                    <span>Total TVA</span> <strong id="tot-tva">0 FCFA</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:1rem;border-top:1px solid #e2e8f0;margin-top:4px">
                    <span style="font-weight:700">Total TTC</span>
                    <strong id="tot-ttc" style="color:#111;font-size:1.1rem">0 FCFA</strong>
                </div>
            </div>
        </div>

        <!-- ── BOUTONS ── -->
        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0">
            <a href="<?= BASE_URL ?>/ventes/commandes"
               style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.9rem;font-weight:500">
                Annuler
            </a>
            <button type="submit"
                    style="padding:10px 24px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:500;cursor:pointer">
                <?= $edition ? 'Enregistrer les modifications' : 'Enregistrer la commande' ?>
            </button>
        </div>

    </form>
</div>

<?php
// Options produits pour JS (sérialisées)
$optsProduits = '';
foreach ($produits as $p) {
    $optsProduits .= sprintf(
        '<option value="%d" data-prix="%s" data-tva="%s">%s (%s) — stock: %s</option>',
        $p['id'], 
        $p['prix_vente_ht'] ?? 0, 
        $p['tva'] ?? 0,
        htmlspecialchars($p['designation'] ?? 'Produit #' . $p['id']), 
        $p['unite'] ?? '', 
        number_format($p['stock_actuel'] ?? 0, 0, ',', ' ')
    );
}
?>
<script>
const inputStyle = 'width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box';

// Échapper les guillemets simples pour le JS
const optionsHtml = `<?= addslashes($optsProduits) ?>`;

const tpl = `<tr class="ligne-cmd" style="border-bottom:1px solid #f1f5f9">
    <td style="padding:8px 12px">
        <select name="produit_id[]" class="sel-produit" required style="${inputStyle};color:#111">
            <option value="">— Produit —</option>${optionsHtml}
        </select>
    </td>
    <td style="padding:8px 12px"><input type="number" name="quantite[]"         class="inp-qte"  min="0.001" step="0.001" required style="${inputStyle};text-align:center"></td>
    <td style="padding:8px 12px"><input type="number" name="prix_unitaire_ht[]" class="inp-prix" min="0"     step="1"     required style="${inputStyle};text-align:right"></td>
    <td style="padding:8px 12px"><input type="number" name="tva[]"              class="inp-tva"  min="0" max="100" step="0.01" value="19.25" style="${inputStyle};text-align:center"></td>
    <td style="padding:8px 12px"><input type="number" name="remise_pct[]"       class="inp-rem"  min="0" max="100" step="0.01" value="0" style="${inputStyle};text-align:center"></td>
    <td style="padding:8px 12px;text-align:right;font-weight:600;color:#111" class="ht-ligne">—</td>
    <td style="padding:8px 12px;text-align:center">
        <button type="button" class="btn-suppr" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">✕</button>
    </td>
</tr>`;

document.getElementById('btn-add').addEventListener('click', () => {
    document.getElementById('tbody-lignes').insertAdjacentHTML('beforeend', tpl);
    bind();
});

function bind() {
    document.querySelectorAll('.sel-produit').forEach(s => {
        s.onchange = function() {
            const o = this.selectedOptions[0];
            const tr = this.closest('tr');
            if (o && o.dataset.prix) {
                const prixInput = tr.querySelector('.inp-prix');
                const tvaInput = tr.querySelector('.inp-tva');
                if (prixInput && o.dataset.prix) prixInput.value = o.dataset.prix;
                if (tvaInput && o.dataset.tva) tvaInput.value = o.dataset.tva;
            }
            recalc();
        };
    });
    document.querySelectorAll('.inp-qte,.inp-prix,.inp-tva,.inp-rem').forEach(i => i.oninput = recalc);
    document.querySelectorAll('.btn-suppr').forEach(b => b.onclick = function() {
        if (document.querySelectorAll('.ligne-cmd').length > 1) {
            this.closest('tr').remove(); 
        } else {
            alert('Il doit rester au moins une ligne.');
        }
        recalc();
    });
}

function recalc() {
    let ht = 0, tva = 0;
    document.querySelectorAll('.ligne-cmd').forEach(tr => {
        const q = parseFloat(tr.querySelector('.inp-qte')?.value)  || 0;
        const p = parseFloat(tr.querySelector('.inp-prix')?.value) || 0;
        const t = parseFloat(tr.querySelector('.inp-tva')?.value)  || 0;
        const r = parseFloat(tr.querySelector('.inp-rem')?.value)  || 0;
        const h = q * p * (1 - r/100);
        tr.querySelector('.ht-ligne').textContent = fmt(h);
        ht += h; 
        tva += h * (t/100);
    });
    const rg = parseFloat(document.getElementById('remise_pct').value) || 0;
    ht = ht * (1 - rg/100);
    document.getElementById('tot-ht').textContent  = fmt(ht);
    document.getElementById('tot-tva').textContent = fmt(tva);
    document.getElementById('tot-ttc').textContent = fmt(ht + tva);
}

function fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA'; }

document.getElementById('remise_pct').addEventListener('input', recalc);
bind(); 
recalc();
</script>