<?php
// modules/structure/views/familles/detail.php
// Variables : $famille
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Détail de la famille</h1>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/structure/familles/editer/<?= $famille['id'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
            <a href="<?= BASE_URL ?>/structure/familles" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="carte mt-4 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="label">Code</label>
                <p class="text-gray-900 font-mono"><?= htmlspecialchars($famille['code']) ?></p>
            </div>
            <div class="form-group">
                <label class="label">Libellé</label>
                <p class="text-gray-900"><?= htmlspecialchars($famille['libelle']) ?></p>
            </div>
            <div class="form-group col-span-2">
                <label class="label">Description</label>
                <p class="text-gray-700"><?= nl2br(htmlspecialchars($famille['description'] ?? '')) ?></p>
            </div>
            <div class="form-group">
                <label class="label">Date de création</label>
                <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($famille['date_creation'] ?? 'now')) ?></p>
            </div>
        </div>
    </div>
</div>