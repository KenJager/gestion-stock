<?php
// modules/structure/views/produits/fractionnement.php
?>
<div class="carte p-6">
    <h1 class="text-xl font-semibold mb-2"><?= htmlspecialchars($titre) ?></h1>
    <p class="text-sm text-gray-500 mb-4">Produit père : <strong><?= htmlspecialchars($produitPere['designation']) ?></strong></p>

    <form method="POST" action="<?= BASE_URL ?>/structure/produits/fractionner/<?= $produitPere['id'] ?>">
        <div class="space-y-4">
            <div id="fractions-container">
                <!-- Template pour une ligne de fils -->
                <template id="fraction-template">
                    <div class="fraction-ligne border p-4 rounded-md mb-2 bg-gray-50">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-group"><label class="label">Code fils</label><input type="text" data-bind="fils_code___INDEX__" class="input" required></div>
                            <div class="form-group"><label class="label">Désignation</label><input type="text" data-bind="fils_designation___INDEX__" class="input" required></div>
                            <div class="form-group"><label class="label">Unité</label><input type="text" data-bind="fils_unite___INDEX__" class="input"></div>
                            <div class="form-group"><label class="label">Prix vente HT</label><input type="number" step="any" data-bind="fils_prix_vente_ht___INDEX__" class="input" value="0"></div>
                            <div class="form-group"><label class="label">Quantité par père</label><input type="number" step="any" data-bind="fils_quantite___INDEX__" class="input" value="1"></div>
                            <div class="flex items-end"><button type="button" class="btn btn-danger supprimer-fraction">Supprimer</button></div>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" id="ajouter-fraction" class="btn btn-secondary"><i class="fa-solid fa-plus"></i> Ajouter un produit fils</button>

            <div class="flex justify-end gap-2 mt-4">
                <a href="<?= BASE_URL ?>/structure/produits" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer le fractionnement</button>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const container = document.getElementById('fractions-container');
    const template = document.getElementById('fraction-template');
    const addBtn = document.getElementById('ajouter-fraction');
    if (!container || !template) return;

    function addFraction() {
        const index = container.children.length;
        const clone = template.content.cloneNode(true);
        clone.querySelectorAll('[data-bind]').forEach(el => {
            const name = el.getAttribute('data-bind').replace('__INDEX__', index);
            el.setAttribute('name', name);
        });
        const supprBtn = clone.querySelector('.supprimer-fraction');
        if (supprBtn) supprBtn.addEventListener('click', function() { this.closest('.fraction-ligne').remove(); });
        container.appendChild(clone);
    }

    addBtn.addEventListener('click', addFraction);
    addFraction(); // première ligne par défaut
})();
</script>