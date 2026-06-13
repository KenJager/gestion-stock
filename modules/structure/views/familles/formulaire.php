<?php
// modules/structure/views/familles/formulaire.php
$famille = $famille ?? null;
$action = $famille ? "modifier/{$famille['id']}" : "";
$methode = $famille ? "POST" : "POST";
?>
<div class="carte p-6 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold mb-4"><?= htmlspecialchars($titre) ?></h1>
    <form method="POST" action="<?= BASE_URL ?>/structure/familles/<?= $action ?>">
        <div class="space-y-4">
            <div class="form-group">
                <label class="label label-required">Code</label>
                <input type="text" name="code" class="input" value="<?= htmlspecialchars($famille['code'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="label label-required">Libellé</label>
                <input type="text" name="libelle" class="input" value="<?= htmlspecialchars($famille['libelle'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="label">Description</label>
                <textarea name="description" class="input" rows="3"><?= htmlspecialchars($famille['description'] ?? '') ?></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <a href="<?= BASE_URL ?>/structure/familles" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </div>
    </form>
</div>