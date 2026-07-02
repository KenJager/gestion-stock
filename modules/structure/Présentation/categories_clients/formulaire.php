<?php
// modules/structure/views/categories_clients/formulaire.php
// Variables : $categorie (null si création), $titre (string)
$estEdition = $categorie !== null;
$action     = $estEdition
    ? BASE_URL . '/structure/categories-clients/modifier/' . $categorie['id']
    : BASE_URL . '/structure/categories-clients';
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($titre) ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= $estEdition ? 'Modifier les informations de la catégorie' : 'Créer une nouvelle catégorie de clients' ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/structure/categories-clients" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="POST" action="<?= $action ?>">

        <div class="carte p-6 space-y-5">
            <!-- Code -->
            <div class="form-group">
                <label class="label label-required">Code</label>
                <input type="text" name="code"
                       class="input font-mono"
                       placeholder="ex : VIP, GROSSISTE, DETAIL"
                       value="<?= htmlspecialchars($categorie['code'] ?? '') ?>"
                       required>
                <p class="text-xs text-gray-400 mt-1">Identifiant unique de la catégorie.</p>
            </div>

            <!-- Libellé -->
            <div class="form-group">
                <label class="label label-required">Libellé</label>
                <input type="text" name="libelle"
                       class="input"
                       placeholder="ex : Clients VIP, Grossistes, Particuliers"
                       value="<?= htmlspecialchars($categorie['libelle'] ?? '') ?>"
                       required>
            </div>

            <!-- Remise (%) -->
            <div class="form-group">
                <label class="label">Remise (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" min="0" max="100" name="remise_pct"
                           class="input pr-8"
                           value="<?= number_format((float)($categorie['remise_pct'] ?? 0), 1, '.', '') ?>">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">%</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    Remise automatique appliquée aux clients de cette catégorie.
                </p>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="label">Description</label>
                <textarea name="description"
                          class="input"
                          rows="3"
                          placeholder="Description facultative de la catégorie"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Boutons -->
        <div class="flex justify-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/structure/categories-clients" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid <?= $estEdition ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
                <?= $estEdition ? 'Enregistrer les modifications' : 'Créer la catégorie' ?>
            </button>
        </div>

    </form>
</div>