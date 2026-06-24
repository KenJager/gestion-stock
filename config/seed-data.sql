-- ============================================================================
-- SCRIPT COMPLET DE SEED DATA - GESTION DE STOCK
-- ============================================================================

BEGIN;

-- ============================================================================
-- 1. GROUPES D'UTILISATEURS (4 groupes)
-- ============================================================================

INSERT INTO public.groupes_utilisateurs (nom, description) VALUES
('ADMINISTRATEURS', 'Super administrateur - tous droits d''accès'),
('VENDEURS', 'Personnel affecté au comptoir et suivi client'),
('LOGISTICIENS', 'Gestionnaires de stock et des approvisionnements'),
('COMPTABILITE', 'Gestion des factures et règlements')
ON CONFLICT (nom) DO NOTHING;

-- ============================================================================
-- 2. UTILISATEURS
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
-- 3. DROITS (4 modules)
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
-- 12. NETTOYAGE DES DONNÉES DE TEST EXISTANTES
-- ============================================================================

DELETE FROM public.mouvements_stock 
WHERE source IN ('BON_RECEPTION', 'BON_LIVR_CLIENT', 'VENTE_COMPTANT')
  AND (source_id IN (SELECT id FROM public.bons_reception WHERE numero LIKE 'BR-2026-%')
       OR source_id IN (SELECT id FROM public.bons_livraison_client WHERE numero LIKE 'BL-2026-%')
       OR source_id IN (SELECT id FROM public.ventes_comptant WHERE numero LIKE 'VTC-2026-00%'));

DELETE FROM public.lignes_reception WHERE bon_reception_id IN (SELECT id FROM public.bons_reception WHERE numero LIKE 'BR-2026-%');
DELETE FROM public.lignes_commande_fournisseur WHERE bon_commande_id IN (SELECT id FROM public.bons_commande_fournisseur WHERE numero LIKE 'BCF-2026-%');
DELETE FROM public.lignes_livraison_client WHERE bon_livraison_id IN (SELECT id FROM public.bons_livraison_client WHERE numero LIKE 'BL-2026-%');
DELETE FROM public.lignes_commande_client WHERE bon_commande_id IN (SELECT id FROM public.bons_commande_client WHERE numero LIKE 'BCC-2026-%');
DELETE FROM public.lignes_vente_comptant WHERE vente_comptant_id IN (SELECT id FROM public.ventes_comptant WHERE numero LIKE 'VTC-2026-00%');

DELETE FROM public.reglements_fournisseur WHERE facture_id IN (SELECT id FROM public.factures_fournisseur WHERE numero LIKE 'FAC-FOUR-9989%');
DELETE FROM public.reglements_client WHERE facture_id IN (SELECT id FROM public.factures_client WHERE numero LIKE 'FAC-CLI-2026-%');

DELETE FROM public.factures_fournisseur WHERE numero LIKE 'FAC-FOUR-9989%';
DELETE FROM public.factures_client WHERE numero LIKE 'FAC-CLI-2026-%';

DELETE FROM public.bons_reception WHERE numero LIKE 'BR-2026-%';
DELETE FROM public.bons_livraison_client WHERE numero LIKE 'BL-2026-%';
DELETE FROM public.bons_commande_fournisseur WHERE numero LIKE 'BCF-2026-%';
DELETE FROM public.bons_commande_client WHERE numero LIKE 'BCC-2026-%';
DELETE FROM public.ventes_comptant WHERE numero LIKE 'VTC-2026-00%';

-- ============================================================================
-- 13. APPROVISIONNEMENTS - COMMANDE CHOCOCAM (FOUR-002)
-- ============================================================================

DO $$
DECLARE
    v_fournisseur_chococam INT;
    v_user_logis INT;
    v_user_compta INT;
    v_banque_bicec INT;
    v_produit_choc_tart INT;
    v_produit_choc_tab INT;
    v_produit_choc_poudre INT;
    v_bc_id INT;
    v_br_id INT;
    v_ff_id INT;
BEGIN
    SELECT id INTO v_fournisseur_chococam FROM public.fournisseurs WHERE code = 'FOUR-002';
    SELECT id INTO v_user_logis FROM public.utilisateurs WHERE login = 'logis';
    SELECT id INTO v_user_compta FROM public.utilisateurs WHERE login = 'compta';
    SELECT id INTO v_banque_bicec FROM public.banques WHERE code = 'BICEC';
    SELECT id INTO v_produit_choc_tart FROM public.produits WHERE code = 'CHOC-TART-1KG';
    SELECT id INTO v_produit_choc_tab FROM public.produits WHERE code = 'CHOC-TAB-100G';
    SELECT id INTO v_produit_choc_poudre FROM public.produits WHERE code = 'CHOC-POUDRE';

    -- Bons de commande fournisseur
    INSERT INTO public.bons_commande_fournisseur (numero, fournisseur_id, date_commande, date_livraison_prevue, statut, montant_ht, montant_tva, montant_ttc, notes, utilisateur_id) 
    VALUES ('BCF-2026-002', v_fournisseur_chococam, '2026-02-01', '2026-02-10', 'LIVRÉ', 125000.00, 24062.50, 149062.50, 'Commande chocolats pour saison des vacances', v_user_logis)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bc_id;

    IF v_bc_id IS NOT NULL THEN
        -- Lignes commande
        INSERT INTO public.lignes_commande_fournisseur (bon_commande_id, produit_id, quantite_commandee, quantite_recue, prix_unitaire_ht, tva) VALUES
        (v_bc_id, v_produit_choc_tart, 20.000, 20.000, 2500.00, 19.25),
        (v_bc_id, v_produit_choc_tab, 30.000, 30.000, 250.00, 19.25),
        (v_bc_id, v_produit_choc_poudre, 20.000, 20.000, 600.00, 19.25);

        -- Réception
        INSERT INTO public.bons_reception (numero, bon_commande_id, fournisseur_id, date_reception, statut, notes, utilisateur_id) 
        VALUES ('BR-2026-002', v_bc_id, v_fournisseur_chococam, '2026-02-09', 'REÇU', 'Conforme, vérifié par magasinier', v_user_logis)
        ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_br_id;

        IF v_br_id IS NOT NULL THEN
            -- Lignes réception
            INSERT INTO public.lignes_reception (bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva, date_peremption) VALUES
            (v_br_id, v_produit_choc_tart, 20.000, 2500.00, 19.25, '2027-01-31'),
            (v_br_id, v_produit_choc_tab, 30.000, 250.00, 19.25, '2027-02-28'),
            (v_br_id, v_produit_choc_poudre, 20.000, 600.00, 19.25, '2027-01-31');

            -- Facture fournisseur
            INSERT INTO public.factures_fournisseur (numero, bon_reception_id, fournisseur_id, date_facture, date_echeance, statut, montant_ht, montant_tva, montant_ttc, montant_regle, notes, utilisateur_id) 
            VALUES ('FAC-FOUR-9989', v_br_id, v_fournisseur_chococam, '2026-02-09', '2026-03-11', 'REGLÉE', 125000.00, 24062.50, 149062.50, 149062.50, 'Facture réglée par virement', v_user_compta)
            ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_ff_id;

            IF v_ff_id IS NOT NULL THEN
                -- Règlement fournisseur
                INSERT INTO public.reglements_fournisseur (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, utilisateur_id) 
                VALUES (v_ff_id, '2026-02-15', 149062.50, 'VIREMENT', 'VIR-2026-002', v_banque_bicec, v_user_compta);
            END IF;
        END IF;
    END IF;
END $$;

-- ============================================================================
-- 14. APPROVISIONNEMENTS - COMMANDE BRASSERIES (FOUR-004)
-- ============================================================================

DO $$
DECLARE
    v_fournisseur_brasseries INT;
    v_user_logis INT;
    v_produit_biere INT;
    v_produit_eau INT;
    v_bc_id INT;
    v_br_id INT;
BEGIN
    SELECT id INTO v_fournisseur_brasseries FROM public.fournisseurs WHERE code = 'FOUR-004';
    SELECT id INTO v_user_logis FROM public.utilisateurs WHERE login = 'logis';
    SELECT id INTO v_produit_biere FROM public.produits WHERE code = 'BIERE-33CL';
    SELECT id INTO v_produit_eau FROM public.produits WHERE code = 'EAU-MINALE';

    -- Bons de commande fournisseur
    INSERT INTO public.bons_commande_fournisseur (numero, fournisseur_id, date_commande, date_livraison_prevue, statut, montant_ht, montant_tva, montant_ttc, notes, utilisateur_id) 
    VALUES ('BCF-2026-003', v_fournisseur_brasseries, '2026-02-15', '2026-02-25', 'LIVRÉ', 80000.00, 15400.00, 95400.00, 'Stock boissons pour événements', v_user_logis)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bc_id;

    IF v_bc_id IS NOT NULL THEN
        -- Lignes commande
        INSERT INTO public.lignes_commande_fournisseur (bon_commande_id, produit_id, quantite_commandee, quantite_recue, prix_unitaire_ht, tva) VALUES
        (v_bc_id, v_produit_biere, 100.000, 100.000, 300.00, 19.25),
        (v_bc_id, v_produit_eau, 80.000, 80.000, 200.00, 19.25);

        -- Réception
        INSERT INTO public.bons_reception (numero, bon_commande_id, fournisseur_id, date_reception, statut, notes, utilisateur_id) 
        VALUES ('BR-2026-003', v_bc_id, v_fournisseur_brasseries, '2026-02-24', 'REÇU', 'Livré avec bon de livraison BDL-2026-01', v_user_logis)
        ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_br_id;

        IF v_br_id IS NOT NULL THEN
            -- Lignes réception
            INSERT INTO public.lignes_reception (bon_reception_id, produit_id, quantite_recue, prix_unitaire_ht, tva, date_peremption) VALUES
            (v_br_id, v_produit_biere, 100.000, 300.00, 19.25, '2026-10-31'),
            (v_br_id, v_produit_eau, 80.000, 200.00, 19.25, '2027-03-31');
        END IF;
    END IF;
END $$;

-- ============================================================================
-- 15. VENTES CLIENTS - COMMANDE SND (CLI-002)
-- ============================================================================

DO $$
DECLARE
    v_client_snd INT;
    v_user_vendeur INT;
    v_user_compta INT;
    v_banque_afriland INT;
    v_produit_choc_tart INT;
    v_produit_choc_tab INT;
    v_produit_jus INT;
    v_produit_thon INT;
    v_bcc_id INT;
    v_bl_id INT;
    v_fc_id INT;
BEGIN
    SELECT id INTO v_client_snd FROM public.clients WHERE code = 'CLI-002';
    SELECT id INTO v_user_vendeur FROM public.utilisateurs WHERE login = 'vendeur';
    SELECT id INTO v_user_compta FROM public.utilisateurs WHERE login = 'compta';
    SELECT id INTO v_banque_afriland FROM public.banques WHERE code = 'AFRILAND';
    SELECT id INTO v_produit_choc_tart FROM public.produits WHERE code = 'CHOC-TART-1KG';
    SELECT id INTO v_produit_choc_tab FROM public.produits WHERE code = 'CHOC-TAB-100G';
    SELECT id INTO v_produit_jus FROM public.produits WHERE code = 'JUS-ORANGE';
    SELECT id INTO v_produit_thon FROM public.produits WHERE code = 'THON';

    -- Bons de commande client
    INSERT INTO public.bons_commande_client (numero, client_id, date_commande, date_livraison_prevue, statut, montant_ht, montant_tva, montant_ttc, remise_pct, notes, utilisateur_id) 
    VALUES ('BCC-2026-002', v_client_snd, '2026-02-01', '2026-02-05', 'LIVRÉ', 127500.00, 24543.75, 152043.75, 10.00, 'Commande grossiste - remise 10%', v_user_vendeur)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bcc_id;

    IF v_bcc_id IS NOT NULL THEN
        -- Lignes commande
        INSERT INTO public.lignes_commande_client (bon_commande_id, produit_id, quantite_commandee, quantite_livree, prix_unitaire_ht, tva, remise_pct) VALUES
        (v_bcc_id, v_produit_choc_tart, 10.000, 10.000, 3200.00, 19.25, 10.00),
        (v_bcc_id, v_produit_choc_tab, 20.000, 20.000, 400.00, 19.25, 10.00),
        (v_bcc_id, v_produit_jus, 15.000, 15.000, 750.00, 19.25, 10.00),
        (v_bcc_id, v_produit_thon, 5.000, 5.000, 1200.00, 19.25, 10.00);

        -- Livraison
        INSERT INTO public.bons_livraison_client (numero, bon_commande_id, client_id, date_livraison, statut, notes, utilisateur_id) 
        VALUES ('BL-2026-002', v_bcc_id, v_client_snd, '2026-02-04', 'LIVRÉ', 'Livré complet, MME Etoa satisfaite', v_user_vendeur)
        ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bl_id;

        IF v_bl_id IS NOT NULL THEN
            -- Lignes livraison
            INSERT INTO public.lignes_livraison_client (bon_livraison_id, produit_id, quantite_livree, prix_unitaire_ht, tva, remise_pct) VALUES
            (v_bl_id, v_produit_choc_tart, 10.000, 3200.00, 19.25, 10.00),
            (v_bl_id, v_produit_choc_tab, 20.000, 400.00, 19.25, 10.00),
            (v_bl_id, v_produit_jus, 15.000, 750.00, 19.25, 10.00),
            (v_bl_id, v_produit_thon, 5.000, 1200.00, 19.25, 10.00);

            -- Facture client
            INSERT INTO public.factures_client (numero, bon_livraison_id, client_id, date_facture, date_echeance, type_vente, montant_ht, montant_tva, montant_ttc, remise_montant, montant_regle, statut, utilisateur_id) 
            VALUES ('FAC-CLI-2026-002', v_bl_id, v_client_snd, '2026-02-04', '2026-03-06', 'CREDIT', 127500.00, 24543.75, 152043.75, 12750.00, 152043.75, 'REGLÉE', v_user_vendeur)
            ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_fc_id;

            IF v_fc_id IS NOT NULL THEN
                -- Règlement client
                INSERT INTO public.reglements_client (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, utilisateur_id) 
                VALUES (v_fc_id, '2026-02-10', 152043.75, 'CHEQUE', 'CH-66789', v_banque_afriland, v_user_compta);
            END IF;
        END IF;
    END IF;
END $$;

-- ============================================================================
-- 16. VENTES CLIENTS - COMMANDE SUPERMARCHE MFOUNDI (CLI-004)
-- ============================================================================

DO $$
DECLARE
    v_client_supermarche INT;
    v_user_vendeur INT;
    v_produit_jus INT;
    v_produit_eau INT;
    v_produit_biere INT;
    v_produit_thon INT;
    v_produit_savon INT;
    v_bcc_id INT;
    v_bl_id INT;
    v_fc_id INT;
BEGIN
    SELECT id INTO v_client_supermarche FROM public.clients WHERE code = 'CLI-004';
    SELECT id INTO v_user_vendeur FROM public.utilisateurs WHERE login = 'vendeur';
    SELECT id INTO v_produit_jus FROM public.produits WHERE code = 'JUS-ORANGE';
    SELECT id INTO v_produit_eau FROM public.produits WHERE code = 'EAU-MINALE';
    SELECT id INTO v_produit_biere FROM public.produits WHERE code = 'BIERE-33CL';
    SELECT id INTO v_produit_thon FROM public.produits WHERE code = 'THON';
    SELECT id INTO v_produit_savon FROM public.produits WHERE code = 'SAVON';

    -- Bons de commande client
    INSERT INTO public.bons_commande_client (numero, client_id, date_commande, date_livraison_prevue, statut, montant_ht, montant_tva, montant_ttc, remise_pct, notes, utilisateur_id) 
    VALUES ('BCC-2026-003', v_client_supermarche, '2026-02-10', '2026-02-12', 'LIVRÉ', 92000.00, 17710.00, 109710.00, 0.00, 'Livraison à l''entrepôt du supermarché', v_user_vendeur)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bcc_id;

    IF v_bcc_id IS NOT NULL THEN
        -- Lignes commande
        INSERT INTO public.lignes_commande_client (bon_commande_id, produit_id, quantite_commandee, quantite_livree, prix_unitaire_ht, tva, remise_pct) VALUES
        (v_bcc_id, v_produit_jus, 20.000, 20.000, 750.00, 19.25, 0.00),
        (v_bcc_id, v_produit_eau, 50.000, 50.000, 350.00, 19.25, 0.00),
        (v_bcc_id, v_produit_biere, 40.000, 40.000, 500.00, 19.25, 0.00),
        (v_bcc_id, v_produit_thon, 20.000, 20.000, 1200.00, 19.25, 0.00),
        (v_bcc_id, v_produit_savon, 30.000, 30.000, 600.00, 19.25, 0.00);

        -- Livraison
        INSERT INTO public.bons_livraison_client (numero, bon_commande_id, client_id, date_livraison, statut, notes, utilisateur_id) 
        VALUES ('BL-2026-003', v_bcc_id, v_client_supermarche, '2026-02-11', 'LIVRÉ', 'Livré complet, réceptionné par M. Ndi', v_user_vendeur)
        ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_bl_id;

        IF v_bl_id IS NOT NULL THEN
            -- Lignes livraison
            INSERT INTO public.lignes_livraison_client (bon_livraison_id, produit_id, quantite_livree, prix_unitaire_ht, tva, remise_pct) VALUES
            (v_bl_id, v_produit_jus, 20.000, 750.00, 19.25, 0.00),
            (v_bl_id, v_produit_eau, 50.000, 350.00, 19.25, 0.00),
            (v_bl_id, v_produit_biere, 40.000, 500.00, 19.25, 0.00),
            (v_bl_id, v_produit_thon, 20.000, 1200.00, 19.25, 0.00),
            (v_bl_id, v_produit_savon, 30.000, 600.00, 19.25, 0.00);

            -- Facture client (partiellement réglée)
            INSERT INTO public.factures_client (numero, bon_livraison_id, client_id, date_facture, date_echeance, type_vente, montant_ht, montant_tva, montant_ttc, remise_montant, montant_regle, statut, utilisateur_id) 
            VALUES ('FAC-CLI-2026-003', v_bl_id, v_client_supermarche, '2026-02-11', '2026-03-13', 'CREDIT', 92000.00, 17710.00, 109710.00, 0.00, 50000.00, 'REGLÉE_PARTIEL', v_user_vendeur)
            ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_fc_id;

            IF v_fc_id IS NOT NULL THEN
                -- Règlement client partiel
                INSERT INTO public.reglements_client (facture_id, date_reglement, montant, mode_paiement, reference, banque_id, utilisateur_id) 
                VALUES (v_fc_id, '2026-02-15', 50000.00, 'ESPECES', 'RECIPISE-015', NULL, v_user_vendeur);
            END IF;
        END IF;
    END IF;
END $$;

-- ============================================================================
-- 17. VENTES AU COMPTOIR
-- ============================================================================

DO $$
DECLARE
    v_client_standard INT;
    v_client_partenaire INT;
    v_user_vendeur INT;
    v_produit_choc_tab INT;
    v_produit_choc_tart INT;
    v_produit_jus INT;
    v_produit_sardine INT;
    v_produit_thon INT;
    v_produit_detergeant INT;
    v_produit_beurre INT;
    v_produit_lait_boite INT;
    v_produit_yaourt INT;
    v_produit_mais INT;
    v_vc_id INT;
BEGIN
    SELECT id INTO v_client_standard FROM public.clients WHERE code = 'CLI-003';
    SELECT id INTO v_client_partenaire FROM public.clients WHERE code = 'CLI-006';
    SELECT id INTO v_user_vendeur FROM public.utilisateurs WHERE login = 'vendeur';
    SELECT id INTO v_produit_choc_tab FROM public.produits WHERE code = 'CHOC-TAB-100G';
    SELECT id INTO v_produit_choc_tart FROM public.produits WHERE code = 'CHOC-TART-1KG';
    SELECT id INTO v_produit_jus FROM public.produits WHERE code = 'JUS-ORANGE';
    SELECT id INTO v_produit_sardine FROM public.produits WHERE code = 'SARDINE';
    SELECT id INTO v_produit_thon FROM public.produits WHERE code = 'THON';
    SELECT id INTO v_produit_detergeant FROM public.produits WHERE code = 'DETERGENT';
    SELECT id INTO v_produit_beurre FROM public.produits WHERE code = 'BEURRE';
    SELECT id INTO v_produit_lait_boite FROM public.produits WHERE code = 'LAIT-BOITE';
    SELECT id INTO v_produit_yaourt FROM public.produits WHERE code = 'YAOURT';
    SELECT id INTO v_produit_mais FROM public.produits WHERE code = 'MAIS-DOUX';

    -- Vente comptant 1
    INSERT INTO public.ventes_comptant (numero, client_id, date_vente, montant_ht, montant_tva, montant_ttc, remise_montant, montant_percu, mode_paiement, notes, utilisateur_id) 
    VALUES ('VTC-2026-0002', v_client_standard, '2026-02-03', 7200.00, 1386.00, 8586.00, 0.00, 9000.00, 'MOBILE_MONEY', 'Paiement Orange Money - client régulier', v_user_vendeur)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_vc_id;

    IF v_vc_id IS NOT NULL THEN
        INSERT INTO public.lignes_vente_comptant (vente_comptant_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct) VALUES
        (v_vc_id, v_produit_choc_tab, 3.000, 400.00, 19.25, 0.00),
        (v_vc_id, v_produit_jus, 2.000, 750.00, 19.25, 0.00),
        (v_vc_id, v_produit_sardine, 4.000, 550.00, 19.25, 0.00);
    END IF;

    -- Vente comptant 2
    INSERT INTO public.ventes_comptant (numero, client_id, date_vente, montant_ht, montant_tva, montant_ttc, remise_montant, montant_percu, mode_paiement, notes, utilisateur_id) 
    VALUES ('VTC-2026-0003', v_client_partenaire, '2026-02-08', 12500.00, 2406.25, 14906.25, 500.00, 15000.00, 'CARTE', 'Paiement par carte bancaire - remise fidélité', v_user_vendeur)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_vc_id;

    IF v_vc_id IS NOT NULL THEN
        INSERT INTO public.lignes_vente_comptant (vente_comptant_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct) VALUES
        (v_vc_id, v_produit_choc_tart, 2.000, 3200.00, 19.25, 5.00),
        (v_vc_id, v_produit_thon, 3.000, 1200.00, 19.25, 5.00),
        (v_vc_id, v_produit_detergeant, 2.000, 1800.00, 19.25, 5.00),
        (v_vc_id, v_produit_beurre, 4.000, 1200.00, 19.25, 5.00);
    END IF;

    -- Vente comptant 3
    INSERT INTO public.ventes_comptant (numero, client_id, date_vente, montant_ht, montant_tva, montant_ttc, remise_montant, montant_percu, mode_paiement, notes, utilisateur_id) 
    VALUES ('VTC-2026-0004', v_client_standard, '2026-02-12', 5600.00, 1078.00, 6678.00, 0.00, 7000.00, 'ESPECES', 'Client pressé - achat rapide', v_user_vendeur)
    ON CONFLICT (numero) DO NOTHING RETURNING id INTO v_vc_id;

    IF v_vc_id IS NOT NULL THEN
        INSERT INTO public.lignes_vente_comptant (vente_comptant_id, produit_id, quantite, prix_unitaire_ht, tva, remise_pct) VALUES
        (v_vc_id, v_produit_lait_boite, 5.000, 550.00, 19.25, 0.00),
        (v_vc_id, v_produit_yaourt, 4.000, 450.00, 19.25, 0.00),
        (v_vc_id, v_produit_sardine, 2.000, 550.00, 19.25, 0.00),
        (v_vc_id, v_produit_mais, 3.000, 400.00, 19.25, 0.00);
    END IF;
END $$;

-- ============================================================================
-- 18. MOUVEMENTS DE STOCK
-- ============================================================================

DO $$
DECLARE
    v_user_logis INT;
    v_user_vendeur INT;
BEGIN
    SELECT id INTO v_user_logis FROM public.utilisateurs WHERE login = 'logis';
    SELECT id INTO v_user_vendeur FROM public.utilisateurs WHERE login = 'vendeur';

    -- Entrées suite réceptions
    INSERT INTO public.mouvements_stock (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id, date_mouvement)
    SELECT 
        p.id,
        'ENTREE',
        'BON_RECEPTION',
        br.id,
        lr.quantite_recue,
        COALESCE(p.stock_actuel, 0),
        COALESCE(p.stock_actuel, 0) + lr.quantite_recue,
        v_user_logis,
        br.date_reception
    FROM public.bons_reception br
    JOIN public.lignes_reception lr ON br.id = lr.bon_reception_id
    JOIN public.produits p ON lr.produit_id = p.id
    WHERE br.numero IN ('BR-2026-002', 'BR-2026-003');

    -- Sorties suite livraisons clients
    INSERT INTO public.mouvements_stock (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id, date_mouvement)
    SELECT 
        p.id,
        'SORTIE',
        'BON_LIVR_CLIENT',
        bl.id,
        -ll.quantite_livree,
        COALESCE(p.stock_actuel, 0),
        COALESCE(p.stock_actuel, 0) - ll.quantite_livree,
        v_user_vendeur,
        bl.date_livraison
    FROM public.bons_livraison_client bl
    JOIN public.lignes_livraison_client ll ON bl.id = ll.bon_livraison_id
    JOIN public.produits p ON ll.produit_id = p.id
    WHERE bl.numero IN ('BL-2026-002', 'BL-2026-003');

    -- Sorties suite ventes comptant
    INSERT INTO public.mouvements_stock (produit_id, type_mouvement, source, source_id, quantite, stock_avant, stock_apres, utilisateur_id, date_mouvement)
    SELECT 
        p.id,
        'SORTIE',
        'VENTE_COMPTANT',
        vc.id,
        -lv.quantite,
        COALESCE(p.stock_actuel, 0),
        COALESCE(p.stock_actuel, 0) - lv.quantite,
        v_user_vendeur,
        vc.date_vente
    FROM public.ventes_comptant vc
    JOIN public.lignes_vente_comptant lv ON vc.id = lv.vente_comptant_id
    JOIN public.produits p ON lv.produit_id = p.id
    WHERE vc.numero IN ('VTC-2026-0002', 'VTC-2026-0003', 'VTC-2026-0004');
END $$;

-- ============================================================================
-- 19. MISE À JOUR DES STOCKS ACTUELS
-- ============================================================================

UPDATE public.produits p
SET stock_actuel = COALESCE((
    SELECT SUM(
        CASE WHEN m.type_mouvement = 'ENTREE' THEN m.quantite
             WHEN m.type_mouvement = 'SORTIE' THEN m.quantite
             ELSE 0
        END
    )
    FROM public.mouvements_stock m
    WHERE m.produit_id = p.id
), 0);

-- ============================================================================
-- 20. VÉRIFICATION FINALE
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
SELECT '🚚 Fournisseurs: ' || COUNT(*)::text FROM public.fournisseurs
UNION ALL
SELECT '📋 Bons commande fournisseur: ' || COUNT(*)::text FROM public.bons_commande_fournisseur
UNION ALL
SELECT '📦 Lignes commande fournisseur: ' || COUNT(*)::text FROM public.lignes_commande_fournisseur
UNION ALL
SELECT '📥 Bons réception: ' || COUNT(*)::text FROM public.bons_reception
UNION ALL
SELECT '📄 Factures fournisseur: ' || COUNT(*)::text FROM public.factures_fournisseur
UNION ALL
SELECT '📋 Bons commande client: ' || COUNT(*)::text FROM public.bons_commande_client
UNION ALL
SELECT '📦 Lignes commande client: ' || COUNT(*)::text FROM public.lignes_commande_client
UNION ALL
SELECT '🚚 Bons livraison client: ' || COUNT(*)::text FROM public.bons_livraison_client
UNION ALL
SELECT '📄 Factures client: ' || COUNT(*)::text FROM public.factures_client
UNION ALL
SELECT '💰 Ventes comptant: ' || COUNT(*)::text FROM public.ventes_comptant
UNION ALL
SELECT '📊 Mouvements stock: ' || COUNT(*)::text FROM public.mouvements_stock;

-- ============================================================================
-- 21. RÉSUMÉ DES OPÉRATIONS
-- ============================================================================

SELECT '=== RÉSUMÉ DES MONTANTS ===' AS info;

SELECT 
    'APPROVISIONNEMENTS' AS module,
    COUNT(*) AS total,
    SUM(montant_ttc) AS montant_total_ttc
FROM public.bons_commande_fournisseur
UNION ALL
SELECT 
    'VENTES CLIENTS',
    COUNT(*),
    SUM(montant_ttc)
FROM public.bons_commande_client
UNION ALL
SELECT 
    'VENTES COMPTOIR',
    COUNT(*),
    SUM(montant_ttc)
FROM public.ventes_comptant;

COMMIT;