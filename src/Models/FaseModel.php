<?php

namespace App\Models;

use PDO;
use PDOException;

class FaseModel
{
    private $db;
    private $table = "fases";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCampeonato($campeonatoId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE campeonato_id = :campeonato_id");
        $stmt->execute(['campeonato_id' => $campeonatoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (campeonato_id, nombre, orden, tipo, estado, fecha_inicio, fecha_fin) 
                VALUES (:campeonato_id, :nombre, :orden, :tipo, :estado, :fecha_inicio, :fecha_fin)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function actualizar($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET campeonato_id = :campeonato_id, nombre = :nombre, orden = :orden, tipo = :tipo, estado = :estado, 
                    fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, actualizado_en = CURRENT_TIMESTAMP
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function eliminar($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
