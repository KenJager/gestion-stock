<?php
// modules/Approvisionnements/Views/commande_formulaire.php
$isEdit = $isEdit ?? false;
$commande = $commande ?? null;
?>

<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouvelle commande') ?></h1>
        <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">
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
        <form method="POST" action="<?= $isEdit ? BASE_URL . '/appros/commandes/' . $commande['id'] . '/modifier' : BASE_URL . '/appros/commandes/creer' ?>">
            
            <!-- Informations générales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label label-required" for="fournisseur_id">Fournisseur</label>
                    <select name="fournisseur_id" id="fournisseur_id" class="input" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($isEdit && $commande['fournisseur_id'] == $f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['raison_sociale']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="date_commande">Date commande</label>
                    <input type="date" name="date_commande" id="date_commande"
                           value="<?= $isEdit ? date('Y-m-d', strtotime($commande['date_commande'])) : date('Y-m-d') ?>" 
                           class="input">
                </div>
            </div>

            <div class="form-group mt-4">
                <label class="label" for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="input" placeholder="Informations complémentaires..."><?= $isEdit ? htmlspecialchars($commande['notes']) : '' ?></textarea>
            </div>

            <!-- Lignes de commande -->

            <div class="flex items-center justify-between mb-4 mt-4">
                <h2 class="font-semibold">Lignes de commande</h2>
                <button type="button" class="btn btn-secondary" onclick="ajouterLigne()">
                    <i class="fa-solid fa-plus"></i> Ajouter un produit
                </button>
            </div>

            <div id="lignes-container" class="space-y-2">
                <?php if ($isEdit && !empty($commande['lignes'])): ?>
                    <?php foreach ($commande['lignes'] as $index => $ligne): ?>
                        <div class="ligne-item flex flex-wrap items-center gap-2 p-3 bg-secondary rounded-md">
                            <div class="flex-1 min-w-[150px]">
                                <select name="lignes[<?= $index ?>][produit_id]" class="input w-full" required>
                                    <option value="">-- Produit --</option>
                                    <?php foreach ($produits as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($ligne['produit_id'] == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['designation']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="w-[100px]">
                                <input type="number" name="lignes[<?= $index ?>][quantite]" 
                                       value="<?= $ligne['quantite_commandee'] ?>" 
                                       step="0.001" class="input w-full" required placeholder="Qté">
                            </div>
                            <div class="w-[120px]">
                                <input type="number" name="lignes[<?= $index ?>][prix_unitaire]" 
                                       value="<?= $ligne['prix_unitaire_ht'] ?>" 
                                       step="0.01" class="input w-full" required placeholder="Prix HT">
                            </div>
                            <div class="w-[100px]">
                                <input type="number" name="lignes[<?= $index ?>][tva]" 
                                       value="<?= $ligne['tva'] ?? 19.25 ?>" 
                                       step="0.01" class="input w-full" placeholder="TVA %">
                            </div>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-danger" onclick="this.closest('.ligne-item').remove()">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Ligne vide par défaut -->
                    <div class="ligne-item flex flex-wrap items-center gap-2 p-3 bg-secondary rounded-md">
                        <div class="flex-1 min-w-[150px]">
                            <select name="lignes[0][produit_id]" class="input w-full" required>
                                <option value="">-- Produit --</option>
                                <?php foreach ($produits as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-[100px]">
                            <input type="number" name="lignes[0][quantite]" placeholder="Qté" step="0.001" class="input w-full" required>
                        </div>
                        <div class="w-[120px]">
                            <input type="number" name="lignes[0][prix_unitaire]" placeholder="Prix HT" step="0.01" class="input w-full" required>
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
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-6 pt-4 border-t">
                <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid <?= $isEdit ? 'fa-pen' : 'fa-floppy-disk' ?>"></i>
                    <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
let ligneIndex = <?= $isEdit ? count($commande['lignes'] ?? []) : 1 ?>;

function ajouterLigne() {
    const container = document.getElementById('lignes-container');
    const template = document.querySelector('.ligne-item');
    const newItem = template.cloneNode(true);
    
    // Réinitialiser les champs
    newItem.querySelectorAll('input').forEach(input => {
        if (input.type === 'number') {
            input.value = '';
        }
        if (input.type === 'text') {
            input.value = '';
        }
        if (input.type === 'date') {
            input.value = '';
        }
    });
    newItem.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });
    
    // Mettre à jour les names avec l'index
    newItem.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${ligneIndex}]`);
    });
    
    container.appendChild(newItem);
    ligneIndex++;
}
</script>