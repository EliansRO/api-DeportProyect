<?php

namespace App\Controllers;

use PDO;
use PDOException;

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
        try {
            $stmt = $this->db->query("SELECT id, nombre, cedula, sexo, fecha_nacimiento,
                                             estado_salud, correo, telefono, direccion, ciudad, pais,
                                             url_foto_perfil, rol, fecha_registro, ultimo_login
                                      FROM Usuario");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $stmt = $this->db->prepare("SELECT id, nombre, cedula, sexo, fecha_nacimiento,
                                               estado_salud, correo, telefono, direccion, ciudad, pais,
                                               url_foto_perfil, rol, fecha_registro, ultimo_login
                                        FROM Usuario
                                        WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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
            $stmt = $this->db->prepare("SELECT id, nombre, cedula, sexo, fecha_nacimiento,
                                               estado_salud, correo, telefono, direccion, ciudad, pais,
                                               url_foto_perfil, rol, fecha_registro, ultimo_login
                                        FROM Usuario
                                        WHERE correo = :correo");
            $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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

        $campos = ['nombre', 'cedula', 'sexo', 'fecha_nacimiento', 'estado_salud', 'correo', 'telefono', 'direccion', 'ciudad', 'pais', 'url_foto_perfil', 'rol'];
        $setClause = [];
        $params = [':id' => $id];

        foreach ($campos as $campo) {
            if (isset($data[$campo])) {
                $setClause[] = "$campo = :$campo";
                $params[":$campo"] = $data[$campo];
            }
        }

        if (empty($setClause)) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'No se proporcionaron datos para actualizar'
            ]);
            return;
        }

        try {
            $sql = "UPDATE Usuario SET " . implode(', ', $setClause) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'status' => 200,
                'message' => 'Usuario actualizado correctamente'
            ]);
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
            $stmt = $this->db->prepare("DELETE FROM Usuario WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
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