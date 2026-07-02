<?php
// core/Auth.php — Gestion des sessions et des droits

class Auth {

    public static function demarrer(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function connecter(array $utilisateur): void {
        session_regenerate_id(true);
        $_SESSION['utilisateur_id']    = $utilisateur['id'];
        $_SESSION['utilisateur_nom']   = trim(($utilisateur['nom'] ?? '') . ' ' . ($utilisateur['prenom'] ?? ''));
        $_SESSION['utilisateur_login'] = $utilisateur['login'];
        $_SESSION['groupe_id']         = $utilisateur['groupe_id'] ?? null;
        $_SESSION['groupe_nom']        = $utilisateur['groupe_nom'] ?? 'Non défini';
        $_SESSION['connecte']          = true;
    }

    public static function deconnecter(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function estConnecte(): bool {
        return isset($_SESSION['connecte']) && $_SESSION['connecte'] === true;        //envoie true si la variable est 'connecte' exite en sexion
    }

    public static function utilisateurCourant(): ?array {
        if (!self::estConnecte()) return null;                                  
        return [
            'id'         => $_SESSION['utilisateur_id'],
            'nom'        => $_SESSION['utilisateur_nom']   ?? '',
            'login'      => $_SESSION['utilisateur_login'] ?? '',
            'groupe_id'  => $_SESSION['groupe_id']         ?? null,
            'groupe_nom' => $_SESSION['groupe_nom']        ?? 'Non défini',
        ];
    }

    public static function peut(string $code): bool {
        if (!self::estConnecte()) return false;
        $groupeId = $_SESSION['groupe_id'] ?? null;
        if ($groupeId === null) return false;
        if (!isset($_SESSION['droits_cache'])) {
            $_SESSION['droits_cache'] = self::chargerDroits((int) $groupeId);
        }
        return in_array($code, $_SESSION['droits_cache'], true);
    }

    public static function verifierConnexion(): void {
        if (!self::estConnecte()) {
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }
    }

    public static function verifierDroit(string $code): void {
        self::verifierConnexion();
        if (!self::peut($code)) {
            header('Location: ' . BASE_URL . '/acces-refuse');
            exit;
        }
    }

    private static function chargerDroits(int $groupeId): array {
        try {
            $sql  = "SELECT d.code FROM droits d
                     JOIN groupes_droits gd ON gd.droit_id = d.id
                     WHERE gd.groupe_id = :groupe_id";
            $stmt = Database::query($sql, [':groupe_id' => $groupeId]);
            return array_column($stmt->fetchAll(), 'code');
        } catch (Exception $e) {
            return [];
        }
    }

    public static function viderCacheDroits(): void {
        unset($_SESSION['droits_cache']);
    }
}