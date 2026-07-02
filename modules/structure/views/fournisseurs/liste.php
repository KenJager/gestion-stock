<?php
// modules/structure/views/fournisseurs/liste.php
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Fournisseurs</h1>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/fournisseurs/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouveau fournisseur
            </a>
        <?php endif; ?>
    </div>

    <div class="mt-4 table-container">
        <table class="table">
            <thead><tr><th>Code</th><th>Raison sociale</th><th>Contact</th><th>Téléphone</th><th>Ville</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($fournisseurs as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['code']) ?></td>
                    <td><?= htmlspecialchars($f['raison_sociale']) ?></td>
                    <td><?= htmlspecialchars($f['contact'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($f['telephone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($f['ville'] ?? '-') ?></td>
                    <td class="text-right space-x-2">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= BASE_URL ?>/structure/fournisseurs/<?= $f['id'] ?>" class="text-blue-600 btn btn-secondary"><i class="fa-regular fa-eye"></i></a>
                            <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                <a href="<?= BASE_URL ?>/structure/fournisseurs/editer/<?= $f['id'] ?>" class="text-green-600 btn btn-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
                            <?php endif; ?>
                            <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                <form method="POST"
                                        action="<?= BASE_URL ?>/structure/fournisseurs/supprimer/<?= $f['id'] ?>"
                                        class="inline"
                                        data-confirmer="Supprimer le fournisseur « <?= htmlspecialchars($f['code'] ?? '') ?> » ?">
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