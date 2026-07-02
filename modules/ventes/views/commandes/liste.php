<?php // modules/ventes/views/commandes/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Commandes clients</h1>
        <a href="<?= BASE_URL ?>/ventes/commandes/creer" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
    </div>

    <?php if (!empty($erreur ?? '')): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if (empty($commandes)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucune commande client enregistrée.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Livraison prévue</th>
                        <th class="text-right">Montant TTC</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $c): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>" class="text-blue-600 font-medium hover:underline">
                                <?= htmlspecialchars($c['numero']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($c['client_nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['date_commande'])) ?></td>
                        <td><?= $c['date_livraison_prevue'] ? date('d/m/Y', strtotime($c['date_livraison_prevue'])) : '—' ?></td>
                        <td class="text-right font-semibold"><?= number_format((float)$c['montant_ttc'], 0, ',', ' ') ?> FCFA</td>
                        <td>
                            <?php
                            $sc = ['EN_ATTENTE'=>'background:#fef3c7;color:#92400e','LIVRÉ_PARTIEL'=>'background:#dbeafe;color:#1e40af','LIVRÉ_TOTAL'=>'background:#dcfce7;color:#166534','ANNULÉ'=>'background:#fee2e2;color:#991b1b'];
                            $ss = $sc[$c['statut']] ?? 'background:#f1f5f9;color:#475569';
                            ?>
                            <span style="<?= $ss ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500"><?= htmlspecialchars($c['statut']) ?></span>
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>" class="btn btn-secondary" title="Voir">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/pdf" target="_blank" class="btn btn-secondary" title="PDF">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <?php if ($c['statut'] === 'EN_ATTENTE'): ?>
                            <a href="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/modifier" class="btn btn-secondary" title="Modifier">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/commandes/<?= $c['id'] ?>/supprimer"
                                  onsubmit="return confirm('Supprimer cette commande ?')" style="display:inline">
                                <button type="submit" class="btn btn-danger" title="Supprimer">
                                    <i class="fa-regular fa-trash-can"></i>
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
