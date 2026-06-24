<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Commandes archivées') ?></h1>
        <a href="<?= BASE_URL ?>/appros/commandes" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour aux commandes
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($archives)): ?>
        <div class="carte p-4 text-center text-gray-500 mt-4">
            Aucune commande archivée.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container  mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Date suppression</th>
                            <th>Taille</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archives as $archive): ?>
                        <tr>
                            <td><?= htmlspecialchars($archive['numero']) ?></td>
                            <td><?= htmlspecialchars($archive['date_suppression']) ?></td>
                            <td><?= number_format($archive['taille'] / 1024, 1) ?> KB</td>
                            <td class="text-center whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/appros/archives/<?= $archive['filename'] ?>" class="btn btn-outline">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/appros/archives/<?= $archive['filename'] ?>/restaurer" style="display:inline;">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Restaurer cette commande ?')">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?= BASE_URL ?>/appros/archives/<?= $archive['filename'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cette archive ?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>