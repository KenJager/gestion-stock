<?php
// core/Model.php

abstract class Model {
    protected string $table;
    protected string $cle = 'id';

    protected function pdo(): PDO {
        return Database::connexion();
    }

    public function listerTout(): array {
        $sql = "SELECT * FROM {$this->table}";
        return Database::query($sql)->fetchAll();
    }

    public function trouverParId(int $id): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->cle} = :id";
        $resultat = Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            return false;
        }
        return $resultat;
    }

    public function supprimer(int $id): void {
        $sql = "DELETE FROM {$this->table} WHERE {$this->cle} = :id";
        Database::query($sql, [':id' => $id]);
    }

    public function existe(int $id): bool {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->cle} = :id";
        return (bool) Database::query($sql, [':id' => $id])->fetch();
    }

    protected function requete(string $sql, array $parametres = []) {
        $stmt = Database::connexion()->prepare($sql);
        $stmt->execute($parametres);
        return new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function toutRecuperer(): array { return $this->stmt->fetchAll(); }
            public function unRecuperer(): array|false { return $this->stmt->fetch(); }
        };
    }
}
