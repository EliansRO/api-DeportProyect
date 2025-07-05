<?php
namespace App\Models;

use PDO;

class InscripcionUsuarioModel {
    private PDO $db;
    private string $table = 'InscripcionUsuario';

    public function __construct(PDO $db){ $this->db = $db; }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $d): int {
        $stmt = $this->db->prepare("INSERT INTO {$this->table}
            (usuario_id, equipo_id, rol_usuario)
            VALUES (:u, :e, :r)");
        $stmt->execute([
            ':u'=>$d['usuario_id'],
            ':e'=>$d['equipo_id'],
            ':r'=>$d['rol_usuario']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            usuario_id=:u, equipo_id=:e, rol_usuario=:r
            WHERE id=:id");
        return $stmt->execute([
            ':u'=>$d['usuario_id'],
            ':e'=>$d['equipo_id'],
            ':r'=>$d['rol_usuario'],
            ':id'=>$id
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id")
                        ->execute([':id'=>$id]);
    }
}
