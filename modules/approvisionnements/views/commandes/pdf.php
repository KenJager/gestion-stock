<?php
// modules/approvisionnements/views/commandes/pdf.php
// Variables : $commande, $lignes
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bon de commande <?= htmlspecialchars($commande['numero']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { font-size: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f3f4f6; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background: #eff6ff; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">
        🖨️ Imprimer / Enregistrer PDF
    </button>
</div>

<div class="header">
    <div>
        <h1>BON DE COMMANDE</h1>
        <p><strong>N° :</strong> <?= htmlspecialchars($commande['numero']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($commande['date_commande'])) ?></p>
    </div>
    <div class="info-box">
        <p><strong>Fournisseur :</strong></p>
        <p><?= htmlspecialchars($commande['fournisseur_nom']) ?></p>
        <?php if (!empty($commande['date_livraison_prevue'])): ?>
            <p><strong>Livraison prévue :</strong> <?= date('d/m/Y', strtotime($commande['date_livraison_prevue'])) ?></p>
        <?php endif; ?>
        <p><strong>Statut :</strong> <?= htmlspecialchars($commande['statut']) ?></p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="text-right">Qté</th>
            <th class="text-right">PU HT</th>
            <th class="text-right">TVA %</th>
            <th class="text-right">Total HT</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lignes as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['designation']) ?></td>
                <td class="text-right"><?= number_format((float)$l['quantite_commandee'], 2, ',', ' ') ?></td>
                <td class="text-right"><?= number_format((float)$l['prix_unitaire_ht'], 0, ',', ' ') ?></td>
                <td class="text-right"><?= $l['tva'] ?> %</td>
                <td class="text-right"><?= number_format((float)$l['montant_ht'], 0, ',', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="4" class="text-right">Total HT</td>
            <td class="text-right"><?= number_format((float)$commande['montant_ht'], 0, ',', ' ') ?> FCFA</td>
        </tr>
        <tr class="total-row">
            <td colspan="4" class="text-right">TVA</td>
            <td class="text-right"><?= number_format((float)$commande['montant_tva'], 0, ',', ' ') ?> FCFA</td>
        </tr>
        <tr class="total-row" style="background: #dbeafe;">
            <td colspan="4" class="text-right">Total TTC</td>
            <td class="text-right"><?= number_format((float)$commande['montant_ttc'], 0, ',', ' ') ?> FCFA</td>
        </tr>
    </tbody>
</table>

<?php if (!empty($commande['notes'])): ?>
    <div class="info-box" style="margin-top: 20px;">
        <strong>Notes :</strong><br>
        <?= nl2br(htmlspecialchars($commande['notes'])) ?>
    </div>
<?php endif; ?>

<div class="footer">
    Document généré le <?= date('d/m/Y à H:i') ?>
</div>

</body>
</html>