<?php
// modules/approvisionnements/views/factures/detail.php
// Variables : $facture, $reglements
$badge = match($facture['statut']) {
    'REGLÉE'                 => 'badge-success',
    'PARTIELLEMENT_REGLÉE'   => 'badge-warning',
    default                  => 'badge-danger',
};
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Facture <?= htmlspecialchars($facture['numero']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Fournisseur : <strong><?= htmlspecialchars($facture['fournisseur_nom']) ?></strong></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/appros/factures" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/pdf" target="_blank" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <?php if (Auth::peut('APPRO_MODIFIER')): ?>
                <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/modifier" class="btn btn-secondary">
                    <i class="fa-regular fa-pen-to-square"></i> Modifier
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Infos -->
    <div class="carte p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Date facture</p>
                <p class="font-medium"><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Échéance</p>
                <p class="font-medium"><?= $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : '—' ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Statut</p>
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($facture['statut']) ?></span>
            </div>
        </div>
    </div>

    <!-- Totaux -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="carte p-4 text-center">
            <p class="text-xs text-gray-500">Montant HT</p>
            <p class="text-lg font-bold font-mono"><?= number_format((float)$facture['montant_ht'], 0, ',', ' ') ?> FCFA</p>
        </div>
        <div class="carte p-4 text-center">
            <p class="text-xs text-gray-500">TVA</p>
            <p class="text-lg font-bold font-mono"><?= number_format((float)$facture['montant_tva'], 0, ',', ' ') ?> FCFA</p>
        </div>
        <div class="carte p-4 text-center bg-blue-50">
            <p class="text-xs text-gray-500">TTC</p>
            <p class="text-lg font-bold font-mono text-blue-700"><?= number_format((float)$facture['montant_ttc'], 0, ',', ' ') ?> FCFA</p>
        </div>
        <div class="carte p-4 text-center <?= (float)$facture['solde'] > 0 ? 'bg-red-50' : 'bg-green-50' ?>">
            <p class="text-xs text-gray-500">Solde restant</p>
            <p class="text-lg font-bold font-mono <?= (float)$facture['solde'] > 0 ? 'text-red-700' : 'text-green-700' ?>">
                <?= number_format((float)$facture['solde'], 0, ',', ' ') ?> FCFA
            </p>
        </div>
    </div>

    <!-- Formulaire règlement -->
    <?php if ((float)$facture['solde'] > 0 && Auth::peut('APPRO_CREER')): ?>
        <div class="carte p-6 bg-blue-50">
            <h3 class="font-semibold text-gray-800 mb-3">Enregistrer un règlement</h3>
            <form method="POST" action="<?= BASE_URL ?>/appros/reglements/creer" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="facture_id" value="<?= $facture['id'] ?>">
                <div>
                    <label class="label">Date *</label>
                    <input type="date" name="date_reglement" class="input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div>
                    <label class="label">Montant *</label>
                    <input type="number" step="0.01" name="montant" class="input" max="<?= $facture['solde'] ?>" value="<?= $facture['solde'] ?>" required>
                </div>
                <div>
                    <label class="label">Mode de paiement</label>
                    <select name="mode_paiement" class="input">
                        <option value="ESPÈCES">Espèces</option>
                        <option value="VIREMENT">Virement</option>
                        <option value="CHÈQUE">Chèque</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fa-solid fa-money-bill-wave"></i> Régler
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Historique règlements -->
    <?php if (!empty($reglements)): ?>
        <div class="carte p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Historique des règlements</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Montant</th>
                        <th>Mode</th>
                        <th>Référence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reglements as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['date_reglement'])) ?></td>
                            <td class="text-right font-mono text-green-600"><?= number_format((float)$r['montant'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= htmlspecialchars($r['mode_paiement']) ?></td>
                            <td class="text-gray-500"><?= htmlspecialchars($r['reference'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>