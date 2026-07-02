<?php
// modules/structure/views/banques/liste.php
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Banques</h1>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/banques/creer" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle banque</a>
        <?php endif; ?>
    </div>
    <div class="table-container mt-4">
        <table class="table">
            <thead><tr><th>Code</th><th>Nom</th><th>Numéro de compte</th><th>Téléphone</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($banques as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['code']) ?></td>
                    <td><?= htmlspecialchars($b['nom']) ?></td>
                    <td><?= htmlspecialchars($b['numero_compte'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($b['telephone'] ?? '-') ?></td>
                    <td class="text-right space-x-2">
                        <div class="flex items-center justify-end gap-2">
                            <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                <a href="<?= BASE_URL ?>/structure/banques/editer/<?= $b['id'] ?>" class="text-green-600 btn btn-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
                            <?php endif; ?>
                            <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                <form method="POST"
                                        action="<?= BASE_URL ?>/structure/banques/supprimer/<?= $b['id'] ?>"
                                        class="inline"
                                        data-confirmer="Supprimer la banque « <?= htmlspecialchars($b['nom'] ?? '') ?> » ?">
                                    <button type="submit" class="btn btn-danger text-red-500 hover:text-red-700 hover:bg-red-50"
                                            title="Supprimer">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>