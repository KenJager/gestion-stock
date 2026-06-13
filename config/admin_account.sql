-- Script rapide pour les droits affichés dans votre capture d'écran

-- 1. Droits
INSERT INTO droits (code, module, description) VALUES
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

INSERT INTO groupes_utilisateurs (nom, description) 
VALUES ('ADMINISTRATEURS', 'Super administrateur - tous droits');

INSERT INTO groupes_droits (groupe_id, droit_id)
SELECT (SELECT id FROM groupes_utilisateurs WHERE nom = 'ADMINISTRATEURS'), id FROM droits;

INSERT INTO utilisateurs (nom, prenom, login, mot_de_passe, email, groupe_id, actif)
SELECT 'Admin', 'Système', 'admin', '$2a$10$Fsad3QmJoKQgBYoXku0IR.gkoLAI4VV.4OUOKsFQwtQFvQQ2cPsce', 'admin@stock.com', id, true
FROM groupes_utilisateurs WHERE nom = 'ADMINISTRATEURS';

-- login = admin
-- password = 12345