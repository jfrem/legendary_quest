<?php
// controllers/UserController.php

class UserController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new User($db);
    }

    public function getAllUser()
    {
        $users = $this->userModel->getAllUser();
        if ($users) {
            Response::json($users);
        } else {
            Response::error("No hay usuarios registrados.", 404);
        }
    }

    public function getUser($id)
    {
        if (!is_numeric($id)) {
            Response::error("ID de usuario inválido.", 400);
        }

        $user = $this->userModel->getUserById($id);

        if ($user) {
            Response::json($user);
        } else {
            Response::error("Usuario no encontrado.", 404);
        }
    }

    public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            Response::error("Todos los campos son requeridos.", 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error("Email inválido.", 400);
        }

        if ($this->userModel->exists($data['username'], $data['email'])) {
            Response::error("El nombre de usuario o el correo electrónico ya están en uso.", 400);
        }

        if ($this->userModel->register($data['username'], $data['email'], $data['password'])) {
            Response::json(["message" => "Usuario creado exitosamente."], 201);
        } else {
            Response::error("Error al crear el usuario.", 500);
        }
    }

    public function updateUser($id)
    {
        if (!is_numeric($id)) {
            Response::error("ID de usuario inválido.", 400);
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data)) {
            Response::error("No se proporcionaron datos para actualizar.", 400);
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error("Email inválido.", 400);
        }

        // Verificar si el username o email ya existen, excluyendo al usuario actual
        if (isset($data['username']) || isset($data['email'])) {
            $username = $data['username'] ?? null;
            $email = $data['email'] ?? null;
            if ($this->userModel->exists($username, $email, $id)) {
                Response::error("El nombre de usuario o el correo electrónico ya están en uso.", 409);
            }
        }

        if ($this->userModel->updateUser($id, $data)) {
            Response::json(["message" => "Usuario actualizado exitosamente."]);
        } else {
            Response::error("Error al actualizar el usuario.", 500);
        }
    }

    public function deleteUser($id)
    {
        if (!is_numeric($id)) {
            Response::error("ID de usuario inválido.", 400);
        }

        if ($this->userModel->deleteUser($id)) {
            Response::json(["message" => "Usuario eliminado exitosamente."]);
        } else {
            Response::error("Error al eliminar el usuario.", 500);
        }
    }
}
