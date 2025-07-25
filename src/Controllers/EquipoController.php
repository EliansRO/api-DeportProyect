<?php

namespace App\Controllers;

use App\Models\EquipoModel;
use PDO;
use PDOException;

require_once __DIR__ . '/../../middlewares/auth.php';

class EquipoController
{
    private $db;
    private $model;
    private $user;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new EquipoModel($db);
        $this->user = getAuthUser();

        // Verificar si el usuario está autenticado
        if (!$this->user) {
            http_response_code(401);
            echo json_encode([
                'status' => 401,
                'message' => 'No autenticado',
                'data' => null
            ]);
            exit;
        }

        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $equipos = $this->model->obtenerTodos();
            echo json_encode([
                'status' => 200,
                'message' => 'Equipos obtenidos correctamente',
                'data' => ['equipos' => $equipos]
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al obtener los equipos', $e);
        }
    }

    public function show(int $id)
    {
        try {
            $equipo = $this->model->obtenerPorId($id);

            // Verificar si el equipo existe
            if ($equipo) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Equipo obtenido correctamente',
                    'data' => ['equipo' => $equipo]
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Equipo no encontrado',
                    'data' => null
                ]);
            }
        } catch (PDOException $e) {
            $this->errorResponse('Error al obtener el equipo', $e);
        }
    }

    public function misEquipos()
    {
        try {
            $usuarioId = $this->user['id'];

            $equiposPropietarios = $this->model->obtenerPorPropietario($usuarioId);
            $equiposMiembros = $this->model->obtenerComoMiembro($usuarioId);

            // Combinar ambos arrays
            $todosEquipos = array_merge($equiposPropietarios, $equiposMiembros);

            // Eliminar duplicados basándonos en el id del equipo
            $equiposUnicos = [];
            foreach ($todosEquipos as $equipo) {
                $equiposUnicos[$equipo['id']] = $equipo;
            }
            $equiposUnicos = array_values($equiposUnicos);

            echo json_encode([
                'status' => 200,
                'message' => 'Equipos del usuario obtenidos correctamente',
                'data' => $equiposUnicos
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al obtener los equipos del usuario', $e);
        }
    }

    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar que los campos obligatorios no estén vacíos
        if (empty($data['nombre']) || empty($data['deporte'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Faltan campos obligatorios (nombre, deporte)',
                'data' => null
            ]);
            return;
        }

        try {
            // Asignar automáticamente el propietario
            $data['propietario_id'] = $this->user['id'];

            $nuevo = $this->model->crear($data);
            http_response_code(201);
            echo json_encode([
                'status' => 201,
                'message' => 'Equipo creado exitosamente',
                'data' => ['equipo' => $nuevo]
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al crear el equipo', $e);
        }
    }

    public function update(int $id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            $equipo = $this->model->obtenerPorId($id);
            
            // Verificar si el equipo existe
            if (!$equipo) {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Equipo no encontrado',
                    'data' => null
                ]);
                return;
            }

            // Validar que el usuario sea el propietario del equipo
            if ($equipo['propietario_id'] != $this->user['id']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 403,
                    'message' => 'No tienes permiso para editar este equipo',
                    'data' => null
                ]);
                return;
            }

            // Evitar que modifiquen el propietario por accidente o malicia
            unset($data['propietario_id']);

            $actualizado = $this->model->actualizar($id, $data);
            echo json_encode([
                'status' => 200,
                'message' => 'Equipo actualizado correctamente',
                'data' => ['equipo' => $actualizado]
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al actualizar el equipo', $e);
        }
    }

    public function buscarPorNombre()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $nombre = $data['nombre'] ?? '';

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'El campo nombre es obligatorio',
                'data' => null
            ]);
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM Equipo WHERE nombre LIKE :nombre");
            $like = '%' . $nombre . '%';
            $stmt->bindParam(':nombre', $like, PDO::PARAM_STR);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 200,
                'message' => 'Equipos encontrados',
                'data' => ['equipos' => $resultados]
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al buscar equipos por nombre', $e);
        }
    }


    public function delete(int $id)
    {
        try {
            $equipo = $this->model->obtenerPorId($id);

            // Verificar si el equipo existe
            if (!$equipo) {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => 'Equipo no encontrado',
                    'data' => null
                ]);
                return;
            }

            // Verificar si el usuario es el propietario del equipo
            if ($equipo['propietario_id'] != $this->user['id']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 403,
                    'message' => 'No tienes permiso para eliminar este equipo',
                    'data' => null
                ]);
                return;
            }

            $this->model->eliminar($id);

            echo json_encode([
                'status' => 200,
                'message' => 'Equipo eliminado correctamente',
                'data' => null
            ]);
        } catch (PDOException $e) {
            $this->errorResponse('Error al eliminar el equipo', $e);
        }
    }

    private function errorResponse(string $message, PDOException $e)
    {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => $message,
            'data' => ['detalles' => $e->getMessage()]
        ]);
    }
}
