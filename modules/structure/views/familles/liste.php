<?php
// modules/structure/views/familles/liste.php
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre) ?></h1>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/familles/creer" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle famille
            </a>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th>Code</th><th>Libellé</th><th>Description</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($familles as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['code']) ?></td>
                    <td><?= htmlspecialchars($f['libelle']) ?></td>
                    <td><?= htmlspecialchars($f['description'] ?? '') ?></td>
                    <td class="text-right space-x-2">
                        <a href="<?= BASE_URL ?>/structure/familles/editer/<?= $f['id'] ?>" class="text-blue-600"><i class="fa-regular fa-pen-to-square"></i></a>
                        <button class="text-red-600 btn-supprimer" data-id="<?= $f['id'] ?>" data-nom="<?= htmlspecialchars($f['libelle']) ?>"><i class="fa-regular fa-trash-can"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>