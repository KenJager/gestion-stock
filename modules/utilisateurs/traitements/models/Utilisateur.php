<?php
// modules/Utilisateurs/Models/Utilisateur.php

namespace utilisateurs\traitements\models;

class Utilisateur extends \Model {

    protected string $table = 'utilisateurs';

    public function trouverParLogin(string $login): array|false {
        $sql = "SELECT u.*, g.nom AS groupe_nom
                FROM utilisateurs u
                LEFT JOIN groupes_utilisateurs g ON g.id = u.groupe_id
                WHERE u.login = :login AND u.actif = true";
        return \Database::query($sql, [':login' => $login])->fetch();
    }

    public function listerTout(): array {
        $sql = "SELECT u.*, g.nom AS groupe_nom
                FROM utilisateurs u
                LEFT JOIN groupes_utilisateurs g ON g.id = u.groupe_id
                ORDER BY u.id DESC";
        return \Database::query($sql)->fetchAll();
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO utilisateurs (nom, prenom, login, mot_de_passe, email, groupe_id, actif)
                VALUES (:nom, :prenom, :login, :mot_de_passe, :email, :groupe_id, true)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':nom'          => $donnees['nom'],
            ':prenom'       => $donnees['prenom'],
            ':login'        => $donnees['login'],
            ':mot_de_passe' => $donnees['mot_de_passe'],
            ':email'        => $donnees['email'],
            ':groupe_id'    => $donnees['groupe_id'],
        ])->fetch();

        return (int) $resultat['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE utilisateurs
                SET nom = :nom, prenom = :prenom, login = :login,
                    email = :email, groupe_id = :groupe_id, actif = :actif
                WHERE id = :id";
        \Database::query($sql, [
            ':id'        => $id,
            ':nom'       => $donnees['nom'],
            ':prenom'    => $donnees['prenom'],
            ':login'     => $donnees['login'],
            ':email'     => $donnees['email'],
            ':groupe_id' => $donnees['groupe_id'],
            ':actif'     => $donnees['actif'],
        ]);
    }

    public function changerMotDePasse(int $id, string $motDePasseHash): void {
        $sql = "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id";
        \Database::query($sql, [':id' => $id, ':mdp' => $motDePasseHash]);
    }

    public function loginExiste(string $login, ?int $excluId = null): bool {
        $sql = "SELECT 1 FROM utilisateurs WHERE login = :login";
        $params = [':login' => $login];
        if ($excluId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excluId;
        }
        return (bool) \Database::query($sql, $params)->fetch();
    }
}
