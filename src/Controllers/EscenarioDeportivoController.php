<?php

namespace App\Controllers;

use App\Models\EscenarioDeportivoModel;
use PDO;
use PDOException;

class EscenarioDeportivoController
{
    private $model;

    public function __construct(PDO $db)
    {
        $this->model = new EscenarioDeportivoModel($db);
        header('Content-Type: application/json');
    }

    // GET /escenarios-deportivos
    public function index()
    {
        try{
            $escenarios = $this->model->obtenerTodos();
            if (!$escenarios) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron escenarios deportivos'
                ]);
                return;
            }
            echo json_encode([
                'status'  => 200,
                'message' => 'Escenarios deportivos obtenidos correctamente',
                'data'    => $escenarios
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener escenarios deportivos',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /escenarios-deportivos/{id}
    public function show($id)
    {
        try {
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de escenario deportivo no proporcionado'
                ]);
                return;
            }

            $escenario = $this->model->obtenerPorId($id);
            if (!$escenario) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Escenario deportivo no encontrado'
                ]);
                return;
            }
            echo json_encode([
                'status'  => 200,
                'message' => 'Escenario deportivo obtenido correctamente',
                'data'    => $escenario
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener escenario deportivo',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /escenarios-deportivos/buscar?nombre={nombre}
    public function showByName($nombre)
    {
        try {
            if (!$nombre) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Nombre de escenario deportivo no proporcionado'
                ]);
                return;
            }

            $escenario = $this->model->obtenerPorNombre($nombre);
            if (!$escenario) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Escenario deportivo no encontrado'
                ]);
                return;
            }
            echo json_encode([
                'status'  => 200,
                'message' => 'Escenario deportivo obtenido correctamente',
                'data'    => $escenario
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener escenario deportivo por nombre',
                'details' => $e->getMessage()
            ]);
        }
    }

    // POST /escenarios-deportivos
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $data = $this->model->crear($data);
            if ($data) {
                http_response_code(201);
                echo json_encode([
                    'status'  => 201,
                    'message' => 'Escenario deportivo creado exitosamente',
                    'data'    => $data
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al crear escenario deportivo'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al crear escenario deportivo',
                'details' => $e->getMessage()
            ]);
        }
    }

    // PUT /escenarios-deportivos/{id}
    public function update($id)
    {
        try{
            // Validar ID
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de escenario deportivo no proporcionado'
                ]);
                return;
            }

            // Validar que los datos no sean nulos o vacíos
            if (empty(file_get_contents('php://input'))) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Datos de escenario deportivo no proporcionados'
                ]);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $data = $this->model->actualizar($id, $data);
            if ($data) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Escenario deportivo actualizado exitosamente',
                    'data'    => $data
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al actualizar escenario deportivo'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al actualizar escenario deportivo',
                'details' => $e->getMessage()
            ]);
        }
    }

    // DELETE /escenarios-deportivos/{id}
    public function delete($id)
    {
        try {
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de escenario deportivo no proporcionado'
                ]);
                return;
            }

            if ($this->model->eliminar($id)) {
                http_response_code(204);
                echo json_encode([
                    'status'  => 204,
                    'message' => 'Escenario deportivo eliminado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Escenario deportivo no encontrado'
                ]);
            }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'status' => 500,
                    'message' => 'Error al eliminar escenario deportivo',
                    'details' => $e->getMessage()
                ]);
        }
    }
}
