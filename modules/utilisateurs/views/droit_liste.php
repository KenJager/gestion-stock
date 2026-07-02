<?php
// modules/Utilisateurs/views/droit_liste.php
// Variable : $droitsParModule (array module => [droits])
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Droits d'accès</h1>
</div>

<p style="color:#71717a; font-size:14px; margin-bottom:16px;">
    Liste de tous les droits disponibles dans le système, regroupés par module.
    Pour affecter ces droits à un groupe, rendez-vous dans
    <a href="<?= BASE_URL ?>/groupes" style="color:#2563eb;">Groupes d'utilisateurs</a>.
</p>

<div class="grid grid-cols-2">
    <?php foreach ($droitsParModule as $module => $droits): ?>
        <div class="carte p-4 mb-4">
            <h2 style="font-size:14px; font-weight:600; margin-bottom:12px; text-transform:uppercase; color:#3f3f46;">
                <?= htmlspecialchars($module) ?>
            </h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($droits as $droit): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($droit['code']) ?></code></td>
                                <td style="color:#71717a;"><?= htmlspecialchars($droit['description'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
