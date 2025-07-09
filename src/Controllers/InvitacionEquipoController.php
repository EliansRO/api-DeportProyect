<?php

namespace App\Controllers;

use App\Models\InvitacionEquipoModel;
use PDOException;

require_once __DIR__ . '/../../middlewares/auth.php';

class InvitacionEquipoController
{
    private $model;
    private $user;

    public function __construct($db)
    {
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

        $this->model = new InvitacionEquipoModel($db);
        header('Content-Type: application/json');
    }

    // Ver invitaciones recibidas
    public function index()
    {
        try {
            $invitaciones = $this->model->obtenerInvitacionesParaUsuario($this->user['id']);

            echo json_encode([
                'status' => 200,
                'message' => 'Invitaciones obtenidas correctamente',
                'data' => ['invitaciones' => $invitaciones]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener invitaciones',
                'data' => ['detalles' => $e->getMessage()]
            ]);
        }
    }

    // Enviar invitación
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['para_usuario_id']) || empty($data['equipo_id'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Faltan campos obligatorios (para_usuario_id, equipo_id)',
                'data' => null
            ]);
            return;
        }

        if ($this->user['id'] == $data['para_usuario_id']) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'No puedes invitarte a ti mismo',
                'data' => null
            ]);
            return;
        }

        $mensaje = $data['mensaje'] ?? null;

        $resultado = $this->model->crearInvitacion(
            $this->user['id'],
            $data['para_usuario_id'],
            $data['equipo_id'],
            $mensaje
        );

        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => $resultado['error'],
                'data' => null
            ]);
        } else {
            http_response_code(201);
            echo json_encode([
                'status' => 201,
                'message' => 'Invitación enviada correctamente',
                'data' => null
            ]);
        }
    }

    // Aceptar o rechazar invitación
    public function update(int $id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $estado = $data['estado'] ?? null;

        if (!in_array($estado, ['aceptado', 'rechazado'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Estado inválido (aceptado o rechazado)',
                'data' => null
            ]);
            return;
        }

        $resultado = $this->model->actualizarEstadoInvitacion($id, $this->user['id'], $estado);

        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al actualizar invitación',
                'data' => ['detalles' => $resultado['error']]
            ]);
        } elseif ($resultado) {
            echo json_encode([
                'status' => 200,
                'message' => 'Invitación actualizada correctamente',
                'data' => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 404,
                'message' => 'Invitación no encontrada o no autorizada',
                'data' => null
            ]);
        }
    }

    // Eliminar invitación
    public function delete(int $id)
    {
        $ok = $this->model->eliminarInvitacion($id, $this->user['id']);

        if ($ok) {
            echo json_encode([
                'status' => 200,
                'message' => 'Invitación eliminada correctamente',
                'data' => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 404,
                'message' => 'No autorizad@ o invitación inexistente',
                'data' => null
            ]);
        }
    }
}
