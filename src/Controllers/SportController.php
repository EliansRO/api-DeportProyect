<?php
namespace App\Controllers;

use App\Models\SportModel;

class SportController {
    private SportModel $model;

    public function __construct(\PDO $db) {
        $this->model = new SportModel($db);
    }

    public function index() {
        echo json_encode($this->model->getAll());
    }

    public function show(int $id) {
        $result = $this->model->getById($id);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Deporte no encontrado']);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->model->create($data);
        http_response_code(201);
        echo json_encode(['id' => $id]);
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
