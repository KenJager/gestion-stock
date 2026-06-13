/**
 * Module Structure - Groupe 3
 */
// (function() {
//     'use strict';

//     document.addEventListener('DOMContentLoaded', function() {
//         // Mise en évidence des stocks faibles
//         document.querySelectorAll('.stock-actuel').forEach(cell => {
//             const stock = parseFloat(cell.dataset.stock);
//             const min = parseFloat(cell.dataset.stockMin);
//             if (stock < min) {
//                 cell.classList.add('structure-stock-faible');
//                 cell.title = `Stock minimum : ${min}`;
//             }
//         });
//     });
// })();

function filtrerTableau(terme) {
    terme = terme.toLowerCase().trim();
    const lignes = document.querySelectorAll('#tableau-produits tbody tr[data-recherche]');
    lignes.forEach(function(tr) {
        if (!terme || tr.dataset.recherche.includes(terme)) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}