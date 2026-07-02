<?php
// routes/web.php — Toutes les routes du projet
// ============================================================================
// Format handler : 'Namespace\controllers\NomController@methode'
// Le namespace correspond au dossier modules/ (ex: utilisateurs\controllers\...)
//
// Méthodes HTTP utilisées :
//   GET  → affichage / lecture
//   POST → création, modification, suppression (HTML forms ne supportent pas PUT/DELETE)
//
// Convention URL : pluriel, minuscules, tirets pour les espaces
// ============================================================================

// ── Pages publiques (sans authentification) ──────────────────────────────────
Router::get('/',        'utilisateurs\controllers\AuthController@afficherLogin');
Router::get('/connexion',  'utilisateurs\controllers\AuthController@afficherLogin');
Router::post('/connexion', 'utilisateurs\controllers\AuthController@traiterLogin');
Router::get('/deconnexion','utilisateurs\controllers\AuthController@deconnecter');

// ── Tableau de bord (authentifié) ────────────────────────────────────────────
Router::get('/tableau-de-bord', 'utilisateurs\controllers\DashboardController@index');

// ── Accès refusé ─────────────────────────────────────────────────────────────
Router::get('/acces-refuse', 'utilisateurs\controllers\AuthController@accesRefuse');


// ============================================================================
// MODULE UTILISATEURS — Groupe 4
// ============================================================================

// Utilisateurs
Router::get( '/utilisateurs',                  'utilisateurs\controllers\UtilisateurController@index');
Router::get( '/utilisateurs/creer',            'utilisateurs\controllers\UtilisateurController@creer');
Router::post('/utilisateurs/creer',            'utilisateurs\controllers\UtilisateurController@enregistrer');
Router::get( '/utilisateurs/{id}/modifier',    'utilisateurs\controllers\UtilisateurController@editer');
Router::post('/utilisateurs/{id}/modifier',    'utilisateurs\controllers\UtilisateurController@mettreAJour');
Router::post('/utilisateurs/{id}/supprimer',   'utilisateurs\controllers\UtilisateurController@supprimer');

// Groupes d'utilisateurs
Router::get( '/groupes',                       'utilisateurs\controllers\GroupeController@index');
Router::get( '/groupes/creer',                 'utilisateurs\controllers\GroupeController@creer');
Router::post('/groupes/creer',                 'utilisateurs\controllers\GroupeController@enregistrer');
Router::get( '/groupes/{id}/modifier',         'utilisateurs\controllers\GroupeController@editer');
Router::post('/groupes/{id}/modifier',         'utilisateurs\controllers\GroupeController@mettreAJour');
Router::post('/groupes/{id}/supprimer',        'utilisateurs\controllers\GroupeController@supprimer');
Router::get( '/groupes/{id}/droits',           'utilisateurs\controllers\GroupeController@gererDroits');
Router::post('/groupes/{id}/droits',           'utilisateurs\controllers\GroupeController@enregistrerDroits');

// Droits
Router::get( '/droits',                        'utilisateurs\controllers\DroitController@index');


// ========== MODULE STRUCTURE (G3) ==========
// Familles
Router::get('/structure/familles', 'structure\controllers\FamilleController@index');
Router::get('/structure/familles/creer', 'structure\controllers\FamilleController@creer');
Router::post('/structure/familles', 'structure\controllers\FamilleController@enregistrer');
Router::get('/structure/familles/detail/{id}', 'structure\controllers\FamilleController@detail');
Router::get('/structure/familles/editer/{id}', 'structure\controllers\FamilleController@editer');
Router::post('/structure/familles/modifier/{id}', 'structure\controllers\FamilleController@mettreAJour');
Router::post('/structure/familles/supprimer/{id}', 'structure\controllers\FamilleController@supprimer');

// Produits
Router::get('/structure/produits', 'structure\controllers\ProduitController@index');
Router::get('/structure/produits/creer', 'structure\controllers\ProduitController@creer');
Router::get('/structure/produits/{id}', 'structure\controllers\ProduitController@detail');
Router::post('/structure/produits', 'structure\controllers\ProduitController@enregistrer');
Router::get('/structure/produits/editer/{id}', 'structure\controllers\ProduitController@editer');
Router::post('/structure/produits/modifier/{id}', 'structure\controllers\ProduitController@mettreAJour');
Router::post('/structure/produits/supprimer/{id}', 'structure\controllers\ProduitController@supprimer');
Router::get('/structure/produits/fractionner/{id}', 'structure\controllers\ProduitController@fractionner');
Router::post('/structure/produits/fractionner/{id}', 'structure\controllers\ProduitController@enregistrerFractionnement');

// Fournisseurs
Router::get('/structure/fournisseurs', 'structure\controllers\FournisseurController@index');
Router::get('/structure/fournisseurs/creer', 'structure\controllers\FournisseurController@creer');
Router::post('/structure/fournisseurs', 'structure\controllers\FournisseurController@enregistrer');
Router::get('/structure/fournisseurs/editer/{id}', 'structure\controllers\FournisseurController@editer');
Router::post('/structure/fournisseurs/modifier/{id}', 'structure\controllers\FournisseurController@mettreAJour');
Router::post('/structure/fournisseurs/supprimer/{id}', 'structure\controllers\FournisseurController@supprimer');

// Clients
Router::get('/structure/clients', 'structure\controllers\ClientController@index');
Router::get('/structure/clients/creer', 'structure\controllers\ClientController@creer');
Router::post('/structure/clients', 'structure\controllers\ClientController@enregistrer');
Router::get('/structure/clients/editer/{id}', 'structure\controllers\ClientController@editer');
Router::post('/structure/clients/modifier/{id}', 'structure\controllers\ClientController@mettreAJour');
Router::post('/structure/clients/supprimer/{id}', 'structure\controllers\ClientController@supprimer');

// Catégories clients 
Router::get('/structure/categories-clients', 'structure\controllers\CategorieClientController@index');
Router::get('/structure/categories-clients/creer', 'structure\controllers\CategorieClientController@creer');
Router::post('/structure/categories-clients', 'structure\controllers\CategorieClientController@enregistrer');
Router::get('/structure/categories-clients/{id}', 'structure\controllers\CategorieClientController@detail');
Router::get('/structure/categories-clients/editer/{id}', 'structure\controllers\CategorieClientController@editer');
Router::post('/structure/categories-clients/modifier/{id}', 'structure\controllers\CategorieClientController@mettreAJour');
Router::post('/structure/categories-clients/supprimer/{id}', 'structure\controllers\CategorieClientController@supprimer');

// Banques
Router::get('/structure/banques', 'structure\controllers\BanqueController@index');
Router::get('/structure/banques/creer', 'structure\controllers\BanqueController@creer');
Router::post('/structure/banques', 'structure\controllers\BanqueController@enregistrer');
Router::get('/structure/banques/editer/{id}', 'structure\controllers\BanqueController@editer');
Router::post('/structure/banques/modifier/{id}', 'structure\controllers\BanqueController@mettreAJour');
Router::post('/structure/banques/supprimer/{id}', 'structure\controllers\BanqueController@supprimer');

// Versements
Router::get('/structure/versements', 'structure\controllers\VersementController@index');
Router::get('/structure/versements/creer', 'structure\controllers\VersementController@creer');
Router::post('/structure/versements', 'structure\controllers\VersementController@enregistrer');
Router::get('/structure/versements/editer/{id}', 'structure\controllers\VersementController@editer');
Router::post('/structure/versements/modifier/{id}', 'structure\controllers\VersementController@mettreAJour');
Router::post('/structure/versements/supprimer/{id}', 'structure\controllers\VersementController@supprimer');

// ============================================================================
// MODULE APPROVISIONNEMENTS — Groupe 1
// ============================================================================

// Bons de commande fournisseur
Router::get( '/appros/commandes',                   'approvisionnements\controllers\BonCommandeController@index');
Router::get( '/appros/commandes/creer',             'approvisionnements\controllers\BonCommandeController@creer');
Router::post('/appros/commandes/creer',             'approvisionnements\controllers\BonCommandeController@enregistrer');
Router::get( '/appros/commandes/{id}',              'approvisionnements\controllers\BonCommandeController@detail');
Router::get( '/appros/commandes/{id}/modifier',     'approvisionnements\controllers\BonCommandeController@editer');
Router::post('/appros/commandes/{id}/modifier',     'approvisionnements\controllers\BonCommandeController@mettreAJour');
Router::post('/appros/commandes/{id}/valider',      'approvisionnements\controllers\BonCommandeController@valider');
Router::post('/appros/commandes/{id}/annuler',      'approvisionnements\controllers\BonCommandeController@annuler');
Router::post('/appros/commandes/{id}/supprimer',    'approvisionnements\controllers\BonCommandeController@supprimer');
Router::get( '/appros/commandes/{id}/pdf',          'approvisionnements\controllers\BonCommandeController@pdf');

// Bons de réception
Router::get( '/appros/receptions/get-commande/{id}', 'approvisionnements\controllers\BonReceptionController@getCommande');
Router::get( '/appros/receptions',             'approvisionnements\controllers\BonReceptionController@index');
Router::get( '/appros/receptions/creer',       'approvisionnements\controllers\BonReceptionController@creer');
Router::post('/appros/receptions/creer',       'approvisionnements\controllers\BonReceptionController@enregistrer');
Router::get( '/appros/receptions/{id}',        'approvisionnements\controllers\BonReceptionController@detail');
Router::get( '/appros/receptions/{id}/pdf',    'approvisionnements\controllers\BonReceptionController@pdf');

// Routes supplémentaires pour les réceptions
Router::get( '/appros/receptions/{id}/modifier', 'approvisionnements\controllers\BonReceptionController@editer');
Router::post('/appros/receptions/{id}/modifier', 'approvisionnements\controllers\BonReceptionController@mettreAJour');
Router::post('/appros/receptions/{id}/supprimer', 'approvisionnements\controllers\BonReceptionController@supprimer');
Router::get( '/appros/receptions/get-montants/{id}', 'approvisionnements\controllers\BonReceptionController@getMontants');



// Entrées dons
Router::get( '/appros/dons',                     'approvisionnements\controllers\DonController@index');
Router::get( '/appros/dons/creer',               'approvisionnements\controllers\DonController@creer');
Router::post('/appros/dons/creer',               'approvisionnements\controllers\DonController@enregistrer');
Router::get( '/appros/dons/{id}',                'approvisionnements\controllers\DonController@detail');
Router::get( '/appros/dons/{id}/modifier',       'approvisionnements\controllers\DonController@editer');
Router::post('/appros/dons/{id}/modifier',       'approvisionnements\controllers\DonController@mettreAJour');
Router::post('/appros/dons/{id}/supprimer',      'approvisionnements\controllers\DonController@supprimer');
Router::get( '/appros/dons/{id}/pdf',            'approvisionnements\controllers\DonController@pdf');


// FACTURES FOURNISSEURS

// Routes spécifiques (DOIVENT être avant la route {id})
Router::get( '/appros/factures/creer',             'approvisionnements\controllers\FactureFournisseurController@creer');
Router::post('/appros/factures/creer',             'approvisionnements\controllers\FactureFournisseurController@enregistrer');
Router::post('/appros/factures/{id}/supprimer',    'approvisionnements\controllers\FactureFournisseurController@supprimer');

// Routes génériques (avec paramètres)
Router::get( '/appros/factures',                   'approvisionnements\controllers\FactureFournisseurController@index');
Router::get( '/appros/factures/{id}',              'approvisionnements\controllers\FactureFournisseurController@detail');
Router::get( '/appros/factures/{id}/modifier',     'approvisionnements\controllers\FactureFournisseurController@editer');
Router::post('/appros/factures/{id}/modifier',     'approvisionnements\controllers\FactureFournisseurController@mettreAJour');
Router::get( '/appros/factures/{id}/pdf',          'approvisionnements\controllers\FactureFournisseurController@pdf');

// RÈGLEMENTS FOURNISSEURS

// Routes spécifiques (DOIVENT être avant les routes avec paramètres)
Router::get( '/appros/reglements/creer',           'approvisionnements\controllers\ReglementFournisseurController@creer');
Router::post('/appros/reglements/creer',           'approvisionnements\controllers\ReglementFournisseurController@enregistrer');
Router::post('/appros/reglements/{id}/supprimer',  'approvisionnements\controllers\ReglementFournisseurController@supprimer');

// Routes génériques
Router::get( '/appros/reglements',                 'approvisionnements\controllers\ReglementFournisseurController@index');
Router::get( '/appros/reglements/{id}/recu',       'approvisionnements\controllers\ReglementFournisseurController@recu');


// États / rapports

Router::get( '/appros/etats/journalier',       'approvisionnements\controllers\EtatController@journalier');
Router::get( '/appros/etats/annuel',           'approvisionnements\controllers\EtatController@annuel');
Router::get( '/appros/etats/journalier/pdf',   'approvisionnements\controllers\EtatController@journalierPdf');
Router::get( '/appros/etats/annuel/pdf',       'approvisionnements\controllers\EtatController@annuelPdf');

Router::get( '/appros/corbeille',          'approvisionnements\controllers\CorbeilleApprovisionnementController@index');
Router::post('/appros/corbeille/restaurer','approvisionnements\controllers\CorbeilleApprovisionnementController@restaurer');
Router::post('/appros/corbeille/purger',   'approvisionnements\controllers\CorbeilleApprovisionnementController@purger');

// Archives XML anciennes (rétrocompatibilité)
Router::get( '/appros/archives',                      'approvisionnements\controllers\ArchiveController@index');
Router::get( '/appros/archives/{filename}',           'approvisionnements\controllers\ArchiveController@detail');
Router::post('/appros/archives/{filename}/restaurer', 'approvisionnements\controllers\ArchiveController@restaurer');
Router::post('/appros/archives/{filename}/supprimer', 'approvisionnements\controllers\ArchiveController@supprimerArchive');


// ============================================================================
// MODULE VENTES — Groupe 2
// ============================================================================

// Commandes clients
Router::get( '/ventes/commandes',              'ventes\controllers\CommandeClientController@index');
Router::get( '/ventes/commandes/creer',        'ventes\controllers\CommandeClientController@creer');
Router::post('/ventes/commandes/creer',        'ventes\controllers\CommandeClientController@enregistrer');
Router::get( '/ventes/commandes/{id}',         'ventes\controllers\CommandeClientController@detail');
Router::get( '/ventes/commandes/{id}/modifier','ventes\controllers\CommandeClientController@editer');
Router::post('/ventes/commandes/{id}/modifier','ventes\controllers\CommandeClientController@mettreAJour');
Router::post('/ventes/commandes/{id}/supprimer','ventes\controllers\CommandeClientController@supprimer');
Router::get( '/ventes/commandes/{id}/pdf',     'ventes\controllers\CommandeClientController@pdf');

// Bons de livraison
Router::get( '/ventes/livraisons',                  'ventes\controllers\LivraisonController@index');
Router::get( '/ventes/livraisons/creer',            'ventes\controllers\LivraisonController@creer');
Router::post('/ventes/livraisons/creer',            'ventes\controllers\LivraisonController@enregistrer');
Router::get( '/ventes/livraisons/{id}',             'ventes\controllers\LivraisonController@detail');
Router::get( '/ventes/livraisons/{id}/modifier',    'ventes\controllers\LivraisonController@editer');
Router::post('/ventes/livraisons/{id}/modifier',    'ventes\controllers\LivraisonController@mettreAJour');
Router::post('/ventes/livraisons/{id}/supprimer',   'ventes\controllers\LivraisonController@supprimer');
Router::get( '/ventes/livraisons/{id}/pdf',         'ventes\controllers\LivraisonController@pdf');

// Factures clients
Router::get( '/ventes/factures',                    'ventes\controllers\FactureClientController@index');
Router::post('/ventes/factures/generer',            'ventes\controllers\FactureClientController@generer');
Router::get( '/ventes/factures/{id}',               'ventes\controllers\FactureClientController@detail');
Router::get( '/ventes/factures/{id}/modifier',      'ventes\controllers\FactureClientController@editer');
Router::post('/ventes/factures/{id}/modifier',      'ventes\controllers\FactureClientController@mettreAJour');
Router::post('/ventes/factures/{id}/supprimer',     'ventes\controllers\FactureClientController@supprimer');
Router::get( '/ventes/factures/{id}/pdf',           'ventes\controllers\FactureClientController@pdf');

// Règlements clients
Router::get( '/ventes/reglements',             'ventes\controllers\ReglementClientController@index');
Router::post('/ventes/reglements/creer',       'ventes\controllers\ReglementClientController@enregistrer');

// Ventes comptant
Router::get( '/ventes/comptant',                    'ventes\controllers\VenteComptantController@index');
Router::get( '/ventes/comptant/creer',              'ventes\controllers\VenteComptantController@creer');
Router::post('/ventes/comptant/creer',              'ventes\controllers\VenteComptantController@enregistrer');
Router::post('/ventes/comptant/{id}/supprimer',     'ventes\controllers\VenteComptantController@supprimer');
Router::get( '/ventes/comptant/{id}/pdf',           'ventes\controllers\VenteComptantController@pdf');

// Sorties produits
Router::get( '/ventes/sorties',                'ventes\controllers\BonSortieController@index');
Router::get( '/ventes/sorties/creer',          'ventes\controllers\BonSortieController@creer');
Router::post('/ventes/sorties/creer',          'ventes\controllers\BonSortieController@enregistrer');
Router::get( '/ventes/sorties/{id}/modifier',  'ventes\controllers\BonSortieController@editer');
Router::get( '/ventes/sorties/{id}/modifier',  'ventes\controllers\BonSortieController@editer');
Router::post('/ventes/sorties/{id}/supprimer',  'ventes\controllers\BonSortieController@supprimer');
Router::get( '/ventes/sorties/{id}/pdf',       'ventes\controllers\BonSortieController@pdf');

// États / rapports
Router::get( '/ventes/etats/journalier',       'ventes\controllers\EtatController@journalier');
Router::get( '/ventes/etats/annuel',           'ventes\controllers\EtatController@annuel');
Router::get( '/ventes/etats/journalier/pdf',   'ventes\controllers\EtatController@journalierPdf');
Router::get( '/ventes/etats/annuel/pdf',       'ventes\controllers\EtatController@annuelPdf');

// Corbeille (module Ventes)
Router::get( '/ventes/corbeille',              'ventes\controllers\CorbeilleVentesController@index');
Router::post('/ventes/corbeille/restaurer',    'ventes\controllers\CorbeilleVentesController@restaurer');
Router::post('/ventes/corbeille/purger',       'ventes\controllers\CorbeilleVentesController@purger');
// Paramètres de connexion (utilisateur courant)
Router::get( '/parametres',  'utilisateurs\controllers\ParametreController@index');
Router::post('/parametres',  'utilisateurs\controllers\ParametreController@mettreAJour');

// Journal d'audit (lecture seule)
Router::get('/audit', 'utilisateurs\controllers\AuditController@index');

// Corbeille (module Utilisateurs)
Router::get( '/utilisateurs/corbeille',          'utilisateurs\controllers\CorbeilleController@index');
Router::post('/utilisateurs/corbeille/restaurer','utilisateurs\controllers\CorbeilleController@restaurer');
Router::post('/utilisateurs/corbeille/purger',   'utilisateurs\controllers\CorbeilleController@purger');

// Corbeille (module Structure)
Router::get( '/structure/corbeille',          'structure\controllers\CorbeilleController@index');
Router::post('/structure/corbeille/restaurer','structure\controllers\CorbeilleController@restaurer');
Router::post('/structure/corbeille/purger',   'structure\controllers\CorbeilleController@purger');
