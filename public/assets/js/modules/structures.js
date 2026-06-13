/**
 * Module Structure - Groupe 3
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Confirmation suppression
        document.querySelectorAll('.btn-supprimer').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const nom = this.dataset.nom || 'cet élément';
                if (!confirm(`Supprimer définitivement ${nom} ? (ON DELETE CASCADE)`)) {
                    e.preventDefault();
                    return;
                }
                const id = this.dataset.id;
                if (!id) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.BASE_URL + window.location.pathname.split('/').slice(0, -1).join('/') + '/supprimer/' + id;
                document.body.appendChild(form);
                form.submit();
            });
        });

        // Mise en évidence des stocks faibles
        document.querySelectorAll('.stock-actuel').forEach(cell => {
            const stock = parseFloat(cell.dataset.stock);
            const min = parseFloat(cell.dataset.stockMin);
            if (stock < min) {
                cell.classList.add('structure-stock-faible');
                cell.title = `Stock minimum : ${min}`;
            }
        });
    });
})();