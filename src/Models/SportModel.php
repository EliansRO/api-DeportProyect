<?php
namespace App\Models;

use PDO;

class SportModel {
    private PDO $db;
    private string $table = 'Sport';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (nombre, descripcion, tablero_campeonato_id)
            VALUES (:nombre, :descripcion, :tablero_id)");
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':tablero_id'  => $data['tablero_campeonato_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            nombre = :nombre,
            descripcion = :descripcion,
            tablero_campeonato_id = :tablero_id
            WHERE id = :id");
        return $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':tablero_id'  => $data['tablero_campeonato_id'],
            ':id'          => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
