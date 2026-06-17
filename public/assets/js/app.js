/**
 * app.js — Scripts globaux SGestion
 * Vanilla JS uniquement
 */

'use strict';

// ============================================================
// 1. ALERTES — disparition automatique
// ============================================================
(function gererAlertes() {
    const alertes = document.querySelectorAll('[data-alerte-auto]');
    alertes.forEach(function(el) {
        const duree = parseInt(el.dataset.alerteAuto, 10) || 4000;
        setTimeout(function() {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';
            setTimeout(function() { el.remove(); }, 400);
        }, duree);
    });

    document.querySelectorAll('[data-fermer-alerte]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const alerte = btn.closest('[role="alert"]') || btn.parentElement;
            alerte.style.transition = 'opacity 0.2s ease';
            alerte.style.opacity = '0';
            setTimeout(function() { alerte.remove(); }, 200);
        });
    });
})();

// ============================================================
// 2. CONFIRMATION AVANT SUPPRESSION (VERSION CORRIGÉE)
// ============================================================
(function gererSuppression() {
    // Utiliser la phase de capture pour être SÛR d'intercepter AVANT les autres scripts
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Vérifie si le formulaire a l'attribut data-confirmer
        if (!form.hasAttribute('data-confirmer')) return;
        
        const msg = form.getAttribute('data-confirmer');
        
        // AFFICHER LA POPUP DE CONFIRMATION
        const confirmation = confirm(msg);
        
        if (!confirmation) {
            // L'utilisateur a annulé - on bloque l'envoi
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        // L'utilisateur a confirmé - le formulaire sera soumis normalement
        return true;
    }, true); // true = phase de capture (exécuté en premier)
})();

// ============================================================
// 3. LIGNES DE TABLEAU DYNAMIQUES
// ============================================================
(function gererLignesDynamiques() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-ajouter-ligne]');
        if (!btn) return;

        const cible = btn.dataset.ajouterLigne;
        const tbody = document.querySelector(cible);
        const idModele = btn.dataset.modele;
        const modele = document.querySelector(idModele);
        if (!tbody || !modele) return;

        const index = tbody.querySelectorAll('tr').length;
        let html = modele.innerHTML;
        html = html.replaceAll('__INDEX__', index);

        const tr = document.createElement('tr');
        tr.innerHTML = html;
        tbody.appendChild(tr);

        const premierChamp = tr.querySelector('input, select');
        if (premierChamp) premierChamp.focus();
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-supprimer-ligne]');
        if (!btn) return;
        const tr = btn.closest('tr');
        if (tr) tr.remove();
    });
})();

// ============================================================
// 4. CALCUL AUTOMATIQUE DES TOTAUX
// ============================================================
(function calculerTotaux() {
    function calculerLigne(tr) {
        const qte = parseFloat(tr.querySelector('[data-qte]')?.value) || 0;
        const pu = parseFloat(tr.querySelector('[data-pu]')?.value) || 0;
        const tva = parseFloat(tr.querySelector('[data-tva]')?.value) || 0;
        const remise = parseFloat(tr.querySelector('[data-remise]')?.value) || 0;

        const ht = qte * pu * (1 - remise / 100);
        const ttc = ht * (1 + tva / 100);

        const champHT = tr.querySelector('[data-total-ht]');
        const champTTC = tr.querySelector('[data-total-ttc]');
        if (champHT) champHT.textContent = formaterMontant(ht);
        if (champTTC) champTTC.textContent = formaterMontant(ttc);

        return { ht, ttc };
    }

    function recalculerTable(table) {
        let totalHT = 0, totalTTC = 0;
        table.querySelectorAll('tbody tr').forEach(function(tr) {
            const t = calculerLigne(tr);
            totalHT += t.ht;
            totalTTC += t.ttc;
        });
        const pHt = table.closest('form')?.querySelector('[data-grand-total-ht]');
        const pTtc = table.closest('form')?.querySelector('[data-grand-total-ttc]');
        if (pHt) pHt.textContent = formaterMontant(totalHT);
        if (pTtc) pTtc.textContent = formaterMontant(totalTTC);
    }

    document.addEventListener('input', function(e) {
        const champ = e.target;
        if (!champ.matches('[data-qte],[data-pu],[data-tva],[data-remise]')) return;
        const tr = champ.closest('tr');
        const table = champ.closest('table');
        if (tr) calculerLigne(tr);
        if (table) recalculerTable(table);
    });
})();

// ============================================================
// 5. UTILITAIRES GLOBAUX
// ============================================================

function formaterMontant(montant) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Math.round(montant)) + ' FCFA';
}

function formaterDate(dateISO) {
    if (!dateISO) return '—';
    const [a, m, j] = dateISO.split('-');
    return `${j}/${m}/${a}`;
}

function afficherFlash(message, type) {
    type = type || 'info';
    const conteneur = document.getElementById('flash-messages');
    if (!conteneur) return;

    const classes = {
        succes: 'alert-success',
        erreur: 'alert-error',
        info: 'alert-info',
        avertissement: 'alert-warning'
    };
    const icones = {
        succes: 'fa-circle-check',
        erreur: 'fa-circle-xmark',
        info: 'fa-circle-info',
        avertissement: 'fa-triangle-exclamation'
    };

    const div = document.createElement('div');
    div.setAttribute('role', 'alert');
    div.className = (classes[type] || 'alert-info') + ' alert';
    div.dataset.alerteAuto = '4000';
    div.innerHTML = `
        <i class="fa-solid ${icones[type] || 'fa-circle-info'}"></i>
        <span style="flex:1">${message}</span>
        <button data-fermer-alerte style="background:none;border:none;cursor:pointer;opacity:0.6">
            <i class="fa-solid fa-xmark"></i>
        </button>`;
    conteneur.appendChild(div);
}

window.SGestion = { formaterMontant, formaterDate, afficherFlash };

// ============================================================
// 6. SIDEBAR RESPONSIVE (mobile/tablette)
// ============================================================
(function gererSidebarResponsive() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menu-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const overlay = document.getElementById('sidebar-overlay');
    
    // Fonction pour ouvrir la sidebar
    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Empêcher le scroll du body
    }
    
    // Fonction pour fermer la sidebar
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurer le scroll
    }
    
    // Événement : clic sur le bouton hamburger
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebar();
        });
    }
    
    // Événement : clic sur le bouton fermeture
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }
    
    // Événement : clic sur l'overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Fermer la sidebar lors du clic sur un lien (navigation)
    document.querySelectorAll('[data-nav-close]').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });
    
    // Réinitialiser l'état de la sidebar lors du redimensionnement
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                // Sur desktop, s'assurer que la sidebar est visible
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }, 150);
    });
    
    // Fermer la sidebar avec la touche ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
})();

// ============================================================
// 7. GESTION DES DROPDOWNS (version améliorée avec fermeture auto sur mobile)
// ============================================================
(function gererDropdowns() {
    // Sauvegarder l'état des modules ouverts dans un cookie
    function sauvegarderEtat() {
        const modulesOuverts = [];
        document.querySelectorAll('.nav-dropdown-trigger.open').forEach(trigger => {
            const module = trigger.dataset.module;
            if (module) modulesOuverts.push(module);
        });
        document.cookie = `sidebar_ouvert=${modulesOuverts.join(',')}; path=/; max-age=2592000`;
    }
    
    // Ouvrir/fermer un dropdown
    function toggleDropdown(trigger) {
        const module = trigger.dataset.module;
        const container = document.querySelector(`.submenu-container[data-module="${module}"]`);
        if (!container) return;
        
        const isOpen = trigger.classList.contains('open');
        
        if (isOpen) {
            trigger.classList.remove('open');
            container.style.display = 'none';
        } else {
            trigger.classList.add('open');
            container.style.display = 'block';
        }
        
        sauvegarderEtat();
    }
    
    // Attacher les événements
    document.querySelectorAll('.nav-dropdown-trigger').forEach(trigger => {
        // Supprimer l'ancien listener pour éviter les doublons
        trigger.removeEventListener('click', dropdownHandler);
        trigger.addEventListener('click', dropdownHandler);
    });
    
    function dropdownHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown(this);
    }
})();