<?php

namespace App\Controllers;

use PDO;
use PDOException;
use App\Models\AmistadModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../middlewares/auth.php';

class SolicitudAmistadController
{
    private $db;
    private $user;
    private $amistadModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->user = getAuthUser();

        if (!$this->user) {
            http_response_code(401);
            echo json_encode([
                'status' => 401,
                'message' => 'No autenticado',
                'data' => null
            ]);
            exit;
        }

        $this->amistadModel = new AmistadModel($this->db);
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT sa.*, u.nombre AS nombre_remitente
                FROM SolicitudAmistad sa
                JOIN Usuario u ON u.id = sa.de_usuario_id
                WHERE sa.para_usuario_id = :id
                ORDER BY sa.fecha_envio DESC
            ");
            $stmt->execute([':id' => $this->user['id']]);
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 200,
                'message' => 'Solicitudes obtenidas correctamente',
                'data' => ['solicitudes' => $solicitudes]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener solicitudes',
                'data' => null
            ]);
        }
    }

    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos obligatorios
        if (empty($data['para_usuario_id'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Falta el ID del destinatario',
                'data' => null
            ]);
            return;
        }

        // Validar que el destinatario no sea el mismo usuario
        if ($this->user['id'] == $data['para_usuario_id']) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'No puedes enviarte una solicitud a ti mismo',
                'data' => null
            ]);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM SolicitudAmistad 
                WHERE 
                    ((de_usuario_id = :de AND para_usuario_id = :para) 
                    OR (de_usuario_id = :para AND para_usuario_id = :de))
                    AND estado = 'pendiente'
            ");
            $stmt->execute([
                ':de' => $this->user['id'],
                ':para' => $data['para_usuario_id']
            ]);

            // Verificar si ya existe una solicitud pendiente entre estos usuarios
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode([
                    'status' => 409,
                    'message' => 'Ya existe una solicitud pendiente entre estos usuarios',
                    'data' => null
                ]);
                return;
            }

            $stmt = $this->db->prepare("
                INSERT INTO SolicitudAmistad (de_usuario_id, para_usuario_id) 
                VALUES (:de, :para)
            ");
            $stmt->execute([
                ':de' => $this->user['id'],
                ':para' => $data['para_usuario_id']
            ]);

            http_response_code(201);
            echo json_encode([
                'status' => 201,
                'message' => 'Solicitud enviada correctamente',
                'data' => null
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al enviar solicitud',
                'data' => ['detalles' => $e->getMessage()]
            ]);
        }
    }

    public function update(int $id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!in_array($data['estado'], ['aceptado', 'rechazado'])) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Estado inválido',
                'data'    => null
            ]);
            return;
        }

        try {
            // Actualiza estado y fecha de respuesta
            $stmt = $this->db->prepare("
                UPDATE SolicitudAmistad
                SET estado = :estado, fecha_respuesta = NOW()
                WHERE id = :id AND para_usuario_id = :user_id
            ");
            $stmt->execute([
                ':estado'   => $data['estado'],
                ':id'       => $id,
                ':user_id'  => $this->user['id']
            ]);

            if ($stmt->rowCount() > 0) {
                if ($data['estado'] === 'aceptado') {
                    // Obtener el emisor de la solicitud
                    $stmt2 = $this->db->prepare("
                        SELECT de_usuario_id
                        FROM SolicitudAmistad
                        WHERE id = :id
                    ");
                    $stmt2->execute([':id' => $id]);
                    $solicitud = $stmt2->fetch(PDO::FETCH_ASSOC);

                    if ($solicitud) {
                        // Crear la amistad
                        $this->amistadModel->crearAmistad(
                            $this->user['id'],
                            $solicitud['de_usuario_id']
                        );
                    }
                }

                // Finalmente, eliminar la solicitud original
                $del = $this->db->prepare("
                    DELETE FROM SolicitudAmistad
                    WHERE id = :id
                ");
                $del->execute([':id' => $id]);

                echo json_encode([
                    'status'  => 200,
                    'message' => 'Solicitud procesada y eliminada correctamente',
                    'data'    => null
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Solicitud no encontrada o no autorizada',
                    'data'    => null
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al actualizar solicitud',
                'data'    => null
            ]);
        }
    }


    public function delete(int $id)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM SolicitudAmistad 
                WHERE id = :id AND (de_usuario_id = :uid OR para_usuario_id = :uid)
            ");
            $stmt->execute([
                ':id' => $id,
                ':uid' => $this->user['id']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Solicitud eliminada correctamente',
                    'data' => null
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'No autorizad@ o solicitud inexistente',
                    'data' => null
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al eliminar solicitud',
                'data' => null
            ]);
        }
    }
}
