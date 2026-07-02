<?php // modules/ventes/views/factures/liste.php ?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Factures clients</h1>
    </div>

    <?php if (empty($factures)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">Aucune facture enregistrée.</div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th class="text-right">TTC</th>
                        <th class="text-right">Réglé</th>
                        <th class="text-right">Solde</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $f): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>" class="text-blue-600 font-medium hover:underline">
                                <?= htmlspecialchars($f['numero']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($f['client_nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                        <td style="<?= ($f['date_echeance'] && strtotime($f['date_echeance']) < time() && (float)$f['solde'] > 0) ? 'color:#dc2626;font-weight:600' : '' ?>">
                            <?= $f['date_echeance'] ? date('d/m/Y', strtotime($f['date_echeance'])) : '—' ?>
                        </td>
                        <td class="text-right font-semibold"><?= number_format((float)$f['montant_ttc'], 0, ',', ' ') ?></td>
                        <td class="text-right" style="color:#16a34a"><?= number_format((float)$f['montant_regle'], 0, ',', ' ') ?></td>
                        <td class="text-right font-bold" style="color:<?= (float)$f['solde'] > 0 ? '#dc2626' : '#16a34a' ?>">
                            <?= number_format((float)$f['solde'], 0, ',', ' ') ?>
                        </td>
                        <td>
                            <?php
                            $sc = ['REGLÉE'=>'background:#dcfce7;color:#166534','PARTIELLEMENT_REGLÉE'=>'background:#dbeafe;color:#1e40af','NON_REGLÉE'=>'background:#fee2e2;color:#991b1b'];
                            $ss = $sc[$f['statut']] ?? 'background:#f1f5f9;color:#475569';
                            ?>
                            <span style="<?= $ss ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:500"><?= htmlspecialchars($f['statut']) ?></span>
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>" class="btn btn-secondary" title="Voir">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/pdf" target="_blank" class="btn btn-secondary" title="PDF">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <?php if ($f['statut'] === 'NON_REGLÉE'): ?>
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/modifier" class="btn btn-secondary" title="Modifier">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ((float)$f['solde'] > 0): ?>
                            <a href="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>#regler" class="btn btn-success" title="Encaisser">
                                <i class="fa-solid fa-coins"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ((float)$f['montant_regle'] == 0): ?>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/factures/<?= $f['id'] ?>/supprimer"
                                  onsubmit="return confirm('Supprimer cette facture ?')" style="display:inline">
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
