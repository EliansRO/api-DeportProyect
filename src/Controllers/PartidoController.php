<?php

namespace App\Controllers;

use App\Models\PartidoModel;
use PDO;
use PDOException;

class PartidoController
{
    private $model;

    public function __construct(PDO $db)
    {
        $this->model = new PartidoModel($db);
        header('Content-Type: application/json');
    }

    // GET /partidos
    public function index()
    {
        try{
            $partidos = $this->model->obtenerTodos();
            if (!$partidos) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron partidos'
                ]);
                return;
            }
            http_response_code(200);

            echo json_encode([
                'status'  => 200,
                'message' => 'Partidos obtenidos correctamente',
                'data'    => $partidos
            ]);
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener partidos',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /partidos/{id}
    public function show($id)
    {
        try{
            // Validar que el ID no sea nulo o vacío
            if (!$id || empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de partido no proporcionado'
                ]);
                return;
            }

            $partido = $this->model->obtenerPorId($id);
            if ($partido) {
                http_response_code(200);
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Partido obtenido correctamente',
                    'data'    => $partido
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'Partido no encontrado'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener partido',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /partidos/entre?fecha_inicio=YYYY-MM-DD&fecha_fin=YYYY-MM-DD
    public function buscarPorFecha(){
        try{
            // Validar que los parámetros de fecha no sean nulos o vacíos
            if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin']) || empty($_GET['fecha_inicio']) || empty($_GET['fecha_fin'])) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Parámetros de fecha no proporcionados'
                ]);
                return;
            }

            $fechaInicio = $_GET['fecha_inicio'];
            $fechaFin = $_GET['fecha_fin'];

            $partidos = $this->model->obtenerEntre($fechaInicio, $fechaFin);
            if (!$partidos) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron partidos en el rango de fechas especificado'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'status'  => 200,
                'message' => 'Partidos obtenidos correctamente',
                'data'    => $partidos
            ]);
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al buscar partidos por fecha',
                'details' => $e->getMessage()
            ]);
        }
    }

    // GET /partidos/fase/{faseId}
    public function showByFase($faseId)
    {
        try{
            // Validar que el ID de fase no sea nulo o vacío
            if (!$faseId || empty($faseId)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de fase no proporcionado'
                ]);
                return;
            }

            $partidos = $this->model->obtenerPorFase($faseId);
            if (!$partidos) {
                http_response_code(404);
                echo json_encode([
                    'status'  => 404,
                    'message' => 'No se encontraron partidos para la fase especificada'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'status'  => 200,
                'message' => 'Partidos obtenidos correctamente',
                'data'    => $partidos
            ]);
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener partidos por fase',
                'details' => $e->getMessage()
            ]);
        }
    }

    // POST /partidos
    public function store()
    {
        try{
            // Validar que los datos no estén vacíos
            if (empty($_POST)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Datos del partido no proporcionados'
                ]);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $data = $this->model->crear($data);
            if ($data) {
                http_response_code(201);
                echo json_encode([
                    'status'  => 201,
                    'message' => 'Partido creado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al crear partido'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al crear partido',
                'details' => $e->getMessage()
            ]);
        }
    }

    // PUT /partidos/{id}
    public function update($id)
    {
        try{
            // Validar ID
            if (!$id || empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de partido no proporcionado'
                ]);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $data = $this->model->actualizar($id, $data);
            if ($data) {
                echo json_encode(
                [
                    'status'  => 200,
                    'message' => 'Partido actualizado exitosamente',
                    'data'    => $data
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al actualizar partido'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al actualizar partido',
                'details' => $e->getMessage()
            ]);
        }
    }

    // DELETE /partidos/{id}
    public function delete($id)
    {
        try{
            // Validar ID
            if (!$id || empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'ID de partido no proporcionado'
                ]);
                return;
            }
            // Verificar si el partido existe
            $data = $this->model->obtenerPorId($id);
            if ($data) {
                echo json_encode([
                    'status'  => 200,
                    'message' => 'Partido eliminado exitosamente',
                    'data'    => $this->model->eliminar($id)
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 400,
                    'message' => 'Error al eliminar partido'
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al eliminar partido',
                'details' => $e->getMessage()
            ]);
        }
        
    }
}
