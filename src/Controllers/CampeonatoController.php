<?php

namespace App\Controllers;

use App\Models\CampeonatoModel;
use PDO;
use PDOException;

class CampeonatoController
{
    private CampeonatoModel $model;
    private $user;

    public function __construct(PDO $db)
    {
        $this->model = new CampeonatoModel($db);
        $this->user = getAuthUser();
        header('Content-Type: application/json');
    }

    // GET /campeonatos
    public function index()
    {
        try {
            $items = $this->model->obtenerTodos();
            echo json_encode([
                'status' => 200,
                'message' => 'Campeonatos obtenidos',
                'data' => $items
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al obtener campeonatos',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /campeonatos/{id}
    public function show(int $id)
    {
        try {
            $item = $this->model->obtenerPorId($id);
            if ($item) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Campeonato obtenido',
                    'data'    => $item
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Campeonato no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al obtener campeonato',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /campeonatos/propietario/{propietarioId}
    public function showByPropietario(int $propietarioId)
    {
        // Validación: el ID de propietario debe ser un número mayor a 0
        if ($propietarioId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'ID de propietario inválido'
            ]);
            return;
        }

        try {
            $items = $this->model->obtenerPorPropietario($propietarioId);
            if ($items) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Campeonatos obtenidos por propietario',
                    'data'    => $items
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron campeonatos para este propietario'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al obtener campeonatos por propietario',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /campeonatos/buscar?nombre={nombre}
    public function buscarPorNombre()
    {
        $nombre = $_GET['nombre'] ?? '';
        $nombre = trim($nombre);
        
        // Validación: el parámetro "nombre" es requerido
        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'El parámetro "nombre" es requerido'
            ]);
            return;
        }

        // Validación: el nombre debe tener un mínimo de 3 caracteres y un máximo de 255
        if (strlen($nombre) < 3) {
            http_response_code(422);
            echo json_encode([
                'status'  => 422,
                'message' => 'El parámetro "nombre" debe tener al menos 3 caracteres'
            ]);
            return;
        }
        if (strlen($nombre) > 255) {
            http_response_code(422);
            echo json_encode([
                'status'  => 422,
                'message' => 'El parámetro "nombre" excede la longitud permitida'
            ]);
            return;
        }

        try {
            $items = $this->model->obtenerPorNombre($nombre);
            if ($items) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Campeonatos encontrados',
                    'data'    => $items
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron campeonatos con ese nombre'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al buscar campeonatos',
                'details' => $e->getMessage()
            ]);
        }
    }

    // POST /campeonatos
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Datos inválidos'
            ]);
            return;
        }

        // Validación de campos requeridos (ajustar según tus necesidades)
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            http_response_code(422);
            echo json_encode([
                'status'  => 422,
                'message' => 'El campo "nombre" es requerido'
            ]);
            return;
        }

        try {
            $created = $this->model->crear($data);
            if ($created) {
                echo json_encode([
                    'status'  => 201,
                    'message' => 'Campeonato creado',
                    'data'    => $data
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status'  => 500,
                    'message' => 'Error al crear campeonato'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al crear campeonato',
                'details' => $e->getMessage()
            ]);
        }
    }

    // PUT /campeonatos/{id}
    public function update(int $id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'Datos inválidos'
            ]);
            return;
        }

        // Se obtiene el campeonato actual
        $campeonato = $this->model->obtenerPorId($id);
        if (!$campeonato) {
            http_response_code(404);
            echo json_encode([
                'status'  => 404,
                'message' => 'Campeonato no encontrado'
            ]);
            return;
        }

        // Verificar que el usuario actual es el propietario del campeonato
        if ($campeonato['propietario_id'] != $this->user['id']) {
            http_response_code(403);
            echo json_encode([
                'status'  => 403,
                'message' => 'No autorizado: solo el propietario puede actualizar el campeonato'
            ]);
            return;
        }

        // Validación de campos requeridos (ajustar según tus necesidades)
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            http_response_code(422);
            echo json_encode([
                'status'  => 422,
                'message' => 'El campo "nombre" es requerido'
            ]);
            return;
        }

        try {
            // Se asume que el modelo tiene un método 'actualizar' para modificar el registro existente.
            $updated = $this->model->actualizar($id, $data);
            if ($updated) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Campeonato actualizado',
                    'data'    => $data
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status'  => 500,
                    'message' => 'Error al actualizar campeonato'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al actualizar campeonato',
                'details' => $e->getMessage()
            ]);
        }
    }

    // DELETE /campeonatos/{id}
    public function delete(int $id)
    {
        // Validación: el ID debe ser un número mayor a 0
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'status'  => 400,
                'message' => 'ID inválido'
            ]);
            return;
        }

        try {
            $deleted = $this->model->eliminar($id);
            if ($deleted) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Campeonato eliminado'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Campeonato no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 500,
                'message' => 'Error al eliminar campeonato',
                'details' => $e->getMessage()
            ]);
        }
    }
}
