<?php

namespace App\Controllers;

use PDO;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../Helpers/env.php';

class AuthController
{
    private $db;
    private $jwtSecret;

    public function __construct($db)
    {
        $this->db = $db;
        $this->jwtSecret = env('JWT_SECRET', 'clave_predeterminada_segura');
        header('Content-Type: application/json');
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['nombre', 'cedula', 'sexo', 'fecha_nacimiento', 'correo', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO Usuario (
                    nombre, cedula, sexo, fecha_nacimiento,
                    estado_salud, correo, password,
                    telefono, direccion, ciudad, pais,
                    url_foto_perfil, rol, fecha_registro, ultimo_login
                ) VALUES (
                    :nombre, :cedula, :sexo, :fecha_nacimiento,
                    :estado_salud, :correo, :password,
                    :telefono, :direccion, :ciudad, :pais,
                    :url_foto_perfil, :rol, NOW(), NULL
                )
            ");

            $stmt->execute([
                ':nombre'           => $data['nombre'],
                ':cedula'           => $data['cedula'],
                ':sexo'             => $data['sexo'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':estado_salud'     => $data['estado_salud'] ?? null,
                ':correo'           => $data['correo'],
                ':password'         => password_hash($data['password'], PASSWORD_DEFAULT),
                ':telefono'         => $data['telefono'] ?? null,
                ':direccion'        => $data['direccion'] ?? null,
                ':ciudad'           => $data['ciudad'] ?? null,
                ':pais'             => $data['pais'] ?? null,
                ':url_foto_perfil'  => $data['url_foto_perfil'] ?? null,
                ':rol'              => $data['rol'] ?? 'player',
            ]);

            http_response_code(201);
            echo json_encode(['mensaje' => 'Usuario registrado correctamente']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['error' => 'La cédula o el correo ya están registrados']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al registrar usuario', 'detalles' => $e->getMessage()]);
            }
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['correo']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Correo y contraseña son requeridos'
            ]);
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE correo = :correo");
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
                http_response_code(401);
                echo json_encode([
                    'status'  => 401,
                    'message' => 'Credenciales incorrectas'
                ]);
                return;
            }

            // Actualiza el último login
            $this->actualizarUltimoLogin($usuario['id']);

            // Elimina el password antes de devolverlo
            unset($usuario['password']);

            // Genera el token JWT
            $payload = [
                'sub'    => $usuario['id'],
                'correo' => $usuario['correo'],
                'rol'    => $usuario['rol'],
                'exp'    => time() + (60 * 60 * 24)
            ];
            // Codifica el token
            $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

            // Devuelve la respuesta con status, message y data
            echo json_encode([
                'status'  => 200,
                'message' => 'Inicio de sesión exitoso',
                'data'    => [
                    'user'  => $usuario,
                    'token' => $token
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al iniciar sesión',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function me()
    {
        // 1) Extraer y validar token
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode([
                'status'  => 401,
                'message' => 'Token no proporcionado o mal formado'
            ]);
            return;
        }

        $token = substr($authHeader, 7);

        try {
            // 2) Decodificar JWT
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userId  = $decoded->sub;

            // 3) Consultar todos los datos del usuario en la BD
            $stmt = $this->db->prepare("
                SELECT id, nombre, cedula, sexo, fecha_nacimiento,
                    estado_salud, correo, telefono, direccion, ciudad, pais,
                    url_foto_perfil, rol, fecha_registro, ultimo_login
                FROM Usuario
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Usuario no encontrado'
                ]);
                return;
            }

            // 4) Devolver la respuesta con todos los datos del usuario
            echo json_encode([
                'status'  => 200,
                'message' => 'Inicio de sesión exitoso',
                'data'    => [
                    'user' => $user
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode([
                'status'  => 401,
                'message' => 'Token inválido o expirado'
            ]);
        }
    }


    private function actualizarUltimoLogin($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE Usuario SET ultimo_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("No se pudo actualizar 'ultimo_login': " . $e->getMessage());
        }
    }
}
