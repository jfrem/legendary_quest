<?php
// core/Router.php

require_once __DIR__ . '/../autoload.php';

class Router
{
    private $routes = []; // Arreglo para almacenar las rutas registradas
    private $db; // Conexión a la base de datos

    /**
     * Constructor de la clase Router.
     *
     * @param PDO $database Conexión a la base de datos.
     */
    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Registra una nueva ruta en el enrutador.
     *
     * @param string $method Método HTTP (GET, POST, etc.).
     * @param string $route Ruta de la API.
     * @param string $controller Nombre de la clase del controlador.
     * @param string $action Nombre del método del controlador.
     */
    public function register($method, $route, $controller, $action)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => rtrim($route, '/'), // Eliminar la barra final de la ruta
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Despacha la solicitud a la ruta correspondiente.
     *
     * @param string $method Método HTTP utilizado en la solicitud.
     * @param string $uri URI de la solicitud.
     * @return mixed Resultado de la acción del controlador o un error.
     */
    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
        $uri = rtrim($uri, '/'); // Eliminar la barra final de la URI

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                // Convertir parámetros en la ruta a expresiones regulares
                $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['route']);

                // Comparar la ruta solicitada con las rutas registradas
                if ($route['route'] === $uri || preg_match("#^$pattern$#", $uri, $matches)) {
                    if (isset($matches)) {
                        array_shift($matches); // Eliminar el primer elemento (coincidencia completa)
                    }
                    return $this->callAction($route['controller'], $route['action'], $matches ?? []);
                }
            }
        }

        // Manejar error si no se encontró la ruta
        return $this->handleError(404, "Ruta no encontrada.");
    }

    /**
     * Llama a la acción de un controlador.
     *
     * @param string $controller Nombre de la clase del controlador.
     * @param string $action Nombre del método del controlador.
     * @param array $params Parámetros a pasar a la acción.
     * @return mixed Resultado de la acción del controlador o un error.
     */
    private function callAction($controller, $action, $params = [])
    {
        try {
            if (!class_exists($controller) || !method_exists($controller, $action)) {
                throw new Exception("Controlador o acción no encontrados.", 500);
            }

            $controllerInstance = new $controller($this->db);
            return call_user_func_array([$controllerInstance, $action], $params);
        } catch (Exception $e) {
            // Manejar cualquier error durante la llamada a la acción
            return $this->handleError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Maneja los errores y devuelve una respuesta JSON.
     *
     * @param int $code Código de estado HTTP.
     * @param string $message Mensaje de error.
     * @return mixed Respuesta JSON con el error.
     */
    private function handleError($code, $message)
    {
        http_response_code($code);
        return Response::json(["error" => $message], $code);
    }
}
