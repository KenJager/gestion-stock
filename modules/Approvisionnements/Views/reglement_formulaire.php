<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouveau règlement') ?></h1>
        <a href="<?= $facture_id ? BASE_URL . '/appros/factures/' . $facture_id : BASE_URL . '/appros/reglements' ?>" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="carte p-4">
        <form method="POST" action="<?= BASE_URL ?>/appros/reglements/creer">
            
            <div class="form-group">
                <label class="label label-required">Facture</label>
                <select name="facture_id" class="input" required>
                    <option value="">-- Sélectionner une facture --</option>
                    <?php if ($facture_id && $facture): ?>
                        <option value="<?= $facture['id'] ?>" selected>
                            <?= htmlspecialchars($facture['numero']) ?> - <?= htmlspecialchars($facture['fournisseur_nom']) ?>
                           (Solde: <?= number_format($facture['montant_ttc'] - ($facture['montant_regle'] ?? 0), 2, ',', ' ') ?> FCFA)
                        </option>
                    <?php endif; ?>
                </select>
                <?php if ($facture_id && $facture): ?>
                    <input type="hidden" name="facture_id" value="<?= $facture['id'] ?>">
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label label-required">Date règlement</label>
                    <input type="date" name="date_reglement" value="<?= date('Y-m-d') ?>" class="input" required>
                </div>
                
                <div class="form-group">
                    <label class="label label-required">Montant</label>
                    <input type="number" name="montant" step="0.01" class="input" required placeholder="0.00">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label">Mode de paiement</label>
                    <select name="mode_paiement" class="input">
                        <option value="">-- Sélectionner --</option>
                        <option value="ESPÈCES">Espèces</option>
                        <option value="VIREMENT">Virement</option>
                        <option value="CHÈQUE">Chèque</option>
                        <option value="MOBILE_MONEY">Mobile Money</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="label">Référence</label>
                    <input type="text" name="reference" class="input" placeholder="N° de chèque, virement...">
                </div>
            </div>
            
            <div class="form-group">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input" placeholder="Informations complémentaires..."></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <a href="<?= $facture_id ? BASE_URL . '/appros/factures/' . $facture_id : BASE_URL . '/appros/reglements' ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer le règlement</button>
            </div>
            
        </form>
    </div>
</div>