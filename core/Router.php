<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function route(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [$method, $path, $controller, $action];
    }

    public function get(string $path, string $controller, string $action): void
    {
        $this->route('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->route('POST', $path, $controller, $action);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri ?: '/', '/');

        foreach ($this->routes as [$routeMethod, $routePath, $controller, $action]) {
            if ($routeMethod === $method && preg_match('#^' . $routePath . '$#', $uri, $matches)) {
                $this->call($controller, $action, array_slice($matches, 1));
                return;
            }
        }

        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }

    private function call(string $controller, string $action, array $params): void
    {
        $file = ROOT_PATH . '/controllers/' . $controller . '.php';
        
        if (!file_exists($file) || !method_exists($c = new $controller(), $action)) {
            throw new RuntimeException("Contrôleur/Action introuvable: $controller::$action");
        }
        
        $c->$action(...$params);
    }
}