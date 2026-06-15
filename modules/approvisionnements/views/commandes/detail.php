<?php
// modules/approvisionnements/views/commandes/detail.php
// Variables : $commande, $lignes
$badgeStatut = [
    'BROUILLON'      => 'badge-secondary',
    'VALIDÉ'         => 'badge-info',
    'REÇU_PARTIEL'   => 'badge-warning',
    'REÇU_TOTAL'     => 'badge-success',
    'ANNULÉ'         => 'badge-danger',
][$commande['statut']] ?? 'badge-secondary';
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">
                Bon de commande <?= htmlspecialchars($commande['numero']) ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Fournisseur : <strong><?= htmlspecialchars($commande['fournisseur_nom']) ?></strong>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/pdf" target="_blank" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <?php if (Auth::peut('APPRO_MODIFIER') && $commande['statut'] === 'BROUILLON'): ?>
                <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/modifier" class="btn btn-secondary">
                    <i class="fa-regular fa-pen-to-square"></i> Modifier
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="carte p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Statut</p>
                <span class="badge <?= $badgeStatut ?>"><?= htmlspecialchars($commande['statut']) ?></span>
            </div>
            <div>
                <p class="text-xs text-gray-500">Date commande</p>
                <p class="font-medium"><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Livraison prévue</p>
                <p class="font-medium"><?= $commande['date_livraison_prevue'] ? date('d/m/Y', strtotime($commande['date_livraison_prevue'])) : '—' ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Créé le</p>
                <p class="font-medium"><?= date('d/m/Y H:i', strtotime($commande['date_creation'])) ?></p>
            </div>
        </div>
        <?php if (!empty($commande['notes'])): ?>
            <div class="mt-4 p-3 bg-gray-50 rounded text-sm text-gray-700">
                <strong>Notes :</strong> <?= nl2br(htmlspecialchars($commande['notes'])) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Totaux -->
    <div class="grid grid-cols-3 gap-4">
        <div class="carte p-4 text-center">
            <p class="text-xs text-gray-500">Total HT</p>
            <p class="text-xl font-bold text-gray-800 font-mono">
                <?= number_format((float)$commande['montant_ht'], 0, ',', ' ') ?> <span class="text-xs text-gray-400">FCFA</span>
            </p>
        </div>
        <div class="carte p-4 text-center">
            <p class="text-xs text-gray-500">TVA</p>
            <p class="text-xl font-bold text-gray-800 font-mono">
                <?= number_format((float)$commande['montant_tva'], 0, ',', ' ') ?> <span class="text-xs text-gray-400">FCFA</span>
            </p>
        </div>
        <div class="carte p-4 text-center bg-blue-50">
            <p class="text-xs text-gray-500">Total TTC</p>
            <p class="text-xl font-bold text-blue-700 font-mono">
                <?= number_format((float)$commande['montant_ttc'], 0, ',', ' ') ?> <span class="text-xs text-blue-400">FCFA</span>
            </p>
        </div>
    </div>

    <!-- Lignes -->
    <div class="carte p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Articles commandés</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th class="text-right">Quantité</th>
                    <th class="text-right">Reçue</th>
                    <th class="text-right">PU HT</th>
                    <th class="text-right">TVA %</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td class="font-mono text-xs"><?= htmlspecialchars($l['code']) ?></td>
                        <td><?= htmlspecialchars($l['designation']) ?> <span class="text-gray-400 text-xs">(<?= $l['unite'] ?>)</span></td>
                        <td class="text-right font-mono"><?= number_format((float)$l['quantite_commandee'], 2, ',', ' ') ?></td>
                        <td class="text-right font-mono text-green-600"><?= number_format((float)$l['quantite_recue'], 2, ',', ' ') ?></td>
                        <td class="text-right font-mono"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                        <td class="text-right"><?= $l['tva'] ?> %</td>
                        <td class="text-right font-mono font-semibold">
                            <?= number_format((float)$l['montant_ht'], 0, ',', ' ') ?> FCFA
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>