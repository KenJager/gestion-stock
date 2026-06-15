<?php
// modules/approvisionnements/views/receptions/formulaire.php
// Variables : $commandesEligibles
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">Nouveau bon de réception</h1>
        <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/appros/receptions/creer" class="carte p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Commande fournisseur *</label>
                <select name="bon_commande_id" class="input" id="select-commande" onchange="chargerLignes()" required>
                    <option value="">-- Sélectionner une commande --</option>
                    <?php foreach ($commandesEligibles as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['numero']) ?> — <?= htmlspecialchars($c['fournisseur_nom']) ?>
                            (<?= date('d/m/Y', strtotime($c['date_commande'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label">Date réception *</label>
                <input type="date" name="date_reception" class="input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" class="input" rows="2"></textarea>
            </div>
        </div>

        <!-- Lignes à recevoir -->
        <div id="zone-lignes" class="border-t pt-4" style="display:none;">
            <h3 class="font-semibold text-gray-800 mb-3">Articles à réceptionner</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Commandée</th>
                        <th class="text-right">Déjà reçue</th>
                        <th class="text-right">À recevoir</th>
                        <th class="text-right">Quantité reçue</th>
                    </tr>
                </thead>
                <tbody id="tbody-reception"></tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2 border-t pt-4">
            <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Enregistrer la réception
            </button>
        </div>
    </form>
</div>

<script>
async function chargerLignes() {
    const bonId = document.getElementById('select-commande').value;
    const zone  = document.getElementById('zone-lignes');
    const tbody = document.getElementById('tbody-reception');
    if (!bonId) { zone.style.display = 'none'; return; }

    // Appel AJAX pour récupérer les lignes de la commande
    try {
        const resp = await fetch(`<?= BASE_URL ?>/api/lignes-commande/${bonId}`);
        const lignes = await resp.json();
        tbody.innerHTML = '';
        lignes.forEach(l => {
            const restant = parseFloat(l.quantite_commandee) - parseFloat(l.quantite_recue || 0);
            if (restant <= 0) return;
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${l.code} - ${l.designation}</td>
                    <td class="text-right font-mono">${parseFloat(l.quantite_commandee).toFixed(2)}</td>
                    <td class="text-right font-mono text-green-600">${parseFloat(l.quantite_recue || 0).toFixed(2)}</td>
                    <td class="text-right font-mono text-blue-600">${restant.toFixed(2)}</td>
                    <td class="text-right">
                        <input type="hidden" name="ligne_id[]" value="${l.id}">
                        <input type="number" step="0.001" name="quantite_recue[]" class="input text-right"
                               value="${restant.toFixed(3)}" max="${restant}" min="0">
                    </td>
                </tr>
            `);
        });
        zone.style.display = 'block';
    } catch (e) {
        alert('Erreur de chargement : ' + e.message);
    }
}
</script>