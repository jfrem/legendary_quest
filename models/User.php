<?php
// models/User.php

class User
{
    private $conn;
    private $table_name = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Registrar un nuevo usuario
    public function register($username, $email, $password)
    {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        // Sanitizar y enlazar parámetros
        $stmt->bindParam(":username", htmlspecialchars(strip_tags($username)));
        $stmt->bindParam(":email", filter_var($email, FILTER_SANITIZE_EMAIL));
        $stmt->bindParam(":password", password_hash($password, PASSWORD_BCRYPT));

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Iniciar sesión
    public function login($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", filter_var($email, FILTER_SANITIZE_EMAIL));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todos los usuarios
    public function getAllUser()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Obtener usuario por ID
    public function getUserById($id)
    {
        $query = "SELECT id, username, email, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar usuario
    public function updateUser($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['username'])) {
            $fields[] = "username = :username";
            $params[':username'] = htmlspecialchars(strip_tags($data['username']));
        }

        if (isset($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        return $stmt->execute();
    }

    // Eliminar usuario
    public function deleteUser($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Verificar existencia de usuario por username o email
    public function exists($username, $email, $excludeId = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE (username = :username OR email = :email)";
        if ($excludeId !== null) {
            $query .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        if ($excludeId !== null) {
            $stmt->bindParam(":id", $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
