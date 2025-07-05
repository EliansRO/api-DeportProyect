<?php
namespace App\Models;

use PDO;

class ArenaDeportivaModel {
    private PDO $db;
    private string $table = 'ArenaDeportiva';

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

    public function create(array $d): int {
        $stmt = $this->db->prepare("INSERT INTO {$this->table}
            (nombre, capacidad, tamano, activa, horario_operacion)
            VALUES (:nombre, :capacidad, :tamano, :activa, :horario_operacion)");
        $stmt->execute([
            ':nombre' => $d['nombre'],
            ':capacidad' => $d['capacidad'] ?? 0,
            ':tamano' => $d['tamano'] ?? 0,
            ':activa' => $d['activa'] ?? true,
            ':horario_operacion' => $d['horario_operacion'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            nombre = :nombre, capacidad = :capacidad,
            tamano = :tamano, activa = :activa, horario_operacion = :horario_operacion
            WHERE id = :id");
        return $stmt->execute([
            ':nombre' => $d['nombre'],
            ':capacidad' => $d['capacidad'],
            ':tamano' => $d['tamano'],
            ':activa' => $d['activa'],
            ':horario_operacion' => $d['horario_operacion'],
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id")
                        ->execute([':id' => $id]);
    }
}