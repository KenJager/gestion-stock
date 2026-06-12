<?php
// core/Router.php — Routeur frontal

class Router {

    private static array $routes = [];

    public static function get(string $chemin, string $handler): void {
        self::ajouterRoute('GET', $chemin, $handler);
    }

    public static function post(string $chemin, string $handler): void {
        self::ajouterRoute('POST', $chemin, $handler);
    }

    private static function ajouterRoute(string $methode, string $chemin, string $handler): void {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $chemin);
        $pattern = '#^' . $pattern . '$#u';

        self::$routes[] = [
            'methode' => $methode,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public static function dispatcher(): void {
        $uri     = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri     = rtrim($uri, '/') ?: '/';
        $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $base = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }

        foreach (self::$routes as $route) {
            if ($route['methode'] !== $methode) continue;

            if (preg_match($route['pattern'], $uri, $correspondances)) {
                $params = array_filter(
                    $correspondances,
                    static fn($cle) => is_string($cle),
                    ARRAY_FILTER_USE_KEY
                );
                self::appellerHandler($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        $cheminVue404 = ROOT . '/views/erreurs/404.php';
        if (file_exists($cheminVue404)) {
            require $cheminVue404;
        } else {
            echo "404 — Page non trouvée";
        }
    }

    private static function appellerHandler(string $handler, array $params): void {
        if (!str_contains($handler, '@')) {
            throw new InvalidArgumentException("Handler invalide : '{$handler}'. Format attendu : 'Controller@methode'");
        }

        [$classe, $methode] = explode('@', $handler, 2);

        if (!class_exists($classe)) {
            throw new RuntimeException("Controller introuvable : '{$classe}'. Vérifier l'autoloader et le chemin du fichier.");
        }

        $controller = new $classe();

        if (!method_exists($controller, $methode)) {
            throw new RuntimeException("Méthode introuvable : '{$methode}' dans '{$classe}'.");
        }

        $controller->$methode(...array_values($params));
    }
}
