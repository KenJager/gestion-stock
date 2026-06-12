<?php
// core/Model.php — Classe mère de tous les modèles

abstract class Model {

    protected string $table = '';
    protected string $cle = 'id';

    protected function pdo(): PDO {
        return Database::connexion();
    }

    public function trouverParId(int $id): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->cle} = :id";
        $resultat = Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            throw new RuntimeException("Enregistrement introuvable dans {$this->table} : {$this->cle} = {$id}");
        }
        return $resultat;
    }

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->cle} DESC";
        return Database::query($sql)->fetchAll();
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM {$this->table} WHERE {$this->cle} = :id";
        Database::query($sql, [':id' => $id]);
    }

    public function existe(int $id): bool {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->cle} = :id";
        return (bool) Database::query($sql, [':id' => $id])->fetch();
    }
}
