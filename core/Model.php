<?php
// core/Router.php — Routeur frontal

class Router {

    private static array $routes = [];

    // -------------------------------------------------------------------------
    // Déclaration des routes
    // -------------------------------------------------------------------------

    public static function get(string $chemin, string $handler): void {
        self::ajouterRoute('GET', $chemin, $handler);
    }

    public static function post(string $chemin, string $handler): void {
        self::ajouterRoute('POST', $chemin, $handler);
    }

    private static function ajouterRoute(string $methode, string $chemin, string $handler): void {
        // Convertit /commandes/{id} en regex nommée
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $chemin);
        $pattern = '#^' . $pattern . '$#u';

        self::$routes[] = [
            'methode'  => $methode,
            'pattern'  => $pattern,
            'handler'  => $handler,
        ];
    }

    // -------------------------------------------------------------------------
    // Résolution de la requête courante
    // -------------------------------------------------------------------------

    public static function dispatcher(): void {
        $uri     = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri     = rtrim($uri, '/') ?: '/';
        $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Retirer le préfixe BASE_PATH si le projet est dans un sous-dossier
        $base = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }

        foreach (self::$routes as $route) {
            if ($route['methode'] !== $methode) continue;

            if (preg_match($route['pattern'], $uri, $correspondances)) {
                // Extraire uniquement les paramètres nommés (clés string)
                $params = array_filter(
                    $correspondances,
                    static fn($cle) => is_string($cle),
                    ARRAY_FILTER_USE_KEY
                );
                self::appellerHandler($route['handler'], $params);
                return;
            }
        }

        // Aucune route trouvée
        http_response_code(404);
        require ROOT . '/views/erreurs/404.php';
    }

    // -------------------------------------------------------------------------
    // Appel du controller@méthode
    // -------------------------------------------------------------------------

    private static function appellerHandler(string $handler, array $params): void {
        // Format attendu : "NomController@nomMethode"
        if (!str_contains($handler, '@')) {
            throw new InvalidArgumentException("Handler invalide : '{$handler}'. Format attendu : 'NomController@methode'");
        }

        [$classe, $methode] = explode('@', $handler, 2);

        // Charger automatiquement le fichier du controller dans modules/
        // Chaque module enregistre ses controllers via le fichier routes.php de son module.
        // Le fichier doit avoir été requis avant dispatch().
        if (!class_exists($classe)) {
            throw new RuntimeException("Controller introuvable : '{$classe}'. Vérifier que le fichier est bien chargé.");
        }

        $controller = new $classe();

        if (!method_exists($controller, $methode)) {
            throw new RuntimeException("Méthode introuvable : '{$methode}' dans '{$classe}'.");
        }

        // Passer les paramètres URL comme arguments positionnels ordonnés
        $controller->$methode(...array_values($params));
    }
}