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
            return Response::error("Nombre de usuario, email y contraseña son requeridos.", 400);
        }

        // Sanitizar entradas
        $username = htmlspecialchars(strip_tags($data['username']));
        $email = htmlspecialchars(strip_tags(trim($data['email'])));
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            // Comprobar si el nombre de usuario o el email ya existen
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return Response::error("El nombre de usuario o el email ya están en uso.", 409);
            }

            // Insertar en la base de datos
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);

            if ($stmt->execute()) {
                return Response::json(["message" => "Usuario registrado exitosamente."], 201);
            } else {
                return Response::error("Error al registrar el usuario.", 500);
            }
        } catch (PDOException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() === '23000') { // Código de error para violación de integridad
                return Response::error("El nombre de usuario o el email ya están en uso.", 409);
            }
            return Response::error("Error en la base de datos: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            // Manejo de errores generales
            return Response::error("Error inesperado: " . $e->getMessage(), 500);
        }
    }

    public function login()
    {
        // Obtener y validar los datos de entrada
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar que se reciban email y password
        if (empty($data['email']) || empty($data['password'])) {
            return Response::error("Email y contraseña son requeridos.", 400);
        }

        // Sanitizar entradas
        $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
        $password = $data['password'];

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::error("Email inválido.", 400);
        }

        // Buscar el usuario en la base de datos
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario fue encontrado y si la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            return Response::json([
                "message" => "Inicio de sesión exitoso.",
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            return Response::error("Credenciales inválidas.", 401);
        }
    }

    public function logout($id)
    {
        if ($this->invalidateToken($id)) {
            return Response::json(['message' => 'Sesión cerrada correctamente'], 200);
        } else {
            return Response::error("Error al cerrar sesión", 500);
        }
    }
    private function invalidateToken($userId)
    {
        // $query = "DELETE FROM tokens WHERE user_id = :user_id";
        // $stmt = $this->db->prepare($query);
        // $stmt->bindParam(':user_id', $userId);

        // return $stmt->execute();
        return true;
    }

}
