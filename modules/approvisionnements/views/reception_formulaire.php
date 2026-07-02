<?php
// modules/Approvisionnements/Views/reception_formulaire.php
?>

<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouvelle réception') ?></h1>
        <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="carte p-6">
        <form method="POST" action="<?= BASE_URL ?>/appros/receptions/creer" id="receptionForm">
            
            <div class="form-group">
                <label class="label label-required" for="commandeSelect">Commande à réceptionner</label>
                <select name="bon_commande_id" class="input w-full" id="commandeSelect" required>
                    <option value="">-- Sélectionner une commande --</option>
                    <?php foreach ($commandes as $commande): ?>
                        <option value="<?= $commande['id'] ?>">
                            <?= htmlspecialchars($commande['numero']) ?> - <?= htmlspecialchars($commande['fournisseur_nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-group">
                    <label class="label" for="date_reception">Date réception</label>
                    <input type="date" name="date_reception" id="date_reception" value="<?= date('Y-m-d') ?>" class="input">
                </div>

                <div class="form-group">
                    <label class="label" for="notes">Notes</label>
                    <input type="text" name="notes" id="notes" class="input" placeholder="Informations complémentaires...">
                </div>
            </div>


            <div class="flex items-center justify-between mb-4 mt-4">
                <h2 class="font-semibold">Produits reçus</h2>
                <button type="button" class="btn btn-secondary" onclick="ajouterLigne()">
                    <i class="fa-solid fa-plus"></i> Ajouter un produit
                </button>
            </div>

            <div id="lignes-container" class="space-y-2">
                <div class="ligne-item flex flex-wrap items-center gap-4 p-3 bg-secondary rounded-md">
                    <div class="flex-1 min-w-[200px]">
                        <select name="lignes[0][produit_id]" class="input w-full" required>
                            <option value="">-- Produit --</option>
                        </select>
                    </div>
                    <div class="w-[100px]">
                        <input type="number" name="lignes[0][quantite_recue]" placeholder="Qté" step="0.001" class="input w-full" required>
                    </div>
                    <div class="w-[120px]">
                        <input type="number" name="lignes[0][prix_unitaire_ht]" placeholder="PU HT" step="0.01" class="input w-full" required>
                    </div>
                    <div class="w-[100px]">
                        <input type="number" name="lignes[0][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input w-full">
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-danger" onclick="this.closest('.ligne-item').remove()">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-6 pt-4 border-t">
                <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                </button>
            </div>

        </form>
    </div>
</div>

<script>
let ligneIndex = 1;

function ajouterLigne() {
    const container = document.getElementById('lignes-container');
    const html = `
        <div class="ligne-item flex flex-wrap items-center gap-4 p-3 bg-secondary rounded-md">
            <div class="flex-1 min-w-[150px]">
                <select name="lignes[${ligneIndex}][produit_id]" class="input w-full" required>
                    <option value="">-- Produit --</option>
                </select>
            </div>
            <div class="w-[100px]">
                <input type="number" name="lignes[${ligneIndex}][quantite_recue]" placeholder="Qté" step="0.001" class="input w-full" required>
            </div>
            <div class="w-[120px]">
                <input type="number" name="lignes[${ligneIndex}][prix_unitaire_ht]" placeholder="PU HT" step="0.01" class="input w-full" required>
            </div>
            <div class="w-[100px]">
                <input type="number" name="lignes[${ligneIndex}][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input w-full">
            </div>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-danger" onclick="this.closest('.ligne-item').remove()">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    ligneIndex++;
}

// Charger les produits d'une commande sélectionnée
document.getElementById('commandeSelect')?.addEventListener('change', function() {
    const commandeId = this.value;
    if (!commandeId) return;
    
    fetch('<?= BASE_URL ?>/appros/receptions/get-commande/' + commandeId)
        .then(response => response.json())
        .then(data => {
            const selects = document.querySelectorAll('select[name$="[produit_id]"]');
            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">-- Produit --</option>';
                data.lignes.forEach(ligne => {
                    const option = document.createElement('option');
                    option.value = ligne.produit_id;
                    option.textContent = ligne.designation + ' (Commandé: ' + ligne.quantite_commandee + ')';
                    if (ligne.produit_id == currentValue) option.selected = true;
                    select.appendChild(option);
                });
            });
        })
        .catch(error => console.error('Erreur:', error));
});
</script>