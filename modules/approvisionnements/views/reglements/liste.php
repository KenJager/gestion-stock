<?php
// modules/approvisionnements/views/reglements/liste.php
// Variables : $reglements, $fournisseurs, $fournisseurId
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">Règlements fournisseurs</h1>
    </div>

    <!-- Filtre -->
    <div class="carte p-4">
        <form method="GET" action="<?= BASE_URL ?>/appros/reglements" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="label">Fournisseur</label>
                <select name="fournisseur_id" class="input">
                    <option value="">-- Tous --</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= $fournisseurId == $f['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['raison_sociale']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
        </form>
    </div>

    <div class="mt-4 table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Facture</th>
                    <th class="text-right">Montant</th>
                    <th>Mode</th>
                    <th>Utilisateur</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reglements)): ?>
                    <tr><td colspan="5" class="text-center text-gray-400 py-12">
                        <?= $fournisseurId ? 'Aucun règlement pour ce fournisseur' : 'Sélectionnez un fournisseur' ?>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($reglements as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['date_reglement'])) ?></td>
                            <td class="font-mono text-xs"><?= htmlspecialchars($r['facture_numero']) ?></td>
                            <td class="text-right font-mono font-semibold text-green-600">
                                <?= number_format((float)$r['montant'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td><?= htmlspecialchars($r['mode_paiement']) ?></td>
                            <td class="text-gray-500"><?= htmlspecialchars($r['utilisateur'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>