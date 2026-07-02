<?php
// modules/structure/views/versements/formulaire.php
$isEdit = $versement !== null;
$action = $isEdit ? "modifier/{$versement['id']}" : "";
?>
<div class="carte p-6 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold mb-4"><?= htmlspecialchars($titre) ?></h1>
    <form method="POST" action="<?= BASE_URL ?>/structure/versements/<?= $action ?>">
        <div class="space-y-4">
            <div class="form-group"><label class="label label-required">Banque</label><select name="banque_id" class="input" required><option value="">-- Sélectionner --</option><?php foreach ($banques as $b): ?><option value="<?= $b['id'] ?>" <?= (($versement['banque_id'] ?? '') == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nom']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="label label-required">Montant</label><input type="number" step="any" name="montant" class="input" value="<?= $versement['montant'] ?? '' ?>" required></div>
            <div class="form-group"><label class="label">Date du versement</label><input type="date" name="date_versement" class="input" value="<?= $versement['date_versement'] ?? date('Y-m-d') ?>"></div>
            <div class="form-group"><label class="label">Référence</label><input type="text" name="reference" class="input" value="<?= htmlspecialchars($versement['reference'] ?? '') ?>" placeholder="Facultatif"></div>
            <div class="form-group"><label class="label">Motif</label><textarea name="motif" class="input" rows="3" placeholder="Facultatif"><?= htmlspecialchars($versement['motif'] ?? '') ?></textarea></div>
        </div>
        <div class="flex justify-end gap-2 mt-6"><a href="<?= BASE_URL ?>/structure/versements" class="btn-secondary">Annuler</a><button type="submit" class="btn-primary">Enregistrer</button></div>
    </form>
</div>