<div class="space-y-4">
    <div class="flex justify-between items-center flex-wrap gap-2">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Détail don') ?></h1>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/pdf" class="btn btn-primary" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="<?= BASE_URL ?>/appros/dons" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">N° Don</span>
                <p class="font-semibold"><?= htmlspecialchars($don['numero']) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Donateur</span>
                <p><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date d'entrée</span>
                <p><?= date('d/m/Y', strtotime($don['date_entree'])) ?></p>
            </div>
            <?php if (!empty($don['notes'])): ?>
            <div class="md:col-span-2">
                <span class="text-sm text-gray-500">Notes</span>
                <p><?= nl2br(htmlspecialchars($don['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="carte">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">Valeur estimée</th>
                        <th class="text-right">Total estimé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalEstime = 0;
                    foreach ($don['lignes'] as $ligne): 
                        $total = $ligne['quantite'] * ($ligne['valeur_estimee'] ?? 0);
                        $totalEstime += $total;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['designation']) ?></td>
                        <td class="text-right"><?= number_format($ligne['quantite'], 2, ',', ' ') ?></td>
                        <td class="text-right"><?= number_format($ligne['valeur_estimee'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                        <td class="text-right"><?= number_format($total, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-semibold">Total estimé :</td>
                        <td class="text-right font-semibold"><?= number_format($totalEstime, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/modifier" class="btn btn-secondary">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        <form method="POST" action="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/supprimer" style="display:inline;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce don ? Le stock sera restauré.')">
                <i class="fa-solid fa-trash"></i> Supprimer
            </button>
        </form>
    </div>
</div>