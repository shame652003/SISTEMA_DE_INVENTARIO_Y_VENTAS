<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, string $controller, string $action, string $middleware = ''): void
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'action' => $action];
        if ($middleware) {
            $this->middleware['GET'][$path] = $middleware;
        }
    }

    public function post(string $path, string $controller, string $action, string $middleware = ''): void
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'action' => $action];
        if ($middleware) {
            $this->middleware['POST'][$path] = $middleware;
        }
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        if ($baseUrl !== '' && str_starts_with($uri, $baseUrl)) {
            $uri = substr($uri, strlen($baseUrl));
        }

        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        $uri = rtrim($uri, '/') ?: '/';

        // Desencriptar si es una URL encriptada (prefijo /r/)
        if (str_starts_with($uri, '/r/')) {
            $decrypted = UrlCipher::decrypt($uri);
            if ($decrypted !== null) {
                $uri = rtrim($decrypted, '/') ?: '/';
            }
        }

        foreach (($this->routes[$method] ?? []) as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Ejecutar middleware si existe
                $m = $this->middleware[$method][$route] ?? null;
                if ($m === 'auth') {
                    AuthMiddleware::verificar();
                }

                $controllerClass = 'App\\Controllers\\' . $handler['controller'];
                $action = $handler['action'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        call_user_func_array([$controller, $action], $params);
                        return;
                    }
                }

                http_response_code(500);
                echo "Error: Acción no encontrada";
                return;
            }
        }

        http_response_code(404);
        echo "404 - Página no encontrada";
    }
}
