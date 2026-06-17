<div class="space-y-4">
    <div class="flex justify-between items-center flex-wrap gap-2">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Détail facture') ?></h1>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/appros/factures/<?= $facture['id'] ?>/pdf" class="btn btn-primary btn-sm" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/reglements/creer?facture_id=<?= $facture['id'] ?>" class="btn btn-success btn-sm">
                <i class="fa-solid fa-money-bill-wave"></i> Régler
            </a>
            <a href="<?= BASE_URL ?>/appros/factures" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Informations générales -->
    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">N° Facture</span>
                <p class="font-semibold"><?= htmlspecialchars($facture['numero']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Fournisseur</span>
                <p><?= htmlspecialchars($facture['fournisseur_nom']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Réception</span>
                <p><?= htmlspecialchars($facture['reception_numero'] ?? '-') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date facture</span>
                <p><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Échéance</span>
                <p><?= $facture['date_echeance'] ? date('d/m/Y', strtotime($facture['date_echeance'])) : '-' ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Statut</span>
                <?php
                $badgeClass = match($facture['statut']) {
                    'NON_REGLÉE' => 'badge-annule',
                    'PARTIELLEMENT_REGLÉE' => 'badge-partiel',
                    'REGLÉE' => 'badge-complet',
                    default => 'badge-brouillon'
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($facture['statut']) ?></span>
            </div>
        </div>
    </div>

    <!-- Montants -->
    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <span class="text-sm text-gray-500">Montant HT</span>
                <p class="text-lg font-semibold"><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> FCFA</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">TVA</span>
                <p class="text-lg font-semibold"><?= number_format($facture['montant_tva'], 0, ',', ' ') ?> FCFA</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Total TTC</span>
                <p class="text-lg font-semibold"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> FCFA</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Solde</span>
                <p class="text-lg font-semibold <?= ($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0)) > 0 ? 'text-red-600' : 'text-green-600' ?>">
                    <?= number_format($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0), 0, ',', ' ') ?> FCFA
                </p>
            </div>
        </div>
    </div>

    <!-- Historique des règlements -->
    <div class="carte">
        <div class="p-4 border-b border-border flex justify-between items-center">
            <h2 class="font-semibold">Historique des règlements</h2>
            <a href="<?= BASE_URL ?>/appros/reglements/creer?facture_id=<?= $facture['id'] ?>" class="btn btn-success btn-sm">
                <i class="fa-solid fa-plus"></i> Nouveau règlement
            </a>
        </div>
        
        <?php if (empty($facture['reglements'])): ?>
            <div class="p-4 text-center text-gray-500">
                Aucun règlement enregistré.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facture['reglements'] as $reglement): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($reglement['date_reglement'])) ?></td>
                            <td class="text-right"><?= number_format($reglement['montant'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= htmlspecialchars($reglement['mode_paiement'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($reglement['reference'] ?? '-') ?></td>
                            <td class="text-center whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/appros/reglements/<?= $reglement['id'] ?>/recu" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/appros/reglements/<?= $reglement['id'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce règlement ?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="1" class="text-right font-semibold">Total réglé :</td>
                            <td class="text-right font-semibold"><?= number_format($facture['montant_regle'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notes -->
    <?php if (!empty($facture['notes'])): ?>
        <div class="carte p-4">
            <span class="text-sm text-gray-500">Notes</span>
            <p><?= nl2br(htmlspecialchars($facture['notes'])) ?></p>
        </div>
    <?php endif; ?>
</div>