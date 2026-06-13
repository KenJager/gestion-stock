

-- ============================================================
--  BASE DE DONNÉES GLOBALE — SYSTÈME DE GESTION
--  PostgreSQL | ON DELETE CASCADE activé sur toutes les FK
-- ============================================================
 
-- Extensions utiles
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
 
-- ============================================================
-- MODULE 4 — GESTION DES UTILISATEURS
-- (doit être créé en premier car référencé par tous les modules)
-- ============================================================
 
CREATE TABLE groupes_utilisateurs (
    id               SERIAL PRIMARY KEY,
    nom              VARCHAR(100) NOT NULL UNIQUE,
    description      TEXT,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE droits (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(100) NOT NULL UNIQUE,   -- ex: 'VENTE_CREER', 'ACHAT_VALIDER'
    module           VARCHAR(50)  NOT NULL,           -- ex: 'VENTES', 'APPROVISIONNEMENTS'
    description      TEXT
);
 
CREATE TABLE groupes_droits (
    groupe_id        INT NOT NULL REFERENCES groupes_utilisateurs(id) ON DELETE CASCADE,
    droit_id         INT NOT NULL REFERENCES droits(id) ON DELETE CASCADE,
    PRIMARY KEY (groupe_id, droit_id)
);
 
CREATE TABLE utilisateurs (
    id               SERIAL PRIMARY KEY,
    nom              VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100),
    login            VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe     TEXT        NOT NULL,            -- stocker un hash (bcrypt, etc.)
    email            VARCHAR(150),
    groupe_id        INT REFERENCES groupes_utilisateurs(id) ON DELETE SET NULL,
    actif            BOOLEAN DEFAULT TRUE,
    date_creation    TIMESTAMP DEFAULT NOW(),
    derniere_connexion TIMESTAMP
);
 
-- ============================================================
-- MODULE 3 — GESTION GLOBALE / STRUCTURE
-- ============================================================
 
-- --- Familles de produits ---
CREATE TABLE familles_produits (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    libelle          VARCHAR(150) NOT NULL,
    description      TEXT,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Produits ---
CREATE TABLE produits (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    designation      VARCHAR(200) NOT NULL,
    famille_id       INT NOT NULL REFERENCES familles_produits(id) ON DELETE CASCADE,
    produit_pere_id  INT REFERENCES produits(id) ON DELETE CASCADE,  -- NULL si produit père
    est_pere         BOOLEAN DEFAULT FALSE,
    unite            VARCHAR(30),           -- ex: kg, litre, boîte
    prix_achat_ht    NUMERIC(15,2) DEFAULT 0,
    prix_vente_ht    NUMERIC(15,2) DEFAULT 0,
    tva              NUMERIC(5,2)  DEFAULT 0,
    stock_actuel     NUMERIC(15,3) DEFAULT 0,
    stock_minimum    NUMERIC(15,3) DEFAULT 0,
    date_peremption  DATE,
    actif            BOOLEAN DEFAULT TRUE,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Fractionnement produit père → fils ---
CREATE TABLE fractions_produit (
    id               SERIAL PRIMARY KEY,
    produit_pere_id  INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    produit_fils_id  INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite_fils    NUMERIC(15,3) NOT NULL,  -- qté de fils contenue dans 1 unité père
    date_creation    TIMESTAMP DEFAULT NOW(),
    UNIQUE (produit_pere_id, produit_fils_id)
);
 
-- --- Fournisseurs ---
CREATE TABLE fournisseurs (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    raison_sociale   VARCHAR(200) NOT NULL,
    contact          VARCHAR(150),
    telephone        VARCHAR(50),
    email            VARCHAR(150),
    adresse          TEXT,
    ville            VARCHAR(100),
    pays             VARCHAR(100) DEFAULT 'Cameroun',
    actif            BOOLEAN DEFAULT TRUE,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Catégories de clients ---
CREATE TABLE categories_clients (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    libelle          VARCHAR(150) NOT NULL,
    remise_pct       NUMERIC(5,2) DEFAULT 0,   -- remise accordée à cette catégorie
    description      TEXT
);
 
-- --- Clients ---
CREATE TABLE clients (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    raison_sociale   VARCHAR(200) NOT NULL,
    contact          VARCHAR(150),
    telephone        VARCHAR(50),
    email            VARCHAR(150),
    adresse          TEXT,
    ville            VARCHAR(100),
    pays             VARCHAR(100) DEFAULT 'Cameroun',
    categorie_id     INT REFERENCES categories_clients(id) ON DELETE SET NULL,
    actif            BOOLEAN DEFAULT TRUE,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Banques ---
CREATE TABLE banques (
    id               SERIAL PRIMARY KEY,
    code             VARCHAR(50)  NOT NULL UNIQUE,
    nom              VARCHAR(200) NOT NULL,
    adresse          TEXT,
    telephone        VARCHAR(50),
    numero_compte    VARCHAR(100),
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Versements en banque ---
CREATE TABLE versements_banque (
    id               SERIAL PRIMARY KEY,
    banque_id        INT NOT NULL REFERENCES banques(id) ON DELETE CASCADE,
    montant          NUMERIC(15,2) NOT NULL,
    date_versement   DATE NOT NULL DEFAULT CURRENT_DATE,
    reference        VARCHAR(100),
    motif            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- ============================================================
-- MODULE 1 — GESTION DES APPROVISIONNEMENTS
-- ============================================================
 
-- --- Bons de commande fournisseur ---
CREATE TABLE bons_commande_fournisseur (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    fournisseur_id   INT NOT NULL REFERENCES fournisseurs(id) ON DELETE CASCADE,
    date_commande    DATE NOT NULL DEFAULT CURRENT_DATE,
    date_livraison_prevue DATE,
    statut           VARCHAR(30) NOT NULL DEFAULT 'BROUILLON',
                     -- BROUILLON | VALIDÉ | REÇU_PARTIEL | REÇU_TOTAL | ANNULÉ
    montant_ht       NUMERIC(15,2) DEFAULT 0,
    montant_tva      NUMERIC(15,2) DEFAULT 0,
    montant_ttc      NUMERIC(15,2) DEFAULT 0,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_commande_fournisseur (
    id               SERIAL PRIMARY KEY,
    bon_commande_id  INT NOT NULL REFERENCES bons_commande_fournisseur(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite_commandee NUMERIC(15,3) NOT NULL,
    quantite_recue   NUMERIC(15,3) DEFAULT 0,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    montant_ht       NUMERIC(15,2) GENERATED ALWAYS AS (quantite_commandee * prix_unitaire_ht) STORED
);
 
-- --- Bons de réception ---
CREATE TABLE bons_reception (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    bon_commande_id  INT REFERENCES bons_commande_fournisseur(id) ON DELETE CASCADE,
    fournisseur_id   INT NOT NULL REFERENCES fournisseurs(id) ON DELETE CASCADE,
    date_reception   DATE NOT NULL DEFAULT CURRENT_DATE,
    statut           VARCHAR(30) NOT NULL DEFAULT 'REÇU',
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_reception (
    id               SERIAL PRIMARY KEY,
    bon_reception_id INT NOT NULL REFERENCES bons_reception(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite_recue   NUMERIC(15,3) NOT NULL,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    date_peremption  DATE
);
 
-- --- Factures fournisseurs ---
CREATE TABLE factures_fournisseur (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    bon_reception_id INT REFERENCES bons_reception(id) ON DELETE CASCADE,
    fournisseur_id   INT NOT NULL REFERENCES fournisseurs(id) ON DELETE CASCADE,
    date_facture     DATE NOT NULL DEFAULT CURRENT_DATE,
    date_echeance    DATE,
    montant_ht       NUMERIC(15,2) NOT NULL DEFAULT 0,
    montant_tva      NUMERIC(15,2) DEFAULT 0,
    montant_ttc      NUMERIC(15,2) NOT NULL DEFAULT 0,
    montant_regle    NUMERIC(15,2) DEFAULT 0,
    solde            NUMERIC(15,2) GENERATED ALWAYS AS (montant_ttc - montant_regle) STORED,
    statut           VARCHAR(30) NOT NULL DEFAULT 'NON_REGLÉE',
                     -- NON_REGLÉE | PARTIELLEMENT_REGLÉE | REGLÉE
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Règlements fournisseurs ---
CREATE TABLE reglements_fournisseur (
    id               SERIAL PRIMARY KEY,
    facture_id       INT NOT NULL REFERENCES factures_fournisseur(id) ON DELETE CASCADE,
    date_reglement   DATE NOT NULL DEFAULT CURRENT_DATE,
    montant          NUMERIC(15,2) NOT NULL,
    mode_paiement    VARCHAR(50),   -- ESPÈCES | VIREMENT | CHÈQUE | MOBILE_MONEY
    reference        VARCHAR(100),
    banque_id        INT REFERENCES banques(id) ON DELETE SET NULL,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Entrées produits issus de dons ---
CREATE TABLE entrees_dons (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    donateur         VARCHAR(200),
    date_entree      DATE NOT NULL DEFAULT CURRENT_DATE,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_entree_don (
    id               SERIAL PRIMARY KEY,
    entree_don_id    INT NOT NULL REFERENCES entrees_dons(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite         NUMERIC(15,3) NOT NULL,
    valeur_estimee   NUMERIC(15,2) DEFAULT 0,
    date_peremption  DATE
);
 
-- --- Bons d'entrée (mouvements de stock en entrée) ---
CREATE TABLE bons_entree (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    type_source      VARCHAR(30) NOT NULL,   -- ACHAT | DON | RETOUR_CLIENT | AJUSTEMENT
    source_id        INT,                    -- ID du bon de réception ou entree_don selon type
    date_entree      DATE NOT NULL DEFAULT CURRENT_DATE,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_bon_entree (
    id               SERIAL PRIMARY KEY,
    bon_entree_id    INT NOT NULL REFERENCES bons_entree(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite         NUMERIC(15,3) NOT NULL,
    prix_unitaire    NUMERIC(15,2) DEFAULT 0
);
 
-- ============================================================
-- MODULE 2 — GESTION DES VENTES
-- ============================================================
 
-- --- Bons de commande client ---
CREATE TABLE bons_commande_client (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    client_id        INT NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    date_commande    DATE NOT NULL DEFAULT CURRENT_DATE,
    date_livraison_prevue DATE,
    statut           VARCHAR(30) NOT NULL DEFAULT 'EN_ATTENTE',
                     -- EN_ATTENTE | VALIDÉ | LIVRÉ_PARTIEL | LIVRÉ_TOTAL | ANNULÉ
    montant_ht       NUMERIC(15,2) DEFAULT 0,
    montant_tva      NUMERIC(15,2) DEFAULT 0,
    montant_ttc      NUMERIC(15,2) DEFAULT 0,
    remise_pct       NUMERIC(5,2)  DEFAULT 0,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_commande_client (
    id               SERIAL PRIMARY KEY,
    bon_commande_id  INT NOT NULL REFERENCES bons_commande_client(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite_commandee NUMERIC(15,3) NOT NULL,
    quantite_livree  NUMERIC(15,3) DEFAULT 0,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    remise_pct       NUMERIC(5,2)  DEFAULT 0
);
 
-- --- Bons de livraison client ---
CREATE TABLE bons_livraison_client (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    bon_commande_id  INT REFERENCES bons_commande_client(id) ON DELETE CASCADE,
    client_id        INT NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    date_livraison   DATE NOT NULL DEFAULT CURRENT_DATE,
    statut           VARCHAR(30) NOT NULL DEFAULT 'LIVRÉ',
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_livraison_client (
    id               SERIAL PRIMARY KEY,
    bon_livraison_id INT NOT NULL REFERENCES bons_livraison_client(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite_livree  NUMERIC(15,3) NOT NULL,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    remise_pct       NUMERIC(5,2)  DEFAULT 0
);
 
-- --- Factures client ---
CREATE TABLE factures_client (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    bon_livraison_id INT REFERENCES bons_livraison_client(id) ON DELETE CASCADE,
    client_id        INT NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    date_facture     DATE NOT NULL DEFAULT CURRENT_DATE,
    date_echeance    DATE,
    type_vente       VARCHAR(20) NOT NULL DEFAULT 'CREDIT',  -- COMPTANT | CREDIT
    montant_ht       NUMERIC(15,2) NOT NULL DEFAULT 0,
    montant_tva      NUMERIC(15,2) DEFAULT 0,
    montant_ttc      NUMERIC(15,2) NOT NULL DEFAULT 0,
    remise_montant   NUMERIC(15,2) DEFAULT 0,
    montant_regle    NUMERIC(15,2) DEFAULT 0,
    solde            NUMERIC(15,2) GENERATED ALWAYS AS (montant_ttc - remise_montant - montant_regle) STORED,
    statut           VARCHAR(30) NOT NULL DEFAULT 'NON_REGLÉE',
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_facture_client (
    id               SERIAL PRIMARY KEY,
    facture_id       INT NOT NULL REFERENCES factures_client(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite         NUMERIC(15,3) NOT NULL,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    remise_pct       NUMERIC(5,2)  DEFAULT 0
);
 
-- --- Règlements client ---
CREATE TABLE reglements_client (
    id               SERIAL PRIMARY KEY,
    facture_id       INT NOT NULL REFERENCES factures_client(id) ON DELETE CASCADE,
    date_reglement   DATE NOT NULL DEFAULT CURRENT_DATE,
    montant          NUMERIC(15,2) NOT NULL,
    mode_paiement    VARCHAR(50),
    reference        VARCHAR(100),
    banque_id        INT REFERENCES banques(id) ON DELETE SET NULL,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
-- --- Ventes comptant (sans commande préalable) ---
CREATE TABLE ventes_comptant (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    client_id        INT REFERENCES clients(id) ON DELETE SET NULL,  -- peut être anonyme
    date_vente       DATE NOT NULL DEFAULT CURRENT_DATE,
    montant_ht       NUMERIC(15,2) NOT NULL DEFAULT 0,
    montant_tva      NUMERIC(15,2) DEFAULT 0,
    montant_ttc      NUMERIC(15,2) NOT NULL DEFAULT 0,
    remise_montant   NUMERIC(15,2) DEFAULT 0,
    montant_percu    NUMERIC(15,2) NOT NULL DEFAULT 0,
    monnaie_rendue   NUMERIC(15,2) GENERATED ALWAYS AS (montant_percu - (montant_ttc - remise_montant)) STORED,
    mode_paiement    VARCHAR(50),
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_vente_comptant (
    id               SERIAL PRIMARY KEY,
    vente_comptant_id INT NOT NULL REFERENCES ventes_comptant(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite         NUMERIC(15,3) NOT NULL,
    prix_unitaire_ht NUMERIC(15,2) NOT NULL,
    tva              NUMERIC(5,2)  DEFAULT 0,
    remise_pct       NUMERIC(5,2)  DEFAULT 0
);
 
-- --- Bons de sortie (produits non vendus / périmés) ---
CREATE TABLE bons_sortie (
    id               SERIAL PRIMARY KEY,
    numero           VARCHAR(50)  NOT NULL UNIQUE,
    motif            VARCHAR(50) NOT NULL,   -- NON_VENDU | PÉRIMÉ | CASSE | AJUSTEMENT
    date_sortie      DATE NOT NULL DEFAULT CURRENT_DATE,
    notes            TEXT,
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL,
    date_creation    TIMESTAMP DEFAULT NOW()
);
 
CREATE TABLE lignes_bon_sortie (
    id               SERIAL PRIMARY KEY,
    bon_sortie_id    INT NOT NULL REFERENCES bons_sortie(id) ON DELETE CASCADE,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    quantite         NUMERIC(15,3) NOT NULL,
    prix_unitaire    NUMERIC(15,2) DEFAULT 0,
    observations     TEXT
);
 
-- ============================================================
-- JOURNAL DES MOUVEMENTS DE STOCK (centralisé)
-- ============================================================
CREATE TABLE mouvements_stock (
    id               SERIAL PRIMARY KEY,
    produit_id       INT NOT NULL REFERENCES produits(id) ON DELETE CASCADE,
    type_mouvement   VARCHAR(20) NOT NULL,  -- ENTRÉE | SORTIE
    source           VARCHAR(50) NOT NULL,  -- ACHAT | DON | VENTE | VENTE_COMPTANT | RETOUR | PERTE | AJUSTEMENT
    source_id        INT,                   -- ID du document source
    quantite         NUMERIC(15,3) NOT NULL,
    stock_avant      NUMERIC(15,3) NOT NULL,
    stock_apres      NUMERIC(15,3) NOT NULL,
    date_mouvement   TIMESTAMP DEFAULT NOW(),
    utilisateur_id   INT REFERENCES utilisateurs(id) ON DELETE SET NULL
);
 
-- ============================================================
-- TABLE AUDIT LOG
-- (indépendante, aucune FK, jamais affectée par les cascades)
-- ============================================================
 
CREATE TABLE audit_log (
    id                  SERIAL PRIMARY KEY,
    table_name          VARCHAR(100)    NOT NULL,
    operation           VARCHAR(10)     NOT NULL,   -- INSERT | UPDATE | DELETE
    ancien_valeur       JSONB,                      -- données AVANT (NULL pour INSERT)
    nouvelle_valeur     JSONB,                      -- données APRÈS (NULL pour DELETE)
    utilisateur_id      INT,                        -- ID utilisateur (peut être NULL)
    login_utilisateur   VARCHAR(100),               -- copie du login au moment de l'action
    date_operation      TIMESTAMP       DEFAULT NOW(),
    adresse_ip          VARCHAR(45)                 -- optionnel
);
 
-- ============================================================
-- FONCTION TRIGGER COMMUNE (1 seule pour les 34 tables)
-- ============================================================
 
CREATE OR REPLACE FUNCTION fn_audit_log()
RETURNS TRIGGER AS $$
DECLARE
    v_utilisateur_id INT;
    v_login          VARCHAR(100);
BEGIN
    -- Récupère les infos définies par PHP via set_config(), NULL si non définies
    v_utilisateur_id := NULLIF(current_setting('app.utilisateur_id', true), '')::INT;
    v_login          := NULLIF(current_setting('app.login_utilisateur', true), '');

    IF TG_OP = 'INSERT' THEN
        INSERT INTO audit_log (table_name, operation, ancien_valeur, nouvelle_valeur, utilisateur_id, login_utilisateur)
        VALUES (TG_TABLE_NAME, 'INSERT', NULL, row_to_json(NEW)::JSONB, v_utilisateur_id, v_login);
        RETURN NEW;

    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO audit_log (table_name, operation, ancien_valeur, nouvelle_valeur, utilisateur_id, login_utilisateur)
        VALUES (TG_TABLE_NAME, 'UPDATE', row_to_json(OLD)::JSONB, row_to_json(NEW)::JSONB, v_utilisateur_id, v_login);
        RETURN NEW;

    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO audit_log (table_name, operation, ancien_valeur, nouvelle_valeur, utilisateur_id, login_utilisateur)
        VALUES (TG_TABLE_NAME, 'DELETE', row_to_json(OLD)::JSONB, NULL, v_utilisateur_id, v_login);
        RETURN OLD;
    END IF;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

 
-- ============================================================
-- 34 TRIGGERS (1 par table)
-- ============================================================
 
-- MODULE 4 — UTILISATEURS (4 tables)
CREATE TRIGGER trg_audit_groupes_utilisateurs
    AFTER INSERT OR UPDATE OR DELETE ON groupes_utilisateurs
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_droits
    AFTER INSERT OR UPDATE OR DELETE ON droits
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_groupes_droits
    AFTER INSERT OR UPDATE OR DELETE ON groupes_droits
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_utilisateurs
    AFTER INSERT OR UPDATE OR DELETE ON utilisateurs
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
-- MODULE 3 — STRUCTURE (8 tables)
CREATE TRIGGER trg_audit_familles_produits
    AFTER INSERT OR UPDATE OR DELETE ON familles_produits
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_produits
    AFTER INSERT OR UPDATE OR DELETE ON produits
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_fractions_produit
    AFTER INSERT OR UPDATE OR DELETE ON fractions_produit
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_fournisseurs
    AFTER INSERT OR UPDATE OR DELETE ON fournisseurs
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_categories_clients
    AFTER INSERT OR UPDATE OR DELETE ON categories_clients
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_clients
    AFTER INSERT OR UPDATE OR DELETE ON clients
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_banques
    AFTER INSERT OR UPDATE OR DELETE ON banques
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_versements_banque
    AFTER INSERT OR UPDATE OR DELETE ON versements_banque
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
-- MODULE 1 — APPROVISIONNEMENTS (10 tables)
CREATE TRIGGER trg_audit_bons_commande_fournisseur
    AFTER INSERT OR UPDATE OR DELETE ON bons_commande_fournisseur
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_commande_fournisseur
    AFTER INSERT OR UPDATE OR DELETE ON lignes_commande_fournisseur
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_bons_reception
    AFTER INSERT OR UPDATE OR DELETE ON bons_reception
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_reception
    AFTER INSERT OR UPDATE OR DELETE ON lignes_reception
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_factures_fournisseur
    AFTER INSERT OR UPDATE OR DELETE ON factures_fournisseur
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_reglements_fournisseur
    AFTER INSERT OR UPDATE OR DELETE ON reglements_fournisseur
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_entrees_dons
    AFTER INSERT OR UPDATE OR DELETE ON entrees_dons
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_entree_don
    AFTER INSERT OR UPDATE OR DELETE ON lignes_entree_don
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_bons_entree
    AFTER INSERT OR UPDATE OR DELETE ON bons_entree
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_bon_entree
    AFTER INSERT OR UPDATE OR DELETE ON lignes_bon_entree
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
-- MODULE 2 — VENTES (11 tables)
CREATE TRIGGER trg_audit_bons_commande_client
    AFTER INSERT OR UPDATE OR DELETE ON bons_commande_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_commande_client
    AFTER INSERT OR UPDATE OR DELETE ON lignes_commande_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_bons_livraison_client
    AFTER INSERT OR UPDATE OR DELETE ON bons_livraison_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_livraison_client
    AFTER INSERT OR UPDATE OR DELETE ON lignes_livraison_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_factures_client
    AFTER INSERT OR UPDATE OR DELETE ON factures_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_facture_client
    AFTER INSERT OR UPDATE OR DELETE ON lignes_facture_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_reglements_client
    AFTER INSERT OR UPDATE OR DELETE ON reglements_client
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_ventes_comptant
    AFTER INSERT OR UPDATE OR DELETE ON ventes_comptant
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_vente_comptant
    AFTER INSERT OR UPDATE OR DELETE ON lignes_vente_comptant
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_bons_sortie
    AFTER INSERT OR UPDATE OR DELETE ON bons_sortie
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
CREATE TRIGGER trg_audit_lignes_bon_sortie
    AFTER INSERT OR UPDATE OR DELETE ON lignes_bon_sortie
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
-- STOCK TRANSVERSAL (1 table)
CREATE TRIGGER trg_audit_mouvements_stock
    AFTER INSERT OR UPDATE OR DELETE ON mouvements_stock
    FOR EACH ROW EXECUTE FUNCTION fn_audit_log();
 
