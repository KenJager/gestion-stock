<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Commandes') ?></h1>
        <a href="<?= BASE_URL ?>/appros/commandes/creer" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($commandes)): ?>
        <div class="carte p-4 text-center text-gray-500">
            Aucune commande trouvée.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-right">Montant TTC</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?= htmlspecialchars($commande['numero']) ?></td>
                            <td><?= htmlspecialchars($commande['fournisseur_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></td>
                            <td>
                                <?php
                                $badgeClass = match($commande['statut']) {
                                    'BROUILLON'   => 'badge-brouillon',
                                    'VALIDÉ'      => 'badge-valide',
                                    'REÇU_PARTIEL' => 'badge-partiel',
                                    'REÇU_TOTAL'  => 'badge-complet',
                                    'ANNULÉ'      => 'badge-annule',
                                    default       => 'badge-brouillon'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($commande['statut']) ?></span>
                            </td>
                            <td class="text-right"><?= number_format($commande['montant_ttc'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                            <td class="text-center">
                                <!-- Voir détail -->
                                <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>" class="btn btn-outline btn-sm" title="Voir détails">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Modifier (uniquement BROUILLON) -->
                                <?php if ($commande['statut'] === 'BROUILLON'): ?>
                                    <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/modifier" class="btn btn-secondary btn-sm" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- PDF (toujours disponible) -->
                                <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/pdf" class="btn btn-primary btn-sm" target="_blank" title="PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                <!-- Supprimer (uniquement BROUILLON ou ANNULÉ) -->
                                <?php if ($commande['statut'] === 'BROUILLON' || $commande['statut'] === 'ANNULÉ'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/supprimer" style="display:inline;">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Supprimer définitivement cette commande ? Une sauvegarde XML sera créée.')"
                                                title="Supprimer">
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