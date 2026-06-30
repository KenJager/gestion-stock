<?php // modules/ventes/views/livraisons/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Bons de livraison clients</h1>
        <a href="<?= BASE_URL ?>/ventes/commandes" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voir les commandes
        </a>
    </div>

    <?php if (empty($livraisons)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucun bon de livraison enregistré.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Livraison</th>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date livraison</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livraisons as $l): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>" class="text-blue-600 font-medium hover:underline">
                                <?= htmlspecialchars($l['numero']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($l['commande_numero'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($l['client_nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($l['date_livraison'])) ?></td>
                        <td>
                            <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500">
                                <?= htmlspecialchars($l['statut']) ?>
                            </span>
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>" class="btn btn-secondary" title="Voir">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/pdf" target="_blank" class="btn btn-secondary" title="PDF">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/modifier" class="btn btn-secondary" title="Modifier">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/livraisons/<?= $l['id'] ?>/supprimer"
                                  onsubmit="return confirm('Supprimer ce bon de livraison ? Le stock sera restitué.')" style="display:inline">
                                <button type="submit" class="btn btn-danger" title="Supprimer">
                                    <i class="fa-regular fa-trash-can"></i>
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
