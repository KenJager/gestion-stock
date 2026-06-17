<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouveau don') ?></h1>
        <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="carte p-4">
        <form method="POST" action="<?= $isEdit ? BASE_URL . '/appros/dons/' . $don['id'] . '/modifier' : BASE_URL . '/appros/dons/creer' ?>">
            
            <div class="form-group">
                <label class="label">Donateur</label>
                <input type="text" name="donateur" value="<?= $isEdit ? htmlspecialchars($don['donateur']) : '' ?>" 
                       class="input" placeholder="Nom du donateur">
            </div>
            
            <div class="form-group">
                <label class="label">Date</label>
                <input type="date" name="date_entree" 
                       value="<?= $isEdit ? date('Y-m-d', strtotime($don['date_entree'])) : date('Y-m-d') ?>" 
                       class="input">
            </div>
            
            <div class="form-group">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input" placeholder="Informations complémentaires..."><?= $isEdit ? htmlspecialchars($don['notes']) : '' ?></textarea>
            </div>

            <hr class="my-4">
            <h2 class="font-semibold mb-4">Produits reçus</h2>
            
            <div id="lignes-container">
                <?php if ($isEdit && !empty($don['lignes'])): ?>
                    <?php foreach ($don['lignes'] as $index => $ligne): ?>
                        <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
                            <select name="lignes[<?= $index ?>][produit_id]" class="input" required>
                                <option value="">-- Produit --</option>
                                <?php foreach ($produits as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($ligne['produit_id'] == $p['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['designation']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="lignes[<?= $index ?>][quantite]" 
                                   value="<?= $ligne['quantite'] ?>" 
                                   step="0.001" class="input" required placeholder="Qté">
                            <input type="number" name="lignes[<?= $index ?>][valeur_estimee]" 
                                   value="<?= $ligne['valeur_estimee'] ?? 0 ?>" 
                                   step="0.01" class="input" placeholder="Valeur estimée">
                            <input type="date" name="lignes[<?= $index ?>][date_peremption]" 
                                   value="<?= $ligne['date_peremption'] ?? '' ?>" 
                                   class="input">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
                        <select name="lignes[0][produit_id]" class="input" required>
                            <option value="">-- Produit --</option>
                            <?php foreach ($produits as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="lignes[0][quantite]" placeholder="Quantité" step="0.001" class="input" required>
                        <input type="number" name="lignes[0][valeur_estimee]" placeholder="Valeur estimée" step="0.01" class="input">
                        <input type="date" name="lignes[0][date_peremption]" class="input">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="ajouterLigne()">
                <i class="fa-solid fa-plus"></i> Ajouter un produit
            </button>

            <div class="flex justify-end gap-2 mt-4">
                <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
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
    const html = `
        <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
            <select name="lignes[${ligneIndex}][produit_id]" class="input" required>
                <option value="">-- Produit --</option>
                <?php foreach ($produits as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="lignes[${ligneIndex}][quantite]" placeholder="Quantité" step="0.001" class="input" required>
            <input type="number" name="lignes[${ligneIndex}][valeur_estimee]" placeholder="Valeur estimée" step="0.01" class="input">
            <input type="date" name="lignes[${ligneIndex}][date_peremption]" class="input">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    ligneIndex++;
}
</script>