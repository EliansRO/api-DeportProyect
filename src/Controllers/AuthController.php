<?php

namespace App\Controllers;

use PDO;
use PDOException;

class AuthController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['correo']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Correo y contraseña son requeridos']);
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE correo = :correo");
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($data['password'], $usuario['contraseña'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales incorrectas']);
                return;
            }

            // Si quieres ocultar algunos campos sensibles:
            unset($usuario['contraseña']);

            echo json_encode([
                'mensaje' => 'Inicio de sesión exitoso',
                'usuario' => $usuario
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al iniciar sesión',
                'detalles' => $e->getMessage()
            ]);
        }
    }
}
