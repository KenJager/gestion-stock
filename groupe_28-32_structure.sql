--
-- PostgreSQL database dump
--

\restrict nY5PNw64ufkhu5jc6Dyb1BnWwmk1hPjWvEEAni0w7zheJZcMXpX7Yx1JfxWNlll

-- Dumped from database version 18.3 (Debian 18.3-1+b1)
-- Dumped by pg_dump version 18.3 (Debian 18.3-1+b1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: fn_audit_log(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_audit_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_audit_log() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: audit_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_log (
    id integer NOT NULL,
    table_name character varying(100) NOT NULL,
    operation character varying(10) NOT NULL,
    ancien_valeur jsonb,
    nouvelle_valeur jsonb,
    utilisateur_id integer,
    login_utilisateur character varying(100),
    date_operation timestamp without time zone DEFAULT now(),
    adresse_ip character varying(45)
);


ALTER TABLE public.audit_log OWNER TO postgres;

--
-- Name: audit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_log_id_seq OWNER TO postgres;

--
-- Name: audit_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_log_id_seq OWNED BY public.audit_log.id;


--
-- Name: banques; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.banques (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    nom character varying(200) NOT NULL,
    adresse text,
    telephone character varying(50),
    numero_compte character varying(100),
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.banques OWNER TO postgres;

--
-- Name: banques_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.banques_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.banques_id_seq OWNER TO postgres;

--
-- Name: banques_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.banques_id_seq OWNED BY public.banques.id;


--
-- Name: bons_commande_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_commande_client (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    client_id integer NOT NULL,
    date_commande date DEFAULT CURRENT_DATE NOT NULL,
    date_livraison_prevue date,
    statut character varying(30) DEFAULT 'EN_ATTENTE'::character varying NOT NULL,
    montant_ht numeric(15,2) DEFAULT 0,
    montant_tva numeric(15,2) DEFAULT 0,
    montant_ttc numeric(15,2) DEFAULT 0,
    remise_pct numeric(5,2) DEFAULT 0,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_commande_client OWNER TO postgres;

--
-- Name: bons_commande_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_commande_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_commande_client_id_seq OWNER TO postgres;

--
-- Name: bons_commande_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_commande_client_id_seq OWNED BY public.bons_commande_client.id;


--
-- Name: bons_commande_fournisseur; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_commande_fournisseur (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    fournisseur_id integer NOT NULL,
    date_commande date DEFAULT CURRENT_DATE NOT NULL,
    date_livraison_prevue date,
    statut character varying(30) DEFAULT 'BROUILLON'::character varying NOT NULL,
    montant_ht numeric(15,2) DEFAULT 0,
    montant_tva numeric(15,2) DEFAULT 0,
    montant_ttc numeric(15,2) DEFAULT 0,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_commande_fournisseur OWNER TO postgres;

--
-- Name: bons_commande_fournisseur_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_commande_fournisseur_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_commande_fournisseur_id_seq OWNER TO postgres;

--
-- Name: bons_commande_fournisseur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_commande_fournisseur_id_seq OWNED BY public.bons_commande_fournisseur.id;


--
-- Name: bons_entree; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_entree (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    type_source character varying(30) NOT NULL,
    source_id integer,
    date_entree date DEFAULT CURRENT_DATE NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_entree OWNER TO postgres;

--
-- Name: bons_entree_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_entree_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_entree_id_seq OWNER TO postgres;

--
-- Name: bons_entree_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_entree_id_seq OWNED BY public.bons_entree.id;


--
-- Name: bons_livraison_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_livraison_client (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    bon_commande_id integer,
    client_id integer NOT NULL,
    date_livraison date DEFAULT CURRENT_DATE NOT NULL,
    statut character varying(30) DEFAULT 'LIVRÉ'::character varying NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_livraison_client OWNER TO postgres;

--
-- Name: bons_livraison_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_livraison_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_livraison_client_id_seq OWNER TO postgres;

--
-- Name: bons_livraison_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_livraison_client_id_seq OWNED BY public.bons_livraison_client.id;


--
-- Name: bons_reception; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_reception (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    bon_commande_id integer,
    fournisseur_id integer NOT NULL,
    date_reception date DEFAULT CURRENT_DATE NOT NULL,
    statut character varying(30) DEFAULT 'REÇU'::character varying NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_reception OWNER TO postgres;

--
-- Name: bons_reception_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_reception_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_reception_id_seq OWNER TO postgres;

--
-- Name: bons_reception_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_reception_id_seq OWNED BY public.bons_reception.id;


--
-- Name: bons_sortie; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bons_sortie (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    motif character varying(50) NOT NULL,
    date_sortie date DEFAULT CURRENT_DATE NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.bons_sortie OWNER TO postgres;

--
-- Name: bons_sortie_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bons_sortie_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bons_sortie_id_seq OWNER TO postgres;

--
-- Name: bons_sortie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bons_sortie_id_seq OWNED BY public.bons_sortie.id;


--
-- Name: categories_clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories_clients (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    libelle character varying(150) NOT NULL,
    remise_pct numeric(5,2) DEFAULT 0,
    description text
);


ALTER TABLE public.categories_clients OWNER TO postgres;

--
-- Name: categories_clients_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_clients_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_clients_id_seq OWNER TO postgres;

--
-- Name: categories_clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_clients_id_seq OWNED BY public.categories_clients.id;


--
-- Name: clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clients (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    raison_sociale character varying(200) NOT NULL,
    contact character varying(150),
    telephone character varying(50),
    email character varying(150),
    adresse text,
    ville character varying(100),
    pays character varying(100) DEFAULT 'Cameroun'::character varying,
    categorie_id integer,
    actif boolean DEFAULT true,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.clients OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clients_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_id_seq OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clients_id_seq OWNED BY public.clients.id;


--
-- Name: droits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.droits (
    id integer NOT NULL,
    code character varying(100) NOT NULL,
    module character varying(50) NOT NULL,
    description text
);


ALTER TABLE public.droits OWNER TO postgres;

--
-- Name: droits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.droits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.droits_id_seq OWNER TO postgres;

--
-- Name: droits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.droits_id_seq OWNED BY public.droits.id;


--
-- Name: entrees_dons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.entrees_dons (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    donateur character varying(200),
    date_entree date DEFAULT CURRENT_DATE NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.entrees_dons OWNER TO postgres;

--
-- Name: entrees_dons_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.entrees_dons_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.entrees_dons_id_seq OWNER TO postgres;

--
-- Name: entrees_dons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.entrees_dons_id_seq OWNED BY public.entrees_dons.id;


--
-- Name: factures_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.factures_client (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    bon_livraison_id integer,
    client_id integer NOT NULL,
    date_facture date DEFAULT CURRENT_DATE NOT NULL,
    date_echeance date,
    type_vente character varying(20) DEFAULT 'CREDIT'::character varying NOT NULL,
    montant_ht numeric(15,2) DEFAULT 0 NOT NULL,
    montant_tva numeric(15,2) DEFAULT 0,
    montant_ttc numeric(15,2) DEFAULT 0 NOT NULL,
    remise_montant numeric(15,2) DEFAULT 0,
    montant_regle numeric(15,2) DEFAULT 0,
    solde numeric(15,2) GENERATED ALWAYS AS (((montant_ttc - remise_montant) - montant_regle)) STORED,
    statut character varying(30) DEFAULT 'NON_REGLÉE'::character varying NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.factures_client OWNER TO postgres;

--
-- Name: factures_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.factures_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.factures_client_id_seq OWNER TO postgres;

--
-- Name: factures_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.factures_client_id_seq OWNED BY public.factures_client.id;


--
-- Name: factures_fournisseur; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.factures_fournisseur (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    bon_reception_id integer,
    fournisseur_id integer NOT NULL,
    date_facture date DEFAULT CURRENT_DATE NOT NULL,
    date_echeance date,
    montant_ht numeric(15,2) DEFAULT 0 NOT NULL,
    montant_tva numeric(15,2) DEFAULT 0,
    montant_ttc numeric(15,2) DEFAULT 0 NOT NULL,
    montant_regle numeric(15,2) DEFAULT 0,
    solde numeric(15,2) GENERATED ALWAYS AS ((montant_ttc - montant_regle)) STORED,
    statut character varying(30) DEFAULT 'NON_REGLÉE'::character varying NOT NULL,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.factures_fournisseur OWNER TO postgres;

--
-- Name: factures_fournisseur_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.factures_fournisseur_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.factures_fournisseur_id_seq OWNER TO postgres;

--
-- Name: factures_fournisseur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.factures_fournisseur_id_seq OWNED BY public.factures_fournisseur.id;


--
-- Name: familles_produits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.familles_produits (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    libelle character varying(150) NOT NULL,
    description text,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.familles_produits OWNER TO postgres;

--
-- Name: familles_produits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.familles_produits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.familles_produits_id_seq OWNER TO postgres;

--
-- Name: familles_produits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.familles_produits_id_seq OWNED BY public.familles_produits.id;


--
-- Name: fournisseurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fournisseurs (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    raison_sociale character varying(200) NOT NULL,
    contact character varying(150),
    telephone character varying(50),
    email character varying(150),
    adresse text,
    ville character varying(100),
    pays character varying(100) DEFAULT 'Cameroun'::character varying,
    actif boolean DEFAULT true,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.fournisseurs OWNER TO postgres;

--
-- Name: fournisseurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fournisseurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fournisseurs_id_seq OWNER TO postgres;

--
-- Name: fournisseurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fournisseurs_id_seq OWNED BY public.fournisseurs.id;


--
-- Name: fractions_produit; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fractions_produit (
    id integer NOT NULL,
    produit_pere_id integer NOT NULL,
    produit_fils_id integer NOT NULL,
    quantite_fils numeric(15,3) NOT NULL,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.fractions_produit OWNER TO postgres;

--
-- Name: fractions_produit_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fractions_produit_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fractions_produit_id_seq OWNER TO postgres;

--
-- Name: fractions_produit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fractions_produit_id_seq OWNED BY public.fractions_produit.id;


--
-- Name: groupes_droits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groupes_droits (
    groupe_id integer NOT NULL,
    droit_id integer NOT NULL
);


ALTER TABLE public.groupes_droits OWNER TO postgres;

--
-- Name: groupes_utilisateurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groupes_utilisateurs (
    id integer NOT NULL,
    nom character varying(100) NOT NULL,
    description text,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.groupes_utilisateurs OWNER TO postgres;

--
-- Name: groupes_utilisateurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.groupes_utilisateurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.groupes_utilisateurs_id_seq OWNER TO postgres;

--
-- Name: groupes_utilisateurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.groupes_utilisateurs_id_seq OWNED BY public.groupes_utilisateurs.id;


--
-- Name: lignes_bon_entree; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_bon_entree (
    id integer NOT NULL,
    bon_entree_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite numeric(15,3) NOT NULL,
    prix_unitaire numeric(15,2) DEFAULT 0
);


ALTER TABLE public.lignes_bon_entree OWNER TO postgres;

--
-- Name: lignes_bon_entree_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_bon_entree_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_bon_entree_id_seq OWNER TO postgres;

--
-- Name: lignes_bon_entree_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_bon_entree_id_seq OWNED BY public.lignes_bon_entree.id;


--
-- Name: lignes_bon_sortie; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_bon_sortie (
    id integer NOT NULL,
    bon_sortie_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite numeric(15,3) NOT NULL,
    prix_unitaire numeric(15,2) DEFAULT 0,
    observations text
);


ALTER TABLE public.lignes_bon_sortie OWNER TO postgres;

--
-- Name: lignes_bon_sortie_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_bon_sortie_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_bon_sortie_id_seq OWNER TO postgres;

--
-- Name: lignes_bon_sortie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_bon_sortie_id_seq OWNED BY public.lignes_bon_sortie.id;


--
-- Name: lignes_commande_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_commande_client (
    id integer NOT NULL,
    bon_commande_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite_commandee numeric(15,3) NOT NULL,
    quantite_livree numeric(15,3) DEFAULT 0,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    remise_pct numeric(5,2) DEFAULT 0
);


ALTER TABLE public.lignes_commande_client OWNER TO postgres;

--
-- Name: lignes_commande_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_commande_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_commande_client_id_seq OWNER TO postgres;

--
-- Name: lignes_commande_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_commande_client_id_seq OWNED BY public.lignes_commande_client.id;


--
-- Name: lignes_commande_fournisseur; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_commande_fournisseur (
    id integer NOT NULL,
    bon_commande_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite_commandee numeric(15,3) NOT NULL,
    quantite_recue numeric(15,3) DEFAULT 0,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    montant_ht numeric(15,2) GENERATED ALWAYS AS ((quantite_commandee * prix_unitaire_ht)) STORED
);


ALTER TABLE public.lignes_commande_fournisseur OWNER TO postgres;

--
-- Name: lignes_commande_fournisseur_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_commande_fournisseur_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_commande_fournisseur_id_seq OWNER TO postgres;

--
-- Name: lignes_commande_fournisseur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_commande_fournisseur_id_seq OWNED BY public.lignes_commande_fournisseur.id;


--
-- Name: lignes_entree_don; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_entree_don (
    id integer NOT NULL,
    entree_don_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite numeric(15,3) NOT NULL,
    valeur_estimee numeric(15,2) DEFAULT 0,
    date_peremption date
);


ALTER TABLE public.lignes_entree_don OWNER TO postgres;

--
-- Name: lignes_entree_don_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_entree_don_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_entree_don_id_seq OWNER TO postgres;

--
-- Name: lignes_entree_don_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_entree_don_id_seq OWNED BY public.lignes_entree_don.id;


--
-- Name: lignes_facture_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_facture_client (
    id integer NOT NULL,
    facture_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite numeric(15,3) NOT NULL,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    remise_pct numeric(5,2) DEFAULT 0
);


ALTER TABLE public.lignes_facture_client OWNER TO postgres;

--
-- Name: lignes_facture_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_facture_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_facture_client_id_seq OWNER TO postgres;

--
-- Name: lignes_facture_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_facture_client_id_seq OWNED BY public.lignes_facture_client.id;


--
-- Name: lignes_livraison_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_livraison_client (
    id integer NOT NULL,
    bon_livraison_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite_livree numeric(15,3) NOT NULL,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    remise_pct numeric(5,2) DEFAULT 0
);


ALTER TABLE public.lignes_livraison_client OWNER TO postgres;

--
-- Name: lignes_livraison_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_livraison_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_livraison_client_id_seq OWNER TO postgres;

--
-- Name: lignes_livraison_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_livraison_client_id_seq OWNED BY public.lignes_livraison_client.id;


--
-- Name: lignes_reception; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_reception (
    id integer NOT NULL,
    bon_reception_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite_recue numeric(15,3) NOT NULL,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    date_peremption date
);


ALTER TABLE public.lignes_reception OWNER TO postgres;

--
-- Name: lignes_reception_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_reception_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_reception_id_seq OWNER TO postgres;

--
-- Name: lignes_reception_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_reception_id_seq OWNED BY public.lignes_reception.id;


--
-- Name: lignes_vente_comptant; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lignes_vente_comptant (
    id integer NOT NULL,
    vente_comptant_id integer NOT NULL,
    produit_id integer NOT NULL,
    quantite numeric(15,3) NOT NULL,
    prix_unitaire_ht numeric(15,2) NOT NULL,
    tva numeric(5,2) DEFAULT 0,
    remise_pct numeric(5,2) DEFAULT 0
);


ALTER TABLE public.lignes_vente_comptant OWNER TO postgres;

--
-- Name: lignes_vente_comptant_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lignes_vente_comptant_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lignes_vente_comptant_id_seq OWNER TO postgres;

--
-- Name: lignes_vente_comptant_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lignes_vente_comptant_id_seq OWNED BY public.lignes_vente_comptant.id;


--
-- Name: mouvements_stock; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mouvements_stock (
    id integer NOT NULL,
    produit_id integer NOT NULL,
    type_mouvement character varying(20) NOT NULL,
    source character varying(50) NOT NULL,
    source_id integer,
    quantite numeric(15,3) NOT NULL,
    stock_avant numeric(15,3) NOT NULL,
    stock_apres numeric(15,3) NOT NULL,
    date_mouvement timestamp without time zone DEFAULT now(),
    utilisateur_id integer
);


ALTER TABLE public.mouvements_stock OWNER TO postgres;

--
-- Name: mouvements_stock_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mouvements_stock_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mouvements_stock_id_seq OWNER TO postgres;

--
-- Name: mouvements_stock_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mouvements_stock_id_seq OWNED BY public.mouvements_stock.id;


--
-- Name: produits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.produits (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    designation character varying(200) NOT NULL,
    famille_id integer NOT NULL,
    est_pere boolean DEFAULT false,
    unite character varying(30),
    prix_achat_ht numeric(15,2) DEFAULT 0,
    prix_vente_ht numeric(15,2) DEFAULT 0,
    tva numeric(5,2) DEFAULT 0,
    stock_actuel numeric(15,3) DEFAULT 0,
    stock_minimum numeric(15,3) DEFAULT 0,
    date_peremption date,
    actif boolean DEFAULT true,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.produits OWNER TO postgres;

--
-- Name: produits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.produits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.produits_id_seq OWNER TO postgres;

--
-- Name: produits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.produits_id_seq OWNED BY public.produits.id;


--
-- Name: reglements_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reglements_client (
    id integer NOT NULL,
    facture_id integer NOT NULL,
    date_reglement date DEFAULT CURRENT_DATE NOT NULL,
    montant numeric(15,2) NOT NULL,
    mode_paiement character varying(50),
    reference character varying(100),
    banque_id integer,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.reglements_client OWNER TO postgres;

--
-- Name: reglements_client_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reglements_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reglements_client_id_seq OWNER TO postgres;

--
-- Name: reglements_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reglements_client_id_seq OWNED BY public.reglements_client.id;


--
-- Name: reglements_fournisseur; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reglements_fournisseur (
    id integer NOT NULL,
    facture_id integer NOT NULL,
    date_reglement date DEFAULT CURRENT_DATE NOT NULL,
    montant numeric(15,2) NOT NULL,
    mode_paiement character varying(50),
    reference character varying(100),
    banque_id integer,
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.reglements_fournisseur OWNER TO postgres;

--
-- Name: reglements_fournisseur_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reglements_fournisseur_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reglements_fournisseur_id_seq OWNER TO postgres;

--
-- Name: reglements_fournisseur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reglements_fournisseur_id_seq OWNED BY public.reglements_fournisseur.id;


--
-- Name: utilisateurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.utilisateurs (
    id integer NOT NULL,
    nom character varying(100) NOT NULL,
    prenom character varying(100),
    login character varying(100) NOT NULL,
    mot_de_passe text NOT NULL,
    email character varying(150),
    groupe_id integer,
    actif boolean DEFAULT true,
    date_creation timestamp without time zone DEFAULT now(),
    derniere_connexion timestamp without time zone
);


ALTER TABLE public.utilisateurs OWNER TO postgres;

--
-- Name: utilisateurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.utilisateurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.utilisateurs_id_seq OWNER TO postgres;

--
-- Name: utilisateurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.utilisateurs_id_seq OWNED BY public.utilisateurs.id;


--
-- Name: ventes_comptant; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ventes_comptant (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    client_id integer,
    date_vente date DEFAULT CURRENT_DATE NOT NULL,
    montant_ht numeric(15,2) DEFAULT 0 NOT NULL,
    montant_tva numeric(15,2) DEFAULT 0,
    montant_ttc numeric(15,2) DEFAULT 0 NOT NULL,
    remise_montant numeric(15,2) DEFAULT 0,
    montant_percu numeric(15,2) DEFAULT 0 NOT NULL,
    monnaie_rendue numeric(15,2) GENERATED ALWAYS AS ((montant_percu - (montant_ttc - remise_montant))) STORED,
    mode_paiement character varying(50),
    notes text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.ventes_comptant OWNER TO postgres;

--
-- Name: ventes_comptant_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ventes_comptant_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ventes_comptant_id_seq OWNER TO postgres;

--
-- Name: ventes_comptant_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ventes_comptant_id_seq OWNED BY public.ventes_comptant.id;


--
-- Name: versements_banque; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.versements_banque (
    id integer NOT NULL,
    banque_id integer NOT NULL,
    montant numeric(15,2) NOT NULL,
    date_versement date DEFAULT CURRENT_DATE NOT NULL,
    reference character varying(100),
    motif text,
    utilisateur_id integer,
    date_creation timestamp without time zone DEFAULT now()
);


ALTER TABLE public.versements_banque OWNER TO postgres;

--
-- Name: versements_banque_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.versements_banque_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.versements_banque_id_seq OWNER TO postgres;

--
-- Name: versements_banque_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.versements_banque_id_seq OWNED BY public.versements_banque.id;


--
-- Name: audit_log id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_log ALTER COLUMN id SET DEFAULT nextval('public.audit_log_id_seq'::regclass);


--
-- Name: banques id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.banques ALTER COLUMN id SET DEFAULT nextval('public.banques_id_seq'::regclass);


--
-- Name: bons_commande_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_client ALTER COLUMN id SET DEFAULT nextval('public.bons_commande_client_id_seq'::regclass);


--
-- Name: bons_commande_fournisseur id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_fournisseur ALTER COLUMN id SET DEFAULT nextval('public.bons_commande_fournisseur_id_seq'::regclass);


--
-- Name: bons_entree id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_entree ALTER COLUMN id SET DEFAULT nextval('public.bons_entree_id_seq'::regclass);


--
-- Name: bons_livraison_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client ALTER COLUMN id SET DEFAULT nextval('public.bons_livraison_client_id_seq'::regclass);


--
-- Name: bons_reception id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception ALTER COLUMN id SET DEFAULT nextval('public.bons_reception_id_seq'::regclass);


--
-- Name: bons_sortie id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_sortie ALTER COLUMN id SET DEFAULT nextval('public.bons_sortie_id_seq'::regclass);


--
-- Name: categories_clients id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_clients ALTER COLUMN id SET DEFAULT nextval('public.categories_clients_id_seq'::regclass);


--
-- Name: clients id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients ALTER COLUMN id SET DEFAULT nextval('public.clients_id_seq'::regclass);


--
-- Name: droits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.droits ALTER COLUMN id SET DEFAULT nextval('public.droits_id_seq'::regclass);


--
-- Name: entrees_dons id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrees_dons ALTER COLUMN id SET DEFAULT nextval('public.entrees_dons_id_seq'::regclass);


--
-- Name: factures_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client ALTER COLUMN id SET DEFAULT nextval('public.factures_client_id_seq'::regclass);


--
-- Name: factures_fournisseur id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur ALTER COLUMN id SET DEFAULT nextval('public.factures_fournisseur_id_seq'::regclass);


--
-- Name: familles_produits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familles_produits ALTER COLUMN id SET DEFAULT nextval('public.familles_produits_id_seq'::regclass);


--
-- Name: fournisseurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fournisseurs ALTER COLUMN id SET DEFAULT nextval('public.fournisseurs_id_seq'::regclass);


--
-- Name: fractions_produit id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fractions_produit ALTER COLUMN id SET DEFAULT nextval('public.fractions_produit_id_seq'::regclass);


--
-- Name: groupes_utilisateurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateurs ALTER COLUMN id SET DEFAULT nextval('public.groupes_utilisateurs_id_seq'::regclass);


--
-- Name: lignes_bon_entree id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_entree ALTER COLUMN id SET DEFAULT nextval('public.lignes_bon_entree_id_seq'::regclass);


--
-- Name: lignes_bon_sortie id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_sortie ALTER COLUMN id SET DEFAULT nextval('public.lignes_bon_sortie_id_seq'::regclass);


--
-- Name: lignes_commande_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_client ALTER COLUMN id SET DEFAULT nextval('public.lignes_commande_client_id_seq'::regclass);


--
-- Name: lignes_commande_fournisseur id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_fournisseur ALTER COLUMN id SET DEFAULT nextval('public.lignes_commande_fournisseur_id_seq'::regclass);


--
-- Name: lignes_entree_don id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_entree_don ALTER COLUMN id SET DEFAULT nextval('public.lignes_entree_don_id_seq'::regclass);


--
-- Name: lignes_facture_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_facture_client ALTER COLUMN id SET DEFAULT nextval('public.lignes_facture_client_id_seq'::regclass);


--
-- Name: lignes_livraison_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_livraison_client ALTER COLUMN id SET DEFAULT nextval('public.lignes_livraison_client_id_seq'::regclass);


--
-- Name: lignes_reception id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_reception ALTER COLUMN id SET DEFAULT nextval('public.lignes_reception_id_seq'::regclass);


--
-- Name: lignes_vente_comptant id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_vente_comptant ALTER COLUMN id SET DEFAULT nextval('public.lignes_vente_comptant_id_seq'::regclass);


--
-- Name: mouvements_stock id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mouvements_stock ALTER COLUMN id SET DEFAULT nextval('public.mouvements_stock_id_seq'::regclass);


--
-- Name: produits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produits ALTER COLUMN id SET DEFAULT nextval('public.produits_id_seq'::regclass);


--
-- Name: reglements_client id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_client ALTER COLUMN id SET DEFAULT nextval('public.reglements_client_id_seq'::regclass);


--
-- Name: reglements_fournisseur id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_fournisseur ALTER COLUMN id SET DEFAULT nextval('public.reglements_fournisseur_id_seq'::regclass);


--
-- Name: utilisateurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs ALTER COLUMN id SET DEFAULT nextval('public.utilisateurs_id_seq'::regclass);


--
-- Name: ventes_comptant id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventes_comptant ALTER COLUMN id SET DEFAULT nextval('public.ventes_comptant_id_seq'::regclass);


--
-- Name: versements_banque id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.versements_banque ALTER COLUMN id SET DEFAULT nextval('public.versements_banque_id_seq'::regclass);


--
-- Name: audit_log audit_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_log
    ADD CONSTRAINT audit_log_pkey PRIMARY KEY (id);


--
-- Name: banques banques_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.banques
    ADD CONSTRAINT banques_code_key UNIQUE (code);


--
-- Name: banques banques_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.banques
    ADD CONSTRAINT banques_pkey PRIMARY KEY (id);


--
-- Name: bons_commande_client bons_commande_client_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_client
    ADD CONSTRAINT bons_commande_client_numero_key UNIQUE (numero);


--
-- Name: bons_commande_client bons_commande_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_client
    ADD CONSTRAINT bons_commande_client_pkey PRIMARY KEY (id);


--
-- Name: bons_commande_fournisseur bons_commande_fournisseur_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_fournisseur
    ADD CONSTRAINT bons_commande_fournisseur_numero_key UNIQUE (numero);


--
-- Name: bons_commande_fournisseur bons_commande_fournisseur_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_fournisseur
    ADD CONSTRAINT bons_commande_fournisseur_pkey PRIMARY KEY (id);


--
-- Name: bons_entree bons_entree_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_entree
    ADD CONSTRAINT bons_entree_numero_key UNIQUE (numero);


--
-- Name: bons_entree bons_entree_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_entree
    ADD CONSTRAINT bons_entree_pkey PRIMARY KEY (id);


--
-- Name: bons_livraison_client bons_livraison_client_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client
    ADD CONSTRAINT bons_livraison_client_numero_key UNIQUE (numero);


--
-- Name: bons_livraison_client bons_livraison_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client
    ADD CONSTRAINT bons_livraison_client_pkey PRIMARY KEY (id);


--
-- Name: bons_reception bons_reception_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception
    ADD CONSTRAINT bons_reception_numero_key UNIQUE (numero);


--
-- Name: bons_reception bons_reception_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception
    ADD CONSTRAINT bons_reception_pkey PRIMARY KEY (id);


--
-- Name: bons_sortie bons_sortie_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_sortie
    ADD CONSTRAINT bons_sortie_numero_key UNIQUE (numero);


--
-- Name: bons_sortie bons_sortie_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_sortie
    ADD CONSTRAINT bons_sortie_pkey PRIMARY KEY (id);


--
-- Name: categories_clients categories_clients_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_clients
    ADD CONSTRAINT categories_clients_code_key UNIQUE (code);


--
-- Name: categories_clients categories_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_clients
    ADD CONSTRAINT categories_clients_pkey PRIMARY KEY (id);


--
-- Name: clients clients_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_code_key UNIQUE (code);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: droits droits_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.droits
    ADD CONSTRAINT droits_code_key UNIQUE (code);


--
-- Name: droits droits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.droits
    ADD CONSTRAINT droits_pkey PRIMARY KEY (id);


--
-- Name: entrees_dons entrees_dons_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrees_dons
    ADD CONSTRAINT entrees_dons_numero_key UNIQUE (numero);


--
-- Name: entrees_dons entrees_dons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrees_dons
    ADD CONSTRAINT entrees_dons_pkey PRIMARY KEY (id);


--
-- Name: factures_client factures_client_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client
    ADD CONSTRAINT factures_client_numero_key UNIQUE (numero);


--
-- Name: factures_client factures_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client
    ADD CONSTRAINT factures_client_pkey PRIMARY KEY (id);


--
-- Name: factures_fournisseur factures_fournisseur_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur
    ADD CONSTRAINT factures_fournisseur_numero_key UNIQUE (numero);


--
-- Name: factures_fournisseur factures_fournisseur_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur
    ADD CONSTRAINT factures_fournisseur_pkey PRIMARY KEY (id);


--
-- Name: familles_produits familles_produits_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familles_produits
    ADD CONSTRAINT familles_produits_code_key UNIQUE (code);


--
-- Name: familles_produits familles_produits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familles_produits
    ADD CONSTRAINT familles_produits_pkey PRIMARY KEY (id);


--
-- Name: fournisseurs fournisseurs_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fournisseurs
    ADD CONSTRAINT fournisseurs_code_key UNIQUE (code);


--
-- Name: fournisseurs fournisseurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fournisseurs
    ADD CONSTRAINT fournisseurs_pkey PRIMARY KEY (id);


--
-- Name: fractions_produit fractions_produit_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fractions_produit
    ADD CONSTRAINT fractions_produit_pkey PRIMARY KEY (id);


--
-- Name: fractions_produit fractions_produit_produit_pere_id_produit_fils_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fractions_produit
    ADD CONSTRAINT fractions_produit_produit_pere_id_produit_fils_id_key UNIQUE (produit_pere_id, produit_fils_id);


--
-- Name: groupes_droits groupes_droits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_droits
    ADD CONSTRAINT groupes_droits_pkey PRIMARY KEY (groupe_id, droit_id);


--
-- Name: groupes_utilisateurs groupes_utilisateurs_nom_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateurs
    ADD CONSTRAINT groupes_utilisateurs_nom_key UNIQUE (nom);


--
-- Name: groupes_utilisateurs groupes_utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateurs
    ADD CONSTRAINT groupes_utilisateurs_pkey PRIMARY KEY (id);


--
-- Name: lignes_bon_entree lignes_bon_entree_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_entree
    ADD CONSTRAINT lignes_bon_entree_pkey PRIMARY KEY (id);


--
-- Name: lignes_bon_sortie lignes_bon_sortie_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_sortie
    ADD CONSTRAINT lignes_bon_sortie_pkey PRIMARY KEY (id);


--
-- Name: lignes_commande_client lignes_commande_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_client
    ADD CONSTRAINT lignes_commande_client_pkey PRIMARY KEY (id);


--
-- Name: lignes_commande_fournisseur lignes_commande_fournisseur_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_fournisseur
    ADD CONSTRAINT lignes_commande_fournisseur_pkey PRIMARY KEY (id);


--
-- Name: lignes_entree_don lignes_entree_don_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_entree_don
    ADD CONSTRAINT lignes_entree_don_pkey PRIMARY KEY (id);


--
-- Name: lignes_facture_client lignes_facture_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_facture_client
    ADD CONSTRAINT lignes_facture_client_pkey PRIMARY KEY (id);


--
-- Name: lignes_livraison_client lignes_livraison_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_livraison_client
    ADD CONSTRAINT lignes_livraison_client_pkey PRIMARY KEY (id);


--
-- Name: lignes_reception lignes_reception_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_reception
    ADD CONSTRAINT lignes_reception_pkey PRIMARY KEY (id);


--
-- Name: lignes_vente_comptant lignes_vente_comptant_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_vente_comptant
    ADD CONSTRAINT lignes_vente_comptant_pkey PRIMARY KEY (id);


--
-- Name: mouvements_stock mouvements_stock_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mouvements_stock
    ADD CONSTRAINT mouvements_stock_pkey PRIMARY KEY (id);


--
-- Name: produits produits_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produits
    ADD CONSTRAINT produits_code_key UNIQUE (code);


--
-- Name: produits produits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produits
    ADD CONSTRAINT produits_pkey PRIMARY KEY (id);


--
-- Name: reglements_client reglements_client_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_client
    ADD CONSTRAINT reglements_client_pkey PRIMARY KEY (id);


--
-- Name: reglements_fournisseur reglements_fournisseur_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_fournisseur
    ADD CONSTRAINT reglements_fournisseur_pkey PRIMARY KEY (id);


--
-- Name: utilisateurs utilisateurs_login_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_login_key UNIQUE (login);


--
-- Name: utilisateurs utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_pkey PRIMARY KEY (id);


--
-- Name: ventes_comptant ventes_comptant_numero_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventes_comptant
    ADD CONSTRAINT ventes_comptant_numero_key UNIQUE (numero);


--
-- Name: ventes_comptant ventes_comptant_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventes_comptant
    ADD CONSTRAINT ventes_comptant_pkey PRIMARY KEY (id);


--
-- Name: versements_banque versements_banque_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.versements_banque
    ADD CONSTRAINT versements_banque_pkey PRIMARY KEY (id);


--
-- Name: banques trg_audit_banques; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_banques AFTER INSERT OR DELETE OR UPDATE ON public.banques FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_commande_client trg_audit_bons_commande_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_commande_client AFTER INSERT OR DELETE OR UPDATE ON public.bons_commande_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_commande_fournisseur trg_audit_bons_commande_fournisseur; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_commande_fournisseur AFTER INSERT OR DELETE OR UPDATE ON public.bons_commande_fournisseur FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_entree trg_audit_bons_entree; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_entree AFTER INSERT OR DELETE OR UPDATE ON public.bons_entree FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_livraison_client trg_audit_bons_livraison_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_livraison_client AFTER INSERT OR DELETE OR UPDATE ON public.bons_livraison_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_reception trg_audit_bons_reception; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_reception AFTER INSERT OR DELETE OR UPDATE ON public.bons_reception FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_sortie trg_audit_bons_sortie; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_bons_sortie AFTER INSERT OR DELETE OR UPDATE ON public.bons_sortie FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: categories_clients trg_audit_categories_clients; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_categories_clients AFTER INSERT OR DELETE OR UPDATE ON public.categories_clients FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: clients trg_audit_clients; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_clients AFTER INSERT OR DELETE OR UPDATE ON public.clients FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: droits trg_audit_droits; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_droits AFTER INSERT OR DELETE OR UPDATE ON public.droits FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: entrees_dons trg_audit_entrees_dons; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_entrees_dons AFTER INSERT OR DELETE OR UPDATE ON public.entrees_dons FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: factures_client trg_audit_factures_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_factures_client AFTER INSERT OR DELETE OR UPDATE ON public.factures_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: factures_fournisseur trg_audit_factures_fournisseur; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_factures_fournisseur AFTER INSERT OR DELETE OR UPDATE ON public.factures_fournisseur FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: familles_produits trg_audit_familles_produits; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_familles_produits AFTER INSERT OR DELETE OR UPDATE ON public.familles_produits FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: fournisseurs trg_audit_fournisseurs; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_fournisseurs AFTER INSERT OR DELETE OR UPDATE ON public.fournisseurs FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: fractions_produit trg_audit_fractions_produit; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_fractions_produit AFTER INSERT OR DELETE OR UPDATE ON public.fractions_produit FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: groupes_droits trg_audit_groupes_droits; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_groupes_droits AFTER INSERT OR DELETE OR UPDATE ON public.groupes_droits FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: groupes_utilisateurs trg_audit_groupes_utilisateurs; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_groupes_utilisateurs AFTER INSERT OR DELETE OR UPDATE ON public.groupes_utilisateurs FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_bon_entree trg_audit_lignes_bon_entree; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_bon_entree AFTER INSERT OR DELETE OR UPDATE ON public.lignes_bon_entree FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_bon_sortie trg_audit_lignes_bon_sortie; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_bon_sortie AFTER INSERT OR DELETE OR UPDATE ON public.lignes_bon_sortie FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_commande_client trg_audit_lignes_commande_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_commande_client AFTER INSERT OR DELETE OR UPDATE ON public.lignes_commande_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_commande_fournisseur trg_audit_lignes_commande_fournisseur; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_commande_fournisseur AFTER INSERT OR DELETE OR UPDATE ON public.lignes_commande_fournisseur FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_entree_don trg_audit_lignes_entree_don; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_entree_don AFTER INSERT OR DELETE OR UPDATE ON public.lignes_entree_don FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_facture_client trg_audit_lignes_facture_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_facture_client AFTER INSERT OR DELETE OR UPDATE ON public.lignes_facture_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_livraison_client trg_audit_lignes_livraison_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_livraison_client AFTER INSERT OR DELETE OR UPDATE ON public.lignes_livraison_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_reception trg_audit_lignes_reception; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_reception AFTER INSERT OR DELETE OR UPDATE ON public.lignes_reception FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: lignes_vente_comptant trg_audit_lignes_vente_comptant; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_lignes_vente_comptant AFTER INSERT OR DELETE OR UPDATE ON public.lignes_vente_comptant FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: mouvements_stock trg_audit_mouvements_stock; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_mouvements_stock AFTER INSERT OR DELETE OR UPDATE ON public.mouvements_stock FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: produits trg_audit_produits; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_produits AFTER INSERT OR DELETE OR UPDATE ON public.produits FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: reglements_client trg_audit_reglements_client; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_reglements_client AFTER INSERT OR DELETE OR UPDATE ON public.reglements_client FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: reglements_fournisseur trg_audit_reglements_fournisseur; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_reglements_fournisseur AFTER INSERT OR DELETE OR UPDATE ON public.reglements_fournisseur FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: utilisateurs trg_audit_utilisateurs; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_utilisateurs AFTER INSERT OR DELETE OR UPDATE ON public.utilisateurs FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: ventes_comptant trg_audit_ventes_comptant; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_ventes_comptant AFTER INSERT OR DELETE OR UPDATE ON public.ventes_comptant FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: versements_banque trg_audit_versements_banque; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_audit_versements_banque AFTER INSERT OR DELETE OR UPDATE ON public.versements_banque FOR EACH ROW EXECUTE FUNCTION public.fn_audit_log();


--
-- Name: bons_commande_client bons_commande_client_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_client
    ADD CONSTRAINT bons_commande_client_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: bons_commande_client bons_commande_client_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_client
    ADD CONSTRAINT bons_commande_client_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: bons_commande_fournisseur bons_commande_fournisseur_fournisseur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_fournisseur
    ADD CONSTRAINT bons_commande_fournisseur_fournisseur_id_fkey FOREIGN KEY (fournisseur_id) REFERENCES public.fournisseurs(id) ON DELETE CASCADE;


--
-- Name: bons_commande_fournisseur bons_commande_fournisseur_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_commande_fournisseur
    ADD CONSTRAINT bons_commande_fournisseur_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: bons_entree bons_entree_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_entree
    ADD CONSTRAINT bons_entree_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: bons_livraison_client bons_livraison_client_bon_commande_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client
    ADD CONSTRAINT bons_livraison_client_bon_commande_id_fkey FOREIGN KEY (bon_commande_id) REFERENCES public.bons_commande_client(id) ON DELETE CASCADE;


--
-- Name: bons_livraison_client bons_livraison_client_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client
    ADD CONSTRAINT bons_livraison_client_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: bons_livraison_client bons_livraison_client_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_livraison_client
    ADD CONSTRAINT bons_livraison_client_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: bons_reception bons_reception_bon_commande_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception
    ADD CONSTRAINT bons_reception_bon_commande_id_fkey FOREIGN KEY (bon_commande_id) REFERENCES public.bons_commande_fournisseur(id) ON DELETE CASCADE;


--
-- Name: bons_reception bons_reception_fournisseur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception
    ADD CONSTRAINT bons_reception_fournisseur_id_fkey FOREIGN KEY (fournisseur_id) REFERENCES public.fournisseurs(id) ON DELETE CASCADE;


--
-- Name: bons_reception bons_reception_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_reception
    ADD CONSTRAINT bons_reception_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: bons_sortie bons_sortie_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bons_sortie
    ADD CONSTRAINT bons_sortie_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: clients clients_categorie_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_categorie_id_fkey FOREIGN KEY (categorie_id) REFERENCES public.categories_clients(id) ON DELETE SET NULL;


--
-- Name: entrees_dons entrees_dons_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrees_dons
    ADD CONSTRAINT entrees_dons_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: factures_client factures_client_bon_livraison_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client
    ADD CONSTRAINT factures_client_bon_livraison_id_fkey FOREIGN KEY (bon_livraison_id) REFERENCES public.bons_livraison_client(id) ON DELETE CASCADE;


--
-- Name: factures_client factures_client_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client
    ADD CONSTRAINT factures_client_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: factures_client factures_client_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_client
    ADD CONSTRAINT factures_client_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: factures_fournisseur factures_fournisseur_bon_reception_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur
    ADD CONSTRAINT factures_fournisseur_bon_reception_id_fkey FOREIGN KEY (bon_reception_id) REFERENCES public.bons_reception(id) ON DELETE CASCADE;


--
-- Name: factures_fournisseur factures_fournisseur_fournisseur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur
    ADD CONSTRAINT factures_fournisseur_fournisseur_id_fkey FOREIGN KEY (fournisseur_id) REFERENCES public.fournisseurs(id) ON DELETE CASCADE;


--
-- Name: factures_fournisseur factures_fournisseur_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.factures_fournisseur
    ADD CONSTRAINT factures_fournisseur_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: fractions_produit fractions_produit_produit_fils_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fractions_produit
    ADD CONSTRAINT fractions_produit_produit_fils_id_fkey FOREIGN KEY (produit_fils_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: fractions_produit fractions_produit_produit_pere_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fractions_produit
    ADD CONSTRAINT fractions_produit_produit_pere_id_fkey FOREIGN KEY (produit_pere_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: groupes_droits groupes_droits_droit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_droits
    ADD CONSTRAINT groupes_droits_droit_id_fkey FOREIGN KEY (droit_id) REFERENCES public.droits(id) ON DELETE CASCADE;


--
-- Name: groupes_droits groupes_droits_groupe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_droits
    ADD CONSTRAINT groupes_droits_groupe_id_fkey FOREIGN KEY (groupe_id) REFERENCES public.groupes_utilisateurs(id) ON DELETE CASCADE;


--
-- Name: lignes_bon_entree lignes_bon_entree_bon_entree_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_entree
    ADD CONSTRAINT lignes_bon_entree_bon_entree_id_fkey FOREIGN KEY (bon_entree_id) REFERENCES public.bons_entree(id) ON DELETE CASCADE;


--
-- Name: lignes_bon_entree lignes_bon_entree_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_entree
    ADD CONSTRAINT lignes_bon_entree_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_bon_sortie lignes_bon_sortie_bon_sortie_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_sortie
    ADD CONSTRAINT lignes_bon_sortie_bon_sortie_id_fkey FOREIGN KEY (bon_sortie_id) REFERENCES public.bons_sortie(id) ON DELETE CASCADE;


--
-- Name: lignes_bon_sortie lignes_bon_sortie_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_bon_sortie
    ADD CONSTRAINT lignes_bon_sortie_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_commande_client lignes_commande_client_bon_commande_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_client
    ADD CONSTRAINT lignes_commande_client_bon_commande_id_fkey FOREIGN KEY (bon_commande_id) REFERENCES public.bons_commande_client(id) ON DELETE CASCADE;


--
-- Name: lignes_commande_client lignes_commande_client_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_client
    ADD CONSTRAINT lignes_commande_client_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_commande_fournisseur lignes_commande_fournisseur_bon_commande_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_fournisseur
    ADD CONSTRAINT lignes_commande_fournisseur_bon_commande_id_fkey FOREIGN KEY (bon_commande_id) REFERENCES public.bons_commande_fournisseur(id) ON DELETE CASCADE;


--
-- Name: lignes_commande_fournisseur lignes_commande_fournisseur_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_commande_fournisseur
    ADD CONSTRAINT lignes_commande_fournisseur_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_entree_don lignes_entree_don_entree_don_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_entree_don
    ADD CONSTRAINT lignes_entree_don_entree_don_id_fkey FOREIGN KEY (entree_don_id) REFERENCES public.entrees_dons(id) ON DELETE CASCADE;


--
-- Name: lignes_entree_don lignes_entree_don_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_entree_don
    ADD CONSTRAINT lignes_entree_don_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_facture_client lignes_facture_client_facture_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_facture_client
    ADD CONSTRAINT lignes_facture_client_facture_id_fkey FOREIGN KEY (facture_id) REFERENCES public.factures_client(id) ON DELETE CASCADE;


--
-- Name: lignes_facture_client lignes_facture_client_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_facture_client
    ADD CONSTRAINT lignes_facture_client_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_livraison_client lignes_livraison_client_bon_livraison_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_livraison_client
    ADD CONSTRAINT lignes_livraison_client_bon_livraison_id_fkey FOREIGN KEY (bon_livraison_id) REFERENCES public.bons_livraison_client(id) ON DELETE CASCADE;


--
-- Name: lignes_livraison_client lignes_livraison_client_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_livraison_client
    ADD CONSTRAINT lignes_livraison_client_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_reception lignes_reception_bon_reception_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_reception
    ADD CONSTRAINT lignes_reception_bon_reception_id_fkey FOREIGN KEY (bon_reception_id) REFERENCES public.bons_reception(id) ON DELETE CASCADE;


--
-- Name: lignes_reception lignes_reception_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_reception
    ADD CONSTRAINT lignes_reception_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_vente_comptant lignes_vente_comptant_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_vente_comptant
    ADD CONSTRAINT lignes_vente_comptant_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: lignes_vente_comptant lignes_vente_comptant_vente_comptant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lignes_vente_comptant
    ADD CONSTRAINT lignes_vente_comptant_vente_comptant_id_fkey FOREIGN KEY (vente_comptant_id) REFERENCES public.ventes_comptant(id) ON DELETE CASCADE;


--
-- Name: mouvements_stock mouvements_stock_produit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mouvements_stock
    ADD CONSTRAINT mouvements_stock_produit_id_fkey FOREIGN KEY (produit_id) REFERENCES public.produits(id) ON DELETE CASCADE;


--
-- Name: mouvements_stock mouvements_stock_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mouvements_stock
    ADD CONSTRAINT mouvements_stock_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: produits produits_famille_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produits
    ADD CONSTRAINT produits_famille_id_fkey FOREIGN KEY (famille_id) REFERENCES public.familles_produits(id) ON DELETE CASCADE;


--
-- Name: reglements_client reglements_client_banque_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_client
    ADD CONSTRAINT reglements_client_banque_id_fkey FOREIGN KEY (banque_id) REFERENCES public.banques(id) ON DELETE SET NULL;


--
-- Name: reglements_client reglements_client_facture_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_client
    ADD CONSTRAINT reglements_client_facture_id_fkey FOREIGN KEY (facture_id) REFERENCES public.factures_client(id) ON DELETE CASCADE;


--
-- Name: reglements_client reglements_client_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_client
    ADD CONSTRAINT reglements_client_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: reglements_fournisseur reglements_fournisseur_banque_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_fournisseur
    ADD CONSTRAINT reglements_fournisseur_banque_id_fkey FOREIGN KEY (banque_id) REFERENCES public.banques(id) ON DELETE SET NULL;


--
-- Name: reglements_fournisseur reglements_fournisseur_facture_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_fournisseur
    ADD CONSTRAINT reglements_fournisseur_facture_id_fkey FOREIGN KEY (facture_id) REFERENCES public.factures_fournisseur(id) ON DELETE CASCADE;


--
-- Name: reglements_fournisseur reglements_fournisseur_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reglements_fournisseur
    ADD CONSTRAINT reglements_fournisseur_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: utilisateurs utilisateurs_groupe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_groupe_id_fkey FOREIGN KEY (groupe_id) REFERENCES public.groupes_utilisateurs(id) ON DELETE CASCADE;


--
-- Name: ventes_comptant ventes_comptant_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventes_comptant
    ADD CONSTRAINT ventes_comptant_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE SET NULL;


--
-- Name: ventes_comptant ventes_comptant_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventes_comptant
    ADD CONSTRAINT ventes_comptant_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- Name: versements_banque versements_banque_banque_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.versements_banque
    ADD CONSTRAINT versements_banque_banque_id_fkey FOREIGN KEY (banque_id) REFERENCES public.banques(id) ON DELETE CASCADE;


--
-- Name: versements_banque versements_banque_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.versements_banque
    ADD CONSTRAINT versements_banque_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict nY5PNw64ufkhu5jc6Dyb1BnWwmk1hPjWvEEAni0w7zheJZcMXpX7Yx1JfxWNlll

