<?php

namespace App\Controllers;

use App\Models\MiembrosEquipoModel;
use PDOException;

require_once __DIR__ . '/../../middlewares/auth.php';

class MiembrosEquipoController
{
    private $model;
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
        $this->model = new MiembrosEquipoModel($db);
    }

    // GET /miembros-equipo?equipo_id={id}
    public function index()
    {
        $equipoId = $_GET['equipo_id'] ?? null;
        if (!$equipoId) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'equipo_id es obligatorio',
                'data'    => null
            ]);
            return;
        }

        try {
            $miembros = $this->model->obtenerMiembrosPorEquipo((int)$equipoId);
            echo json_encode([
                'status'  => 200,
                'message' => 'Miembros obtenidos correctamente',
                'data'    => ['miembros' => $miembros]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al obtener miembros',
                'data'    => ['detalles' => $e->getMessage()]
            ]);
        }
    }

    // POST /miembros-equipo
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        foreach (['usuario_id','equipo_id'] as $f) {
            if (empty($data[$f])) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => "Falta el campo obligatorio $f",
                    'data'    => null
                ]);
                return;
            }
        }

        // evita duplicados
        if ($this->model->esMiembro($data['usuario_id'], $data['equipo_id'])) {
            http_response_code(409);
            echo json_encode([
                'status'  => 409,
                'message' => 'El usuario ya es miembro de este equipo',
                'data'    => null
            ]);
            return;
        }

        $rol = $data['rol_usuario'] ?? 'jugador';
        $res = $this->model->agregarMiembro($data['usuario_id'], $data['equipo_id'], $rol);

        if (is_array($res) && isset($res['error'])) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al agregar miembro',
                'data'    => ['detalles' => $res['error']]
            ]);
        } else {
            http_response_code(201);
            echo json_encode([
                'status'  => 201,
                'message' => 'Miembro agregado correctamente',
                'data'    => null
            ]);
        }
    }

    // PATCH /miembros-equipo → cambiar rol
    public function update()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        foreach (['usuario_id','equipo_id','rol_usuario'] as $f) {
            if (empty($data[$f])) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => "Falta el campo obligatorio $f",
                    'data'    => null
                ]);
                return;
            }
        }

        $ok = $this->model->cambiarRol(
            $data['usuario_id'],
            $data['equipo_id'],
            $data['rol_usuario']
        );

        if ($ok) {
            echo json_encode([
                'status'  => 200,
                'message' => 'Rol actualizado correctamente',
                'data'    => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 404,
                'message' => 'Miembro no encontrado o rol idéntico',
                'data'    => null
            ]);
        }
    }

    // DELETE /miembros-equipo → remover miembro
    public function delete()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        foreach (['usuario_id','equipo_id'] as $f) {
            if (empty($data[$f])) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => "Falta el campo obligatorio $f",
                    'data'    => null
                ]);
                return;
            }
        }

        $ok = $this->model->removerMiembro(
            $data['usuario_id'],
            $data['equipo_id']
        );

        if ($ok) {
            echo json_encode([
                'status'  => 200,
                'message' => 'Miembro removido correctamente',
                'data'    => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 404,
                'message' => 'Miembro no encontrado o ya inactivo',
                'data'    => null
            ]);
        }
    }
}
