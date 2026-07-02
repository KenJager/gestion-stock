<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Règlements fournisseurs') ?></h1>
        <?php if (\Auth::peut('APPRO_MODIFIER')): ?>
        <a href="<?= BASE_URL ?>/appros/reglements/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouveau règlement
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($reglements)): ?>
        <div class="carte p-4 text-center text-gray-500 mt-4">
            Aucun règlement enregistré.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Facture</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th class="text-right">Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reglements as $reglement): ?>
                        <tr>
                            <td><?= htmlspecialchars($reglement['facture_numero']) ?></td>
                            <td><?= htmlspecialchars($reglement['fournisseur_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($reglement['date_reglement'])) ?></td>
                            <td class="text-right"><?= number_format($reglement['montant'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= htmlspecialchars($reglement['mode_paiement'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($reglement['reference'] ?? '-') ?></td>
                            <td class="text-center whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/appros/reglements/<?= $reglement['id'] ?>/recu" class="btn btn-primary" target="_blank">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                                <?php if (\Auth::peut('APPRO_SUPPRIMER')): ?>
                                <form method="POST" action="<?= BASE_URL ?>/appros/reglements/<?= $reglement['id'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce règlement ?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>