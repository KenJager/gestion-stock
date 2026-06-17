<?php
// modules/Approvisionnements/Views/commande_formulaire.php
// Variables disponibles : $titre, $fournisseurs, $produits, $commande (si édition), $isEdit (booléen)
$isEdit = $isEdit ?? false;
$commande = $commande ?? null;
?>

<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouvelle commande') ?></h1>
        <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="carte p-4">
        <form method="POST" action="<?= $isEdit ? BASE_URL . '/appros/commandes/' . $commande['id'] . '/modifier' : BASE_URL . '/appros/commandes/creer' ?>">
            
            <!-- Fournisseur -->
            <div class="form-group">
                <label class="label label-required">Fournisseur</label>
                <select name="fournisseur_id" class="input" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($isEdit && $commande['fournisseur_id'] == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['raison_sociale']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Date -->
            <div class="form-group">
                <label class="label">Date commande</label>
                <input type="date" name="date_commande" 
                       value="<?= $isEdit ? date('Y-m-d', strtotime($commande['date_commande'])) : date('Y-m-d') ?>" 
                       class="input">
            </div>
            
            <!-- Notes -->
            <div class="form-group">
                <label class="label">Notes</label>
                <textarea name="notes" rows="3" class="input" placeholder="Informations complémentaires..."><?= $isEdit ? htmlspecialchars($commande['notes']) : '' ?></textarea>
            </div>

            <!-- Lignes de commande -->
            <hr class="my-4">
            <h2 class="font-semibold mb-4">Lignes de commande</h2>
            
            <div id="lignes-container">
                <?php if ($isEdit && !empty($commande['lignes'])): ?>
                    <?php foreach ($commande['lignes'] as $index => $ligne): ?>
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
                                   value="<?= $ligne['quantite_commandee'] ?>" 
                                   step="0.001" class="input" required>
                            <input type="number" name="lignes[<?= $index ?>][prix_unitaire]" 
                                   value="<?= $ligne['prix_unitaire_ht'] ?>" 
                                   step="0.01" class="input" required>
                            <input type="number" name="lignes[<?= $index ?>][tva]" 
                                   value="<?= $ligne['tva'] ?? 19.25 ?>" 
                                   step="0.01" class="input">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Ligne vide par défaut -->
                    <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
                        <select name="lignes[0][produit_id]" class="input" required>
                            <option value="">-- Produit --</option>
                            <?php foreach ($produits as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="lignes[0][quantite]" placeholder="Quantité" step="0.001" class="input" required>
                        <input type="number" name="lignes[0][prix_unitaire]" placeholder="Prix HT" step="0.01" class="input" required>
                        <input type="number" name="lignes[0][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input">
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
                <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
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
    const html = `
        <div class="ligne-item grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
            <select name="lignes[${ligneIndex}][produit_id]" class="input" required>
                <option value="">-- Produit --</option>
                <?php foreach ($produits as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="lignes[${ligneIndex}][quantite]" placeholder="Quantité" step="0.001" class="input" required>
            <input type="number" name="lignes[${ligneIndex}][prix_unitaire]" placeholder="Prix HT" step="0.01" class="input" required>
            <input type="number" name="lignes[${ligneIndex}][tva]" placeholder="TVA %" value="19.25" step="0.01" class="input">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.ligne-item').remove()">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    ligneIndex++;
}
</script>