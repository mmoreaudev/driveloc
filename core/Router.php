<?php
declare(strict_types=1);

/**
 * Router – Dispatche les requêtes HTTP vers le bon contrôleur/action.
 * Supporte les paramètres de route sous forme de groupes de capture regex (ex: (\d+)).
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes[] = ['GET', $path, $controller, $action];
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes[] = ['POST', $path, $controller, $action];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Supprime le préfixe du sous-dossier (ex: /driveloc)
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . ltrim($uri ?: '/', '/');

        foreach ($this->routes as [$routeMethod, $routePath, $controllerName, $actionName]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $pattern = '#^' . $routePath . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Supprime la correspondance complète
                $this->call($controllerName, $actionName, $matches);
                return;
            }
        }

        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }

    private function call(string $controllerName, string $actionName, array $params): void
    {
        $file = ROOT_PATH . '/controllers/' . $controllerName . '.php';

        if (!file_exists($file)) {
            throw new RuntimeException("Contrôleur introuvable : $controllerName");
        }

        require_once $file;

        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            throw new RuntimeException("Action introuvable : $actionName dans $controllerName");
        }

        $controller->$actionName(...$params);
    }
}
