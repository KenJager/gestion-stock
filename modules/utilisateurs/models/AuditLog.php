<?php
// modules/Utilisateurs/Models/AuditLog.php

namespace Utilisateurs\Models;

class AuditLog extends \Model {

    protected string $table = 'audit_log';

    /**
     * Liste paginée avec filtres optionnels (table, opération, login).
     */
    public function lister(array $filtres = [], int $page = 1, int $parPage = 25): array {
        $conditions = [];
        $params = [];

        if (!empty($filtres['table_name'])) {
            $conditions[] = "table_name = :table_name";
            $params[':table_name'] = $filtres['table_name'];
        }
        if (!empty($filtres['operation'])) {
            $conditions[] = "operation = :operation";
            $params[':operation'] = $filtres['operation'];
        }
        if (!empty($filtres['login_utilisateur'])) {
            $conditions[] = "login_utilisateur ILIKE :login";
            $params[':login'] = '%' . $filtres['login_utilisateur'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $offset = ($page - 1) * $parPage;

        $sql = "SELECT * FROM audit_log {$where}
                ORDER BY date_operation DESC
                LIMIT :limite OFFSET :offset";
        $params[':limite'] = $parPage;
        $params[':offset'] = $offset;

        return \Database::query($sql, $params)->fetchAll();
    }

    /**
     * Compte total pour la pagination (avec les mêmes filtres).
     */
    public function compter(array $filtres = []): int {
        $conditions = [];
        $params = [];

        if (!empty($filtres['table_name'])) {
            $conditions[] = "table_name = :table_name";
            $params[':table_name'] = $filtres['table_name'];
        }
        if (!empty($filtres['operation'])) {
            $conditions[] = "operation = :operation";
            $params[':operation'] = $filtres['operation'];
        }
        if (!empty($filtres['login_utilisateur'])) {
            $conditions[] = "login_utilisateur ILIKE :login";
            $params[':login'] = '%' . $filtres['login_utilisateur'] . '%';
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT COUNT(*) AS total FROM audit_log {$where}";

        $resultat = \Database::query($sql, $params)->fetch();
        return (int) $resultat['total'];
    }

    /**
     * Liste distincte des noms de tables présentes dans le journal (pour le filtre).
     */
    public function listerTablesDistinctes(): array {
        $sql = "SELECT DISTINCT table_name FROM audit_log ORDER BY table_name ASC";
        $resultat = \Database::query($sql)->fetchAll();
        return array_column($resultat, 'table_name');
    }
}
