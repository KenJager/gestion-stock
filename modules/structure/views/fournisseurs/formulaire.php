<?php
// modules/structure/views/fournisseurs/formulaire.php
$isEdit = $fournisseur !== null;
$action = $isEdit ? "modifier/{$fournisseur['id']}" : "";
?>
<div class="carte p-6 max-w-3xl mx-auto">
    <h1 class="text-xl font-semibold mb-4"><?= htmlspecialchars($titre) ?></h1>
    <form method="POST" action="<?= BASE_URL ?>/structure/fournisseurs/<?= $action ?>">
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group"><label class="label label-required">Code</label><input type="text" name="code" class="input" value="<?= htmlspecialchars($fournisseur['code'] ?? '') ?>" required></div>
            <div class="form-group"><label class="label label-required">Raison sociale</label><input type="text" name="raison_sociale" class="input" value="<?= htmlspecialchars($fournisseur['raison_sociale'] ?? '') ?>" required></div>
            <div class="form-group"><label class="label">Contact</label><input type="text" name="contact" class="input" value="<?= htmlspecialchars($fournisseur['contact'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Téléphone</label><input type="text" name="telephone" class="input" value="<?= htmlspecialchars($fournisseur['telephone'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Email</label><input type="email" name="email" class="input" value="<?= htmlspecialchars($fournisseur['email'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Adresse</label><input type="text" name="adresse" class="input" value="<?= htmlspecialchars($fournisseur['adresse'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Ville</label><input type="text" name="ville" class="input" value="<?= htmlspecialchars($fournisseur['ville'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Pays</label><input type="text" name="pays" class="input" value="<?= htmlspecialchars($fournisseur['pays'] ?? 'Cameroun') ?>"></div>
            <div class="form-group"><label class="label flex items-center gap-2"><input type="checkbox" name="actif" value="1" <?= (($fournisseur['actif'] ?? true) != false) ? 'checked' : '' ?>> Actif</label></div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/structure/fournisseurs" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>