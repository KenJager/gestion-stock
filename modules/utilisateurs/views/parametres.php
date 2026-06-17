<?php
// modules/Utilisateurs/views/parametres.php
// Variables : $utilisateur, $succes, $erreur
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Mes paramètres</h1>
</div>

<?php if (!empty($succes)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($succes) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($erreur) ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2">

    <!-- Informations personnelles -->
    <div class="carte p-6">
        <h2 style="font-size:14px; font-weight:600; margin-bottom:16px; text-transform:uppercase; color:#3f3f46;">
            Informations personnelles
        </h2>
        <form action="<?= BASE_URL ?>/parametres" method="POST">

            <div class="form-group mb-4">
                <label for="nom" class="label label-required">Nom</label>
                <input type="text" id="nom" name="nom" class="input" required
                       value="<?= htmlspecialchars($utilisateur['nom']) ?>">
            </div>

            <div class="form-group mb-4">
                <label for="prenom" class="label">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="input"
                       value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
            </div>

            <div class="form-group mb-4">
                <label class="label">Login</label>
                <input type="text" class="input" value="<?= htmlspecialchars($utilisateur['login']) ?>" disabled>
                <span style="font-size:12px; color:#a1a1aa;">Le login ne peut pas être modifié ici.</span>
            </div>

            <div class="form-group mb-4">
                <label for="email" class="label">Email</label>
                <input type="email" id="email" name="email" class="input"
                       value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>">
            </div>

            <div class="form-group mb-4">
                <label class="label">Groupe</label>
                <input type="text" class="input" value="<?= htmlspecialchars(\Auth::utilisateurCourant()['groupe_nom'] ?? '') ?>" disabled>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
        </form>
    </div>

    <!-- Changer le mot de passe -->
    <div class="carte p-6">
        <h2 style="font-size:14px; font-weight:600; margin-bottom:16px; text-transform:uppercase; color:#3f3f46;">
            Changer le mot de passe
        </h2>
        <form action="<?= BASE_URL ?>/parametres" method="POST">
            <!-- Champs cachés pour conserver les infos personnelles déjà soumises -->
            <input type="hidden" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>">
            <input type="hidden" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>">

            <div class="form-group mb-4">
                <label for="ancien_mot_de_passe" class="label">Mot de passe actuel</label>
                <input type="password" id="ancien_mot_de_passe" name="ancien_mot_de_passe" class="input">
            </div>

            <div class="form-group mb-4">
                <label for="nouveau_mot_de_passe" class="label">Nouveau mot de passe</label>
                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="input">
            </div>

            <div class="form-group mb-4">
                <label for="confirmer_mot_de_passe" class="label">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="input">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-key"></i> Changer le mot de passe
            </button>
        </form>
    </div>

</div>
