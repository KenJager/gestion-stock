<?php
// modules/ventes/views/corbeille/liste.php
$libellesTable = [
    'bons_commande_client'  => ['label' => 'Commande client',  'icone' => 'fa-solid fa-file-lines'],
    'bons_livraison_client' => ['label' => 'Bon de livraison', 'icone' => 'fa-solid fa-truck'],
    'factures_client'       => ['label' => 'Facture client',   'icone' => 'fa-solid fa-receipt'],
    'ventes_comptant'       => ['label' => 'Vente comptant',   'icone' => 'fa-solid fa-coins'],
    'bons_sortie'           => ['label' => 'Bon de sortie',    'icone' => 'fa-solid fa-arrow-right-from-bracket'],
];
?>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold">
                <i class="fa-solid fa-trash-can mr-2 text-gray-500"></i>Corbeille — Ventes
            </h1>
            <p class="text-sm text-gray-500 mt-1">Les éléments supprimés peuvent être restaurés à tout moment.</p>
        </div>
    </div>

    <?php if (!empty($erreur ?? '')): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if (empty($elements)): ?>
    <div class="carte p-8 text-center text-gray-400 mt-4">
        <i class="fa-solid fa-trash-can text-4xl mb-3 block"></i>
        La corbeille du module Ventes est vide.
    </div>
    <?php else: ?>
    <div class="carte">
        <div class="table-container mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Libellé</th>
                        <th>Supprimé le</th>
                        <th>Supprimé par</th>
                        <th class="text-center">Données liées</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($elements as $el):
                        $info = $libellesTable[$el['table']] ?? ['label' => $el['table'], 'icone' => 'fa-solid fa-file'];
                    ?>
                    <tr>
                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                <i class="<?= $info['icone'] ?>"></i>
                                <?= htmlspecialchars($info['label']) ?>
                            </span>
                        </td>
                        <td class="font-medium"><?= htmlspecialchars($el['libelle']) ?></td>
                        <td><?= date('d/m/Y à H:i', strtotime($el['date_suppression'])) ?></td>
                        <td><?= htmlspecialchars($el['supprime_par']) ?></td>
                        <td class="text-center text-sm text-gray-500">
                            <?= $el['nb_liees'] > 0 ? $el['nb_liees'] . ' table(s)' : '—' ?>
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <form method="POST" action="<?= BASE_URL ?>/ventes/corbeille/restaurer"
                                  onsubmit="return confirm('Restaurer cet élément dans le système ?')" style="display:inline">
                                <input type="hidden" name="fichier" value="<?= htmlspecialchars($el['fichier']) ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurer
                                </button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?>/ventes/corbeille/purger"
                                  onsubmit="return confirm('Supprimer définitivement ? Action irréversible.')" style="display:inline">
                                <input type="hidden" name="fichier" value="<?= htmlspecialchars($el['fichier']) ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa-regular fa-trash-can"></i> Purger
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-xs text-gray-400 mt-2">
        <i class="fa-solid fa-circle-info mr-1"></i>
        La restauration réinjecte l'élément et toutes ses données liées en base. La purge supprime définitivement le fichier archive.
    </p>
    <?php endif; ?>
</div>
