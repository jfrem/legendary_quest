<?php
// models/User.php

class User
{
    private $conn; // Conexión a la base de datos
    private $table_name = "users"; // Nombre de la tabla

    /**
     * Constructor de la clase User.
     *
     * @param PDO $db Conexión a la base de datos.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     *
     * @param string $username Nombre de usuario.
     * @param string $email Correo electrónico.
     * @param string $password Contraseña en texto plano.
     * @return bool Verdadero si el registro fue exitoso, falso en caso contrario.
     */
    public function register($username, $email, $password)
    {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        // Sanitizar y enlazar parámetros
        $stmt->bindParam(":username", htmlspecialchars(strip_tags($username)));
        $stmt->bindParam(":email", filter_var($email, FILTER_SANITIZE_EMAIL));
        $stmt->bindParam(":password", password_hash($password, PASSWORD_BCRYPT));

        return $stmt->execute(); // Retorna verdadero si la ejecución fue exitosa
    }

    /**
     * Inicia sesión y obtiene los datos del usuario por su correo electrónico.
     *
     * @param string $email Correo electrónico del usuario.
     * @return array|false Datos del usuario si se encuentra, falso en caso contrario.
     */
    public function login($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", filter_var($email, FILTER_SANITIZE_EMAIL));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna los datos del usuario
    }

    /**
     * Obtiene todos los usuarios de la base de datos.
     *
     * @return array Lista de usuarios.
     */
    public function getAllUsers()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna un arreglo con todos los usuarios
    }

    /**
     * Obtiene un usuario por su ID.
     *
     * @param int $id ID del usuario.
     * @return array|false Datos del usuario si se encuentra, falso en caso contrario.
     */
    public function getUserById($id)
    {
        $query = "SELECT id, username, email, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna los datos del usuario
    }

    /**
     * Actualiza los datos de un usuario.
     *
     * @param int $id ID del usuario a actualizar.
     * @param array $data Datos a actualizar (username, email, password).
     * @return bool Verdadero si la actualización fue exitosa, falso en caso contrario.
     */
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
            return false; // No se han proporcionado datos para actualizar
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        return $stmt->execute(); // Retorna verdadero si la actualización fue exitosa
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param int $id ID del usuario a eliminar.
     * @return bool Verdadero si la eliminación fue exitosa, falso en caso contrario.
     */
    public function deleteUser($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute(); // Retorna verdadero si la eliminación fue exitosa
    }

    /**
     * Verifica la existencia de un usuario por nombre de usuario o correo electrónico.
     *
     * @param string $username Nombre de usuario.
     * @param string $email Correo electrónico.
     * @param int|null $excludeId ID del usuario a excluir de la búsqueda (para actualizaciones).
     * @return bool Verdadero si existe un usuario con esos datos, falso en caso contrario.
     */
    public function exists($username, $email, $excludeId = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE (username = :username OR email = :email)";
        if ($excludeId !== null) {
            $query .= " AND id != :id"; // Excluir el usuario actual si se proporciona el ID
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        if ($excludeId !== null) {
            $stmt->bindParam(":id", $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0; // Retorna verdadero si se encontró al menos un usuario
    }
}
