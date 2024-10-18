<?php
// core/Router.php

require_once __DIR__ . '/../autoload.php';

class Router
{
    private $routes = [];
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function register($method, $route, $controller, $action)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => rtrim($route, '/'),
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
        $uri = rtrim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['route']);

                if ($route['route'] === $uri || preg_match("#^$pattern$#", $uri, $matches)) {
                    if (isset($matches)) {
                        array_shift($matches);
                    }
                    return $this->callAction($route['controller'], $route['action'], $matches ?? []);
                }
            }
        }

        return $this->handleError(404, "Ruta no encontrada.");
    }

    private function callAction($controller, $action, $params = [])
    {
        try {
            if (!class_exists($controller) || !method_exists($controller, $action)) {
                throw new Exception("Controlador o acción no encontrados.", 500);
            }

            $controllerInstance = new $controller($this->db);
            return call_user_func_array([$controllerInstance, $action], $params);
        } catch (Exception $e) {
            return $this->handleError($e->getCode(), $e->getMessage());
        }
    }

    private function handleError($code, $message)
    {
        http_response_code($code);
        return Response::json(["error" => $message], $code);
    }
}
