<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use PDO;
use PDOException;

class UsuarioController
{
    private UsuarioModel $usuarioModel;

    public function __construct(PDO $db)
    {
        $this->usuarioModel = new UsuarioModel($db);
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $usuarios = $this->usuarioModel->getAll();

            echo json_encode([
                'status' => 200,
                'message' => 'Usuarios obtenidos correctamente',
                'data' => $usuarios
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener usuarios',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $usuario = $this->usuarioModel->getById((int)$id);

            if ($usuario) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Usuario obtenido correctamente',
                    'data' => $usuario
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Usuario no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener usuario',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function showByEmail($email)
    {
        try {
            $usuario = $this->usuarioModel->findByEmail($email);

            if ($usuario) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Usuario obtenido correctamente',
                    'data' => $usuario
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Usuario no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener usuario',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'No se proporcionaron datos para actualizar'
            ]);
            return;
        }

        try {
            $actualizado = $this->usuarioModel->update((int)$id, $data);

            if ($actualizado) {
                $usuarioActualizado = $this->usuarioModel->getById((int)$id);
                echo json_encode([
                    'status' => 200,
                    'message' => 'Usuario actualizado correctamente',
                    'data' => $usuarioActualizado
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Usuario no encontrado o sin cambios'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al actualizar usuario',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $eliminado = $this->usuarioModel->delete((int)$id);

            if ($eliminado) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Usuario eliminado correctamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Usuario no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al eliminar usuario',
                'details' => $e->getMessage()
            ]);
        }
    }
}
