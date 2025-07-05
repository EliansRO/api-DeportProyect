<?php

namespace App\Controllers;

use PDO;
use Exception;

class UsuarioController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        header('Content-Type: application/json');
    }

    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM Usuario");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($usuarios);
    }

    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo json_encode($usuario);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        }
    }

    public function store()
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
                ':nombre'            => $data['nombre'],
                ':cedula'            => $data['cedula'],
                ':sexo'              => $data['sexo'],
                ':fecha_nacimiento'  => $data['fecha_nacimiento'],
                ':estado_salud'      => $data['estado_salud'] ?? null,
                ':correo'            => $data['correo'],
                ':contraseña'          => password_hash($data['password'], PASSWORD_DEFAULT),
                ':telefono'          => $data['telefono'] ?? null,
                ':direccion'         => $data['direccion'] ?? null,
                ':ciudad'            => $data['ciudad'] ?? null,
                ':pais'              => $data['pais'] ?? null,
                ':url_foto_perfil'   => $data['url_foto_perfil'] ?? null,
                ':rol'               => $data['rol'] ?? 'player',
            ]);

            http_response_code(201);
            echo json_encode(['mensaje' => 'Usuario creado correctamente']);

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409); // 409 Conflict
                echo json_encode(['error' => 'La cédula o el correo ya están registrados']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear usuario', 'detalles' => $e->getMessage()]);
            }
        }
    }


    public function update($id)
    {
        echo json_encode([
            'mensaje' => "Actualizando usuario con ID: $id (pendiente implementación)"
        ]);
    }

    public function delete($id)
    {
        echo json_encode([
            'mensaje' => "Eliminando usuario con ID: $id (pendiente implementación)"
        ]);
    }
}
