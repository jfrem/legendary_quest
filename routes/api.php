<?php
// routes/api.php

require_once __DIR__ . '/../autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}



$database = new Database();
$db = $database->getConnection();

$router = new Router($db);

// Registrar rutas
$router->register('POST', '/api/register', AuthController::class, 'register');
$router->register('POST', '/api/login', AuthController::class, 'login');
$router->register('GET', '/api/users/{id}', UserController::class, 'getUser');
$router->register('GET', '/api/users', UserController::class, 'getAllUser');
$router->register('POST', '/api/users', UserController::class, 'createUser');
$router->register('PUT', '/api/users/{id}', UserController::class, 'updateUser');
$router->register('DELETE', '/api/users/{id}', UserController::class, 'deleteUser');

// Despachar la solicitud
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
