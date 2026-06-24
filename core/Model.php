<?php
// core/Model.php — Classe mère de tous les modèles

abstract class Model {

    protected string $table = '';
    protected string $cle = 'id';

    protected function pdo(): PDO {
        return Database::connexion();
    }

    /**
     * Récupère un enregistrement par son ID
     * Retourne false si non trouvé au lieu de lancer une exception
     */
    public function trouverParId(int $id): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->cle} = :id";
        $resultat = Database::query($sql, [':id' => $id])->fetch();

        if (!$resultat) {
            return false;
        }
        return $resultat;
    }

    /**
     * Récupère un enregistrement par son ID et lance une exception si non trouvé
     * Utile quand on est certain que l'enregistrement doit exister
     */
    public function trouverParIdOuException(int $id): array {
        $resultat = $this->trouverParId($id);
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

    /**
     * Convenience wrapper to execute a prepared statement and return
     * an object exposing `toutRecuperer()` and `unRecuperer()` like
     * the rest of the codebase expects.
     */
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