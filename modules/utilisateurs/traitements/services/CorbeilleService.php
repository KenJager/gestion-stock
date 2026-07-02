<?php
// modules/Utilisateurs/Services/CorbeilleService.php
// Restauration spécifique aux tables du module Utilisateurs.

namespace utilisateurs\traitements\services;

class CorbeilleService {

    /**
     * Restaure un utilisateur depuis la corbeille.
     * Note : un utilisateur n'a pas de données "liées" en cascade
     * (les FK utilisateur_id des autres modules sont ON DELETE SET NULL).
     */
    public function restaurerUtilisateur(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);

        if ($donnees['table'] !== 'utilisateurs') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un utilisateur.');
        }

        $p = $donnees['principal'];

        // Vérifier que le login n'est pas déjà repris par un autre compte
        $sqlVerif = "SELECT 1 FROM utilisateurs WHERE login = :login";
        $existe = \Database::query($sqlVerif, [':login' => $p['login']])->fetch();
        if ($existe) {
            throw new \RuntimeException("Impossible de restaurer : le login « {$p['login']} » est déjà utilisé.");
        }

        // Vérifier que le groupe existe encore
        $sqlGroupe = "SELECT 1 FROM groupes_utilisateurs WHERE id = :id";
        $groupeExiste = \Database::query($sqlGroupe, [':id' => $p['groupe_id']])->fetch();
        $groupeId = $groupeExiste ? $p['groupe_id'] : null;

        $sql = "INSERT INTO utilisateurs
                    (nom, prenom, login, mot_de_passe, email, groupe_id, actif, date_creation, derniere_connexion)
                VALUES
                    (:nom, :prenom, :login, :mot_de_passe, :email, :groupe_id, :actif, :date_creation, :derniere_connexion)";

        \Database::query($sql, [
            ':nom'                => $p['nom'],
            ':prenom'             => $p['prenom'],
            ':login'              => $p['login'],
            ':mot_de_passe'       => $p['mot_de_passe'],
            ':email'              => $p['email'],
            ':groupe_id'          => $groupeId,
            ':actif'              => $p['actif'] === 't' || $p['actif'] === '1',
            ':date_creation'      => $p['date_creation'],
            ':derniere_connexion' => $p['derniere_connexion'] ?: null,
        ]);

        \Corbeille::supprimerFichier($fichier);
    }

    /**
     * Restaure un groupe avec ses droits et ses utilisateurs archivés en cascade.
     * Retourne un message de détail si certains utilisateurs n'ont pu être restaurés.
     */
    public function restaurerGroupe(string $fichier): ?string {
        $donnees = \Corbeille::charger($fichier);

        if ($donnees['table'] !== 'groupes_utilisateurs') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un groupe.');
        }

        $p = $donnees['principal'];

        if (\Database::query("SELECT 1 FROM groupes_utilisateurs WHERE nom = :nom", [':nom' => $p['nom']])->fetch()) {
            throw new \RuntimeException("Impossible de restaurer : le groupe « {$p['nom']} » existe déjà.");
        }

        $pdo = \Database::connexion();
        try {
            $pdo->beginTransaction();

            // Tenter de restaurer avec l'ID d'origine pour maintenir la cohérence
            $idOriginal = (int) $p['id'];
            $idPris = \Database::query(
                "SELECT 1 FROM groupes_utilisateurs WHERE id = :id",
                [':id' => $idOriginal]
            )->fetch();

            if ($idPris) {
                $row = \Database::query(
                    "INSERT INTO groupes_utilisateurs (nom, description, date_creation)
                     VALUES (:nom, :description, :date_creation) RETURNING id",
                    [':nom' => $p['nom'], ':description' => $p['description'], ':date_creation' => $p['date_creation']]
                )->fetch();
                $groupeId = (int) $row['id'];
            } else {
                \Database::query(
                    "INSERT INTO groupes_utilisateurs (id, nom, description, date_creation)
                     VALUES (:id, :nom, :description, :date_creation)",
                    [':id' => $idOriginal, ':nom' => $p['nom'], ':description' => $p['description'], ':date_creation' => $p['date_creation']]
                );
                $groupeId = $idOriginal;
                $pdo->query("SELECT setval(pg_get_serial_sequence('groupes_utilisateurs','id'), MAX(id)) FROM groupes_utilisateurs");
            }

            // Restaurer les droits liés
            if (!empty($donnees['liees']['groupes_droits'])) {
                $sqlDroit = "INSERT INTO groupes_droits (groupe_id, droit_id)
                             SELECT :groupe_id, :droit_id
                             WHERE EXISTS (SELECT 1 FROM droits WHERE id = :droit_id_check)";
                foreach ($donnees['liees']['groupes_droits'] as $ligne) {
                    \Database::query($sqlDroit, [
                        ':groupe_id'      => $groupeId,
                        ':droit_id'       => $ligne['droit_id'],
                        ':droit_id_check' => $ligne['droit_id'],
                    ]);
                }
            }

            // Restaurer les utilisateurs archivés en cascade
            $ignores = [];
            if (!empty($donnees['liees']['utilisateurs'])) {
                $sqlVerifLogin = "SELECT 1 FROM utilisateurs WHERE login = :login";
                $sqlUser = "INSERT INTO utilisateurs
                                (id, nom, prenom, login, mot_de_passe, email, groupe_id, actif, date_creation, derniere_connexion)
                            VALUES
                                (:id, :nom, :prenom, :login, :mot_de_passe, :email, :groupe_id, :actif, :date_creation, :derniere_connexion)";

                foreach ($donnees['liees']['utilisateurs'] as $u) {
                    if (\Database::query($sqlVerifLogin, [':login' => $u['login']])->fetch()) {
                        $ignores[] = $u['login'];
                        continue;
                    }
                    \Database::query($sqlUser, [
                        ':id'                 => (int) $u['id'],
                        ':nom'                => $u['nom'],
                        ':prenom'             => $u['prenom'],
                        ':login'              => $u['login'],
                        ':mot_de_passe'       => $u['mot_de_passe'],
                        ':email'              => $u['email'],
                        ':groupe_id'          => $groupeId,
                        ':actif'              => ($u['actif'] === 't' || $u['actif'] === '1' || $u['actif'] === true),
                        ':date_creation'      => $u['date_creation'],
                        ':derniere_connexion' => $u['derniere_connexion'] ?: null,
                    ]);
                }

                $pdo->query("SELECT setval(pg_get_serial_sequence('utilisateurs','id'), MAX(id)) FROM utilisateurs");
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        \Corbeille::supprimerFichier($fichier);

        if (!empty($ignores)) {
            $nbLies = count($donnees['liees']['utilisateurs']);
            $nbRes  = $nbLies - count($ignores);
            return "Groupe « {$p['nom']} » restauré avec {$nbRes}/{$nbLies} utilisateur(s). "
                 . "Login(s) non restauré(s) car déjà utilisé(s) : " . implode(', ', $ignores) . '.';
        }

        return null;
    }
}
