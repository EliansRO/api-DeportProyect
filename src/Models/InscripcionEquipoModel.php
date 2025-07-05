<?php
namespace App\Models;

use PDO;

class InscripcionEquipoModel {
    private PDO $db;
    private string $table = 'InscripcionEquipo';

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
            (campeonato_id, equipo_id, fixture_id)
            VALUES (:campeonato_id, :equipo_id, :fixture_id)");
        $stmt->execute([
            ':campeonato_id' => $data['campeonato_id'],
            ':equipo_id'     => $data['equipo_id'],
            ':fixture_id'    => $data['fixture_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            campeonato_id = :campeonato_id,
            equipo_id = :equipo_id,
            fixture_id = :fixture_id
            WHERE id = :id");
        return $stmt->execute([
            ':campeonato_id' => $data['campeonato_id'],
            ':equipo_id'     => $data['equipo_id'],
            ':fixture_id'    => $data['fixture_id'],
            ':id'            => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
