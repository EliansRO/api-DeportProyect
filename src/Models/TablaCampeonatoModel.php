<?php
namespace App\Models;

use PDO;

class TablaCampeonatoModel {
    private PDO $db;
    private string $table = 'TablaCampeonato';

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
            (nombre, descripcion, url_icono, puntos_acumulados)
            VALUES (:nombre, :descripcion, :url_icono, :puntos_acumulados)");
        $stmt->execute([
            ':nombre'            => $data['nombre'],
            ':descripcion'       => $data['descripcion'] ?? null,
            ':url_icono'         => $data['url_icono'] ?? null,
            ':puntos_acumulados' => $data['puntos_acumulados'] ?? 0
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            nombre = :nombre,
            descripcion = :descripcion,
            url_icono = :url_icono,
            puntos_acumulados = :puntos_acumulados
            WHERE id = :id");
        return $stmt->execute([
            ':nombre'            => $data['nombre'],
            ':descripcion'       => $data['descripcion'],
            ':url_icono'         => $data['url_icono'],
            ':puntos_acumulados' => $data['puntos_acumulados'],
            ':id'                => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
