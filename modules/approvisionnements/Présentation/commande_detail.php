<div class="space-y-4">
    <div class="flex justify-between items-center flex-wrap gap-2">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Détail commande') ?></h1>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/pdf" class="btn btn-primary" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Informations générales -->
    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">N° Commande</span>
                <p class="font-semibold"><?= htmlspecialchars($commande['numero']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Statut</span>
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
            </div>
            <div>
                <span class="text-sm text-gray-500">Fournisseur</span>
                <p><?= htmlspecialchars($commande['fournisseur_nom']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date commande</span>
                <p><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></p>
            </div>
            <?php if (!empty($commande['notes'])): ?>
            <div class="md:col-span-2">
                <span class="text-sm text-gray-500">Notes</span>
                <p><?= nl2br(htmlspecialchars($commande['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lignes de commande -->
    <div class="carte">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">PU HT</th>
                        <th class="text-right">TVA</th>
                        <th class="text-right">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_ht = 0;
                    foreach ($commande['lignes'] as $ligne): 
                        $montant_ht = $ligne['quantite_commandee'] * $ligne['prix_unitaire_ht'];
                        $total_ht += $montant_ht;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['designation']) ?></td>
                        <td class="text-right"><?= number_format($ligne['quantite_commandee'], 2, ',', ' ') ?></td>
                        <td class="text-right"><?= number_format($ligne['prix_unitaire_ht'], 0, ',', ' ') ?> FCFA</td>
                        <td class="text-right"><?= $ligne['tva'] ?>%</td>
                        <td class="text-right"><?= number_format($montant_ht, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right font-semibold">Total HT :</td>
                        <td class="text-right font-semibold"><?= number_format($total_ht, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right font-semibold">TVA (19.25%) :</td>
                        <td class="text-right font-semibold"><?= number_format($total_ht * 0.1925, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right font-semibold text-lg">Total TTC :</td>
                        <td class="text-right font-semibold text-lg"><?= number_format($total_ht * 1.1925, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- 🔥 ACTIONS DISPONIBLES 🔥 -->
    <div class="flex justify-end gap-2 flex-wrap mt-4">

        <!-- 1. VALIDER (uniquement BROUILLON) -->
        <?php if ($commande['statut'] === 'BROUILLON' && \Auth::peut('APPRO_MODIFIER')): ?>
            <form method="POST" action="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/valider" style="display:inline;">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Valider cette commande ? Elle sera envoyée au fournisseur.')">
                    <i class="fa-solid fa-check"></i> Valider la commande
                </button>
            </form>
        <?php endif; ?>

        <!-- 2. MODIFIER (uniquement BROUILLON) -->
        <?php if ($commande['statut'] === 'BROUILLON' && \Auth::peut('APPRO_MODIFIER')): ?>
            <a href="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/modifier" class="btn btn-secondary">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
        <?php endif; ?>

        <!-- 3. RÉCEPTIONNER (uniquement VALIDÉ ou REÇU_PARTIEL) -->
        <?php if (($commande['statut'] === 'VALIDÉ' || $commande['statut'] === 'REÇU_PARTIEL') && \Auth::peut('APPRO_CREER')): ?>
            <a href="<?= BASE_URL ?>/appros/receptions/creer?commande_id=<?= $commande['id'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-boxes"></i> Réceptionner
            </a>
        <?php endif; ?>

        <!-- 4. ANNULER (tout sauf ANNULÉ et REÇU_TOTAL) -->
        <?php if ($commande['statut'] !== 'ANNULÉ' && $commande['statut'] !== 'REÇU_TOTAL' && \Auth::peut('APPRO_MODIFIER')): ?>
            <form method="POST" action="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/annuler" style="display:inline;">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Annuler cette commande ?')">
                    <i class="fa-solid fa-ban"></i> Annuler
                </button>
            </form>
        <?php endif; ?>

        <!-- 5. SUPPRIMER (uniquement BROUILLON ou ANNULÉ) -->
        <?php if (($commande['statut'] === 'BROUILLON' || $commande['statut'] === 'ANNULÉ') && \Auth::peut('APPRO_SUPPRIMER')): ?>
            <form method="POST" action="<?= BASE_URL ?>/appros/commandes/<?= $commande['id'] ?>/supprimer" style="display:inline;">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer définitivement cette commande ? Une sauvegarde XML sera créée.')">
                    <i class="fa-solid fa-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>