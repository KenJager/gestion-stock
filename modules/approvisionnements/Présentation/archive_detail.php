<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Détail archive</h1>
        <div class="flex gap-2">
            <form method="POST" action="<?= BASE_URL ?>/appros/archives/<?= $filename ?>/restaurer" style="display:inline;">
                <button type="submit" class="btn btn-success" onclick="return confirm('Restaurer cette commande ?')">
                    <i class="fa-solid fa-rotate-left"></i> Restaurer
                </button>
            </form>
            <a href="<?= BASE_URL ?>/appros/archives" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">N° Commande</span>
                <p class="font-semibold"><?= htmlspecialchars($archive['commande']['numero'] ?? '-') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Statut</span>
                <p><?= htmlspecialchars($archive['commande']['statut'] ?? '-') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Fournisseur</span>
                <p><?= htmlspecialchars($archive['commande']['fournisseur_id'] ?? '-') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date commande</span>
                <p><?= date('d/m/Y', strtotime($archive['commande']['date_commande'] ?? 'now')) ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Date suppression</span>
                <p><?= htmlspecialchars($archive['date_suppression'] ?? '-') ?></p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Utilisateur</span>
                <p><?= htmlspecialchars($archive['utilisateur'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <div class="carte p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">Montant HT</span>
                <p class="font-semibold"><?= number_format($archive['commande']['montant_ht'] ?? 0, 0, ',', ' ') ?> FCFA</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">TVA</span>
                <p class="font-semibold"><?= number_format($archive['commande']['montant_tva'] ?? 0, 0, ',', ' ') ?> FCFA</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Total TTC</span>
                <p class="font-semibold"><?= number_format($archive['commande']['montant_ttc'] ?? 0, 0, ',', ' ') ?> FCFA</p>
            </div>
        </div>
    </div>

    <div class="carte">
        <h2 class="font-semibold p-4 border-b">Lignes de commande</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">PU HT</th>
                        <th class="text-right">TVA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archive['lignes'] as $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['designation'] ?? 'Produit #' . $ligne['produit_id']) ?></td>
                        <td class="text-right"><?= number_format($ligne['quantite_commandee'] ?? 0, 2, ',', ' ') ?></td>
                        <td class="text-right"><?= number_format($ligne['prix_unitaire_ht'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                        <td class="text-right"><?= $ligne['tva'] ?? 19.25 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>