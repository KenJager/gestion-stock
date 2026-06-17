<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Dons') ?></h1>
        <a href="<?= BASE_URL ?>/appros/dons/creer" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Nouveau don
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($dons)): ?>
        <div class="carte p-4 text-center text-gray-500">
            Aucun don enregistré.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Donateur</th>
                            <th>Date</th>
                            <th class="text-right">Valeur estimée</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dons as $don): ?>
                        <tr>
                            <td><?= htmlspecialchars($don['numero']) ?></td>
                            <td><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></td>
                            <td><?= date('d/m/Y', strtotime($don['date_entree'])) ?></td>
                           <td class="text-right">
                                <?= number_format($don['total_valeur_estimee'] ?? 0, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/modifier" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/pdf" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce don ? Le stock sera restauré.')">
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