<?php

namespace App\Controllers;

use App\Models\InvitacionCampeonatosModel;
use App\Models\MiembrosCampeonatosModel;
use PDO;
use PDOException;

class InvitacionCampeonatosController
{
    private InvitacionCampeonatosModel $model;
    private MiembrosCampeonatosModel $miembrosModel;

    public function __construct(PDO $db)
    {
        $this->model = new InvitacionCampeonatosModel($db);
        $this->miembrosModel = new MiembrosCampeonatosModel($db);
        header('Content-Type: application/json');
    }

    // Obtener todas las invitaciones
    public function index()
    {
        try {
            $items = $this->model->obtenerPorCampeonato(null);
            echo json_encode(['status' => 200, 'message' => 'Invitaciones de campeonatos obtenidas', 'data' => $items]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al obtener invitaciones', 'details' => $e->getMessage()]);
        }
    }

    // Obtener una invitación por ID
    public function show(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'ID inválido']);
            return;
        }
        try {
            $all = $this->model->obtenerPorCampeonato(null);
            $item = null;
            foreach ($all as $inv) {
                if ($inv['id'] == $id) { $item = $inv; break; }
            }
            if ($item) {
                echo json_encode(['status' => 200, 'message' => 'Invitación obtenida', 'data' => $item]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Invitación no encontrada']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al obtener invitación', 'details' => $e->getMessage()]);
        }
    }

    // Crear nueva invitación
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Datos inválidos']);
            return;
        }

        // Validar campos requeridos (ajusta los nombres de campo según tu modelo)
        if (
            !isset($data['id_campeonato']) || !is_numeric($data['id_campeonato']) ||
            !isset($data['id_usuario']) || !is_numeric($data['id_usuario']) ||
            !isset($data['correo']) || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)
        ) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Campos id_campeonato, id_usuario y correo son requeridos y deben ser válidos']);
            return;
        }

        try {
            $created = $this->model->crear($data);
            if ($created) {
                echo json_encode(['status' => 201, 'message' => 'Invitación creada', 'data' => $data]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 500, 'message' => 'Error al crear invitación']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al crear invitación', 'details' => $e->getMessage()]);
        }
    }

    // Actualizar una invitación (por ejemplo, estado)
    public function update(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'ID inválido']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['estado']) || !isset($data['fecha_respuesta'])) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Campos estado y fecha_respuesta requeridos']);
            return;
        }
        
        // Validar que el estado pertenezca al enum: pendiente, aceptado, rechazado
        $estadosValidos = ['pendiente', 'aceptado', 'rechazado'];
        if (!in_array(strtolower($data['estado']), $estadosValidos)) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Estado inválido. Valores permitidos: pendiente, aceptado, rechazado'
            ]);
            return;
        }

        try {
            $updated = $this->model->actualizarEstado($id, $data['estado'], $data['fecha_respuesta']);
            if ($updated) {
                if (strtolower($data['estado']) === 'aceptado') {
                    // Obtener detalles de la invitación para usar en el registro de miembros
                    $inv = $this->model->obtenerPorId($id);
                    if ($inv) {
                        // Agregar asociación de miembro al campeonato
                        $resultado = $this->miembrosModel->agregarMiembro($inv['campeonato_id'], $inv['equipo_id']);
                        if (!$resultado) {
                            http_response_code(500);
                            echo json_encode(['status' => 500, 'message' => 'Error al agregar miembro al campeonato']);
                            return;
                        }
                        // Eliminar la invitación luego de haberla procesado
                        $this->model->eliminarInvitacion($id);
                    }
                }
                echo json_encode(['status' => 200, 'message' => 'Invitación actualizada']);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Invitación no encontrada o sin cambios'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al actualizar invitación',
                'details' => $e->getMessage()
            ]);
        }
    }

    // Eliminar una invitación
    public function delete(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'ID inválido']);
            return;
        }
        try {
            $deleted = $this->model->eliminarInvitacion($id);
            if ($deleted) {
                echo json_encode(['status' => 200, 'message' => 'Invitación eliminada']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Invitación no encontrada']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al eliminar invitación', 'details' => $e->getMessage()]);
        }
    }
}
