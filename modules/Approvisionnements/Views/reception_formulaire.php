<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouvelle réception') ?></h1>
        <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="carte p-4">
        <form method="POST" action="<?= BASE_URL ?>/appros/receptions/creer" id="receptionForm">
            
            <div class="form-group">
                <label class="label label-required">Commande à réceptionner</label>
                <select name="bon_commande_id" class="input" id="commandeSelect" required>
                    <option value="">-- Sélectionner une commande --</option>
                    <?php foreach ($commandes as $commande): ?>
                        <option value="<?= $commande['id'] ?>">
                            <?= htmlspecialchars($commande['numero']) ?> - <?= htmlspecialchars($commande['fournisseur_nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="label">Date réception</label>
                <input type="date" name="date_reception" value="<?= date('Y-m-d') ?>" class="input">
            </div>
            
            <div class="form-group">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input" placeholder="Informations complémentaires..."></textarea>
            </div>

            <hr class="my-4">
            <h2 class="font-semibold mb-4">Produits reçus</h2>
            
            <div id="lignes-container">
                <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
                    <select name="lignes[0][produit_id]" class="input" required>
                        <option value="">-- Produit --</option>
                    </select>
                    <input type="number" name="lignes[0][quantite_recue]" placeholder="Quantité reçue" step="0.001" class="input" required>
                    <input type="number" name="lignes[0][prix_unitaire_ht]" placeholder="PU HT" step="0.01" class="input" required>
                    <input type="number" name="lignes[0][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>

            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="ajouterLigne()">
                <i class="fa-solid fa-plus"></i> Ajouter un produit
            </button>

            <div class="flex justify-end gap-2 mt-4">
                <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>

        </form>
    </div>
</div>

<script>
let ligneIndex = 1;

function ajouterLigne() {
    const container = document.getElementById('lignes-container');
    const html = `
        <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
            <select name="lignes[${ligneIndex}][produit_id]" class="input" required>
                <option value="">-- Produit --</option>
            </select>
            <input type="number" name="lignes[${ligneIndex}][quantite_recue]" placeholder="Quantité reçue" step="0.001" class="input" required>
            <input type="number" name="lignes[${ligneIndex}][prix_unitaire_ht]" placeholder="PU HT" step="0.01" class="input" required>
            <input type="number" name="lignes[${ligneIndex}][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    ligneIndex++;
}

// Charger les produits d'une commande sélectionnée
document.getElementById('commandeSelect').addEventListener('change', function() {
    const commandeId = this.value;
    if (!commandeId) return;
    
    fetch('<?= BASE_URL ?>/appros/receptions/get-commande/' + commandeId)
        .then(response => response.json())
        .then(data => {
            // Remplir les produits
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