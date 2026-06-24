<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Factures fournisseurs') ?></h1>
        <a href="<?= BASE_URL ?>/appros/factures/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle facture
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($factures)): ?>
        <div class="carte p-4 text-center text-gray-500 mt-4">
            Aucune facture enregistrée.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th class="text-right">Montant TTC</th>
                            <th class="text-right">Règlé</th>
                            <th class="text-right">Solde</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $facture): ?>
                        <tr>
                            <td><?= htmlspecialchars($facture['numero']) ?></td>
                            <td><?= htmlspecialchars($facture['fournisseur_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></td>
                            <td class="text-right"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($facture['montant_regle'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                            <td class="text-right"><?= number_format($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                            <td>
                                <?php
                                $badgeClass = match($facture['statut']) {
                                    'NON_REGLÉE' => 'badge-annule',
                                    'PARTIELLEMENT_REGLÉE' => 'badge-partiel',
                                    'REGLÉE' => 'badge-complet',
                                    default => 'badge-brouillon'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($facture['statut']) ?></span>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>" class="btn btn-outline">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($facture['statut'] !== 'REGLÉE'): ?>
                                    <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/modifier" class="btn btn-secondary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/pdf" class="btn btn-primary" target="_blank">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <?php if ($facture['statut'] !== 'REGLÉE'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/supprimer" style="display:inline;">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cette facture ?')">
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