<?php
// modules/Utilisateurs/views/groupe_formulaire.php
// Variables : $groupe (array|null), $erreur

$estEdition = $groupe !== null;
$action = $estEdition
    ? BASE_URL . '/groupes/' . $groupe['id'] . '/modifier'
    : BASE_URL . '/groupes/creer';
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">
        <?= $estEdition ? 'Modifier le groupe' : 'Nouveau groupe' ?>
    </h1>
    <a href="<?= BASE_URL ?>/groupes" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($erreur) ?></span>
    </div>
<?php endif; ?>

<div class="carte p-6" style="max-width:600px;">
    <form action="<?= $action ?>" method="POST">

        <div class="form-group mb-4">
            <label for="nom" class="label label-required">Nom du groupe</label>
            <input type="text" id="nom" name="nom" class="input" required
                   value="<?= htmlspecialchars($groupe['nom'] ?? '') ?>">
        </div>

        <div class="form-group mb-4">
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" class="input" rows="3"><?= htmlspecialchars($groupe['description'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
            <a href="<?= BASE_URL ?>/groupes" class="btn btn-secondary">Annuler</a>
        </div>

    </form>
</div>
