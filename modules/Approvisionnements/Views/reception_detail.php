<?php
// modules/Approvisionnements/Views/reception_detail.php
// Variables : $titre, $reception
?>

<div class="space-y-4">
    <div class="flex justify-between items-center flex-wrap gap-2">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Détail réception') ?></h1>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/pdf" class="btn btn-primary btn-sm" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/receptions" class="btn btn-secondary btn-sm">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">N° Réception</span>
                <p class="font-semibold"><?= htmlspecialchars($reception['numero']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Statut</span>
                <span class="badge badge-complet"><?= htmlspecialchars($reception['statut']) ?></span>
            </div>
            <div>
                <span class="text-sm text-gray-500">Fournisseur</span>
                <p><?= htmlspecialchars($reception['fournisseur_nom']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date réception</span>
                <p><?= date('d/m/Y', strtotime($reception['date_reception'])) ?></p>
            </div>
            <?php if (!empty($reception['notes'])): ?>
            <div class="md:col-span-2">
                <span class="text-sm text-gray-500">Notes</span>
                <p><?= nl2br(htmlspecialchars($reception['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lignes de réception -->
    <div class="carte">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité reçue</th>
                        <th class="text-right">PU HT</th>
                        <th class="text-right">TVA</th>
                        <th class="text-right">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_ht = 0;
                    foreach ($reception['lignes'] as $ligne): 
                        $montant_ht = $ligne['quantite_recue'] * $ligne['prix_unitaire_ht'];
                        $total_ht += $montant_ht;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['designation']) ?></td>
                        <td class="text-right"><?= number_format($ligne['quantite_recue'], 2, ',', ' ') ?></td>
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
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/modifier" class="btn btn-secondary">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        <form method="POST" action="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/supprimer" style="display:inline;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cette réception ? Le stock sera restauré.')">
                <i class="fa-solid fa-trash"></i> Supprimer
            </button>
        </form>
    </div>
</div>