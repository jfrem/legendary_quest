<?php
// routes/api.php

// Incluir el autoloader para cargar las clases necesarias.
require_once __DIR__ . '/../autoload.php';

// Configurar cabeceras CORS para permitir acceso desde otros dominios.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar solicitudes de tipo OPTIONS para preflight CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Responder con código 200 OK.
    exit(); // Terminar la ejecución del script.
}

// Obtener la conexión a la base de datos utilizando el método estático.
$db = Database::getInstance()->getConnection();

// Crear una nueva instancia del enrutador con la conexión a la base de datos.
$router = new Router($db);

// Registrar las rutas de la API con sus respectivos métodos y controladores.
$router->register('POST', '/api/register', AuthController::class, 'register');
$router->register('POST', '/api/login', AuthController::class, 'login');
$router->register('POST', '/api/logout/{id}', AuthController::class, 'logout');
$router->register('GET', '/api/users/{id}', UserController::class, 'getUser');
$router->register('GET', '/api/users', UserController::class, 'getAllUser');
$router->register('POST', '/api/users', UserController::class, 'createUser');
$router->register('PUT', '/api/users/{id}', UserController::class, 'updateUser');
$router->register('DELETE', '/api/users/{id}', UserController::class, 'deleteUser');

// Despachar la solicitud recibida al enrutador para su manejo.
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
