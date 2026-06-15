<?php
// modules/approvisionnements/views/reglements/recu.php
// Variables : $recu
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement <?= htmlspecialchars($recu['facture_numero']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { font-size: 20px; border-bottom: 2px solid #16a34a; padding-bottom: 5px; color: #16a34a; }
        .info-box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .montant { font-size: 24px; font-weight: bold; color: #16a34a; text-align: center; margin: 20px 0; padding: 15px; background: #f0fdf4; border-radius: 8px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #16a34a; color: white; border: none; border-radius: 4px; cursor: pointer;">
        🖨️ Imprimer le reçu
    </button>
    <a href="<?= BASE_URL ?>/appros/factures/<?= $recu['facture_id'] ?>" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; text-decoration: none; margin-left: 8px;">
        ← Retour à la facture
    </a>
</div>

<h1>REÇU DE PAIEMENT</h1>

<div class="info-box">
    <p><strong>Fournisseur :</strong> <?= htmlspecialchars($recu['fournisseur_nom']) ?></p>
    <p><strong>Facture N° :</strong> <?= htmlspecialchars($recu['facture_numero']) ?></p>
    <p><strong>Date du règlement :</strong> <?= date('d/m/Y', strtotime($recu['date_reglement'])) ?></p>
    <p><strong>Mode de paiement :</strong> <?= htmlspecialchars($recu['mode_paiement']) ?></p>
    <?php if (!empty($recu['banque_nom'])): ?>
        <p><strong>Banque :</strong> <?= htmlspecialchars($recu['banque_nom']) ?></p>
    <?php endif; ?>
    <?php if (!empty($recu['reference'])): ?>
        <p><strong>Référence :</strong> <?= htmlspecialchars($recu['reference']) ?></p>
    <?php endif; ?>
    <p><strong>Reçu par :</strong> <?= htmlspecialchars($recu['utilisateur']) ?></p>
</div>

<div class="montant">
    <?= number_format((float)$recu['montant'], 0, ',', ' ') ?> FCFA
</div>

<?php if (!empty($recu['notes'])): ?>
    <div class="info-box">
        <strong>Notes :</strong><br>
        <?= nl2br(htmlspecialchars($recu['notes'])) ?>
    </div>
<?php endif; ?>

<div class="footer">
    Reçu généré le <?= date('d/m/Y à H:i') ?>
</div>

</body>
</html>