<?php
// modules/Utilisateurs/Models/GroupeUtilisateur.php

namespace Utilisateurs\Models;

class GroupeUtilisateur extends \Model {

    protected string $table = 'groupes_utilisateurs';

    /**
     * Liste tous les groupes avec le nombre d'utilisateurs associés.
     */
    public function listerTout(): array {
        $sql = "SELECT g.*, COUNT(u.id) AS nb_utilisateurs
                FROM groupes_utilisateurs g
                LEFT JOIN utilisateurs u ON u.groupe_id = g.id
                GROUP BY g.id
                ORDER BY g.id ASC";
        return \Database::query($sql)->fetchAll();
    }

    public function creer(array $donnees): int {
        $sql = "INSERT INTO groupes_utilisateurs (nom, description)
                VALUES (:nom, :description)
                RETURNING id";
        $resultat = \Database::query($sql, [
            ':nom'         => $donnees['nom'],
            ':description' => $donnees['description'],
        ])->fetch();

        return (int) $resultat['id'];
    }

    public function mettreAJour(int $id, array $donnees): void {
        $sql = "UPDATE groupes_utilisateurs
                SET nom = :nom, description = :description
                WHERE id = :id";
        \Database::query($sql, [
            ':id'          => $id,
            ':nom'         => $donnees['nom'],
            ':description' => $donnees['description'],
        ]);
    }

    /**
     * Retourne les codes de droits déjà affectés à un groupe.
     */
    public function droitsAffectes(int $groupeId): array {
        $sql = "SELECT droit_id FROM groupes_droits WHERE groupe_id = :groupe_id";
        $resultat = \Database::query($sql, [':groupe_id' => $groupeId])->fetchAll();
        return array_column($resultat, 'droit_id');
    }

    /**
     * Remplace tous les droits affectés à un groupe (transaction).
     */
    public function definirDroits(int $groupeId, array $droitIds): void {
        $pdo = $this->pdo();
        try {
            $pdo->beginTransaction();

            \Database::query("DELETE FROM groupes_droits WHERE groupe_id = :groupe_id", [
                ':groupe_id' => $groupeId,
            ]);

            $sql = "INSERT INTO groupes_droits (groupe_id, droit_id) VALUES (:groupe_id, :droit_id)";
            foreach ($droitIds as $droitId) {
                \Database::query($sql, [
                    ':groupe_id' => $groupeId,
                    ':droit_id'  => (int) $droitId,
                ]);
            }

            $pdo->commit();
        } catch (\Exception $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    /**
     * Vérifie si le groupe contient des utilisateurs (pour empêcher suppression).
     */
    public function aDesUtilisateurs(int $id): bool {
        $sql = "SELECT 1 FROM utilisateurs WHERE groupe_id = :id LIMIT 1";
        return (bool) \Database::query($sql, [':id' => $id])->fetch();
    }
}
