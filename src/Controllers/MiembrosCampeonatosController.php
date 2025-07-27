<?php

namespace App\Controllers;

use App\Models\MiembrosCampeonatosModel;
use PDO;
use PDOException;

class MiembrosCampeonatosController
{
    private MiembrosCampeonatosModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new MiembrosCampeonatosModel($db);
        header('Content-Type: application/json');
    }

    // Listar todos los miembros de campeonatos
    public function index()
    {
        try {
            $items = $this->model->obtenerMiembrosPorCampeonato(null);
            echo json_encode(['status' => 200, 'message' => 'Miembros de campeonatos obtenidos', 'data' => $items]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al obtener miembros', 'details' => $e->getMessage()]);
        }
    }

    // Obtener miembro por ID
    public function show(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'El ID debe ser un entero positivo']);
            return;
        }
        try {
            $all = $this->model->obtenerMiembrosPorCampeonato(null);
            $item = null;
            foreach ($all as $member) {
                if ($member['id'] == $id) {
                    $item = $member;
                    break;
                }
            }
            if ($item) {
                echo json_encode(['status' => 200, 'message' => 'Miembro obtenido', 'data' => $item]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Miembro no encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al obtener miembro', 'details' => $e->getMessage()]);
        }
    }

    // Buscar miembros por nombre de campeonato
    public function searchByName(string $nombre)
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'El nombre del campeonato no puede estar vacío']);
            return;
        }
        try {
            $items = $this->model->obtenerMiembrosPorNombreCampeonato($nombre);
            if ($items) {
                echo json_encode(['status' => 200, 'message' => 'Miembros encontrados', 'data' => $items]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'No se encontraron miembros para el campeonato']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al buscar miembros', 'details' => $e->getMessage()]);
        }
    }

    // Agregar un equipo al campeonato
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Error en el JSON recibido']);
            return;
        }
        if (!isset($data['campeonato_id'], $data['equipo_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'campeonato_id y equipo_id son requeridos']);
            return;
        }
        // Validar que sean enteros positivos
        if (!filter_var($data['campeonato_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ||
            !filter_var($data['equipo_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'campeonato_id y equipo_id deben ser enteros positivos']);
            return;
        }
        try {
            $created = $this->model->agregarMiembro($data['campeonato_id'], $data['equipo_id']);
            if ($created) {
                echo json_encode(['status' => 201, 'message' => 'Miembro agregado al campeonato', 'data' => $data]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 500, 'message' => 'Error al agregar miembro']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al agregar miembro', 'details' => $e->getMessage()]);
        }
    }

    // Desactivar miembro del campeonato
    public function update(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'El ID debe ser un entero positivo']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Error en el JSON recibido']);
            return;
        }
        if (!isset($data['campeonato_id'], $data['equipo_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'campeonato_id y equipo_id son requeridos para desactivar']);
            return;
        }
        // Validar que sean enteros positivos
        if (!filter_var($data['campeonato_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ||
            !filter_var($data['equipo_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'campeonato_id y equipo_id deben ser enteros positivos']);
            return;
        }

        try {
            $deactivated = $this->model->desactivarMiembro($data['campeonato_id'], $data['equipo_id']);
            if ($deactivated) {
                echo json_encode(['status' => 200, 'message' => 'Miembro desactivado']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Miembro no encontrado o ya inactivo']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al desactivar miembro', 'details' => $e->getMessage()]);
        }
    }

    // Eliminar relación miembro-campeonato
    public function delete(int $id)
    {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'El ID debe ser un entero positivo']);
            return;
        }
        try {
            $deleted = $this->model->eliminar($id);
            if ($deleted) {
                echo json_encode(['status' => 200, 'message' => 'Miembro eliminado de campeonato']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Miembro no encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Error al eliminar miembro', 'details' => $e->getMessage()]);
        }
    }
}
