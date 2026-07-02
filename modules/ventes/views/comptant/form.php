<?php
// modules/ventes/views/comptant/form.php
// Variables : $clients, $produits, $erreur?

$clients  = $clients ?? [];
$produits = $produits ?? [];
?>

<?php if (!empty($erreur)): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px">
    <?= htmlspecialchars($erreur) ?>
</div>
<?php endif; ?>

<h1 style="font-size:1.6rem;font-weight:700;margin:0 0 24px">Enregistrer une vente comptant</h1>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px">
    <form method="POST" action="<?= BASE_URL ?>/ventes/comptant/creer">

        <!-- EN-TÊTE -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Client (optionnel)
                </label>
                <select name="client_id"
                        style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="">— Client anonyme —</option>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $cl): ?>
                        <option value="<?= $cl['id'] ?>">
                            <?= htmlspecialchars($cl['raison_sociale'] ?? $cl['nom'] ?? 'Client #' . $cl['id']) ?>
                            <?php if (!empty($cl['code'])): ?>
                                (<?= htmlspecialchars($cl['code']) ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Aucun client actif</option>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Mode de paiement
                </label>
                <select name="mode_paiement"
                        style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff">
                    <option value="ESPÈCES">Espèces</option>
                    <option value="MOBILE_MONEY">Mobile Money</option>
                    <option value="CHÈQUE">Chèque</option>
                    <option value="VIREMENT">Virement</option>
                </select>
            </div>

            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Remise globale (FCFA)
                </label>
                <input type="number" name="remise_montant" id="remise" min="0" step="1" value="0"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>

            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                    Notes
                </label>
                <input type="text" name="notes"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
        </div>

        <!-- LIGNES PRODUITS -->
        <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e2e8f0">
            Produits vendus
        </h3>

        <div style="overflow-x:auto;margin-bottom:12px">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem">
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
                <tbody id="tbody-vc">
                    <tr class="ligne-vc" style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px">
                            <select name="produit_id[]" class="sel-p" required
                                    style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;color:#111">
                                <option value="">— Produit —</option>
                                <?php if (!empty($produits)): ?>
                                    <?php foreach ($produits as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                            data-prix="<?= $p['prix_vente_ht'] ?? 0 ?>"
                                            data-tva="<?= $p['tva'] ?? 0 ?>">
                                        <?= htmlspecialchars($p['designation'] ?? 'Produit #' . $p['id']) ?>
                                        <?php if (!empty($p['code'])): ?>
                                            (<?= htmlspecialchars($p['code']) ?>)
                                        <?php endif; ?>
                                        — stock: <?= number_format($p['stock_actuel'] ?? 0, 0, ',', ' ') ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Aucun produit actif</option>
                                <?php endif; ?>
                            </select>
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="quantite[]" class="inp-q"
                                   min="0.001" step="0.001" required
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="prix_unitaire_ht[]" class="inp-p"
                                   min="0" step="1" required
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:right;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="tva[]" class="inp-t"
                                   min="0" max="100" step="0.01" value="19.25"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px">
                            <input type="number" name="remise_pct[]" class="inp-r"
                                   min="0" max="100" step="0.01" value="0"
                                   style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;text-align:center;box-sizing:border-box">
                        </td>
                        <td style="padding:8px 12px;text-align:right;font-weight:600;color:#111" class="ht-vc">—</td>
                        <td style="padding:8px 12px;text-align:center">
                            <button type="button" class="btn-suppr"
                                    style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">✕</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" id="btn-add-vc"
                style="background:#f8fafc;border:1px dashed #cbd5e1;color:#475569;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:.875rem;margin-bottom:24px">
            + Ajouter un produit
        </button>

        <!-- CAISSE -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:24px;background:#f8fafc;border-radius:10px;margin-top:4px">

            <!-- Totaux calculés -->
            <div>
                <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 14px">Récapitulatif</h3>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.875rem;color:#374151;border-bottom:1px solid #e2e8f0">
                    <span>Total HT</span> <strong id="vc-ht">0 FCFA</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.875rem;color:#374151;border-bottom:1px solid #e2e8f0">
                    <span>Total TVA</span> <strong id="vc-tva">0 FCFA</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:.875rem;color:#374151;border-bottom:1px solid #e2e8f0">
                    <span>Remise</span> <strong id="vc-rem">0 FCFA</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:1rem">
                    <span style="font-weight:700">Net à payer</span>
                    <strong id="vc-net" style="font-size:1.2rem;color:#111">0 FCFA</strong>
                </div>
            </div>

            <!-- Caisse -->
            <div>
                <h3 style="font-size:.95rem;font-weight:600;color:#374151;margin:0 0 14px">🧾 Caisse</h3>
                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">
                        Montant perçu (FCFA) <span style="color:#ef4444">*</span>
                    </label>
                    <input type="number" name="montant_percu" id="percu"
                           min="0" step="1" value="0" required
                           style="width:100%;padding:10px 12px;border:2px solid #2563eb;border-radius:6px;font-size:1rem;font-weight:600;box-sizing:border-box;text-align:right">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#fff;border-radius:8px;border:1px solid #e2e8f0">
                    <span style="font-size:.875rem;font-weight:500;color:#374151">Monnaie à rendre</span>
                    <strong id="monnaie" style="font-size:1.3rem;color:#16a34a">0 FCFA</strong>
                </div>
            </div>
        </div>

        <!-- BOUTONS -->
        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0">
            <a href="<?= BASE_URL ?>/ventes/comptant"
               style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.9rem">
                Annuler
            </a>
            <button type="submit"
                    style="padding:10px 28px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:600;cursor:pointer">
                🧾 Enregistrer la vente
            </button>
        </div>

    </form>
</div>

<?php
$iStyle = 'width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box';
$opts = '';
if (!empty($produits)) {
    foreach ($produits as $p) {
        $opts .= sprintf(
            '<option value="%d" data-prix="%s" data-tva="%s">%s (%s) — stock: %s</option>',
            $p['id'], 
            $p['prix_vente_ht'] ?? 0, 
            $p['tva'] ?? 0,
            htmlspecialchars($p['designation'] ?? 'Produit #' . $p['id']), 
            $p['unite'] ?? '', 
            number_format($p['stock_actuel'] ?? 0, 0, ',', ' ')
        );
    }
}
?>
<script>
const tplVC = `<tr class="ligne-vc" style="border-bottom:1px solid #f1f5f9">
    <td style="padding:8px 12px"><select name="produit_id[]" class="sel-p" required style="<?= addslashes($iStyle) ?>;color:#111"><option value="">— Produit —</option><?= addslashes($opts) ?></select></td>
    <td style="padding:8px 12px"><input type="number" name="quantite[]"         class="inp-q" min="0.001" step="0.001" required style="<?= addslashes($iStyle) ?>;text-align:center"></td>
    <td style="padding:8px 12px"><input type="number" name="prix_unitaire_ht[]" class="inp-p" min="0"     step="1"     required style="<?= addslashes($iStyle) ?>;text-align:right"></td>
    <td style="padding:8px 12px"><input type="number" name="tva[]"              class="inp-t" min="0" max="100" step="0.01" value="19.25" style="<?= addslashes($iStyle) ?>;text-align:center"></td>
    <td style="padding:8px 12px"><input type="number" name="remise_pct[]"       class="inp-r" min="0" max="100" step="0.01" value="0" style="<?= addslashes($iStyle) ?>;text-align:center"></td>
    <td style="padding:8px 12px;text-align:right;font-weight:600;color:#111" class="ht-vc">—</td>
    <td style="padding:8px 12px;text-align:center"><button type="button" class="btn-suppr" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;font-size:.85rem">✕</button></td>
</tr>`;

document.getElementById('btn-add-vc').addEventListener('click', () => {
    document.getElementById('tbody-vc').insertAdjacentHTML('beforeend', tplVC);
    bind();
});

function bind() {
    document.querySelectorAll('.sel-p').forEach(s => {
        s.onchange = function() {
            const o = this.selectedOptions[0];
            const tr = this.closest('tr');
            if (o?.dataset.prix) {
                const prixInput = tr.querySelector('.inp-p');
                const tvaInput = tr.querySelector('.inp-t');
                if (prixInput && o.dataset.prix) prixInput.value = o.dataset.prix;
                if (tvaInput && o.dataset.tva) tvaInput.value = o.dataset.tva;
            }
            calc();
        };
    });
    document.querySelectorAll('.inp-q,.inp-p,.inp-t,.inp-r').forEach(i => i.oninput = calc);
    document.querySelectorAll('.btn-suppr').forEach(b => b.onclick = function() {
        if (document.querySelectorAll('.ligne-vc').length > 1) {
            this.closest('tr').remove(); 
        } else {
            alert('Il doit rester au moins une ligne.');
        }
        calc();
    });
}

function calc() {
    let ht = 0, tva = 0;
    document.querySelectorAll('.ligne-vc').forEach(tr => {
        const q = parseFloat(tr.querySelector('.inp-q')?.value) || 0;
        const p = parseFloat(tr.querySelector('.inp-p')?.value) || 0;
        const t = parseFloat(tr.querySelector('.inp-t')?.value) || 0;
        const r = parseFloat(tr.querySelector('.inp-r')?.value) || 0;
        const h = q * p * (1 - r / 100);
        tr.querySelector('.ht-vc').textContent = fmt(h);
        ht += h; 
        tva += h * (t / 100);
    });
    const rem  = parseFloat(document.getElementById('remise').value)  || 0;
    const net  = ht + tva - rem;
    const perc = parseFloat(document.getElementById('percu').value)   || 0;
    const mo   = perc - net;

    document.getElementById('vc-ht').textContent  = fmt(ht);
    document.getElementById('vc-tva').textContent = fmt(tva);
    document.getElementById('vc-rem').textContent = fmt(rem);
    document.getElementById('vc-net').textContent = fmt(net);

    const el = document.getElementById('monnaie');
    el.textContent = fmt(mo);
    el.style.color = mo < 0 ? '#dc2626' : '#16a34a';
}

function fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA'; }

document.getElementById('remise').addEventListener('input', calc);
document.getElementById('percu').addEventListener('input',  calc);
bind(); 
calc();
</script>