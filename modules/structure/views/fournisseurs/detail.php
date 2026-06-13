<?php
// modules/structure/views/fournisseurs/detail.php
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Détail fournisseur</h1>
        <div class="flex gap-2">
            <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                <a href="<?= BASE_URL ?>/structure/fournisseurs/editer/<?= $fournisseur['id'] ?>" class="btn-secondary"><i class="fa-solid fa-pen"></i> Modifier</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/structure/fournisseurs" class="btn-outline"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>
    </div>
    <div class="carte p-6">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="label">Code</label><p class="font-mono"><?= htmlspecialchars($fournisseur['code']) ?></p></div>
            <div><label class="label">Raison sociale</label><p><?= htmlspecialchars($fournisseur['raison_sociale']) ?></p></div>
            <div><label class="label">Contact</label><p><?= htmlspecialchars($fournisseur['contact'] ?? '-') ?></p></div>
            <div><label class="label">Téléphone</label><p><?= htmlspecialchars($fournisseur['telephone'] ?? '-') ?></p></div>
            <div><label class="label">Email</label><p><?= htmlspecialchars($fournisseur['email'] ?? '-') ?></p></div>
            <div><label class="label">Adresse</label><p><?= nl2br(htmlspecialchars($fournisseur['adresse'] ?? '-')) ?></p></div>
            <div><label class="label">Ville</label><p><?= htmlspecialchars($fournisseur['ville'] ?? '-') ?></p></div>
            <div><label class="label">Pays</label><p><?= htmlspecialchars($fournisseur['pays'] ?? '-') ?></p></div>
        </div>
    </div>
</div>