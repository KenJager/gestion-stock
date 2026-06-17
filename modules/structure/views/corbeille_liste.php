<?php
// modules/structure/views/corbeille_liste.php
// Variables : $elements, $succes, $erreur

function libelleTable(string $table): string {
    return match ($table) {
        'banques'            => 'Banque',
        'clients'            => 'Client',
        'categories_clients' => 'Catégorie client',
        'familles_produits'  => 'Famille',
        'fournisseurs'       => 'Fournisseur',
        'produits'           => 'Produit',
        'versements'         => 'Versement',
        default               => $table,
    };
}
?>

<div class="flex justify-between items-center mb-4">
    <h1 style="font-size:20px; font-weight:600;">Corbeille — Structure</h1>
    <a href="<?= BASE_URL ?>/structure" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($succes)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($succes) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($erreur) ?></span>
    </div>
<?php endif; ?>

<div class="carte">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Élément</th>
                    <th>Données liées</th>
                    <th>Supprimé par</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($elements)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="color:#71717a; padding:24px;">
                            La corbeille est vide.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($elements as $e): ?>
                    <tr>
                        <td><span class="badge badge-brouillon"><?= libelleTable($e['table']) ?></span></td>
                        <td style="font-weight:500;"><?= htmlspecialchars($e['libelle']) ?></td>
                        <td><?= $e['nb_liees'] > 0 ? $e['nb_liees'] . ' table(s) liée(s)' : '—' ?></td>
                        <td><?= htmlspecialchars($e['supprime_par']) ?></td>
                        <td style="font-size:12px; color:#71717a;">
                            <?= date('d/m/Y H:i', strtotime($e['date_suppression'])) ?>
                        </td>
                        <td class="text-right" style="white-space:nowrap;">
                            <?php if (\Auth::peut('STRUCTURE_CREER')): ?>
                                <form action="<?= BASE_URL ?>/structure/corbeille/restaurer" method="POST" style="display:inline;"
                                      onsubmit="return confirm('Restaurer cet élément ?');">
                                    <input type="hidden" name="fichier" value="<?= htmlspecialchars($e['fichier']) ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Restaurer">
                                        <i class="fa-solid fa-rotate-left"></i> Restaurer
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if (\Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                <form action="<?= BASE_URL ?>/structure/corbeille/purger" method="POST" style="display:inline;"
                                      onsubmit="return confirm('Supprimer définitivement ? Action irréversible.');">
                                    <input type="hidden" name="fichier" value="<?= htmlspecialchars($e['fichier']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer définitivement">
                                        <i class="fa-solid fa-trash-can"></i>
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
