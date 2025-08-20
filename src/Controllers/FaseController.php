<?php

namespace App\Controllers;

use App\Models\FaseModel;
use PDO;
use PDOException;

class FaseController
{
    private $model;

    public function __construct(PDO $db)
    {
        $this->model = new FaseModel($db);
        header('Content-Type: application/json');
    }

    // GET /fases
    public function index()
    {
        try {
            $fases = $this->model->obtenerTodos();
            if (!$fases) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron fases'
                ]);
                return;
            }
            echo json_encode([
                'status'  => 200,
                'message' => 'Fases obtenidas correctamente',
                'data'    => $fases
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener fases',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /fases/{id}
    public function show($id)
    {
        try{
            // Validar que el ID no sea nulo o vacío
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de fase no proporcionado'
                ]);
                return;
            }

            $fase = $this->model->obtenerPorId($id);
            if ($fase) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Fase encontrada',
                    'data'    => $fase
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Fase no encontrada'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener fase',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /fases/campeonato/{campeonatoId}
    public function showByCampeonato($campeonatoId){
        try{
            // Validar que el ID del campeonato no sea nulo o vacío
            if (!$campeonatoId) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de campeonato no proporcionado'
                ]);
                return;
            }

            $fases = $this->model->obtenerPorCampeonato($campeonatoId);
            if ($fases) {
                http_response_code(200);
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Fases encontradas',
                    'data'    => $fases
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Fases no encontradas para el campeonato especificado'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener fases por campeonato',
                'details' => $e->getMessage()
            ]);
        }
    }

    // POST /fases
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try{
            $data = $this->model->crear($data);
            if ($data){
                http_response_code(201);
                echo json_encode([
                    'status'  => 201,
                    'message' => 'Fase creada exitosamente',
                    'data'    => $data
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al crear fase'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al crear fase',
                'details' => $e->getMessage()
            ]);
        }
    }

    // PUT /fases/{id}
    public function update($id)
    {
        try{
            // Validar que el ID no sea nulo o vacío
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de fase no proporcionado'
                ]);
                return;
            }
            // Validar que los datos no sean nulos o vacíos
            if (empty(file_get_contents('php://input'))) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Datos de fase no proporcionados'
                ]);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if ($this->model->actualizar($id, $data)) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Fase actualizada exitosamente',
                    'data'    => $data
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Error al actualizar fase']);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al actualizar fase',
                'details' => $e->getMessage()
            ]);
        }
        
    }

    // DELETE /fases/{id}
    public function delete($id)
    {
        try{
            // Validar que el ID no sea nulo o vacío
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de fase no proporcionado'
                ]);
                return;
            }
            
            $data = $this->model->eliminar($id);
            if ($data) {
                http_response_code(200);
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Fase eliminada'
            ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al eliminar fase'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al eliminar fase',
                'details' => $e->getMessage()
            ]);
        }
    }
}
