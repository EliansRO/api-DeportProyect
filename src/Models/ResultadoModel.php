<?php
namespace App\Models;

use PDO;

class ResultadoModel {
    private PDO $db;
    private string $table = 'Resultado';

    public function __construct(PDO $db) { $this->db = $db; }

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
            (equipo_local, equipo_visitante, puntos_local, puntos_visitante)
            VALUES (:l, :v, :pl, :pv)");
        $stmt->execute([
            ':l' => $d['equipo_local'],
            ':v' => $d['equipo_visitante'],
            ':pl' => $d['puntos_local'],
            ':pv' => $d['puntos_visitante']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            equipo_local = :l, equipo_visitante = :v,
            puntos_local = :pl, puntos_visitante = :pv
            WHERE id = :id");
        return $stmt->execute([
            ':l' => $d['equipo_local'],
            ':v' => $d['equipo_visitante'],
            ':pl' => $d['puntos_local'],
            ':pv' => $d['puntos_visitante'],
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id")
                        ->execute([':id'=>$id]);
    }
}
