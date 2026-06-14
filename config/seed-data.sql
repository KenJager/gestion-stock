-- =============================================================================
-- SCRIPT DE SEED DATA - COMPLET (UTILISATEURS & STRUCTURE)
-- Base de données : PostgreSQL (Basé sur sql-new.sql & admin_account.sql)
-- =============================================================================

BEGIN;

-- Désactivation temporaire des triggers pour éviter les blocages de clés étrangères lors du nettoyage
SET CONSTRAINTS ALL DEFERRED;

-- Nettoyage complet des tables pour travailler sur des données propres
TRUNCATE public.versements_banque, public.fractions_produit, public.produits, 
         public.familles_produits, public.clients, public.categories_clients, 
         public.fournisseurs, public.banques, public.groupes_droits, 
         public.utilisateurs, public.groupes_utilisateurs, public.droits 
         RESTART IDENTITY CASCADE;

-- =============================================================================
-- 1. CONFIGURATION DU MODULE UTILISATEURS & DROITS (admin_account.sql)
-- =============================================================================

-- Injection des droits d'accès standard
INSERT INTO public.droits (code, module, description) VALUES
('APPRO_LIRE', 'APPROVISIONNEMENTS', 'Consulter les approvisionnements'),
('APPRO_CREER', 'APPROVISIONNEMENTS', 'Créer bons de commande, réceptions, factures fournisseurs'),
('APPRO_MODIFIER', 'APPROVISIONNEMENTS', 'Modifier les documents d''approvisionnement'),
('APPRO_SUPRIMMER', 'APPROVISIONNEMENTS', 'Supprimer des documents (cascade)'),
('STRUCTURE_LIRE', 'STRUCTURE', 'Consulter les référentiels'),
('STRUCTURE_CREER', 'STRUCTURE', 'Créer des entités (famille, produit, fournisseur, client, banque)'),
('STRUCTURE_MODIFIER', 'STRUCTURE', 'Modifier les entités'),
('STRUCTURE_SUPRIMMER', 'STRUCTURE', 'Supprimer des entités (cascade)'),
('USERS_LIRE', 'UTILISATEURS', 'Consulter les utilisateurs et groupes'),
('USERS_CREER', 'UTILISATEURS', 'Créer des utilisateurs / groupes'),
('USERS_MODIFIER', 'UTILISATEURS', 'Modifier utilisateurs / groupes'),
('USERS_SUPRIMMER', 'UTILISATEURS', 'Supprimer utilisateurs / groupes'),
('AUDIT_LIRE', 'UTILISATEURS', 'Consulter les logs d''audit'),
('VENTE_LIRE', 'VENTES', 'Consulter les ventes'),
('VENTE_CREER', 'VENTES', 'Créer commandes clients, factures, ventes comptant'),
('VENTE_MODIFIER', 'VENTES', 'Modifier les documents de vente'),
('VENTE_SUPRIMMER', 'VENTES', 'Supprimer des documents de vente (cascade)');

-- Création du groupe Administrateurs
INSERT INTO public.groupes_utilisateurs (nom, description) 
VALUES ('ADMINISTRATEURS', 'Super administrateur - tous droits');

-- Association de tous les privilèges applicatifs au groupe admin
INSERT INTO public.groupes_droits (groupe_id, droit_id)
SELECT (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'ADMINISTRATEURS'), id FROM public.droits;

-- Création du compte utilisateur Administrateur ('admin' / 'admin123')
INSERT INTO public.utilisateurs (nom, prenom, login, mot_de_passe, email, groupe_id, actif)
VALUES (
    'Admin', 
    'Système', 
    'admin', 
    '$2y$10$mC.vXW3.U6CqUdfV5C8VDeR3f6yG9LOnF0B1W3P6S8bK9W7X2gEqS', -- Hash Bcrypt standard pour le mot de passe 'admin123'
    'admin@gestion-stock.local', 
    (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'ADMINISTRATEURS'), 
    true
);


-- =============================================================================
-- 2. RÉFÉRENTIELS LOGISTIQUES & FINANCIERS (Banques & Fournisseurs)
-- =============================================================================

-- Banques de l'entreprise
INSERT INTO public.banques (code, nom, adresse, telephone, numero_compte) VALUES
('AFT-01', 'Afriland First Bank', 'Place de l''Indépendance, Yaoundé', '+237 222 22 17 77', 'CM21-10005-00089-45612378902-89'),
('SGC-02', 'Société Générale Cameroun', 'Boulevard de la Liberté, Douala', '+237 233 43 00 00', 'CM21-10003-00012-98765432109-12'),
('BIC-03', 'BICEC Cameroun', 'Avenue Charles de Gaulle, Yaoundé', '+237 222 23 45 67', 'CM21-10001-00045-12345678901-45');

-- Fournisseurs de marchandises
INSERT INTO public.fournisseurs (code, nom, rccm, niu, adresse, telephone, email, solde_initial) VALUES
('FOU-SABC', 'SOCIETE DES BRASSERIES DU CAMEROUN (SABC)', 'RC/DLA/1948/B/210', 'M014800000123A', 'Rue Ndokoti, Douala', '+237 233 41 55 55', 'contact@sabc-cm.com', 0.00),
('FOU-NESTLE', 'NESTLE CAMEROUN S.A.', 'RC/DLA/1965/B/950', 'M056500000456B', 'Zone Industrielle de Bassa, Douala', '+237 233 42 67 89', 'appro@cm.nestle.com', 150000.00),
('FOU-GLOBAL', 'DISTRIBUTEUR GLOBAL SARL', 'RC/YAO/2015/B/4510', 'M091500001789C', 'Marché Central, Yaoundé', '+237 222 21 02 03', 'commercial@global-sarl.cm', 0.00);


-- =============================================================================
-- 3. SEGMENTATION & RÉFÉRENTIEL CLIENTS (categories_clients & clients)
-- =============================================================================

-- Catégories de clients (Typologie tarifaire ou commerciale)
INSERT INTO public.categories_clients (code, libelle, description) VALUES
('CAT-COMPT', 'Client Comptant / Passant', 'Vente directe au comptoir sans ligne de crédit ouverte'),
('CAT-GROS', 'Grossistes & Distributeurs', 'Achat en gros volume avec conditions de crédit préférentielles'),
('CAT-VIP', 'Clients Institutionnels / VIP', 'Grands comptes d''entreprises ou partenariats récurrents');

-- Clients
INSERT INTO public.clients (code, nom, categorie_client_id, niu, adresse, telephone, email, plafond_credit, solde_initial) VALUES
('CLI-COMPTANT', 'CLIENT COMPTANT', (SELECT id FROM public.categories_clients WHERE code = 'CAT-COMPT'), NULL, 'Boutique / Comptoir', NULL, NULL, 0.00, 0.00),
('CLI-TOGO', 'ETS TOGO ET FRERES', (SELECT id FROM public.categories_clients WHERE code = 'CAT-GROS'), 'M021400034561X', 'Mvan, Yaoundé', '+237 677 88 99 00', 'togo.freres@yahoo.fr', 750000.00, 25000.00),
('CLI-CENTRE', 'SUPERMARCHE DU CENTRE', (SELECT id FROM public.categories_clients WHERE code = 'CAT-VIP'), 'M089500012478Y', 'Chambre d''agriculture, Yaoundé', '+237 699 55 44 33', 'direction@sm-centre.cm', 2000000.00, 0.00);


-- =============================================================================
-- 4. STRUCTURE DES ARTICLES (familles_produits, produits & fractions_produit)
-- =============================================================================

-- Familles de produits
INSERT INTO public.familles_produits (code, libelle, description) VALUES
('FAM-BOISSON', 'Boissons & Rafraîchissements', 'Bières, jus de fruits, eaux minérales et sodas'),
('FAM-ALIM', 'Produits Alimentaires Secs', 'Épicerie sèche, lait en poudre, cafés et céréales'),
('FAM-HYG', 'Hygiène & Entretien', 'Savons, détergents et consommables de nettoyage');

-- Produits (Simples, Pères et Fils fractionnés)
-- 4.1. Produits Simples (Sans aucune liaison à une table pivot)
INSERT INTO public.produits (code, designation, famille_id, est_pere, unite, prix_achat_ht, prix_vente_ht, tva, stock_actuel, stock_minimum, actif) VALUES
('PROD-SIMP-SEL', 'Sac de Sel de Cuisine 25kg', (SELECT id FROM public.familles_produits WHERE code = 'FAM-ALIM'), false, 'Sac', 7500.00, 9500.00, 19.25, 40.00, 5.00, true),
('PROD-SIMP-SAV', 'Savon Liquide Entretien 5L', (SELECT id FROM public.familles_produits WHERE code = 'FAM-HYG'), false, 'Bidon', 2800.00, 3500.00, 19.25, 15.00, 3.00, true);

-- 4.2. Produits PÈRES (Packages / Conditionnements de gros)
INSERT INTO public.produits (code, designation, famille_id, est_pere, unite, prix_achat_ht, prix_vente_ht, tva, stock_actuel, stock_minimum, actif) VALUES
('CARTON-VIMTO', 'Carton de Jus Vimto (12 Canettes)', (SELECT id FROM public.familles_produits WHERE code = 'FAM-BOISSON'), true, 'Carton', 4200.00, 5400.00, 19.25, 20.00, 2.00, true),
('PALETTE-EAU', 'Palette Eau Minérale Tangui (6 Packs)', (SELECT id FROM public.familles_produits WHERE code = 'FAM-BOISSON'), true, 'Palette', 9000.00, 11500.00, 19.25, 10.00, 1.00, true),
('CARTON-NIDO', 'Carton Lait Nido 400g (24 Boîtes)', (SELECT id FROM public.familles_produits WHERE code = 'FAM-ALIM'), true, 'Carton', 48000.00, 57600.00, 0.00, 5.00, 1.00, true);

-- 4.3. Produits FILS (Sous-unités issues du fractionnement / désassemblage)
INSERT INTO public.produits (code, designation, famille_id, est_pere, unite, prix_achat_ht, prix_vente_ht, tva, stock_actuel, stock_minimum, actif) VALUES
('CAN-VIMTO', 'Canette Vimto unitaire', (SELECT id FROM public.familles_produits WHERE code = 'FAM-BOISSON'), false, 'Canette', 350.00, 500.00, 19.25, 120.00, 24.00, true),
('PACK-EAU', 'Pack Eau Minérale Tangui (6 Bouteilles)', (SELECT id FROM public.familles_produits WHERE code = 'FAM-BOISSON'), false, 'Pack', 1500.00, 2000.00, 19.25, 30.00, 6.00, true),
('BOITE-NIDO', 'Boîte unitaire Lait Nido 400g', (SELECT id FROM public.familles_produits WHERE code = 'FAM-ALIM'), false, 'Boîte', 2000.00, 2600.00, 0.00, 48.00, 12.00, true);

-- 4.4. Liaisons de Fractionnement dans la table pivot (Liaison Pères -> Fils)
INSERT INTO public.fractions_produit (produit_pere_id, produit_fils_id, quantite_fils) VALUES
((SELECT id FROM public.produits WHERE code = 'CARTON-VIMTO'), (SELECT id FROM public.produits WHERE code = 'CAN-VIMTO'), 12),
((SELECT id FROM public.produits WHERE code = 'PALETTE-EAU'),  (SELECT id FROM public.produits WHERE code = 'PACK-EAU'), 6),
((SELECT id FROM public.produits WHERE code = 'CARTON-NIDO'),  (SELECT id FROM public.produits WHERE code = 'BOITE-NIDO'), 24);


-- =============================================================================
-- 5. SUIVI DE LA TRÉSORERIE BANCAIRE (versements_banque)
-- =============================================================================

INSERT INTO public.versements_banque (banque_id, montant, date_versement, reference, motif, utilisateur_id) VALUES
(
    (SELECT id FROM public.banques WHERE code = 'AFT-01'), 
    2500000.00, 
    NOW() - INTERVAL '5 days', 
    'VRST-2026-001', 
    'Versement des fonds de caisse - Ventes semaine 23', 
    (SELECT id FROM public.utilisateurs WHERE login = 'admin')
),
(
    (SELECT id FROM public.banques WHERE code = 'SGC-02'), 
    1200000.00, 
    NOW() - INTERVAL '2 days', 
    'CHQ-NESTLE-441', 
    'Approvisionnement compte courant pour facture Nestlé', 
    (SELECT id FROM public.utilisateurs WHERE login = 'admin')
);


-- =============================================================================
-- MISE À JOUR ET ALIGNEMENT DES SÉQUENCES POSTGRESQL (Évite les bugs d'auto-incrément)
-- =============================================================================
SELECT setval('public.droits_id_seq', COALESCE((SELECT MAX(id) FROM public.droits), 1));
SELECT setval('public.groupes_utilisateurs_id_seq', COALESCE((SELECT MAX(id) FROM public.groupes_utilisateurs), 1));
SELECT setval('public.utilisateurs_id_seq', COALESCE((SELECT MAX(id) FROM public.utilisateurs), 1));
SELECT setval('public.banques_id_seq', COALESCE((SELECT MAX(id) FROM public.banques), 1));
SELECT setval('public.fournisseurs_id_seq', COALESCE((SELECT MAX(id) FROM public.fournisseurs), 1));
SELECT setval('public.categories_clients_id_seq', COALESCE((SELECT MAX(id) FROM public.categories_clients), 1));
SELECT setval('public.clients_id_seq', COALESCE((SELECT MAX(id) FROM public.clients), 1));
SELECT setval('public.familles_produits_id_seq', COALESCE((SELECT MAX(id) FROM public.familles_produits), 1));
SELECT setval('public.produits_id_seq', COALESCE((SELECT MAX(id) FROM public.produits), 1));
SELECT setval('public.versements_banque_id_seq', COALESCE((SELECT MAX(id) FROM public.versements_banque), 1));

COMMIT;