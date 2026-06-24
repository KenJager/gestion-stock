<?php
// modules/Utilisateurs/views/utilisateur_liste.php
// Variables : $utilisateurs, $succes, $erreur
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Utilisateurs</h1>
    <?php if (\Auth::peut('USERS_CREER')): ?>
        <a href="<?= BASE_URL ?>/utilisateurs/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvel utilisateur
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
                    <th>Nom complet</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>Groupe</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($utilisateurs)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="color:#71717a; padding:24px;">
                            Aucun utilisateur enregistré.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars(trim($u['nom'] . ' ' . ($u['prenom'] ?? ''))) ?></td>
                        <td><code><?= htmlspecialchars($u['login']) ?></code></td>
                        <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($u['groupe_nom'] ?? 'Aucun') ?></td>
                        <td>
                            <?php if ($u['actif']): ?>
                                <span class="badge badge-complet">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-annule">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#71717a; font-size:12px;">
                            <?= $u['derniere_connexion'] ? date('d/m/Y H:i', strtotime($u['derniere_connexion'])) : '—' ?>
                        </td>
                        <td class="text-right" style="white-space:nowrap;">
                            <?php if (\Auth::peut('USERS_MODIFIER')): ?>
                                <a href="<?= BASE_URL ?>/utilisateurs/<?= $u['id'] ?>/modifier"
                                   class="btn btn-secondary" title="Modifier">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (\Auth::peut('USERS_SUPPRIMER')): ?>
                                <form action="<?= BASE_URL ?>/utilisateurs/<?= $u['id'] ?>/supprimer"
                                      method="POST" style="display:inline;"
                                      onsubmit="return confirm('Supprimer cet utilisateur ?');">
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
