<?php
namespace App\Models;

use PDO;

class FixtureModel {
    private PDO $db;
    private string $table = 'Fixture';

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
            (fecha_partido, arena_id, resultado_id)
            VALUES (:fecha_partido, :arena_id, :resultado_id)");
        $stmt->execute([
            ':fecha_partido' => $data['fecha_partido'],
            ':arena_id'      => $data['arena_id'],
            ':resultado_id'  => $data['resultado_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            fecha_partido = :fecha_partido,
            arena_id = :arena_id,
            resultado_id = :resultado_id
            WHERE id = :id");
        return $stmt->execute([
            ':fecha_partido' => $data['fecha_partido'],
            ':arena_id'      => $data['arena_id'],
            ':resultado_id'  => $data['resultado_id'],
            ':id'            => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
