<?php
// core/Router.php - Routeur modulaire
class Router {
    private static array $routes = [];

    // Déclarer une route GET
    public static function get(string $path, string $handler): void {
        self::addRoute('GET', $path, $handler);
    }

    // Déclarer une route POST
    public static function post(string $path, string $handler): void {
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, string $handler): void {
        // Convertir : /commandes/{id} → regex
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        self::$routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    // Exécuter le routage
    public static function dispatch(string $requestUri, string $requestMethod): void {
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';
        
        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) continue;
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                self::callHandler($route['handler'], $params);
                return;
            }
        }
        
        http_response_code(404);
        echo "❌ 404 - Page non trouvée";
    }

    private static function callHandler(string $handler, array $params): void {
        [$classPath, $method] = explode('@', $handler);
        
        // 🔥 NE PAS AJOUTER "Modules\" automatiquement
        // Utiliser le handler tel quel
        $fullClass = $classPath;
        
        if (!class_exists($fullClass)) {
            die("❌ Contrôleur non trouvé : {$fullClass}");
        }
        
        $controller = new $fullClass();
        
        if (!method_exists($controller, $method)) {
            die("❌ Méthode non trouvée : {$method} dans " . $fullClass);
        }
        
        // Appeler avec les paramètres nommés
        $controller->$method(...$params);
    }
}