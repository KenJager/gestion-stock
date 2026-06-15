<?php
// modules/approvisionnements/views/dons/formulaire.php
// Variables : $produits
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">Nouvelle entrée de don</h1>
        <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/appros/dons/creer" class="carte p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="label">Numéro *</label>
                <input type="text" name="numero" class="input" required value="DON-<?= date('Ymd') ?>-">
            </div>
            <div>
                <label class="label">Donateur</label>
                <input type="text" name="donateur" class="input">
            </div>
            <div>
                <label class="label">Date *</label>
                <input type="date" name="date_entree" class="input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="md:col-span-3">
                <label class="label">Notes</label>
                <textarea name="notes" class="input" rows="2"></textarea>
            </div>
        </div>

        <!-- Lignes -->
        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-800">Articles donnés</h3>
                <button type="button" onclick="ajouterLigneDon()" class="btn btn-secondary text-sm">
                    <i class="fa-solid fa-plus"></i> Ajouter
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">Valeur estimée</th>
                        <th>Date péremption</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-dons"></tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2 border-t pt-4">
            <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Enregistrer le don
            </button>
        </div>
    </form>
</div>

<?php
$produitsJson = json_encode(
    \Database::query("SELECT id, code, designation FROM produits WHERE actif = true ORDER BY designation")->fetchAll()
);
?>
<script>
const PRODUITS = <?= $produitsJson ?>;
let compteur = 0;

function renderLigneDon() {
    const opts = PRODUITS.map(p => `<option value="${p.id}">${p.code} - ${p.designation}</option>`).join('');
    return `
    <tr>
        <td><select name="produit_id[]" class="input" required>
            <option value="">-- Produit --</option>${opts}
        </select></td>
        <td><input type="number" step="0.001" name="quantite[]" class="input text-right" min="0.001" required></td>
        <td><input type="number" step="0.01" name="valeur_estimee[]" class="input text-right" value="0"></td>
        <td><input type="date" name="date_peremption[]" class="input"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="btn btn-danger text-red-500 text-sm">
            <i class="fa-solid fa-xmark"></i></button></td>
    </tr>`;
}

function ajouterLigneDon() {
    document.getElementById('tbody-dons').insertAdjacentHTML('beforeend', renderLigneDon());
}

document.addEventListener('DOMContentLoaded', () => ajouterLigneDon());
</script>