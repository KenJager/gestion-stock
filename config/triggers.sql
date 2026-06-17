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
 
