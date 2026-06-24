<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($titre ?? 'Nouvelle facture') ?></h1>
        <a href="<?= BASE_URL ?>/appros/factures" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="carte p-4 mt-4">
        <form method="POST" action="<?= $isEdit ? BASE_URL . '/appros/factures/' . $facture['id'] . '/modifier' : BASE_URL . '/appros/factures/creer' ?>">
            
            <div class="form-group">
                <label class="label label-required">Réception</label>
                <select name="bon_reception_id" id="bon_reception_id" class="input" required <?= $isEdit ? 'disabled' : '' ?>>
                    <option value="">-- Sélectionner une réception --</option>
                    <?php foreach ($receptions as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($isEdit && $facture['bon_reception_id'] == $r['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['numero']) ?> - <?= htmlspecialchars($r['fournisseur_nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="bon_reception_id" value="<?= $facture['bon_reception_id'] ?>">
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label label-required">Date facture</label>
                    <input type="date" name="date_facture" value="<?= $isEdit ? date('Y-m-d', strtotime($facture['date_facture'])) : date('Y-m-d') ?>" class="input" required>
                </div>
                
                <div class="form-group">
                    <label class="label">Date échéance</label>
                    <input type="date" name="date_echeance" value="<?= $isEdit && $facture['date_echeance'] ? date('Y-m-d', strtotime($facture['date_echeance'])) : '' ?>" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group">
                    <label class="label label-required">Montant HT</label>
                    <input type="number" name="montant_ht" id="montant_ht" step="0.01" 
                           value="<?= $isEdit ? $facture['montant_ht'] : 0 ?>" class="input" required>
                </div>

                <div class="form-group">
                    <label class="label label-required">Taux TVA (%)</label>
                    <input type="number" name="taux_tva" id="taux_tva" step="0.01" 
                           value="<?= $isEdit ? ($facture['montant_tva'] / $facture['montant_ht'] * 100) : 19.25 ?>" 
                           class="input" required>
                </div>
                
                <div class="form-group">
                    <label class="label label-required">Montant TVA</label>
                    <input type="number" name="montant_tva" id="montant_tva" step="0.01" 
                           value="<?= $isEdit ? $facture['montant_tva'] : 0 ?>" class="input" required readonly>
                </div>
                
                <div class="form-group">
                    <label class="label label-required">Montant TTC</label>
                    <input type="number" name="montant_ttc" id="montant_ttc" step="0.01" 
                           value="<?= $isEdit ? $facture['montant_ttc'] : 0 ?>" class="input" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input" placeholder="Informations complémentaires..."><?= $isEdit ? htmlspecialchars($facture['notes']) : '' ?></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <a href="<?= BASE_URL ?>/appros/factures" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?></button>
            </div>
            
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectReception = document.getElementById('bon_reception_id');
    const montantHT = document.getElementById('montant_ht');
    const tauxTVA = document.getElementById('taux_tva');
    const montantTVA = document.getElementById('montant_tva');
    const montantTTC = document.getElementById('montant_ttc');

    // Fonction de calcul
    function calculerMontants() {
        let ht = parseFloat(montantHT.value) || 0;
        let taux = parseFloat(tauxTVA.value) || 0;

        let tva = ht * (taux / 100);
        let ttc = ht + tva;

        montantTVA.value = tva.toFixed(2);
        montantTTC.value = ttc.toFixed(2);
    }

    // Écouter les modifications
    montantHT.addEventListener('input', calculerMontants);
    tauxTVA.addEventListener('input', calculerMontants);
    montantTTC.addEventListener('input', function() {
        let ttc = parseFloat(this.value) || 0;
        let taux = parseFloat(tauxTVA.value) || 0;
        if (taux === 0) return;
        let ht = ttc / (1 + taux / 100);
        let tva = ttc - ht;
        montantHT.value = ht.toFixed(2);
        montantTVA.value = tva.toFixed(2);
    });

    <?php if (!$isEdit): ?>
    // Charger les montants automatiquement lors du changement de réception
    selectReception.addEventListener('change', function() {
        const receptionId = this.value;
        if (!receptionId) {
            montantHT.value = 0;
            montantTVA.value = 0;
            montantTTC.value = 0;
            return;
        }

        fetch('<?= BASE_URL ?>/appros/receptions/get-montants/' + receptionId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                montantHT.value = data.montant_ht;
                // Recalculer avec le taux actuel
                calculerMontants();
            })
            .catch(error => console.error('Erreur:', error));
    });
    <?php endif; ?>

    // Appliquer les calculs au chargement
    calculerMontants();
});
</script>