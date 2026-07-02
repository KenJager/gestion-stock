<?php
// routes/web.php — Toutes les routes du projet
// ============================================================================
// Format handler : 'Namespace\controllers\NomController@methode'
// Le namespace correspond au dossier modules/ (ex: utilisateurs\traitements\controllers\...)
//
// Méthodes HTTP utilisées :
//   GET  → affichage / lecture
//   POST → création, modification, suppression (HTML forms ne supportent pas PUT/DELETE)
//
// Convention URL : pluriel, minuscules, tirets pour les espaces
// ============================================================================

// ── Pages publiques (sans authentification) ──────────────────────────────────
Router::get('/',        'utilisateurs\traitements\controllers\AuthController@afficherLogin');
Router::get('/connexion',  'utilisateurs\traitements\controllers\AuthController@afficherLogin');
Router::post('/connexion', 'utilisateurs\traitements\controllers\AuthController@traiterLogin');
Router::get('/deconnexion','utilisateurs\traitements\controllers\AuthController@deconnecter');

// ── Tableau de bord (authentifié) ────────────────────────────────────────────
Router::get('/tableau-de-bord', 'utilisateurs\traitements\controllers\DashboardController@index');

// ── Accès refusé ─────────────────────────────────────────────────────────────
Router::get('/acces-refuse', 'utilisateurs\traitements\controllers\AuthController@accesRefuse');


// ============================================================================
// MODULE UTILISATEURS — Groupe 4
// ============================================================================

// Utilisateurs
Router::get( '/utilisateurs',                  'utilisateurs\traitements\controllers\UtilisateurController@index');
Router::get( '/utilisateurs/creer',            'utilisateurs\traitements\controllers\UtilisateurController@creer');
Router::post('/utilisateurs/creer',            'utilisateurs\traitements\controllers\UtilisateurController@enregistrer');
Router::get( '/utilisateurs/{id}/modifier',    'utilisateurs\traitements\controllers\UtilisateurController@editer');
Router::post('/utilisateurs/{id}/modifier',    'utilisateurs\traitements\controllers\UtilisateurController@mettreAJour');
Router::post('/utilisateurs/{id}/supprimer',   'utilisateurs\traitements\controllers\UtilisateurController@supprimer');

// Groupes d'utilisateurs
Router::get( '/groupes',                       'utilisateurs\traitements\controllers\GroupeController@index');
Router::get( '/groupes/creer',                 'utilisateurs\traitements\controllers\GroupeController@creer');
Router::post('/groupes/creer',                 'utilisateurs\traitements\controllers\GroupeController@enregistrer');
Router::get( '/groupes/{id}/modifier',         'utilisateurs\traitements\controllers\GroupeController@editer');
Router::post('/groupes/{id}/modifier',         'utilisateurs\traitements\controllers\GroupeController@mettreAJour');
Router::post('/groupes/{id}/supprimer',        'utilisateurs\traitements\controllers\GroupeController@supprimer');
Router::get( '/groupes/{id}/droits',           'utilisateurs\traitements\controllers\GroupeController@gererDroits');
Router::post('/groupes/{id}/droits',           'utilisateurs\traitements\controllers\GroupeController@enregistrerDroits');

// Droits
Router::get( '/droits',                        'utilisateurs\traitements\controllers\DroitController@index');


// ========== MODULE STRUCTURE (G3) ==========
// Familles
Router::get('/structure/familles', 'structure\traitements\controllers\FamilleController@index');
Router::get('/structure/familles/creer', 'structure\traitements\controllers\FamilleController@creer');
Router::post('/structure/familles', 'structure\traitements\controllers\FamilleController@enregistrer');
Router::get('/structure/familles/detail/{id}', 'structure\traitements\controllers\FamilleController@detail');
Router::get('/structure/familles/editer/{id}', 'structure\traitements\controllers\FamilleController@editer');
Router::post('/structure/familles/modifier/{id}', 'structure\traitements\controllers\FamilleController@mettreAJour');
Router::post('/structure/familles/supprimer/{id}', 'structure\traitements\controllers\FamilleController@supprimer');

// Produits
Router::get('/structure/produits', 'structure\traitements\controllers\ProduitController@index');
Router::get('/structure/produits/creer', 'structure\traitements\controllers\ProduitController@creer');
Router::get('/structure/produits/{id}', 'structure\traitements\controllers\ProduitController@detail');
Router::post('/structure/produits', 'structure\traitements\controllers\ProduitController@enregistrer');
Router::get('/structure/produits/editer/{id}', 'structure\traitements\controllers\ProduitController@editer');
Router::post('/structure/produits/modifier/{id}', 'structure\traitements\controllers\ProduitController@mettreAJour');
Router::post('/structure/produits/supprimer/{id}', 'structure\traitements\controllers\ProduitController@supprimer');
Router::get('/structure/produits/fractionner/{id}', 'structure\traitements\controllers\ProduitController@fractionner');
Router::post('/structure/produits/fractionner/{id}', 'structure\traitements\controllers\ProduitController@enregistrerFractionnement');

// Fournisseurs
Router::get('/structure/fournisseurs', 'structure\traitements\controllers\FournisseurController@index');
Router::get('/structure/fournisseurs/creer', 'structure\traitements\controllers\FournisseurController@creer');
Router::post('/structure/fournisseurs', 'structure\traitements\controllers\FournisseurController@enregistrer');
Router::get('/structure/fournisseurs/editer/{id}', 'structure\traitements\controllers\FournisseurController@editer');
Router::post('/structure/fournisseurs/modifier/{id}', 'structure\traitements\controllers\FournisseurController@mettreAJour');
Router::post('/structure/fournisseurs/supprimer/{id}', 'structure\traitements\controllers\FournisseurController@supprimer');

// Clients
Router::get('/structure/clients', 'structure\traitements\controllers\ClientController@index');
Router::get('/structure/clients/creer', 'structure\traitements\controllers\ClientController@creer');
Router::post('/structure/clients', 'structure\traitements\controllers\ClientController@enregistrer');
Router::get('/structure/clients/editer/{id}', 'structure\traitements\controllers\ClientController@editer');
Router::post('/structure/clients/modifier/{id}', 'structure\traitements\controllers\ClientController@mettreAJour');
Router::post('/structure/clients/supprimer/{id}', 'structure\traitements\controllers\ClientController@supprimer');

// Catégories clients 
Router::get('/structure/categories-clients', 'structure\traitements\controllers\CategorieClientController@index');
Router::get('/structure/categories-clients/creer', 'structure\traitements\controllers\CategorieClientController@creer');
Router::post('/structure/categories-clients', 'structure\traitements\controllers\CategorieClientController@enregistrer');
Router::get('/structure/categories-clients/{id}', 'structure\traitements\controllers\CategorieClientController@detail');
Router::get('/structure/categories-clients/editer/{id}', 'structure\traitements\controllers\CategorieClientController@editer');
Router::post('/structure/categories-clients/modifier/{id}', 'structure\traitements\controllers\CategorieClientController@mettreAJour');
Router::post('/structure/categories-clients/supprimer/{id}', 'structure\traitements\controllers\CategorieClientController@supprimer');

// Banques
Router::get('/structure/banques', 'structure\traitements\controllers\BanqueController@index');
Router::get('/structure/banques/creer', 'structure\traitements\controllers\BanqueController@creer');
Router::post('/structure/banques', 'structure\traitements\controllers\BanqueController@enregistrer');
Router::get('/structure/banques/editer/{id}', 'structure\traitements\controllers\BanqueController@editer');
Router::post('/structure/banques/modifier/{id}', 'structure\traitements\controllers\BanqueController@mettreAJour');
Router::post('/structure/banques/supprimer/{id}', 'structure\traitements\controllers\BanqueController@supprimer');

// Versements
Router::get('/structure/versements', 'structure\traitements\controllers\VersementController@index');
Router::get('/structure/versements/creer', 'structure\traitements\controllers\VersementController@creer');
Router::post('/structure/versements', 'structure\traitements\controllers\VersementController@enregistrer');
Router::get('/structure/versements/editer/{id}', 'structure\traitements\controllers\VersementController@editer');
Router::post('/structure/versements/modifier/{id}', 'structure\traitements\controllers\VersementController@mettreAJour');
Router::post('/structure/versements/supprimer/{id}', 'structure\traitements\controllers\VersementController@supprimer');

// ============================================================================
// MODULE APPROVISIONNEMENTS — Groupe 1
// ============================================================================

// Bons de commande fournisseur
Router::get( '/appros/commandes',                   'approvisionnements\traitements\controllers\BonCommandeController@index');
Router::get( '/appros/commandes/creer',             'approvisionnements\traitements\controllers\BonCommandeController@creer');
Router::post('/appros/commandes/creer',             'approvisionnements\traitements\controllers\BonCommandeController@enregistrer');
Router::get( '/appros/commandes/{id}',              'approvisionnements\traitements\controllers\BonCommandeController@detail');
Router::get( '/appros/commandes/{id}/modifier',     'approvisionnements\traitements\controllers\BonCommandeController@editer');
Router::post('/appros/commandes/{id}/modifier',     'approvisionnements\traitements\controllers\BonCommandeController@mettreAJour');
Router::post('/appros/commandes/{id}/valider',      'approvisionnements\traitements\controllers\BonCommandeController@valider');
Router::post('/appros/commandes/{id}/annuler',      'approvisionnements\traitements\controllers\BonCommandeController@annuler');
Router::post('/appros/commandes/{id}/supprimer',    'approvisionnements\traitements\controllers\BonCommandeController@supprimer');
Router::get( '/appros/commandes/{id}/pdf',          'approvisionnements\traitements\controllers\BonCommandeController@pdf');

// Bons de réception
Router::get( '/appros/receptions/get-commande/{id}', 'approvisionnements\traitements\controllers\BonReceptionController@getCommande');
Router::get( '/appros/receptions',             'approvisionnements\traitements\controllers\BonReceptionController@index');
Router::get( '/appros/receptions/creer',       'approvisionnements\traitements\controllers\BonReceptionController@creer');
Router::post('/appros/receptions/creer',       'approvisionnements\traitements\controllers\BonReceptionController@enregistrer');
Router::get( '/appros/receptions/{id}',        'approvisionnements\traitements\controllers\BonReceptionController@detail');
Router::get( '/appros/receptions/{id}/pdf',    'approvisionnements\traitements\controllers\BonReceptionController@pdf');

// Routes supplémentaires pour les réceptions
Router::get( '/appros/receptions/{id}/modifier', 'approvisionnements\traitements\controllers\BonReceptionController@editer');
Router::post('/appros/receptions/{id}/modifier', 'approvisionnements\traitements\controllers\BonReceptionController@mettreAJour');
Router::post('/appros/receptions/{id}/supprimer', 'approvisionnements\traitements\controllers\BonReceptionController@supprimer');
Router::get( '/appros/receptions/get-montants/{id}', 'approvisionnements\traitements\controllers\BonReceptionController@getMontants');



// Entrées dons
Router::get( '/appros/dons',                     'approvisionnements\traitements\controllers\DonController@index');
Router::get( '/appros/dons/creer',               'approvisionnements\traitements\controllers\DonController@creer');
Router::post('/appros/dons/creer',               'approvisionnements\traitements\controllers\DonController@enregistrer');
Router::get( '/appros/dons/{id}',                'approvisionnements\traitements\controllers\DonController@detail');
Router::get( '/appros/dons/{id}/modifier',       'approvisionnements\traitements\controllers\DonController@editer');
Router::post('/appros/dons/{id}/modifier',       'approvisionnements\traitements\controllers\DonController@mettreAJour');
Router::post('/appros/dons/{id}/supprimer',      'approvisionnements\traitements\controllers\DonController@supprimer');
Router::get( '/appros/dons/{id}/pdf',            'approvisionnements\traitements\controllers\DonController@pdf');


// FACTURES FOURNISSEURS

// Routes spécifiques (DOIVENT être avant la route {id})
Router::get( '/appros/factures/creer',             'approvisionnements\traitements\controllers\FactureFournisseurController@creer');
Router::post('/appros/factures/creer',             'approvisionnements\traitements\controllers\FactureFournisseurController@enregistrer');
Router::post('/appros/factures/{id}/supprimer',    'approvisionnements\traitements\controllers\FactureFournisseurController@supprimer');

// Routes génériques (avec paramètres)
Router::get( '/appros/factures',                   'approvisionnements\traitements\controllers\FactureFournisseurController@index');
Router::get( '/appros/factures/{id}',              'approvisionnements\traitements\controllers\FactureFournisseurController@detail');
Router::get( '/appros/factures/{id}/modifier',     'approvisionnements\traitements\controllers\FactureFournisseurController@editer');
Router::post('/appros/factures/{id}/modifier',     'approvisionnements\traitements\controllers\FactureFournisseurController@mettreAJour');
Router::get( '/appros/factures/{id}/pdf',          'approvisionnements\traitements\controllers\FactureFournisseurController@pdf');

// RÈGLEMENTS FOURNISSEURS

// Routes spécifiques (DOIVENT être avant les routes avec paramètres)
Router::get( '/appros/reglements/creer',           'approvisionnements\traitements\controllers\ReglementFournisseurController@creer');
Router::post('/appros/reglements/creer',           'approvisionnements\traitements\controllers\ReglementFournisseurController@enregistrer');
Router::post('/appros/reglements/{id}/supprimer',  'approvisionnements\traitements\controllers\ReglementFournisseurController@supprimer');

// Routes génériques
Router::get( '/appros/reglements',                 'approvisionnements\traitements\controllers\ReglementFournisseurController@index');
Router::get( '/appros/reglements/{id}/recu',       'approvisionnements\traitements\controllers\ReglementFournisseurController@recu');


// États / rapports

Router::get( '/appros/etats/journalier',       'approvisionnements\traitements\controllers\EtatController@journalier');
Router::get( '/appros/etats/annuel',           'approvisionnements\traitements\controllers\EtatController@annuel');
Router::get( '/appros/etats/journalier/pdf',   'approvisionnements\traitements\controllers\EtatController@journalierPdf');
Router::get( '/appros/etats/annuel/pdf',       'approvisionnements\traitements\controllers\EtatController@annuelPdf');

Router::get( '/appros/corbeille',          'approvisionnements\traitements\controllers\CorbeilleApprovisionnementController@index');
Router::post('/appros/corbeille/restaurer','approvisionnements\traitements\controllers\CorbeilleApprovisionnementController@restaurer');
Router::post('/appros/corbeille/purger',   'approvisionnements\traitements\controllers\CorbeilleApprovisionnementController@purger');

// Archives XML anciennes (rétrocompatibilité)
Router::get( '/appros/archives',                      'approvisionnements\traitements\controllers\ArchiveController@index');
Router::get( '/appros/archives/{filename}',           'approvisionnements\traitements\controllers\ArchiveController@detail');
Router::post('/appros/archives/{filename}/restaurer', 'approvisionnements\traitements\controllers\ArchiveController@restaurer');
Router::post('/appros/archives/{filename}/supprimer', 'approvisionnements\traitements\controllers\ArchiveController@supprimerArchive');


// ============================================================================
// MODULE VENTES — Groupe 2
// ============================================================================

// Commandes clients
Router::get( '/ventes/commandes',              'ventes\traitements\controllers\CommandeClientController@index');
Router::get( '/ventes/commandes/creer',        'ventes\traitements\controllers\CommandeClientController@creer');
Router::post('/ventes/commandes/creer',        'ventes\traitements\controllers\CommandeClientController@enregistrer');
Router::get( '/ventes/commandes/{id}',         'ventes\traitements\controllers\CommandeClientController@detail');
Router::get( '/ventes/commandes/{id}/modifier','ventes\traitements\controllers\CommandeClientController@editer');
Router::post('/ventes/commandes/{id}/modifier','ventes\traitements\controllers\CommandeClientController@mettreAJour');
Router::post('/ventes/commandes/{id}/supprimer','ventes\traitements\controllers\CommandeClientController@supprimer');
Router::get( '/ventes/commandes/{id}/pdf',     'ventes\traitements\controllers\CommandeClientController@pdf');

// Bons de livraison
Router::get( '/ventes/livraisons',                  'ventes\traitements\controllers\LivraisonController@index');
Router::get( '/ventes/livraisons/creer',            'ventes\traitements\controllers\LivraisonController@creer');
Router::post('/ventes/livraisons/creer',            'ventes\traitements\controllers\LivraisonController@enregistrer');
Router::get( '/ventes/livraisons/{id}',             'ventes\traitements\controllers\LivraisonController@detail');
Router::get( '/ventes/livraisons/{id}/modifier',    'ventes\traitements\controllers\LivraisonController@editer');
Router::post('/ventes/livraisons/{id}/modifier',    'ventes\traitements\controllers\LivraisonController@mettreAJour');
Router::post('/ventes/livraisons/{id}/supprimer',   'ventes\traitements\controllers\LivraisonController@supprimer');
Router::get( '/ventes/livraisons/{id}/pdf',         'ventes\traitements\controllers\LivraisonController@pdf');

// Factures clients
Router::get( '/ventes/factures',                    'ventes\traitements\controllers\FactureClientController@index');
Router::post('/ventes/factures/generer',            'ventes\traitements\controllers\FactureClientController@generer');
Router::get( '/ventes/factures/{id}',               'ventes\traitements\controllers\FactureClientController@detail');
Router::get( '/ventes/factures/{id}/modifier',      'ventes\traitements\controllers\FactureClientController@editer');
Router::post('/ventes/factures/{id}/modifier',      'ventes\traitements\controllers\FactureClientController@mettreAJour');
Router::post('/ventes/factures/{id}/supprimer',     'ventes\traitements\controllers\FactureClientController@supprimer');
Router::get( '/ventes/factures/{id}/pdf',           'ventes\traitements\controllers\FactureClientController@pdf');

// Règlements clients
Router::get( '/ventes/reglements',             'ventes\traitements\controllers\ReglementClientController@index');
Router::post('/ventes/reglements/creer',       'ventes\traitements\controllers\ReglementClientController@enregistrer');

// Ventes comptant
Router::get( '/ventes/comptant',                    'ventes\traitements\controllers\VenteComptantController@index');
Router::get( '/ventes/comptant/creer',              'ventes\traitements\controllers\VenteComptantController@creer');
Router::post('/ventes/comptant/creer',              'ventes\traitements\controllers\VenteComptantController@enregistrer');
Router::post('/ventes/comptant/{id}/supprimer',     'ventes\traitements\controllers\VenteComptantController@supprimer');
Router::get( '/ventes/comptant/{id}/pdf',           'ventes\traitements\controllers\VenteComptantController@pdf');

// Sorties produits
Router::get( '/ventes/sorties',                'ventes\traitements\controllers\BonSortieController@index');
Router::get( '/ventes/sorties/creer',          'ventes\traitements\controllers\BonSortieController@creer');
Router::post('/ventes/sorties/creer',          'ventes\traitements\controllers\BonSortieController@enregistrer');
Router::get( '/ventes/sorties/{id}/modifier',  'ventes\traitements\controllers\BonSortieController@editer');
Router::get( '/ventes/sorties/{id}/modifier',  'ventes\traitements\controllers\BonSortieController@editer');
Router::post('/ventes/sorties/{id}/supprimer',  'ventes\traitements\controllers\BonSortieController@supprimer');
Router::get( '/ventes/sorties/{id}/pdf',       'ventes\traitements\controllers\BonSortieController@pdf');

// États / rapports
Router::get( '/ventes/etats/journalier',       'ventes\traitements\controllers\EtatController@journalier');
Router::get( '/ventes/etats/annuel',           'ventes\traitements\controllers\EtatController@annuel');
Router::get( '/ventes/etats/journalier/pdf',   'ventes\traitements\controllers\EtatController@journalierPdf');
Router::get( '/ventes/etats/annuel/pdf',       'ventes\traitements\controllers\EtatController@annuelPdf');

// Corbeille (module Ventes)
Router::get( '/ventes/corbeille',              'ventes\traitements\controllers\CorbeilleVentesController@index');
Router::post('/ventes/corbeille/restaurer',    'ventes\traitements\controllers\CorbeilleVentesController@restaurer');
Router::post('/ventes/corbeille/purger',       'ventes\traitements\controllers\CorbeilleVentesController@purger');
// Paramètres de connexion (utilisateur courant)
Router::get( '/parametres',  'utilisateurs\traitements\controllers\ParametreController@index');
Router::post('/parametres',  'utilisateurs\traitements\controllers\ParametreController@mettreAJour');

// Journal d'audit (lecture seule)
Router::get('/audit', 'utilisateurs\traitements\controllers\AuditController@index');

// Corbeille (module Utilisateurs)
Router::get( '/utilisateurs/corbeille',          'utilisateurs\traitements\controllers\CorbeilleController@index');
Router::post('/utilisateurs/corbeille/restaurer','utilisateurs\traitements\controllers\CorbeilleController@restaurer');
Router::post('/utilisateurs/corbeille/purger',   'utilisateurs\traitements\controllers\CorbeilleController@purger');

// Corbeille (module Structure)
Router::get( '/structure/corbeille',          'structure\traitements\controllers\CorbeilleController@index');
Router::post('/structure/corbeille/restaurer','structure\traitements\controllers\CorbeilleController@restaurer');
Router::post('/structure/corbeille/purger',   'structure\traitements\controllers\CorbeilleController@purger');
