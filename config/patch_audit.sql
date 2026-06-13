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
