-- ============================================================================
-- SCRIPT COMPLET D'INITIALISATION - 4 GROUPES UNIQUEMENT
-- ============================================================================

BEGIN;

-- ============================================================================
-- 1. CRÉATION DES GROUPES (4 groupes uniquement)
-- ============================================================================

INSERT INTO public.groupes_utilisateurs (nom, description) VALUES
('ADMINISTRATEURS', 'Super administrateur - tous droits d''accès'),
('VENDEURS', 'Personnel affecté au comptoir et suivi client'),
('LOGISTICIENS', 'Gestionnaires de stock et des approvisionnements'),
('COMPTABILITE', 'Gestion des factures et règlements')
ON CONFLICT (nom) DO NOTHING;

-- ============================================================================
-- 2. CRÉATION DES UTILISATEURS
-- ============================================================================

INSERT INTO public.utilisateurs (nom, prenom, login, mot_de_passe, email, groupe_id, actif) VALUES
('SALIH', 'Ken', 'admin', '$2a$10$Fsad3QmJoKQgBYoXku0IR.gkoLAI4VV.4OUOKsFQwtQFvQQ2cPsce', 'k.admin@stock.cm', 
    (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'ADMINISTRATEURS'), true),
('FOKOU', 'Marie', 'vendeur', '$2a$10$Fsad3QmJoKQgBYoXku0IR.gkoLAI4VV.4OUOKsFQwtQFvQQ2cPsce', 'm.fokou@stock.cm', 
    (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'VENDEURS'), true),
('KAMGA', 'Jean', 'logis', '$2a$10$Fsad3QmJoKQgBYoXku0IR.gkoLAI4VV.4OUOKsFQwtQFvQQ2cPsce', 'j.kamga@stock.cm', 
    (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'LOGISTICIENS'), true),
('NDONGMO', 'Claire', 'compta', '$2a$10$Fsad3QmJoKQgBYoXku0IR.gkoLAI4VV.4OUOKsFQwtQFvQQ2cPsce', 'c.ndongmo@stock.cm', 
    (SELECT id FROM public.groupes_utilisateurs WHERE nom = 'COMPTABILITE'), true)
ON CONFLICT (login) DO NOTHING;

-- ============================================================================
-- 3. CRÉATION DES DROITS (4 modules)
-- ============================================================================

INSERT INTO public.droits (code, module, description) VALUES
-- Module STRUCTURE
('STRUCTURE_LIRE', 'STRUCTURE', 'Consulter les référentiels de base'),
('STRUCTURE_CREER', 'STRUCTURE', 'Créer des entités'),
('STRUCTURE_MODIFIER', 'STRUCTURE', 'Modifier des entités'),
('STRUCTURE_SUPPRIMER', 'STRUCTURE', 'Supprimer des entités'),
-- Module VENTES
('VENTE_LIRE', 'VENTES', 'Consulter les ventes'),
('VENTE_CREER', 'VENTES', 'Créer des ventes et factures clients'),
('VENTE_MODIFIER', 'VENTES', 'Modifier des ventes'),
('VENTE_SUPPRIMER', 'VENTES', 'Supprimer des documents de vente'),
-- Module APPROVISIONNEMENTS
('APPRO_LIRE', 'APPROVISIONNEMENTS', 'Consulter les approvisionnements'),
('APPRO_CREER', 'APPROVISIONNEMENTS', 'Créer des bons de commande fournisseur'),
('APPRO_MODIFIER', 'APPROVISIONNEMENTS', 'Modifier des commandes fournisseur'),
('APPRO_SUPPRIMER', 'APPROVISIONNEMENTS', 'Supprimer des documents d''approvisionnement'),
-- Module STOCK
('STOCK_LIRE', 'STOCK', 'Consulter les mouvements de stock'),
('STOCK_ENTREE', 'STOCK', 'Gérer les entrées de stock'),
('STOCK_SORTIE', 'STOCK', 'Gérer les sorties de stock'),
('STOCK_INVENTAIRE', 'STOCK', 'Réaliser des inventaires'),
-- Module COMPTABILITE
('COMPTA_LIRE', 'COMPTABILITE', 'Consulter la comptabilité'),
('COMPTA_VALIDER', 'COMPTABILITE', 'Valider les écritures comptables'),
('COMPTA_ECRITURE', 'COMPTABILITE', 'Créer des écritures manuelles'),
-- Module UTILISATEURS
('USERS_LIRE', 'UTILISATEURS', 'Consulter les utilisateurs et groupes'),
('USERS_CREER', 'UTILISATEURS', 'Créer des utilisateurs / groupes'),
('USERS_MODIFIER', 'UTILISATEURS', 'Modifier utilisateurs / groupes'),
('USERS_SUPPRIMER', 'UTILISATEURS', 'Supprimer utilisateurs / groupes'),
('AUDIT_LIRE', 'UTILISATEURS', 'Consulter les logs d''audit')
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 4. ASSOCIATION DES DROITS AUX GROUPES
-- ============================================================================

-- ADMINISTRATEURS : TOUS les droits
INSERT INTO public.groupes_droits (groupe_id, droit_id)
SELECT g.id, d.id
FROM public.groupes_utilisateurs g
CROSS JOIN public.droits d
WHERE g.nom = 'ADMINISTRATEURS'
ON CONFLICT (groupe_id, droit_id) DO NOTHING;

-- VENDEURS : Ventes + Lecture stock + Lecture structure
INSERT INTO public.groupes_droits (groupe_id, droit_id)
SELECT g.id, d.id
FROM public.groupes_utilisateurs g
CROSS JOIN public.droits d
WHERE g.nom = 'VENDEURS' 
  AND d.code IN ('STRUCTURE_LIRE', 'VENTE_LIRE', 'VENTE_CREER', 'VENTE_MODIFIER', 'VENTE_SUPPRIMER', 'STOCK_LIRE')
ON CONFLICT (groupe_id, droit_id) DO NOTHING;

-- LOGISTICIENS : Approvisionnements + Stock (entrée/sortie) + Lecture structure
INSERT INTO public.groupes_droits (groupe_id, droit_id)
SELECT g.id, d.id
FROM public.groupes_utilisateurs g
CROSS JOIN public.droits d
WHERE g.nom = 'LOGISTICIENS' 
  AND d.code IN ('STRUCTURE_LIRE', 'APPRO_LIRE', 'APPRO_CREER', 'APPRO_MODIFIER', 'APPRO_SUPPRIMER', 'STOCK_LIRE', 'STOCK_ENTREE', 'STOCK_SORTIE', 'STOCK_INVENTAIRE')
ON CONFLICT (groupe_id, droit_id) DO NOTHING;

-- COMPTABILITE : Lecture seule (tous modules) + Validation comptable
INSERT INTO public.groupes_droits (groupe_id, droit_id)
SELECT g.id, d.id
FROM public.groupes_utilisateurs g
CROSS JOIN public.droits d
WHERE g.nom = 'COMPTABILITE' 
  AND d.code IN ('STRUCTURE_LIRE', 'VENTE_LIRE', 'APPRO_LIRE', 'STOCK_LIRE', 'COMPTA_LIRE', 'COMPTA_VALIDER', 'USERS_LIRE', 'AUDIT_LIRE')
ON CONFLICT (groupe_id, droit_id) DO NOTHING;

-- ============================================================================
-- 5. BANQUES
-- ============================================================================

INSERT INTO public.banques (code, nom, adresse, telephone, numero_compte) VALUES
('AFRILAND', 'Afriland First Bank', 'Place de l''Indépendance, Yaoundé', '+237 222 23 30 68', '10005-00001-12345678901-45'),
('BICEC', 'BICEC Cameroun', 'Avenue Général de Gaulle, Douala', '+237 233 43 60 00', '10001-00002-98765432109-88'),
('SCB', 'Société Camerounaise de Banque', 'Boulevard du 20 Mai, Yaoundé', '+237 222 22 44 55', '10004-00003-45678912301-12'),
('UBA', 'United Bank for Africa', 'Rue Joffre, Douala', '+237 233 42 58 00', '10005-00004-78912345601-33')
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 6. CATÉGORIES CLIENTS
-- ============================================================================

INSERT INTO public.categories_clients (code, libelle, remise_pct, description) VALUES
('GROSSISTE', 'Clients Grossistes / Revendeurs', 10.00, 'Acheteurs en grande quantité, remise de 10%'),
('DETAIL', 'Clients de détail', 0.00, 'Acheteurs standards au comptoir'),
('PARTENAIRE', 'Partenaires privilégiés', 15.00, 'Partenaires commerciaux avec remise exceptionnelle'),
('ETAT', 'Administrations publiques', 0.00, 'Services de l''État et collectivités')
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 7. CLIENTS
-- ============================================================================

INSERT INTO public.clients (code, raison_sociale, contact, telephone, email, adresse, ville, pays, categorie_id, actif) VALUES
('CLI-001', 'Établissements FOUMAN & Fils', 'M. Fouman Pierre', '+237 677 11 22 33', 'contact@fouman.cm', 'Marché Central', 'Yaoundé', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'GROSSISTE'), true),
('CLI-002', 'SOCIETE NOUVELLE DE DISTRIBUTION', 'Mme. Etoa Alice', '+237 699 44 55 66', 'etoa@snd.cm', 'Akwa', 'Douala', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'GROSSISTE'), true),
('CLI-003', 'Client Comptoir Standard', 'Anonyme', NULL, NULL, 'Bafoussam Centre', 'Bafoussam', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'DETAIL'), true),
('CLI-004', 'SUPERMARCHE DE MFOUNDI', 'M. Ndi Marc', '+237 677 88 99 00', 'contact@supermarchemfoundi.cm', 'Mfoundi, Yaoundé', 'Yaoundé', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'GROSSISTE'), true),
('CLI-005', 'HOPITAL CENTRAL DE YAOUNDE', 'Dr. Mbarga', '+237 222 23 45 67', 'achats@hcy.cm', 'Hôpital Central', 'Yaoundé', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'ETAT'), true),
('CLI-006', 'PARTENAIRE Sarl', 'M. Essomba', '+237 655 44 33 22', 'contact@partenaire.cm', 'Bastos', 'Yaoundé', 'Cameroun', 
    (SELECT id FROM public.categories_clients WHERE code = 'PARTENAIRE'), true)
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 8. FOURNISSEURS
-- ============================================================================

INSERT INTO public.fournisseurs (code, raison_sociale, contact, telephone, email, adresse, ville, pays, actif) VALUES
('FOUR-001', 'CAMLAIT S.A.', 'Service Commercial', '+237 233 42 43 44', 'commandes@camlait.cm', 'Zone Industrielle Bassa', 'Douala', 'Cameroun', true),
('FOUR-002', 'CHOCOCAM', 'Direction des ventes', '+237 233 37 01 00', 'sales@chococam.cm', 'Quartier Ndokoti', 'Douala', 'Cameroun', true),
('FOUR-003', 'BOLLORE LOGISTICS', 'Service Clients', '+237 233 43 11 11', 'douala@bollore.com', 'Quai de l''Ouest', 'Douala', 'Cameroun', true),
('FOUR-004', 'BRASSERIES DU CAMEROUN', 'Commercial', '+237 233 45 67 89', 'commandes@brasseries.cm', 'Route de Bonabéri', 'Douala', 'Cameroun', true),
('FOUR-005', 'NESTLE CAMEROUN', 'Service Client', '+237 222 21 11 11', 'nestle@nestle.cm', 'Boulevard de la Liberté', 'Douala', 'Cameroun', true),
('FOUR-006', 'SABC (S.A. Brasseries)', 'Commercial', '+237 233 46 00 00', 'sabc@sabc.cm', 'Bonabéri', 'Douala', 'Cameroun', true)
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 9. FAMILLES DE PRODUITS
-- ============================================================================

INSERT INTO public.familles_produits (code, libelle, description) VALUES
('LAITIER', 'Produits Laitiers', 'Laits, yaourts, fromages, beurre'),
('CHOCO', 'Confiseries & Chocolats', 'Tablettes, tartines, poudres de cacao'),
('BOISSONS', 'Boissons', 'Jus, sodas, eaux, bières'),
('CONSERVE', 'Conserves', 'Poissons, légumes, sauces'),
('HYGIENE', 'Hygiène et entretien', 'Savons, détergents, produits ménagers')
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 10. PRODUITS
-- ============================================================================

INSERT INTO public.produits (code, designation, famille_id, est_pere, unite, prix_achat_ht, prix_vente_ht, tva, stock_actuel, stock_minimum, actif, date_peremption) VALUES
('LAIT-CARTON', 'Carton de Lait Bonnet Rouge (40 boîtes)', 
    (SELECT id FROM public.familles_produits WHERE code = 'LAITIER'), true, 'Carton', 16000.00, 19500.00, 19.25, 50.000, 5.000, true, '2026-12-31'),
('LAIT-BOITE', 'Boîte de Lait Bonnet Rouge 160g', 
    (SELECT id FROM public.familles_produits WHERE code = 'LAITIER'), false, 'Boîte', 400.00, 550.00, 19.25, 250.000, 20.000, true, '2026-12-31'),
('YAOURT', 'Yaourt Nature sucré 500g', 
    (SELECT id FROM public.familles_produits WHERE code = 'LAITIER'), false, 'Pot', 300.00, 450.00, 19.25, 100.000, 15.000, true, '2026-09-30'),
('BEURRE', 'Beurre Doux 250g', 
    (SELECT id FROM public.familles_produits WHERE code = 'LAITIER'), false, 'Plaquette', 800.00, 1200.00, 19.25, 80.000, 10.000, true, '2026-08-31'),
('CHOC-TART-1KG', 'Chocolat Mambo à tartiner 1kg', 
    (SELECT id FROM public.familles_produits WHERE code = 'CHOCO'), false, 'Pot', 2500.00, 3200.00, 19.25, 30.000, 3.000, true, '2027-01-31'),
('CHOC-TAB-100G', 'Tablette Chocolat Noir 100g', 
    (SELECT id FROM public.familles_produits WHERE code = 'CHOCO'), false, 'Tablette', 250.00, 400.00, 19.25, 200.000, 50.000, true, '2027-02-28'),
('CHOC-POUDRE', 'Poudre de Cacao sucrée 500g', 
    (SELECT id FROM public.familles_produits WHERE code = 'CHOCO'), false, 'Boîte', 600.00, 900.00, 19.25, 60.000, 10.000, true, '2027-01-31'),
('JUS-ORANGE', 'Jus d''Orange 1L', 
    (SELECT id FROM public.familles_produits WHERE code = 'BOISSONS'), false, 'Brique', 500.00, 750.00, 19.25, 150.000, 30.000, true, '2026-11-30'),
('EAU-MINALE', 'Eau Minérale 1.5L', 
    (SELECT id FROM public.familles_produits WHERE code = 'BOISSONS'), false, 'Bouteille', 200.00, 350.00, 19.25, 500.000, 100.000, true, '2027-03-31'),
('BIERE-33CL', 'Bière 33cl', 
    (SELECT id FROM public.familles_produits WHERE code = 'BOISSONS'), false, 'Canette', 300.00, 500.00, 19.25, 300.000, 50.000, true, '2026-10-31'),
('THON', 'Thon entier naturel 250g', 
    (SELECT id FROM public.familles_produits WHERE code = 'CONSERVE'), false, 'Boîte', 800.00, 1200.00, 19.25, 80.000, 15.000, true, '2027-04-30'),
('SARDINE', 'Sardines à l''huile 125g', 
    (SELECT id FROM public.familles_produits WHERE code = 'CONSERVE'), false, 'Boîte', 350.00, 550.00, 19.25, 120.000, 25.000, true, '2027-05-31'),
('MAIS-DOUX', 'Maïs doux en grains 200g', 
    (SELECT id FROM public.familles_produits WHERE code = 'CONSERVE'), false, 'Boîte', 250.00, 400.00, 19.25, 100.000, 20.000, true, '2027-06-30'),
('SAVON', 'Savon de Marseille 300g', 
    (SELECT id FROM public.familles_produits WHERE code = 'HYGIENE'), false, 'Pain', 400.00, 600.00, 19.25, 200.000, 40.000, true, NULL),
('DETERGENT', 'Lessive en poudre 2kg', 
    (SELECT id FROM public.familles_produits WHERE code = 'HYGIENE'), false, 'Boîte', 1200.00, 1800.00, 19.25, 60.000, 10.000, true, '2026-12-31')
ON CONFLICT (code) DO NOTHING;

-- ============================================================================
-- 11. FRACTIONNEMENT (1 Carton = 40 Boîtes)
-- ============================================================================

INSERT INTO public.fractions_produit (produit_pere_id, produit_fils_id, quantite_fils) 
SELECT p1.id, p2.id, 40.000
FROM public.produits p1, public.produits p2
WHERE p1.code = 'LAIT-CARTON' AND p2.code = 'LAIT-BOITE'
ON CONFLICT (produit_pere_id, produit_fils_id) DO NOTHING;

-- ============================================================================
-- 12. VÉRIFICATION FINALE
-- ============================================================================

SELECT '🏷️ Groupes: ' || COUNT(*)::text FROM public.groupes_utilisateurs
UNION ALL
SELECT '👥 Utilisateurs: ' || COUNT(*)::text FROM public.utilisateurs
UNION ALL
SELECT '🔐 Droits: ' || COUNT(*)::text FROM public.droits
UNION ALL
SELECT '📁 Familles: ' || COUNT(*)::text FROM public.familles_produits
UNION ALL
SELECT '📦 Produits: ' || COUNT(*)::text FROM public.produits
UNION ALL
SELECT '🏦 Banques: ' || COUNT(*)::text FROM public.banques
UNION ALL
SELECT '👤 Clients: ' || COUNT(*)::text FROM public.clients
UNION ALL
SELECT '🚚 Fournisseurs: ' || COUNT(*)::text FROM public.fournisseurs;

-- ============================================================================
-- 13. MATRICE DES DROITS PAR GROUPE
-- ============================================================================

SELECT 
    d.module,
    d.code,
    d.description,
    CASE WHEN EXISTS (
        SELECT 1 FROM public.groupes_droits gd 
        JOIN public.groupes_utilisateurs gu ON gd.groupe_id = gu.id
        WHERE gd.droit_id = d.id AND gu.nom = 'ADMINISTRATEURS'
    ) THEN '✅' ELSE '❌' END AS admin,
    CASE WHEN EXISTS (
        SELECT 1 FROM public.groupes_droits gd 
        JOIN public.groupes_utilisateurs gu ON gd.groupe_id = gu.id
        WHERE gd.droit_id = d.id AND gu.nom = 'VENDEURS'
    ) THEN '✅' ELSE '❌' END AS vendeurs,
    CASE WHEN EXISTS (
        SELECT 1 FROM public.groupes_droits gd 
        JOIN public.groupes_utilisateurs gu ON gd.groupe_id = gu.id
        WHERE gd.droit_id = d.id AND gu.nom = 'LOGISTICIENS'
    ) THEN '✅' ELSE '❌' END AS logisticiens,
    CASE WHEN EXISTS (
        SELECT 1 FROM public.groupes_droits gd 
        JOIN public.groupes_utilisateurs gu ON gd.groupe_id = gu.id
        WHERE gd.droit_id = d.id AND gu.nom = 'COMPTABILITE'
    ) THEN '✅' ELSE '❌' END AS comptabilite
FROM public.droits d
ORDER BY d.module, d.code;

COMMIT;