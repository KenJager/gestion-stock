<?php
// modules/Utilisateurs/Services/CorbeilleService.php
// Restauration spécifique aux tables du module Utilisateurs.

namespace utilisateurs\services;

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
     * Restaure un groupe d'utilisateurs ainsi que ses droits liés (groupes_droits).
     */
    public function restaurerGroupe(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);

        if ($donnees['table'] !== 'groupes_utilisateurs') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un groupe.');
        }

        $p = $donnees['principal'];

        $sqlVerif = "SELECT 1 FROM groupes_utilisateurs WHERE nom = :nom";
        $existe = \Database::query($sqlVerif, [':nom' => $p['nom']])->fetch();
        if ($existe) {
            throw new \RuntimeException("Impossible de restaurer : le groupe « {$p['nom']} » existe déjà.");
        }

        $pdo = \Database::connexion();
        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO groupes_utilisateurs (nom, description, date_creation)
                    VALUES (:nom, :description, :date_creation)
                    RETURNING id";
            $nouveauGroupe = \Database::query($sql, [
                ':nom'           => $p['nom'],
                ':description'   => $p['description'],
                ':date_creation' => $p['date_creation'],
            ])->fetch();

            $nouveauId = (int) $nouveauGroupe['id'];

            // Restaurer les droits liés
            if (!empty($donnees['liees']['groupes_droits'])) {
                $sqlDroit = "INSERT INTO groupes_droits (groupe_id, droit_id)
                             SELECT :groupe_id, :droit_id
                             WHERE EXISTS (SELECT 1 FROM droits WHERE id = :droit_id_check)";
                foreach ($donnees['liees']['groupes_droits'] as $ligne) {
                    \Database::query($sqlDroit, [
                        ':groupe_id'      => $nouveauId,
                        ':droit_id'       => $ligne['droit_id'],
                        ':droit_id_check' => $ligne['droit_id'],
                    ]);
                }
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        \Corbeille::supprimerFichier($fichier);
    }
}
