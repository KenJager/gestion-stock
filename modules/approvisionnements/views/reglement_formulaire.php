<?php
// modules/Approvisionnements/Views/reglement_formulaire.php
$facture_id = $facture_id ?? null;
$facture = $facture ?? null;
?>

<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouveau règlement') ?></h1>
        <a href="<?= $facture_id ? BASE_URL . '/appros/factures/' . $facture_id : BASE_URL . '/appros/reglements' ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="carte p-6">
        <form method="POST" action="<?= BASE_URL ?>/appros/reglements/creer">
            
            <div class="form-group">
                <label class="label label-required" for="facture_id">Facture</label>
                <select name="facture_id" id="facture_id" class="input w-full" required <?= $facture_id ? 'disabled' : '' ?>>
                    <option value="">-- Sélectionner une facture --</option>
                    <?php if ($facture_id && $facture): ?>
                        <option value="<?= $facture['id'] ?>" selected>
                            <?= htmlspecialchars($facture['numero']) ?> - <?= htmlspecialchars($facture['fournisseur_nom']) ?>
                            (Solde: <?= number_format($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0), 2, ',', ' ') ?> FCFA)
                        </option>
                    <?php endif; ?>
                </select>
                <?php if ($facture_id): ?>
                    <input type="hidden" name="facture_id" value="<?= $facture['id'] ?>">
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-group">
                    <label class="label label-required" for="date_reglement">Date règlement</label>
                    <input type="date" name="date_reglement" id="date_reglement" value="<?= date('Y-m-d') ?>" class="input" required>
                </div>

                <div class="form-group">
                    <label class="label label-required" for="montant">Montant</label>
                    <input type="number" name="montant" id="montant" step="0.01" class="input" required placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-group">
                    <label class="label" for="mode_paiement">Mode de paiement</label>
                    <select name="mode_paiement" id="mode_paiement" class="input">
                        <option value="">-- Sélectionner --</option>
                        <option value="ESPÈCES">Espèces</option>
                        <option value="VIREMENT">Virement</option>
                        <option value="CHÈQUE">Chèque</option>
                        <option value="MOBILE_MONEY">Mobile Money</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="reference">Référence</label>
                    <input type="text" name="reference" id="reference" class="input" placeholder="N° de chèque, virement...">
                </div>
            </div>

            <div class="form-group mt-4">
                <label class="label" for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="input" placeholder="Informations complémentaires..."></textarea>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-6 pt-4 border-t">
                <a href="<?= $facture_id ? BASE_URL . '/appros/factures/' . $facture_id : BASE_URL . '/appros/reglements' ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                </button>
            </div>

        </form>
    </div>
</div>