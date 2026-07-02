<?php
// modules/Utilisateurs/views/utilisateur_formulaire.php
// Variables : $utilisateur (array|null), $groupes, $erreur

$estEdition = $utilisateur !== null;
$action = $estEdition
    ? BASE_URL . '/utilisateurs/' . $utilisateur['id'] . '/modifier'
    : BASE_URL . '/utilisateurs/creer';
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">
        <?= $estEdition ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?>
    </h1>
    <a href="<?= BASE_URL ?>/utilisateurs" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($erreur) ?></span>
    </div>
<?php endif; ?>

<div class="carte p-6">
    <form action="<?= $action ?>" method="POST">

        <div class="grid grid-cols-2 mb-4">
            <div class="form-group">
                <label for="nom" class="label label-required">Nom</label>
                <input type="text" id="nom" name="nom" class="input" required
                       value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="prenom" class="label">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="input"
                       value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
            </div>
        </div>

        <div class="grid grid-cols-2 mb-4">
            <div class="form-group">
                <label for="login" class="label label-required">Login</label>
                <input type="text" id="login" name="login" class="input" required
                       value="<?= htmlspecialchars($utilisateur['login'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email" class="label">Email</label>
                <input type="email" id="email" name="email" class="input"
                       value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>">
            </div>
        </div>

        <div class="grid grid-cols-2 mb-4">
            <div class="form-group">
                <label for="groupe_id" class="label label-required">Groupe</label>
                <select id="groupe_id" name="groupe_id" class="input" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($groupes as $g): ?>
                        <option value="<?= $g['id'] ?>"
                            <?= (isset($utilisateur['groupe_id']) && (int)$utilisateur['groupe_id'] === (int)$g['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="mot_de_passe" class="label <?= $estEdition ? '' : 'label-required' ?>">
                    Mot de passe <?= $estEdition ? '(laisser vide pour ne pas changer)' : '' ?>
                </label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" class="input"
                       <?= $estEdition ? '' : 'required' ?>
                       placeholder="<?= $estEdition ? '••••••••' : '' ?>">
            </div>
        </div>

        <?php if ($estEdition): ?>
            <div class="form-group mb-4">
                <label class="label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="actif" value="1"
                           <?= !empty($utilisateur['actif']) ? 'checked' : '' ?>>
                    Compte actif
                </label>
            </div>
        <?php endif; ?>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
            <a href="<?= BASE_URL ?>/utilisateurs" class="btn btn-secondary">Annuler</a>
        </div>

    </form>
</div>
