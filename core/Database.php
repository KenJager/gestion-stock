<?php
// core/Database.php — Singleton PDO pour PostgreSQL

class Database {

    private static ?PDO $instance = null;
    private static array $config  = [];

    private function __construct() {}

    /**
     * Charge la configuration. À appeler une fois dans public/index.php :
     *   Database::chargerConfig(require ROOT . '/config/database.php');
     */
    public static function chargerConfig(array $config): void {
        self::$config = $config;
    }

    /**
     * Retourne la connexion PDO (crée au premier appel).
     */
    public static function connexion(): PDO {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                throw new RuntimeException('Database : configuration non chargée. Appeler Database::chargerConfig() dans index.php.');
            }
            $cfg = self::$config;
            $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};options='--client_encoding={$cfg['charset']}'";
            try {
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Ne pas exposer le message exact en production
                error_log('Erreur connexion BD : ' . $e->getMessage());
                die('Impossible de se connecter à la base de données.');
            }
        }
        return self::$instance;
    }

    /**
     * Exécute une requête préparée et retourne le PDOStatement.
     * Utiliser des paramètres NOMMÉS (:param) — jamais de concaténation.
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Démarre une transaction.
     */
    public static function debuterTransaction(): void {
        self::connexion()->beginTransaction();
    }

    /**
     * Valide la transaction en cours.
     */
    public static function validerTransaction(): void {
        self::connexion()->commit();
    }

    /**
     * Annule la transaction en cours.
     */
    public static function annulerTransaction(): void {
        self::connexion()->rollBack();
    }

    /**
     * Définit les variables de session PostgreSQL utilisées par fn_audit_log()
     * pour tracer utilisateur_id et login_utilisateur dans audit_log.
     */
    public static function definirUtilisateurAudit(?int $utilisateurId, ?string $login): void {
        $pdo = self::connexion();
        $pdo->exec("SELECT set_config('app.utilisateur_id', "
            . $pdo->quote((string)($utilisateurId ?? '')) . ", false)");
        $pdo->exec("SELECT set_config('app.login_utilisateur', "
            . $pdo->quote($login ?? '') . ", false)");
    }
}