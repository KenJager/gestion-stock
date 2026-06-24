<?php
// modules/structure/views/produits/formulaire.php
// Variables :
//   $produit      — null si création, array si édition
//   $familles     — array des familles
//   $produitsPeres— array des produits pères disponibles
//   $etape        — 1 | 2 | null (null = mode édition, pas de multi-étapes)
//   $etape1       — données saisies à l'étape 1 (disponible à l'étape 2)
//   $titre        — string

$estEdition = $produit !== null;
$etape      = $etape ?? null;   // null en mode édition

// En mode édition : action directe, en création : action unique POST /structure/produits
$action = $estEdition
    ? BASE_URL . '/structure/produits/modifier/' . $produit['id']
    : BASE_URL . '/structure/produits';

$actif   = $produit['actif']   ?? true;
$estActif = ($actif === true || $actif === 't' || $actif === 1);

$estPere  = ($produit['est_pere'] ?? false) === true
         || ($produit['est_pere'] ?? false) === 1
         || ($produit['est_pere'] ?? false) === 't';

// Utilisation de la clé parent_id harmonisée entre l'édition et l'étape 1
$id_du_pere = $produit['parent_id'] ?? $e1['parent_id'] ?? null;
$estFils  = !$estPere && $id_du_pere !== null;

// Données reçues de l'étape 1 (pour pré-remplir l'étape 2)
$e1 = $etape1 ?? [];

// ── Indicateur d'étapes (affiché seulement en création) ──────────────────────
function indicateurEtapes(int $etapeCourante): void {
    $etapes = ['Identification', 'Prix & Stock'];
    echo '<div class="flex items-center gap-0 mb-6">';
    foreach ($etapes as $i => $label) {
        $num    = $i + 1;
        $actif  = $num === $etapeCourante;
        $fait   = $num < $etapeCourante;
        $cercle = $fait
            ? 'bg-gray-900 text-white'
            : ($actif ? 'bg-gray-900 text-white ring-2 ring-offset-2 ring-gray-400' : 'bg-gray-200 text-gray-500');
        $texte  = $actif ? 'text-gray-900 font-semibold' : ($fait ? 'text-gray-600' : 'text-gray-400');
        echo "<div class='flex items-center gap-2" . ($i > 0 ? ' flex-1' : '') . "'>";
        if ($i > 0) {
            echo "<div class='flex-1 h-px " . ($fait ? 'bg-gray-900' : 'bg-gray-200') . " mx-2'></div>";
        }
        echo "<div class='flex items-center gap-2'>";
        echo "<span class='w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {$cercle}'>";
        echo $fait ? '<i class="fa-solid fa-check text-xs"></i>' : $num;
        echo "</span>";
        echo "<span class='text-sm {$texte}'>{$label}</span>";
        echo "</div></div>";
    }
    echo '</div>';
}
?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($titre) ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                <?php if ($estEdition): ?>
                    Modifier les informations du produit
                <?php elseif ($etape === 1): ?>
                    Étape 1 sur 2 — Identification du produit
                <?php else: ?>
                    Étape 2 sur 2 — Prix, fiscalité et stock
                <?php endif; ?>
            </p>
        </div>
        <?php if ($etape === 2): ?>
            <!-- Retour à l'étape 1 : soumettre avec etape=1 serait complexe ; on redirige simplement -->
            <a href="<?= BASE_URL ?>/structure/produits/creer"
               class="btn btn-secondary"
               onclick="return confirm('Retourner à l\'étape 1 ? Les données de l\'étape 2 seront perdues.')">
                <i class="fa-solid fa-arrow-left"></i> Étape précédente
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/structure/produits" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        <?php endif; ?>
    </div>

    <?php if (!$estEdition): ?>
        <?= indicateurEtapes($etape ?? 1) ?>
    <?php endif; ?>

    <form method="POST" action="<?= $action ?>">

        <?php if (!$estEdition): ?>
            <input type="hidden" name="etape" value="<?= $etape ?? 1 ?>">
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════
             ÉTAPE 1 (ou formulaire d'édition complet)
             Identification du produit
        ════════════════════════════════════════════════════════════ -->
        <?php if ($estEdition || $etape === 1): ?>

        <div class="carte p-6 space-y-5 mt-4">
            <h2 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4">
                <i class="fa-solid fa-tag text-gray-400 mr-2"></i> Identification
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Code -->
                <div class="form-group">
                    <label class="label label-required">Code produit</label>
                    <input type="text" name="code"
                           class="input font-mono"
                           placeholder="ex : PROD-001"
                           value="<?= htmlspecialchars($produit['code'] ?? '') ?>"
                           required>
                </div>

                <!-- Désignation -->
                <div class="form-group">
                    <label class="label label-required">Désignation</label>
                    <input type="text" name="designation"
                           class="input"
                           placeholder="Nom complet du produit"
                           value="<?= htmlspecialchars($produit['designation'] ?? '') ?>"
                           required>
                </div>

                <!-- Famille -->
                <div class="form-group">
                    <label class="label label-required">Famille de produits</label>
                    <select name="famille_id" class="input" required>
                        <option value="">— Sélectionner une famille —</option>
                        <?php foreach ($familles as $f): ?>
                            <option value="<?= $f['id'] ?>"
                                    <?= (($produit['famille_id'] ?? '') == $f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Unité -->
                <div class="form-group">
                    <label class="label">Unité de mesure</label>
                    <input type="text" name="unite"
                           class="input"
                           placeholder="kg, litre, boîte, carton…"
                           value="<?= htmlspecialchars($produit['unite'] ?? '') ?>">
                </div>

            </div>

            <!-- Option : produit père -->
            <label class="flex items-start gap-2 cursor-pointer group mt-2">
                <input type="checkbox" name="est_pere" value="1" id="checkbox-est-pere"
                    class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-400"
                    <?= $estPere ? 'checked' : '' ?>>
                <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">
                    Produit père (fractionnable)
                </span>
            </label>

            <?php if ($estEdition): ?>
                <!-- Alertes pour les transitions -->
                <?php 
                    $ancienEstPere = isset($produit['est_pere']) && (int)$produit['est_pere'] === 1;
                    $estActuellementFils = isset($produit['parent_id']) && !empty($produit['parent_id']);
                ?>
                
                <?php if ($ancienEstPere): ?>
                    <div class="rounded-md bg-amber-50 border border-amber-200 p-3 flex items-center gap-3 mt-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                        <p class="text-xs text-amber-700">
                            <strong>Attention :</strong> Ce produit est actuellement un <strong>produit père</strong>.
                            <?php if (!$estPere): ?>
                                Si vous le passez en produit fils/simple, <strong>tous ses fils seront supprimés</strong> de la relation.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if ($estActuellementFils && !$estPere): ?>
                    <div class="rounded-md bg-blue-50 border border-blue-200 p-3 flex items-center gap-3 mt-2">
                        <i class="fa-solid fa-info-circle text-blue-500 text-sm"></i>
                        <p class="text-xs text-blue-700">
                            Ce produit est actuellement un <strong>produit fils</strong>. 
                            Vous pouvez changer son père ou en faire un produit père.
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (!$ancienEstPere && $estPere): ?>
                    <div class="rounded-md bg-green-50 border border-green-200 p-3 flex items-center gap-3 mt-2">
                        <i class="fa-solid fa-arrow-right text-green-500 text-sm"></i>
                        <p class="text-xs text-green-700">
                            Ce produit va devenir un <strong>produit père</strong>. 
                            <?php if ($estActuellementFils): ?>
                                Il sera retiré de sa relation de fils existante.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($estEdition && $estFils): ?>
            <div class="rounded-md bg-blue-50 border border-blue-200 p-3 flex items-center gap-3 mt-2">
                <i class="fa-solid fa-code-branch text-blue-500 text-sm"></i>
                <p class="text-xs text-blue-700">
                    Ce produit est un <strong>produit fils</strong>. Il est lié au produit père sélectionné ci-dessus.
                </p>
            </div>
            <?php endif; ?>

            <!-- Produit père (uniquement si ce n'est PAS un produit père lui-même) -->
            <div class="form-group mt-2" id="bloc-produit-pere" style="<?= $estPere ? 'display:none;' : '' ?>">
                <label class="label">Rattacher à un produit père</label>
                <select name="parent_id" id="select-produit-pere" class="input" <?= $estPere ? 'disabled' : '' ?>>
                    <option value="">-- Aucun (Produit simple) --</option>
                    <?php foreach ($produitsPeres as $p): ?>
                        <?php 
                            // Évite qu'un produit puisse se sélectionner lui-même s'il passe de simple à fils
                            if ($estEdition && (int)$produit['id'] === (int)$p['id']) continue;
                            
                            $selected = (!empty($id_du_pere) && (int)$id_du_pere === (int)$p['id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $p['id'] ?>" <?= $selected ?>><?= htmlspecialchars($p['designation']) ?> (<?= htmlspecialchars($p['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Sélectionnez un produit père pour que ce produit devienne un produit fils.
                </p>
            </div>

            <div class="mt-2" id="bloc-quantite-fils" style="<?= !$estFils ? 'display:none;' : '' ?>">
                <label class="label">Quantité par père</label>
                <input type="number" name="quantite_fils" id="input-quantite-fils" step="1" min="1" 
                    class="input max-w-xs" value="<?= htmlspecialchars($produit['quantite_fils'] ?? 1) ?>" <?= !$estFils ? 'disabled' : '' ?>>
                <p class="text-xs text-gray-400 mt-1">
                    Nombre d'unités de ce produit dans une unité du produit père.
                    Exemple: 12 morceaux par carton.
                </p>
            </div>

        <?php endif; /* fin étape 1 / édition */ ?>


        <!-- ═══════════════════════════════════════════════════════════
             ÉTAPE 2 uniquement — Prix, TVA, Stock, Statut
        ════════════════════════════════════════════════════════════ -->
        <?php if ($estEdition || $etape === 2): ?>

        <!-- Rappel des données de l'étape 1 (lecture seule) en étape 2 -->
        <?php if ($etape === 2): ?>
        <div class="carte p-4 mt-4 bg-gray-50 border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Récapitulatif — Étape 1</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-700">
                <div><span class="text-gray-400">Code</span><br><strong><?= htmlspecialchars($e1['code'] ?? '—') ?></strong></div>
                <div><span class="text-gray-400">Désignation</span><br><strong><?= htmlspecialchars($e1['designation'] ?? '—') ?></strong></div>
                <div><span class="text-gray-400">Famille</span><br>
                    <?php
                    $fl = array_filter($familles, fn($f) => $f['id'] == ($e1['famille_id'] ?? ''));
                    $fl = reset($fl);
                    echo '<strong>' . htmlspecialchars($fl['libelle'] ?? '—') . '</strong>';
                    ?>
                </div>
                <div><span class="text-gray-400">Unité</span><br><strong><?= htmlspecialchars($e1['unite'] ?? '—') ?></strong></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Prix & TVA ──────────────────────────────── -->
        <div class="carte p-6 space-y-5 mt-4">
            <h2 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4">
                <i class="fa-solid fa-coins text-gray-400 mr-2"></i> Prix & Fiscalité
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Prix achat HT -->
                <div class="form-group">
                    <label class="label">Prix d'achat HT</label>
                    <div class="relative">
                        <input type="number" step="1" min="0" name="prix_achat_ht"
                               class="input pr-16"
                               value="<?= (int)($produit['prix_achat_ht'] ?? 0) ?>">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">FCFA</span>
                    </div>
                </div>

                <!-- Prix vente HT -->
                <div class="form-group">
                    <label class="label">Prix de vente HT</label>
                    <div class="relative">
                        <input type="number" step="1" min="0" name="prix_vente_ht"
                               class="input pr-16"
                               value="<?= (int)($produit['prix_vente_ht'] ?? 0) ?>">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">FCFA</span>
                    </div>
                </div>

                <!-- TVA -->
                <div class="form-group">
                    <label class="label">Taux TVA</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="tva"
                               class="input pr-8"
                               value="<?= number_format((float)($produit['tva'] ?? 19.25), 2, '.', '') ?>">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">%</span>
                    </div>
                </div>
            </div>

            <!-- Aperçu TTC -->
            <p class="text-xs text-gray-400 mt-1">
                Prix TTC indicatif :
                <span id="apercu-ttc" class="font-semibold text-gray-600">—</span>
            </p>
        </div>

        <!-- ── Stock ──────────────────────────────────── -->
        <div class="carte p-6 space-y-5 mt-4">
            <h2 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4">
                <i class="fa-solid fa-warehouse text-gray-400 mr-2"></i> Stock
            </h2>

            <!-- En édition : lecture seule -->
            <div class="rounded-md bg-gray-50 border border-gray-200 p-3 flex items-center mt-2 gap-3">
                <i class="fa-solid fa-circle-info text-gray-400 text-sm"></i>
                <p class="text-xs text-gray-500">
                    Stock actuel : <strong class="text-gray-800"><?= number_format((float)($produit['stock_actuel'] ?? 0), 3, ',', ' ') ?></strong>
                    — modifiable uniquement via les bons d'entrée/sortie.
                </p>
            </div>

            <!-- Stock minimum -->
            <div class="form-group mt-2">
                <label class="label">Stock minimum (seuil d'alerte)</label>
                <input type="number" step="0.001" min="0" name="stock_minimum"
                       class="input max-w-xs"
                       value="<?= number_format((float)($produit['stock_minimum'] ?? 0), 3, '.', '') ?>">
                <p class="text-xs text-gray-400 mt-1">
                    Une alerte sera déclenchée quand le stock passe sous ce seuil.
                </p>
            </div>

            <!-- Date de péremption -->
            <div class="form-group">
                <label class="label">Date de péremption</label>
                <input type="date" name="date_peremption"
                       class="input max-w-xs"
                       value="<?= htmlspecialchars($produit['date_peremption'] ?? '') ?>">
            </div>
        </div>

        <!-- ── Statut ─────────────────────────────────── -->
        <div class="carte p-6 mt-4">
            <h2 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4">
                <i class="fa-solid fa-sliders text-gray-400 mr-2"></i> Statut
            </h2>
            <label class="flex items-start gap-2 cursor-pointer group">
                <input type="checkbox" name="actif" value="1"
                       class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-400"
                       <?= $estActif ? 'checked' : '' ?>>
                <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Produit actif</span>
            </label>
        </div>

        <?php endif; /* fin étape 2 / édition */ ?>

        <!-- ── Boutons ─────────────────────────────────────────────── -->
        <div class="flex justify-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/structure/produits" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <?php if ($estEdition): ?>
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
                <?php elseif ($etape === 1): ?>
                    Étape suivante <i class="fa-solid fa-arrow-right ml-1"></i>
                <?php else: ?>
                    <i class="fa-solid fa-plus"></i> Créer le produit
                <?php endif; ?>
            </button>
        </div>

    </form>
</div>

<script>
// ── Aperçu prix TTC en temps réel ─────────────────────────────────────────
(function () {
    const champPV  = document.querySelector('[name="prix_vente_ht"]');
    const champTVA = document.querySelector('[name="tva"]');
    const apercu   = document.getElementById('apercu-ttc');
    if (!champPV || !champTVA || !apercu) return;

    function mettreAJour() {
        const pv  = parseFloat(champPV.value)  || 0;
        const tva = parseFloat(champTVA.value) || 0;
        const ttc = pv * (1 + tva / 100);
        apercu.textContent = new Intl.NumberFormat('fr-FR').format(Math.round(ttc)) + ' FCFA';
    }

    champPV.addEventListener('input', mettreAJour);
    champTVA.addEventListener('input', mettreAJour);
    mettreAJour();
})();

// ── Masquer le sélecteur "produit père" et la "quantité" si "est_pere" est coché ──────────
// ── Gestion dynamique des blocs "Produit Père" et "Quantité Fils" ──────────
// ── Gestion dynamique des transitions (Simple <-> Père <-> Fils) ──────────
(function () {
    const checkbox      = document.getElementById('checkbox-est-pere');
    const blocPere      = document.getElementById('bloc-produit-pere');
    const selectParent  = document.getElementById('select-produit-pere');
    const blocQuantite  = document.getElementById('bloc-quantite-fils');
    const inputQuantite = document.getElementById('input-quantite-fils');
    
    if (!checkbox) return;

    function sync() {
        if (checkbox.checked) {
            // Devenu ou est un produit PÈRE : masquer et désactiver les options de fils
            if (blocPere) blocPere.style.display = 'none';
            if (selectParent) { selectParent.disabled = true; selectParent.value = ''; }
            
            if (blocQuantite) blocQuantite.style.display = 'none';
            if (inputQuantite) inputQuantite.disabled = true;
        } else {
            // Produit SIMPLE ou FILS : réafficher le sélecteur de parent
            if (blocPere) blocPere.style.display = 'block';
            if (selectParent) selectParent.disabled = false;
            
            // Gérer l'apparition de la quantité uniquement si un parent est sélectionné
            syncQuantiteVisibilite();
        }
    }

    function syncQuantiteVisibilite() {
        if (!blocQuantite || !selectParent) return;
        
        // Si l'utilisateur choisit un parent (devient fils), on affiche la quantité
        if (selectParent.value !== '') {
            blocQuantite.style.display = 'block';
            if (inputQuantite) inputQuantite.disabled = false;
        } else {
            // Si la valeur est vide (redevient produit simple), on cache la quantité
            blocQuantite.style.display = 'none';
            if (inputQuantite) inputQuantite.disabled = true;
        }
    }

    // Écouteurs d'événements
    checkbox.addEventListener('change', sync);
    if (selectParent) {
        selectParent.addEventListener('change', syncQuantiteVisibilite);
    }

    // Calcul de l'état initial au chargement de la page
    sync();
})();
</script>