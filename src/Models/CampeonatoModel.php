<?php
namespace App\Models;

use PDO;

class CampeonatoModel {
    private PDO $db;
    private string $table = 'Campeonato';

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
            (nombre, descripcion, telefono_contacto, estado, inscripciones_abiertas, fecha_inicio, fecha_fin, deporte_id)
        VALUES (:nombre,:descripcion,:telefono_contacto,:estado,:inscripciones_abiertas,:fecha_inicio,:fecha_fin,:deporte_id)");
        $stmt->execute([
          ':nombre'=>$d['nombre'], ':descripcion'=>$d['descripcion'] ?? null,
          ':telefono_contacto'=>$d['telefono_contacto'] ?? null,
          ':estado'=>$d['estado'], ':inscripciones_abiertas'=>$d['inscripciones_abiertas'] ?? 1,
          ':fecha_inicio'=>$d['fecha_inicio'], ':fecha_fin'=>$d['fecha_fin'],
          ':deporte_id'=>$d['deporte_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            nombre=:nombre, descripcion=:descripcion, telefono_contacto=:telefono_contacto,
            estado=:estado, inscripciones_abiertas=:inscripciones_abiertas,
            fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, deporte_id=:deporte_id
          WHERE id=:id");
        return $stmt->execute(array_merge($d, [':id'=>$id]));
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id")->execute([':id'=>$id]);
    }
}
