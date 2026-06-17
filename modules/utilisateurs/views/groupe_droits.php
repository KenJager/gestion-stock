<?php
// modules/Utilisateurs/views/groupe_droits.php
// Variables : $groupe, $droitsParModule (array module => [droits]), $droitsAffectes (ids), $succes
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1 style="font-size:20px; font-weight:600;">Droits du groupe</h1>
        <p style="color:#71717a; font-size:14px;"><?= htmlspecialchars($groupe['nom']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/groupes" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($succes)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($succes) ?></span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/groupes/<?= $groupe['id'] ?>/droits" method="POST">

    <div class="grid grid-cols-2">
        <?php foreach ($droitsParModule as $module => $droits): ?>
            <div class="carte p-4 mb-4">
                <h2 style="font-size:14px; font-weight:600; margin-bottom:12px; text-transform:uppercase; color:#3f3f46;">
                    <?= htmlspecialchars($module) ?>
                </h2>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php foreach ($droits as $droit): ?>
                        <label style="display:flex; align-items:flex-start; gap:8px; font-size:14px; cursor:pointer;">
                            <input type="checkbox" name="droits[]" value="<?= $droit['id'] ?>"
                                   <?= in_array($droit['id'], $droitsAffectes) ? 'checked' : '' ?>
                                   style="margin-top:3px;">
                            <span>
                                <strong><?= htmlspecialchars($droit['code']) ?></strong><br>
                                <span style="color:#71717a; font-size:12px;"><?= htmlspecialchars($droit['description'] ?? '') ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer les droits
        </button>
        <a href="<?= BASE_URL ?>/groupes" class="btn btn-secondary">Annuler</a>
    </div>

</form>
