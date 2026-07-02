<?php
// modules/Utilisateurs/views/groupe_liste.php
// Variables : $groupes, $succes, $erreur
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Groupes d'utilisateurs</h1>
    <?php if (\Auth::peut('USERS_CREER')): ?>
        <a href="<?= BASE_URL ?>/groupes/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouveau groupe
        </a>
    <?php endif; ?>
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

<div class="carte">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Utilisateurs</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groupes)): ?>
                    <tr>
                        <td colspan="4" class="text-center" style="color:#71717a; padding:24px;">
                            Aucun groupe enregistré.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($groupes as $g): ?>
                    <tr>
                        <td style="font-weight:500;"><?= htmlspecialchars($g['nom']) ?></td>
                        <td style="color:#71717a;"><?= htmlspecialchars($g['description'] ?? '—') ?></td>
                        <td><span class="badge badge-valide"><?= (int) $g['nb_utilisateurs'] ?></span></td>
                        <td class="text-right" style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/groupes/<?= $g['id'] ?>/droits"
                               class="btn btn-outline" title="Gérer les droits">
                                <i class="fa-solid fa-shield-halved"></i> Droits
                            </a>
                            <?php if (\Auth::peut('USERS_MODIFIER')): ?>
                                <a href="<?= BASE_URL ?>/groupes/<?= $g['id'] ?>/modifier"
                                   class="btn btn-secondary" title="Modifier">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (\Auth::peut('USERS_SUPPRIMER')): ?>
                                <?php
                                $nbU = (int) $g['nb_utilisateurs'];
                                $confirmMsg = $nbU > 0
                                    ? "Supprimer ce groupe et ses {$nbU} utilisateur(s) associé(s) ? Ils seront archivés en cascade dans la corbeille."
                                    : 'Supprimer ce groupe ?';
                                ?>
                                <form action="<?= BASE_URL ?>/groupes/<?= $g['id'] ?>/supprimer"
                                      method="POST" style="display:inline;"
                                      onsubmit="return confirm('<?= addslashes($confirmMsg) ?>');">
                                    <button type="submit" class="btn btn-danger" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
