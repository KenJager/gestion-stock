<?php
// modules/structure/views/produits/detail.php
// Variables : $produit, $fractions (liste des produits fils si produit père)
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Détail du produit</h1>
        <div class="flex gap-2">
            <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                <a href="<?= BASE_URL ?>/structure/produits/editer/<?= $produit['id'] ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
            <?php endif; ?>
            <?php if (Auth::peut('STRUCTURE_CREER') && $produit['est_pere']): ?>
                <a href="<?= BASE_URL ?>/structure/produits/fractionner/<?= $produit['id'] ?>" class="btn btn-primary">
                    <i class="fa-solid fa-code-branch"></i> Fractionner
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/structure/produits" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="carte p-6 mt-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group"><label class="label">Code</label><p class="font-mono"><?= htmlspecialchars($produit['code']) ?></p></div>
            <div class="form-group"><label class="label">Désignation</label><p><?= htmlspecialchars($produit['designation']) ?></p></div>
            <div class="form-group"><label class="label">Famille</label><p><?= htmlspecialchars($produit['famille_libelle'] ?? '-') ?></p></div>
            <div class="form-group"><label class="label">Unité</label><p><?= htmlspecialchars($produit['unite'] ?? '-') ?></p></div>
            <div class="form-group"><label class="label">Prix achat HT</label><p><?= number_format($produit['prix_achat_ht'], 0, ',', ' ') ?> FCFA</p></div>
            <div class="form-group"><label class="label">Prix vente HT</label><p><?= number_format($produit['prix_vente_ht'], 0, ',', ' ') ?> FCFA</p></div>
            <div class="form-group"><label class="label">TVA</label><p><?= $produit['tva'] ?> %</p></div>
            <div class="form-group"><label class="label">Stock actuel</label><p class="<?= ($produit['stock_actuel'] < $produit['stock_minimum']) ? 'text-red-600 font-bold' : '' ?>"><?= number_format($produit['stock_actuel'], 2, ',', ' ') ?></p></div>
            <div class="form-group"><label class="label">Stock minimum</label><p><?= number_format($produit['stock_minimum'], 2, ',', ' ') ?></p></div>
            <div class="form-group"><label class="label">Date péremption</label><p><?= $produit['date_peremption'] ? date('d/m/Y', strtotime($produit['date_peremption'])) : '-' ?></p></div>
            <div class="form-group"><label class="label">Statut</label><p><?= ($produit['actif'] == 't' || $produit['actif'] === true) ? 'Actif' : 'Inactif' ?></p></div>
        </div>
    </div>

    <?php if (!empty($fractions)): ?>
    <div class="carte">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold">Produits fils (fractionnement)</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead><tr><th>Code</th><th>Désignation</th><th>Unité</th><th>Quantité par père</th></tr></thead>
                <tbody>
                    <?php foreach ($fractions as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['fils_code']) ?></td>
                        <td><?= htmlspecialchars($f['fils_designation']) ?></td>
                        <td><?= htmlspecialchars($f['fils_unite'] ?? '-') ?></td>
                        <td><?= number_format($f['quantite_fils'], 2, ',', ' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>