<?php // modules/ventes/views/comptant/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Ventes comptant</h1>
        <a href="<?= BASE_URL ?>/ventes/comptant/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle vente
        </a>
    </div>

    <?php if (empty($ventes)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucune vente comptant enregistrée.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Vente</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th class="text-right">TTC</th>
                        <th class="text-right">Remise</th>
                        <th class="text-right">Perçu</th>
                        <th class="text-right">Monnaie rendue</th>
                        <th>Mode</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventes as $v): ?>
                    <tr>
                        <td class="font-medium"><?= htmlspecialchars($v['numero']) ?></td>
                        <td><?= htmlspecialchars($v['client_nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($v['date_vente'])) ?></td>
                        <td class="text-right font-semibold"><?= number_format((float)$v['montant_ttc'], 0, ',', ' ') ?></td>
                        <td class="text-right"><?= number_format((float)$v['remise_montant'], 0, ',', ' ') ?></td>
                        <td class="text-right font-semibold" style="color:#16a34a"><?= number_format((float)$v['montant_percu'], 0, ',', ' ') ?></td>
                        <td class="text-right"><?= number_format((float)$v['monnaie_rendue'], 0, ',', ' ') ?></td>
                        <td><?= htmlspecialchars($v['mode_paiement'] ?? '—') ?></td>
                        <td class="text-center whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/ventes/comptant/<?= $v['id'] ?>/pdf" target="_blank" class="btn btn-secondary" title="Reçu PDF">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/comptant/<?= $v['id'] ?>/supprimer"
                                  onsubmit="return confirm('Supprimer cette vente ? Le stock sera restitué.')" style="display:inline">
                                <button type="submit" class="btn btn-danger" title="Supprimer">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold">
                        <td colspan="3">Total</td>
                        <td class="text-right"><?= number_format(array_sum(array_column($ventes, 'montant_ttc')), 0, ',', ' ') ?> FCFA</td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
