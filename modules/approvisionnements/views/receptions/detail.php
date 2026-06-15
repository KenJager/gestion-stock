<?php
// modules/approvisionnements/views/receptions/detail.php
// Variables : $reception, $lignes
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Bon de réception <?= htmlspecialchars($reception['numero']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                Commande : <strong><?= htmlspecialchars($reception['commande_numero']) ?></strong> —
                Fournisseur : <strong><?= htmlspecialchars($reception['fournisseur_nom']) ?></strong>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/pdf" target="_blank" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="carte p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-500">Date réception</p>
                <p class="font-medium"><?= date('d/m/Y', strtotime($reception['date_reception'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Statut</p>
                <span class="badge badge-success"><?= htmlspecialchars($reception['statut']) ?></span>
            </div>
        </div>
    </div>

    <div class="carte p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Articles reçus</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th class="text-right">Quantité reçue</th>
                    <th class="text-right">PU HT</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td class="font-mono text-xs"><?= htmlspecialchars($l['code']) ?></td>
                        <td><?= htmlspecialchars($l['designation']) ?> <span class="text-gray-400 text-xs">(<?= $l['unite'] ?>)</span></td>
                        <td class="text-right font-mono"><?= number_format((float)$l['quantite_recue'], 2, ',', ' ') ?></td>
                        <td class="text-right font-mono"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                        <td class="text-right font-mono font-semibold">
                            <?= number_format((float)$l['quantite_recue'] * (float)$l['prix_unitaire_ht'], 0, ',', ' ') ?> FCFA
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>