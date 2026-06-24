<?php
// modules/Approvisionnements/Views/don_formulaire.php
$isEdit = $isEdit ?? false;
$don = $don ?? null;
?>

<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouveau don') ?></h1>
        <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">
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
        <form method="POST" action="<?= $isEdit ? BASE_URL . '/appros/dons/' . $don['id'] . '/modifier' : BASE_URL . '/appros/dons/creer' ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label" for="donateur">Donateur</label>
                    <input type="text" name="donateur" id="donateur" 
                           value="<?= $isEdit ? htmlspecialchars($don['donateur']) : '' ?>" 
                           class="input" placeholder="Nom du donateur">
                </div>

                <div class="form-group">
                    <label class="label" for="date_entree">Date</label>
                    <input type="date" name="date_entree" id="date_entree"
                           value="<?= $isEdit ? date('Y-m-d', strtotime($don['date_entree'])) : date('Y-m-d') ?>" 
                           class="input">
                </div>
            </div>

            <div class="form-group mt-4">
                <label class="label" for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="input" placeholder="Informations complémentaires..."><?= $isEdit ? htmlspecialchars($don['notes']) : '' ?></textarea>
            </div>

            <div class="flex items-center justify-between mb-4 mt-4">
                <h2 class="font-semibold">Produits reçus</h2>
                <button type="button" class="btn btn-secondary" onclick="ajouterLigne()">
                    <i class="fa-solid fa-plus"></i> Ajouter un produit
                </button>
            </div>

            <div id="lignes-container" class="space-y-2">
                <?php if ($isEdit && !empty($don['lignes'])): ?>
                    <?php foreach ($don['lignes'] as $index => $ligne): ?>
                        <div class="ligne-item flex flex-wrap items-center gap-4 p-3 bg-secondary rounded-md">
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
                                       value="<?= $ligne['quantite'] ?>" 
                                       step="0.001" class="input w-full" required placeholder="Qté">
                            </div>
                            <div class="w-[120px]">
                                <input type="number" name="lignes[<?= $index ?>][valeur_estimee]" 
                                       value="<?= $ligne['valeur_estimee'] ?? 0 ?>" 
                                       step="0.01" class="input w-full" placeholder="Valeur">
                            </div>
                            <div class="w-[130px]">
                                <input type="date" name="lignes[<?= $index ?>][date_peremption]" 
                                       value="<?= $ligne['date_peremption'] ?? '' ?>" 
                                       class="input w-full">
                            </div>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-danger" onclick="this.closest('.ligne-item').remove()">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ligne-item flex flex-wrap items-center gap-4 p-3 bg-secondary rounded-md">
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
                            <input type="number" name="lignes[0][valeur_estimee]" placeholder="Valeur" step="0.01" class="input w-full">
                        </div>
                        <div class="w-[130px]">
                            <input type="date" name="lignes[0][date_peremption]" class="input w-full">
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
                <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid <?= $isEdit ? 'fa-pen' : 'fa-floppy-disk' ?>"></i>
                    <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
let ligneIndex = <?= $isEdit ? count($don['lignes'] ?? []) : 1 ?>;

function ajouterLigne() {
    const container = document.getElementById('lignes-container');
    const template = document.querySelector('.ligne-item');
    const newItem = template.cloneNode(true);
    
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
    
    newItem.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${ligneIndex}]`);
    });
    
    container.appendChild(newItem);
    ligneIndex++;
}
</script>