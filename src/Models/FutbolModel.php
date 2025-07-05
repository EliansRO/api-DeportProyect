<?php
namespace App\Models;

use PDO;

class FutbolModel {
    private PDO $db;
    private string $sportTable = 'Sport';
    private string $futbolTable = 'Futbol';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $sql = "SELECT s.*, f.modalidad, f.duracion_partido, f.campo_tamanio
                FROM {$this->sportTable} s
                INNER JOIN {$this->futbolTable} f ON s.id = f.id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT s.*, f.modalidad, f.duracion_partido, f.campo_tamanio
                FROM {$this->sportTable} s
                INNER JOIN {$this->futbolTable} f ON s.id = f.id
                WHERE s.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int {
        $this->db->beginTransaction();
        try {
            // 1. Insertar en Sport
            $stmt1 = $this->db->prepare("INSERT INTO {$this->sportTable}
                (nombre, descripcion, tablero_campeonato_id)
                VALUES (:nombre, :descripcion, :tablero_id)");
            $stmt1->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':tablero_id' => $data['tablero_campeonato_id']
            ]);
            $sportId = (int)$this->db->lastInsertId();

            // 2. Insertar en Futbol
            $stmt2 = $this->db->prepare("INSERT INTO {$this->futbolTable}
                (id, modalidad, duracion_partido, campo_tamanio)
                VALUES (:id, :modalidad, :duracion, :campo)");
            $stmt2->execute([
                ':id' => $sportId,
                ':modalidad' => $data['modalidad'],
                ':duracion' => $data['duracion_partido'],
                ':campo' => $data['campo_tamanio']
            ]);

            $this->db->commit();
            return $sportId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool {
        $this->db->beginTransaction();
        try {
            $stmt1 = $this->db->prepare("UPDATE {$this->sportTable} SET
                nombre = :nombre,
                descripcion = :descripcion,
                tablero_campeonato_id = :tablero_id
                WHERE id = :id");
            $stmt1->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':tablero_id' => $data['tablero_campeonato_id'],
                ':id' => $id
            ]);

            $stmt2 = $this->db->prepare("UPDATE {$this->futbolTable} SET
                modalidad = :modalidad,
                duracion_partido = :duracion,
                campo_tamanio = :campo
                WHERE id = :id");
            $stmt2->execute([
                ':modalidad' => $data['modalidad'],
                ':duracion' => $data['duracion_partido'],
                ':campo' => $data['campo_tamanio'],
                ':id' => $id
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete(int $id): bool {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM {$this->futbolTable} WHERE id = :id")
                     ->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM {$this->sportTable} WHERE id = :id")
                     ->execute([':id' => $id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
