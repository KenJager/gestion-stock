<?php
// modules/structure/views/versements/liste.php
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Versements en banque</h1>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/versements/creer" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouveau versement</a>
        <?php endif; ?>
    </div>
    <div class="table-container">
        <table class="table">
            <thead><tr><th>Référence</th><th>Banque</th><th>Date</th><th class="text-right">Montant</th><th>Motif</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($versements as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['reference'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($v['banque_nom']) ?></td>
                    <td><?= date('d/m/Y', strtotime($v['date_versement'])) ?></td>
                    <td class="text-right"><?= number_format($v['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($v['motif'] ?? '-') ?></td>
                    <td class="text-right space-x-2">
                        <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                            <a href="<?= BASE_URL ?>/structure/versements/editer/<?= $v['id'] ?>" class="text-green-600"><i class="fa-regular fa-pen-to-square"></i></a>
                        <?php endif; ?>
                        <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                            <button class="text-red-600 btn-supprimer" data-id="<?= $v['id'] ?>" data-nom="versement du <?= date('d/m/Y', strtotime($v['date_versement'])) ?>"><i class="fa-regular fa-trash-can"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>