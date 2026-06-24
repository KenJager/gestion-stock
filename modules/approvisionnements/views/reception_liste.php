<?php
// modules/Approvisionnements/Views/reception_liste.php
// Variables : $titre, $receptions
?>

<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Bons de réception') ?></h1>
        <a href="<?= BASE_URL ?>/appros/receptions/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle réception
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($receptions)): ?>
        <div class="carte p-4 text-center text-gray-500 mt-4">
            Aucune réception trouvée.
        </div>
    <?php else: ?>
        <div class="carte">
            <div class="table-container mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receptions as $reception): ?>
                        <tr>
                            <td><?= htmlspecialchars($reception['numero']) ?></td>
                            <td><?= htmlspecialchars($reception['fournisseur_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($reception['date_reception'])) ?></td>
                            <td>
                                <span class="badge badge-complet"><?= htmlspecialchars($reception['statut']) ?></span>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <!-- Voir détail -->
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>" class="btn btn-outline" title="Voir les détails">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <!-- PDF -->
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/pdf" class="btn btn-primary" target="_blank" title="Télécharger PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <!-- Modifier -->
                                <a href="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/modifier" class="btn btn-secondary" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <!-- Supprimer -->
                                <form method="POST" action="<?= BASE_URL ?>/appros/receptions/<?= $reception['id'] ?>/supprimer" style="display:inline;">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Supprimer cette réception ? Le stock sera restauré.')"
                                            title="Supprimer">
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