<?php
// controllers/AuthController.php

class AuthController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function register()
    {
        // Obtener y validar los datos de entrada
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar entradas
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            Response::error("Nombre de usuario, email y contraseña son requeridos.", 400);
            exit();
        }

        // Sanitizar entradas
        $username = htmlspecialchars(strip_tags($data['username']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            // Comprobar si el nombre de usuario ya existe
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                Response::error("El nombre de usuario o el email ya están en uso.", 409);
                exit();
            }

            // Insertar en la base de datos
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);

            if ($stmt->execute()) {
                Response::json(["message" => "Usuario registrado exitosamente."], 201);
            } else {
                Response::error("Error al registrar el usuario.", 500);
            }
        } catch (PDOException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() === '23000') { // Código de error para violación de integridad
                Response::error("El nombre de usuario o el email ya están en uso.", 409);
            } else {
                Response::error("Error en la base de datos: " . $e->getMessage(), 500);
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            Response::error("Error inesperado: " . $e->getMessage(), 500);
        }
    }


    public function login()
    {
        // Obtener y validar los datos de entrada
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar que se reciban email y password
        if (empty($data['email']) || empty($data['password'])) {
            Response::error("Email y contraseña son requeridos.", 400);
            exit();
        }

        // Sanitizar entradas
        $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
        $password = $data['password'];

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error("Email inválido.", 400);
            exit();
        }

        // Buscar el usuario en la base de datos
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario fue encontrado y si la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            Response::json([
                "message" => "Inicio de sesión exitoso.",
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            Response::error("Credenciales inválidas.", 401);
        }
    }
}
