<?php
// modules/structure/views/produits/liste.php
// Variables : $produits (array)
?>
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Produits</h1>
            <p class="text-sm text-gray-500 mt-1"><?= count($produits) ?> produit(s) enregistré(s)</p>
        </div>
        <?php if (Auth::peut('STRUCTURE_CREER')): ?>
            <a href="<?= BASE_URL ?>/structure/produits/creer" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouveau produit
            </a>
        <?php endif; ?>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="mt-4 carte p-3">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- Recherche -->
            <div class="flex items-center gap-2 flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                <input type="search"
                       id="recherche-produit"
                       placeholder="Rechercher par code, désignation ou famille…"
                       class="input border-0 shadow-none focus:ring-0 py-1 px-0 text-sm flex-1 w-full"
                       oninput="filtrerTableau()">
            </div>
            
            <!-- Filtres -->
            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <span class="text-xs text-gray-400 font-medium">Filtres :</span>
                
                <!-- Bouton Tous -->
                <button type="button" 
                        class="btn filtre-btn px-3 py-1 text-xs rounded-full border transition-all active"
                        data-filtre="tous"
                        onclick="setFiltre('tous')">
                    Tous
                </button>
                
                <!-- Bouton Pères -->
                <button type="button" 
                        class="btn filtre-btn px-3 py-1 text-xs rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition-all"
                        data-filtre="peres"
                        onclick="setFiltre('peres')">
                    <i class="fa-solid fa-code-branch text-[10px]"></i> Pères
                </button>
                
                <!-- Bouton Fils -->
                <button type="button" 
                        class="btn filtre-btn px-3 py-1 text-xs rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition-all"
                        data-filtre="fils"
                        onclick="setFiltre('fils')">
                    <i class="fa-solid fa-sitemap text-[10px]"></i> Fils
                </button>
                
                <!-- Bouton Simples -->
                <button type="button" 
                        class="btn filtre-btn px-3 py-1 text-xs rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition-all"
                        data-filtre="simples"
                        onclick="setFiltre('simples')">
                    <i class="fa-solid fa-cube text-[10px]"></i> Simples
                </button>
            </div>
        </div>
        
        <!-- Badges de compte par catégorie -->
        <div class="flex items-center gap-4 mt-2 pt-2 border-t border-gray-100 text-xs text-gray-400">
            <span>Total : <strong class="text-gray-700" id="total-produits"><?= count($produits) ?></strong></span>
            <span class="text-gray-300">|</span>
            <span><i class="fa-solid fa-code-branch text-[10px]"></i> Pères : <strong class="text-gray-700" id="total-peres">0</strong></span>
            <span><i class="fa-solid fa-sitemap text-[10px]"></i> Fils : <strong class="text-gray-700" id="total-fils">0</strong></span>
            <span><i class="fa-solid fa-cube text-[10px]"></i> Simples : <strong class="text-gray-700" id="total-simples">0</strong></span>
        </div>
    </div>

    <!-- Tableau -->
    <div class="mt-4 table-container">
        <table class="table" id="tableau-produits">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th>Famille</th>
                    <th>Unité</th>
                    <th class="text-right">Prix vente HT</th>
                    <th class="text-right">Stock</th>
                    <th class="text-right">Type</th>
                    <th class="text-right">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produits)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-gray-400 py-12">
                            <i class="fa-solid fa-box-open text-2xl mb-2 block"></i>
                            Aucun produit enregistré
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produits as $p): 
                        // 🔥 Déterminer le type du produit de façon robuste
                        $estPere = !empty($p['est_pere']) && 
                                   ($p['est_pere'] === true || 
                                    $p['est_pere'] === 't' || 
                                    $p['est_pere'] === 1 || 
                                    $p['est_pere'] === 'true' ||
                                    $p['est_pere'] === '1');
                        
                        // Un produit est fils s'il a un parent_id non null ET différent de 0
                        // ET qu'il n'est PAS un produit père
                        $estFils = isset($p['parent_id']) && 
                                   !empty($p['parent_id']) && 
                                   (int)$p['parent_id'] > 0 && 
                                   !$estPere;
                        
                        // Déterminer le type
                        if ($estPere) {
                            $typeProduit = 'pere';
                        } elseif ($estFils) {
                            $typeProduit = 'fils';
                        } else {
                            $typeProduit = 'simple';
                        }
                        
                        $stockBas = (float)($p['stock_actuel'] ?? 0) < (float)($p['stock_minimum'] ?? 0);
                        $searchString = strtolower(
                            ($p['code'] ?? '') . ' ' . 
                            ($p['designation'] ?? '') . ' ' . 
                            ($p['famille_libelle'] ?? '')
                        );
                    ?>
                        <tr data-recherche="<?= htmlspecialchars($searchString) ?>"
                            data-type="<?= $typeProduit ?>">

                            <!-- Code -->
                            <td class="font-mono text-xs text-gray-500">
                                <?= htmlspecialchars($p['code'] ?? '') ?>
                            </td>

                            <!-- Désignation + badge -->
                            <td>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($p['designation'] ?? '') ?>
                                </span>
                            </td>

                            <!-- Famille -->
                            <td class="text-gray-600">
                                <?= htmlspecialchars($p['famille_libelle'] ?? '—') ?>
                            </td>

                            <!-- Unité -->
                            <td class="text-gray-500 text-xs">
                                <?= htmlspecialchars($p['unite'] ?? '—') ?>
                            </td>

                            <!-- Prix vente HT -->
                            <td class="text-right font-mono text-sm">
                                <?= number_format((float)($p['prix_vente_ht'] ?? 0), 0, ',', ' ') ?> 
                                <span class="text-gray-400 text-xs">FCFA</span>
                            </td>

                            <!-- Stock -->
                            <td class="text-right">
                                <span class="font-mono text-sm <?= $stockBas ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                                    <?= number_format((float)($p['stock_actuel'] ?? 0), 2, ',', ' ') ?>
                                </span>
                                <?php if ($stockBas): ?>
                                    <i class="fa-solid fa-triangle-exclamation text-red-400 ml-1 text-xs"
                                       title="Sous le stock minimum (<?= number_format((float)($p['stock_minimum'] ?? 0), 2, ',', ' ') ?>)"></i>
                                <?php endif; ?>
                            </td>

                            <!-- Type -->
                            <td class="text-right">
                                <?php if ($estPere): ?>
                                    <span class="badge badge-info ml-1">
                                        <i class="fa-solid fa-code-branch text-[9px]"></i> Père
                                    </span>
                                <?php elseif ($estFils): ?>
                                    <span class="badge badge-warning ml-1">
                                        <i class="fa-solid fa-sitemap text-[9px]"></i> Fils
                                    </span>
                                    <span class="text-xs text-gray-400 ml-1">
                                        (x<?= (int)$p['quantite_fils'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 font-medium">
                                        <i class="fa-solid fa-cube text-[10px]"></i> Simple
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Statut actif -->
                            <td class="text-right">
                                <?php 
                                    $estActif = ($p['actif'] ?? true);
                                    if ($estActif === true || $estActif === 't' || $estActif === 1 || $estActif === 'true'):
                                ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactif</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">

                                    <!-- Détail -->
                                    <a href="<?= BASE_URL ?>/structure/produits/<?= $p['id'] ?>"
                                       class="btn btn-secondary text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                                       title="Voir le détail">
                                        <i class="fa-regular fa-eye text-sm"></i>
                                    </a>

                                    <!-- Modifier -->
                                    <?php if (Auth::peut('STRUCTURE_MODIFIER')): ?>
                                        <a href="<?= BASE_URL ?>/structure/produits/editer/<?= $p['id'] ?>"
                                           class="btn btn-secondary text-green-600 hover:text-green-800 hover:bg-green-50"
                                           title="Modifier">
                                            <i class="fa-regular fa-pen-to-square text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Fractionner (uniquement si produit père) -->
                                    <?php if (Auth::peut('STRUCTURE_CREER') && $estPere): ?>
                                        <a href="<?= BASE_URL ?>/structure/produits/fractionner/<?= $p['id'] ?>"
                                           class="btn btn-secondary text-purple-600 hover:text-purple-800 hover:bg-purple-50"
                                           title="Fractionner">
                                            <i class="fa-solid fa-code-branch text-sm"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Supprimer -->
                                    <?php if (Auth::peut('STRUCTURE_SUPPRIMER')): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/structure/produits/supprimer/<?= $p['id'] ?>"
                                              class="inline"
                                              data-confirmer="Supprimer le produit « <?= htmlspecialchars($p['designation'] ?? '') ?> » ? Cette action est irréversible.">
                                            <button type="submit" class="btn btn-danger text-red-500 hover:text-red-700 hover:bg-red-50"
                                                    title="Supprimer">
                                                <i class="fa-regular fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
// ── Filtrage dynamique ─────────────────────────────────────────────
let filtreActif = 'tous';

/**
 * Met à jour les compteurs par catégorie
 */
function mettreAJourCompteurs() {
    const lignes = document.querySelectorAll('#tableau-produits tbody tr:not(.no-data)');
    let total = 0;
    let peres = 0;
    let fils = 0;
    let simples = 0;
    
    lignes.forEach(tr => {
        const type = tr.dataset.type;
        if (type === 'pere') peres++;
        else if (type === 'fils') fils++;
        else if (type === 'simple') simples++;
        total++;
    });
    
    document.getElementById('total-produits').textContent = total;
    document.getElementById('total-peres').textContent = peres;
    document.getElementById('total-fils').textContent = fils;
    document.getElementById('total-simples').textContent = simples;
}

/**
 * Filtre le tableau selon la recherche et le filtre actif
 */
function filtrerTableau() {
    const recherche = document.getElementById('recherche-produit').value.toLowerCase().trim();
    const lignes = document.querySelectorAll('#tableau-produits tbody tr:not(.no-data)');
    let visible = 0;
    
    lignes.forEach(tr => {
        const texte = tr.dataset.recherche || '';
        const type = tr.dataset.type || 'simple';
        
        // Vérifier le filtre
        let matchFiltre = true;
        if (filtreActif === 'peres') {
            matchFiltre = type === 'pere';
        } else if (filtreActif === 'fils') {
            matchFiltre = type === 'fils';
        } else if (filtreActif === 'simples') {
            matchFiltre = type === 'simple';
        }
        // 'tous' : matchFiltre reste true
        
        // Vérifier la recherche
        const matchRecherche = recherche === '' || texte.includes(recherche);
        
        if (matchFiltre && matchRecherche) {
            tr.style.display = '';
            visible++;
        } else {
            tr.style.display = 'none';
        }
    });
    
    // Afficher/masquer le message "Aucun résultat"
    let msg = document.getElementById('aucun-resultat');
    if (visible === 0) {
        if (!msg) {
            const tbody = document.querySelector('#tableau-produits tbody');
            const tr = document.createElement('tr');
            tr.id = 'aucun-resultat';
            tr.className = 'no-data';
            tr.innerHTML = `
                <td colspan="9" class="text-center text-gray-400 py-12">
                    <i class="fa-solid fa-search text-2xl mb-2 block"></i>
                    Aucun produit ne correspond à votre recherche
                </td>
            `;
            tbody.appendChild(tr);
        }
    } else {
        if (msg) msg.remove();
    }
}

/**
 * Change le filtre actif et met à jour l'interface
 */
function setFiltre(filtre) {
    filtreActif = filtre;
    
    // Mettre à jour les boutons
    document.querySelectorAll('.filtre-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-gray-900', 'text-white', 'border-gray-900');
        btn.classList.add('border-gray-200', 'text-gray-600');
        
        if (btn.dataset.filtre === filtre) {
            btn.classList.remove('border-gray-200', 'text-gray-600');
            btn.classList.add('active', 'bg-gray-900', 'text-white', 'border-gray-900');
        }
    });
    
    // Appliquer le filtre
    filtrerTableau();
}

// ── Confirmation de suppression ──────────────────────────────────
document.querySelectorAll('form[data-confirmer]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm(this.dataset.confirmer)) {
            e.preventDefault();
        }
    });
});

// ── Initialisation ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que tous les produits ont un type défini
    const lignes = document.querySelectorAll('#tableau-produits tbody tr:not(.no-data)');
    lignes.forEach(tr => {
        if (!tr.dataset.type || tr.dataset.type === '') {
            // Déterminer le type à partir des badges
            const badgePere = tr.querySelector('.badge-info');
            const badgeFils = tr.querySelector('.badge-warning');
            if (badgePere) tr.dataset.type = 'pere';
            else if (badgeFils) tr.dataset.type = 'fils';
            else tr.dataset.type = 'simple';
        }
    });
    
    mettreAJourCompteurs();
    
    // Activer le filtre "tous" par défaut
    setFiltre('tous');
    
    // Recherche en temps réel
    const input = document.getElementById('recherche-produit');
    input.addEventListener('input', filtrerTableau);
});

// ── Rendre les fonctions globales pour les événements inline ──
window.filtrerTableau = filtrerTableau;
window.setFiltre = setFiltre;
</script>

<style>
/* Styles additionnels pour les filtres */
.filtre-btn.active {
    background-color: #18181b;
    color: white;
    border-color: #18181b;
}

.filtre-btn:hover:not(.active) {
    background-color: #f4f4f5;
}

/* Badge Fils */
.badge-warning {
    background-color: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

/* Animation douce pour les lignes */
#tableau-produits tbody tr {
    transition: opacity 0.2s ease;
}
</style>