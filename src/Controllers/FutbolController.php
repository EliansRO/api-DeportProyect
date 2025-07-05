<?php
namespace App\Controllers;

use App\Models\FutbolModel;

class FutbolController {
    private FutbolModel $model;

    public function __construct(\PDO $db) {
        $this->model = new FutbolModel($db);
    }

    public function index() {
        echo json_encode($this->model->getAll());
    }

    public function show(int $id) {
        $data = $this->model->getById($id);
        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Fútbol no encontrado']);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $id = $this->model->create($data);
            http_response_code(201);
            echo json_encode(['id' => $id]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear', 'message' => $e->getMessage()]);
        }
    }

    public function update(int $id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok = $this->model->update($id, $data);
        echo json_encode(['success' => $ok]);
    }

    public function delete(int $id) {
        $ok = $this->model->delete($id);
        echo json_encode(['success' => $ok]);
    }
}
