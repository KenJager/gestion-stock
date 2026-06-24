<?php // modules/Ventes/Views/livraisons/edit_form.php ?>

<h1 style="font-size:1.6rem;font-weight:700;margin:0 0 24px">Éditer le bon de livraison <?= htmlspecialchars($livraison['numero']) ?></h1>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px">
    <form method="POST" action="<?= BASE_URL ?>/ventes/livraisons/<?= $livraison['id'] ?>/modifier">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Date de livraison</label>
                <input type="date" name="date_livraison"
                       value="<?= htmlspecialchars($livraison['date_livraison']) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:6px">Notes</label>
                <input type="text" name="notes"
                       value="<?= htmlspecialchars($livraison['notes'] ?? '') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box">
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:12px;padding-top:20px;border-top:1px solid #e2e8f0">
            <a href="<?= BASE_URL ?>/ventes/livraisons/<?= $livraison['id'] ?>"
               style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;color:#374151;text-decoration:none;font-size:.9rem">Annuler</a>
            <button type="submit"
                    style="padding:10px 24px;background:#111;color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:500;cursor:pointer">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
