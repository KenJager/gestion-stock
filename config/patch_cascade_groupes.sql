-- Patch : CASCADE DELETE sur utilisateurs.groupe_id
-- Suppression du groupe => suppression automatique des utilisateurs du groupe

ALTER TABLE public.utilisateurs
    DROP CONSTRAINT IF EXISTS utilisateurs_groupe_id_fkey;

ALTER TABLE public.utilisateurs
    ADD CONSTRAINT utilisateurs_groupe_id_fkey
    FOREIGN KEY (groupe_id)
    REFERENCES public.groupes_utilisateurs (id)
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
