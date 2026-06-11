<?php
// routes/web.php — Toutes les routes du projet
// ============================================================================
// Format handler : 'Namespace\Controllers\NomController@methode'
// Le namespace correspond au dossier modules/ (ex: Utilisateurs\Controllers\...)
//
// Méthodes HTTP utilisées :
//   GET  → affichage / lecture
//   POST → création, modification, suppression (HTML forms ne supportent pas PUT/DELETE)
//
// Convention URL : pluriel, minuscules, tirets pour les espaces
// ============================================================================

// ── Pages publiques (sans authentification) ──────────────────────────────────
Router::get('/',        'Utilisateurs\Controllers\AuthController@afficherLogin');
Router::get('/connexion',  'Utilisateurs\Controllers\AuthController@afficherLogin');
Router::post('/connexion', 'Utilisateurs\Controllers\AuthController@traiterLogin');
Router::get('/deconnexion','Utilisateurs\Controllers\AuthController@deconnecter');

// ── Tableau de bord (authentifié) ────────────────────────────────────────────
Router::get('/tableau-de-bord', 'Utilisateurs\Controllers\DashboardController@index');

// ── Accès refusé ─────────────────────────────────────────────────────────────
Router::get('/acces-refuse', 'Utilisateurs\Controllers\AuthController@accesRefuse');


// ============================================================================
// MODULE UTILISATEURS — Groupe 4
// ============================================================================

// Utilisateurs
Router::get( '/utilisateurs',                  'Utilisateurs\Controllers\UtilisateurController@index');
Router::get( '/utilisateurs/creer',            'Utilisateurs\Controllers\UtilisateurController@creer');
Router::post('/utilisateurs/creer',            'Utilisateurs\Controllers\UtilisateurController@enregistrer');
Router::get( '/utilisateurs/{id}/modifier',    'Utilisateurs\Controllers\UtilisateurController@editer');
Router::post('/utilisateurs/{id}/modifier',    'Utilisateurs\Controllers\UtilisateurController@mettreAJour');
Router::post('/utilisateurs/{id}/supprimer',   'Utilisateurs\Controllers\UtilisateurController@supprimer');

// Groupes d'utilisateurs
Router::get( '/groupes',                       'Utilisateurs\Controllers\GroupeController@index');
Router::get( '/groupes/creer',                 'Utilisateurs\Controllers\GroupeController@creer');
Router::post('/groupes/creer',                 'Utilisateurs\Controllers\GroupeController@enregistrer');
Router::get( '/groupes/{id}/modifier',         'Utilisateurs\Controllers\GroupeController@editer');
Router::post('/groupes/{id}/modifier',         'Utilisateurs\Controllers\GroupeController@mettreAJour');
Router::post('/groupes/{id}/supprimer',        'Utilisateurs\Controllers\GroupeController@supprimer');
Router::get( '/groupes/{id}/droits',           'Utilisateurs\Controllers\GroupeController@gererDroits');
Router::post('/groupes/{id}/droits',           'Utilisateurs\Controllers\GroupeController@enregistrerDroits');

// Droits
Router::get( '/droits',                        'Utilisateurs\Controllers\DroitController@index');


// ============================================================================
// MODULE STRUCTURE — Groupe 3
// ============================================================================

// Familles de produits
Router::get( '/familles',                      'Structure\Controllers\FamilleController@index');
Router::get( '/familles/creer',                'Structure\Controllers\FamilleController@creer');
Router::post('/familles/creer',                'Structure\Controllers\FamilleController@enregistrer');
Router::get( '/familles/{id}/modifier',        'Structure\Controllers\FamilleController@editer');
Router::post('/familles/{id}/modifier',        'Structure\Controllers\FamilleController@mettreAJour');
Router::post('/familles/{id}/supprimer',       'Structure\Controllers\FamilleController@supprimer');

// Produits
Router::get( '/produits',                      'Structure\Controllers\ProduitController@index');
Router::get( '/produits/creer',                'Structure\Controllers\ProduitController@creer');
Router::post('/produits/creer',                'Structure\Controllers\ProduitController@enregistrer');
Router::get( '/produits/{id}',                 'Structure\Controllers\ProduitController@detail');
Router::get( '/produits/{id}/modifier',        'Structure\Controllers\ProduitController@editer');
Router::post('/produits/{id}/modifier',        'Structure\Controllers\ProduitController@mettreAJour');
Router::post('/produits/{id}/supprimer',       'Structure\Controllers\ProduitController@supprimer');
Router::get( '/produits/{id}/fractionner',     'Structure\Controllers\ProduitController@fractionner');
Router::post('/produits/{id}/fractionner',     'Structure\Controllers\ProduitController@enregistrerFraction');

// Fournisseurs
Router::get( '/fournisseurs',                  'Structure\Controllers\FournisseurController@index');
Router::get( '/fournisseurs/creer',            'Structure\Controllers\FournisseurController@creer');
Router::post('/fournisseurs/creer',            'Structure\Controllers\FournisseurController@enregistrer');
Router::get( '/fournisseurs/{id}/modifier',    'Structure\Controllers\FournisseurController@editer');
Router::post('/fournisseurs/{id}/modifier',    'Structure\Controllers\FournisseurController@mettreAJour');
Router::post('/fournisseurs/{id}/supprimer',   'Structure\Controllers\FournisseurController@supprimer');

// Clients
Router::get( '/clients',                       'Structure\Controllers\ClientController@index');
Router::get( '/clients/creer',                 'Structure\Controllers\ClientController@creer');
Router::post('/clients/creer',                 'Structure\Controllers\ClientController@enregistrer');
Router::get( '/clients/{id}/modifier',         'Structure\Controllers\ClientController@editer');
Router::post('/clients/{id}/modifier',         'Structure\Controllers\ClientController@mettreAJour');
Router::post('/clients/{id}/supprimer',        'Structure\Controllers\ClientController@supprimer');

// Catégories clients
Router::get( '/categories-clients',            'Structure\Controllers\CategorieClientController@index');
Router::get( '/categories-clients/creer',      'Structure\Controllers\CategorieClientController@creer');
Router::post('/categories-clients/creer',      'Structure\Controllers\CategorieClientController@enregistrer');
Router::get( '/categories-clients/{id}/modifier', 'Structure\Controllers\CategorieClientController@editer');
Router::post('/categories-clients/{id}/modifier', 'Structure\Controllers\CategorieClientController@mettreAJour');
Router::post('/categories-clients/{id}/supprimer','Structure\Controllers\CategorieClientController@supprimer');

// Banques
Router::get( '/banques',                       'Structure\Controllers\BanqueController@index');
Router::get( '/banques/creer',                 'Structure\Controllers\BanqueController@creer');
Router::post('/banques/creer',                 'Structure\Controllers\BanqueController@enregistrer');
Router::get( '/banques/{id}/modifier',         'Structure\Controllers\BanqueController@editer');
Router::post('/banques/{id}/modifier',         'Structure\Controllers\BanqueController@mettreAJour');
Router::post('/banques/{id}/supprimer',        'Structure\Controllers\BanqueController@supprimer');

// Versements
Router::get( '/versements',                    'Structure\Controllers\VersementController@index');
Router::get( '/versements/creer',              'Structure\Controllers\VersementController@creer');
Router::post('/versements/creer',              'Structure\Controllers\VersementController@enregistrer');


// ============================================================================
// MODULE APPROVISIONNEMENTS — Groupe 1
// ============================================================================

// Bons de commande fournisseur
Router::get( '/appros/commandes',              'Approvisionnements\Controllers\BonCommandeController@index');
Router::get( '/appros/commandes/creer',        'Approvisionnements\Controllers\BonCommandeController@creer');
Router::post('/appros/commandes/creer',        'Approvisionnements\Controllers\BonCommandeController@enregistrer');
Router::get( '/appros/commandes/{id}',         'Approvisionnements\Controllers\BonCommandeController@detail');
Router::get( '/appros/commandes/{id}/modifier','Approvisionnements\Controllers\BonCommandeController@editer');
Router::post('/appros/commandes/{id}/modifier','Approvisionnements\Controllers\BonCommandeController@mettreAJour');
Router::post('/appros/commandes/{id}/supprimer','Approvisionnements\Controllers\BonCommandeController@supprimer');
Router::get( '/appros/commandes/{id}/pdf',     'Approvisionnements\Controllers\BonCommandeController@pdf');

// Bons de réception
Router::get( '/appros/receptions',             'Approvisionnements\Controllers\BonReceptionController@index');
Router::get( '/appros/receptions/creer',       'Approvisionnements\Controllers\BonReceptionController@creer');
Router::post('/appros/receptions/creer',       'Approvisionnements\Controllers\BonReceptionController@enregistrer');
Router::get( '/appros/receptions/{id}',        'Approvisionnements\Controllers\BonReceptionController@detail');
Router::get( '/appros/receptions/{id}/pdf',    'Approvisionnements\Controllers\BonReceptionController@pdf');

// Factures fournisseurs
Router::get( '/appros/factures',               'Approvisionnements\Controllers\FactureFournisseurController@index');
Router::get( '/appros/factures/{id}',          'Approvisionnements\Controllers\FactureFournisseurController@detail');
Router::get( '/appros/factures/{id}/modifier', 'Approvisionnements\Controllers\FactureFournisseurController@editer');
Router::post('/appros/factures/{id}/modifier', 'Approvisionnements\Controllers\FactureFournisseurController@mettreAJour');
Router::get( '/appros/factures/{id}/pdf',      'Approvisionnements\Controllers\FactureFournisseurController@pdf');

// Règlements fournisseurs
Router::get( '/appros/reglements',             'Approvisionnements\Controllers\ReglementFournisseurController@index');
Router::post('/appros/reglements/creer',       'Approvisionnements\Controllers\ReglementFournisseurController@enregistrer');
Router::get( '/appros/reglements/{id}/recu',   'Approvisionnements\Controllers\ReglementFournisseurController@recu');

// Entrées dons
Router::get( '/appros/dons',                   'Approvisionnements\Controllers\DonController@index');
Router::get( '/appros/dons/creer',             'Approvisionnements\Controllers\DonController@creer');
Router::post('/appros/dons/creer',             'Approvisionnements\Controllers\DonController@enregistrer');
Router::get( '/appros/dons/{id}/pdf',          'Approvisionnements\Controllers\DonController@pdf');

// Bons d'entrée
Router::get( '/appros/bons-entree',            'Approvisionnements\Controllers\BonEntreeController@index');
Router::get( '/appros/bons-entree/{id}/pdf',   'Approvisionnements\Controllers\BonEntreeController@pdf');

// États / rapports
Router::get( '/appros/etats/journalier',       'Approvisionnements\Controllers\EtatController@journalier');
Router::get( '/appros/etats/annuel',           'Approvisionnements\Controllers\EtatController@annuel');
Router::get( '/appros/etats/journalier/pdf',   'Approvisionnements\Controllers\EtatController@journalierPdf');
Router::get( '/appros/etats/annuel/pdf',       'Approvisionnements\Controllers\EtatController@annuelPdf');


// ============================================================================
// MODULE VENTES — Groupe 2
// ============================================================================

// Commandes clients
Router::get( '/ventes/commandes',              'Ventes\Controllers\CommandeClientController@index');
Router::get( '/ventes/commandes/creer',        'Ventes\Controllers\CommandeClientController@creer');
Router::post('/ventes/commandes/creer',        'Ventes\Controllers\CommandeClientController@enregistrer');
Router::get( '/ventes/commandes/{id}',         'Ventes\Controllers\CommandeClientController@detail');
Router::get( '/ventes/commandes/{id}/modifier','Ventes\Controllers\CommandeClientController@editer');
Router::post('/ventes/commandes/{id}/modifier','Ventes\Controllers\CommandeClientController@mettreAJour');
Router::post('/ventes/commandes/{id}/supprimer','Ventes\Controllers\CommandeClientController@supprimer');
Router::get( '/ventes/commandes/{id}/pdf',     'Ventes\Controllers\CommandeClientController@pdf');

// Bons de livraison
Router::get( '/ventes/livraisons',             'Ventes\Controllers\LivraisonController@index');
Router::get( '/ventes/livraisons/creer',       'Ventes\Controllers\LivraisonController@creer');
Router::post('/ventes/livraisons/creer',       'Ventes\Controllers\LivraisonController@enregistrer');
Router::get( '/ventes/livraisons/{id}',        'Ventes\Controllers\LivraisonController@detail');
Router::get( '/ventes/livraisons/{id}/modifier','Ventes\Controllers\LivraisonController@editer');
Router::post('/ventes/livraisons/{id}/modifier','Ventes\Controllers\LivraisonController@mettreAJour');
Router::get( '/ventes/livraisons/{id}/pdf',    'Ventes\Controllers\LivraisonController@pdf');

// Factures clients
Router::get( '/ventes/factures',               'Ventes\Controllers\FactureClientController@index');
Router::get( '/ventes/factures/{id}',          'Ventes\Controllers\FactureClientController@detail');
Router::get( '/ventes/factures/{id}/modifier', 'Ventes\Controllers\FactureClientController@editer');
Router::post('/ventes/factures/{id}/modifier', 'Ventes\Controllers\FactureClientController@mettreAJour');
Router::get( '/ventes/factures/{id}/pdf',      'Ventes\Controllers\FactureClientController@pdf');

// Règlements clients
Router::get( '/ventes/reglements',             'Ventes\Controllers\ReglementClientController@index');
Router::post('/ventes/reglements/creer',       'Ventes\Controllers\ReglementClientController@enregistrer');

// Ventes comptant
Router::get( '/ventes/comptant',               'Ventes\Controllers\VenteComptantController@index');
Router::get( '/ventes/comptant/creer',         'Ventes\Controllers\VenteComptantController@creer');
Router::post('/ventes/comptant/creer',         'Ventes\Controllers\VenteComptantController@enregistrer');
Router::get( '/ventes/comptant/{id}/pdf',      'Ventes\Controllers\VenteComptantController@pdf');

// Sorties produits
Router::get( '/ventes/sorties',                'Ventes\Controllers\BonSortieController@index');
Router::get( '/ventes/sorties/creer',          'Ventes\Controllers\BonSortieController@creer');
Router::post('/ventes/sorties/creer',          'Ventes\Controllers\BonSortieController@enregistrer');
Router::get( '/ventes/sorties/{id}/modifier',  'Ventes\Controllers\BonSortieController@editer');
Router::post('/ventes/sorties/{id}/modifier',  'Ventes\Controllers\BonSortieController@mettreAJour');
Router::get( '/ventes/sorties/{id}/pdf',       'Ventes\Controllers\BonSortieController@pdf');

// États / rapports
Router::get( '/ventes/etats/journalier',       'Ventes\Controllers\EtatController@journalier');
Router::get( '/ventes/etats/annuel',           'Ventes\Controllers\EtatController@annuel');
Router::get( '/ventes/etats/journalier/pdf',   'Ventes\Controllers\EtatController@journalierPdf');
Router::get( '/ventes/etats/annuel/pdf',       'Ventes\Controllers\EtatController@annuelPdf');