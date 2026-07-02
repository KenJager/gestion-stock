<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Dons') ?></h1>
        <?php if (\Auth::peut('APPRO_CREER')): ?>
        <a href="<?= BASE_URL ?>/appros/dons/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouveau don
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($dons)): ?>
        <div class="carte p-4 text-center text-gray-500 mt-4">
            Aucun don enregistré.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container  mt-4">
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
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>" class="btn btn-outline">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if (\Auth::peut('APPRO_MODIFIER')): ?>
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/modifier" class="btn btn-secondary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/pdf" class="btn btn-primary" target="_blank">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <?php if (\Auth::peut('APPRO_SUPPRIMER')): ?>
                                <form method="POST" action="<?= BASE_URL ?>/appros/dons/<?= $don['id'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce don ? Le stock sera restauré.')">
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