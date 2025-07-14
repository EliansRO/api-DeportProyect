<?php

namespace App\Controllers;

use App\Models\AmistadModel;
use PDOException;

require_once __DIR__ . '/../../middlewares/auth.php';

class AmistadController
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

        $this->model = new AmistadModel($db);
        header('Content-Type: application/json');
    }

    // GET /amistades → lista de amigos
    public function index()
    {
        try {
            $amigos = $this->model->obtenerAmigos($this->user['id']);

            echo json_encode([
                'status' => 200,
                'message' => 'Amigos obtenidos correctamente',
                'data' => ['amigos' => $amigos]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 500,
                'message' => 'Error al obtener amistades',
                'data' => ['detalles' => $e->getMessage()]
            ]);
        }
    }

    // POST /amistades → crear amistad
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['usuario_id'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Falta el ID del otro usuario',
                'data' => null
            ]);
            return;
        }

        if ($data['usuario_id'] == $this->user['id']) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'No puedes ser amigo de ti mismo',
                'data' => null
            ]);
            return;
        }

        $resultado = $this->model->crearAmistad($this->user['id'], $data['usuario_id']);

        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code(409);
            echo json_encode([
                'status' => 409,
                'message' => $resultado['error'],
                'data' => null
            ]);
        } else {
            http_response_code(201);
            echo json_encode([
                'status' => 201,
                'message' => 'Amistad creada exitosamente',
                'data' => null
            ]);
        }
    }

    // DELETE /amistades/{id} → eliminar amistad con usuario_id = {id}
    public function delete(int $id)
    {
        $ok = $this->model->eliminarAmistad($this->user['id'], $id);

        if ($ok) {
            echo json_encode([
                'status' => 200,
                'message' => 'Amistad eliminada correctamente',
                'data' => null
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 404,
                'message' => 'Amistad no encontrada',
                'data' => null
            ]);
        }
    }
}
