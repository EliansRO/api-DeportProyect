<?php

namespace App\Controllers;

use App\Models\InvitacionEquipoModel;
use App\Models\MiembrosEquipoModel;
use PDOException;

require_once __DIR__ . '/../../middlewares/auth.php';

class InvitacionEquipoController
{
    private $model;
    private $miembrosModel;
    private $user;

    public function __construct($db)
    {
        $this->user = getAuthUser();
        if (!$this->user) {
            http_response_code(401);
            echo json_encode([
                'status'  => 401,
                'message' => 'No autenticado',
                'data'    => null
            ]);
            exit;
        }

        header('Content-Type: application/json');
        $this->model         = new InvitacionEquipoModel($db);
        $this->miembrosModel = new MiembrosEquipoModel($db);
    }

    // GET /invitaciones-equipo
    public function index()
    {
        try {
            $invitaciones = $this->model->obtenerInvitacionesParaUsuario($this->user['id']);
            echo json_encode([
                'status'  => 200,
                'message' => 'Invitaciones obtenidas correctamente',
                'data'    => ['invitaciones' => $invitaciones]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al obtener invitaciones',
                'data'    => ['detalles' => $e->getMessage()]
            ]);
        }
    }

    // POST /invitaciones-equipo
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos obligatorios
        if (empty($data['para_usuario_id']) || empty($data['equipo_id'])) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Faltan campos obligatorios (para_usuario_id, equipo_id)',
                'data'    => null
            ]);
            return;
        }

        // Validar que el usuario no se invite a sí mismo
        if ($data['para_usuario_id'] == $this->user['id']) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'No puedes invitarte a ti mismo',
                'data'    => null
            ]);
            return;
        }

        $resultado = $this->model->crearInvitacion(
            $this->user['id'],
            $data['para_usuario_id'],
            $data['equipo_id'],
            $data['mensaje'] ?? null
        );
        
        // Manejar errores específicos de la invitación
        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code(409);
            echo json_encode([
                'status'  => 409,
                'message' => $resultado['error'],
                'data'    => null
            ]);
        } else {
            http_response_code(201);
            echo json_encode([
                'status'  => 201,
                'message' => 'Invitación enviada correctamente',
                'data'    => null
            ]);
        }
    }

    // PUT|PATCH /invitaciones-equipo/{id}
    public function update(int $id)
    {
        $data   = json_decode(file_get_contents("php://input"), true);
        $estado = $data['estado'] ?? '';

        // Validar que el estado sea aceptado o rechazado
        if (!in_array($estado, ['aceptado', 'rechazado'])) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Estado inválido (aceptado o rechazado)',
                'data'    => null
            ]);
            return;
        }

        // Actualizar el estado de la invitación
        $resultado = $this->model->actualizarEstadoInvitacion($id, $this->user['id'], $estado);

        // Manejar errores específicos de la actualización
        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al actualizar invitación',
                'data'    => $resultado['error']
            ]);
        } elseif ($resultado) {
            // Si la invitación fue aceptada, agregar el usuario al equipo
            if ($estado === 'aceptado') {
                $inv = $this->model->obtenerPorId($id);
                if ($inv) {
                    $this->miembrosModel->agregarMiembro(
                        $this->user['id'],
                        $inv['equipo_id'],
                        'jugador'
                    );
                }
            }

            // Eliminar la invitación después de procesarla
            $this->model->eliminarInvitacion($id, $this->user['id']);

            echo json_encode([
                'status'  => 200,
                'message' => 'Invitación procesada y eliminada correctamente',
                'data'    => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 404,
                'message' => 'Invitación no encontrada o no autorizada',
                'data'    => null
            ]);
        }
    }


    // DELETE /invitaciones-equipo/{id}
    public function delete(int $id)
    {
        $ok = $this->model->eliminarInvitacion($id, $this->user['id']);
        if ($ok) {
            echo json_encode([
                'status'  => 200,
                'message' => 'Invitación eliminada correctamente',
                'data'    => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 404,
                'message' => 'No autorizado o invitación inexistente',
                'data'    => null
            ]);
        }
    }
}
