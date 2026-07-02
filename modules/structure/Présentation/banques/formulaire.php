<?php
// modules/structure/views/banques/formulaire.php
$isEdit = $banque !== null;
$action = $isEdit ? "modifier/{$banque['id']}" : "";
?>
<div class="carte p-6 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold mb-4"><?= htmlspecialchars($titre) ?></h1>
    <form method="POST" action="<?= BASE_URL ?>/structure/banques/<?= $action ?>">
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group"><label class="label label-required">Code</label><input type="text" name="code" class="input" value="<?= htmlspecialchars($banque['code'] ?? '') ?>" required></div>
            <div class="form-group"><label class="label label-required">Nom</label><input type="text" name="nom" class="input" value="<?= htmlspecialchars($banque['nom'] ?? '') ?>" required></div>
            <div class="form-group"><label class="label">Numéro de compte</label><input type="text" name="numero_compte" class="input" value="<?= htmlspecialchars($banque['numero_compte'] ?? '') ?>"></div>
            <div class="form-group"><label class="label">Téléphone</label><input type="text" name="telephone" class="input" value="<?= htmlspecialchars($banque['telephone'] ?? '') ?>"></div>
            <div class="form-group col-span-2"><label class="label">Adresse</label><textarea name="adresse" class="input" rows="2"><?= htmlspecialchars($banque['adresse'] ?? '') ?></textarea></div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/structure/banques" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button></div>
    </form>
</div>