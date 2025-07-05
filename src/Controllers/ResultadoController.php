<?php
namespace App\Controllers;

use App\Models\ResultadoModel;

class ResultadoController {
    private ResultadoModel $m;

    public function __construct(\PDO $db) {
        $this->m = new ResultadoModel($db);
    }

    public function index() { echo json_encode($this->m->getAll()); }

    public function show(int $id) {
        $r = $this->m->getById($id);
        if ($r) echo json_encode($r);
        else { http_response_code(404); echo json_encode(['error'=>'No encontrado']); }
    }

    public function store() {
        $d = json_decode(file_get_contents('php://input'), true);
        $id = $this->m->create($d);
        http_response_code(201);
        echo json_encode(['id'=>$id]);
    }

    public function update(int $id) {
        $d = json_decode(file_get_contents('php://input'), true);
        $ok = $this->m->update($id, $d);
        echo json_encode(['success'=>$ok]);
    }

    public function delete(int $id) {
        $ok = $this->m->delete($id);
        echo json_encode(['success'=>$ok]);
    }
}
