<?php // modules/ventes/views/sorties/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Bons de sortie</h1>
        <a href="<?= BASE_URL ?>/ventes/sorties/creer" class="btn btn-danger">
            <i class="fa-solid fa-plus"></i> Nouveau bon de sortie
        </a>
    </div>

    <?php if (empty($sorties)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucun bon de sortie enregistré.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Bon</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Notes</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sorties as $s): ?>
                    <tr>
                        <td class="font-medium"><?= htmlspecialchars($s['numero']) ?></td>
                        <td><?= date('d/m/Y', strtotime($s['date_sortie'])) ?></td>
                        <td>
                            <?php
                            $mc = ['NON_VENDU'=>'background:#fef3c7;color:#92400e','PÉRIMÉ'=>'background:#fee2e2;color:#991b1b','CASSE'=>'background:#fed7aa;color:#9a3412','AJUSTEMENT'=>'background:#dbeafe;color:#1e40af'];
                            $ms = $mc[$s['motif']] ?? 'background:#f1f5f9;color:#475569';
                            ?>
                            <span style="<?= $ms ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500"><?= htmlspecialchars($s['motif']) ?></span>
                        </td>
                        <td class="text-sm text-gray-500"><?= htmlspecialchars($s['notes'] ?? '—') ?></td>
                        <td class="text-center whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/pdf" target="_blank" class="btn btn-secondary" title="PDF">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/modifier" class="btn btn-secondary" title="Modifier">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/sorties/<?= $s['id'] ?>/supprimer"
                                  onsubmit="return confirm('Supprimer ce bon de sortie ? Le stock sera restitué.')" style="display:inline">
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
