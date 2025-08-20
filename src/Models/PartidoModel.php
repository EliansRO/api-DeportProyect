<?php

namespace App\Models;

use PDO;
use PDOException;

class PartidoModel
{
    private $db;
    private $table = "partidos";

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

    public function obtenerPorFase($faseId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE fase_id = :fase_id");
        $stmt->execute(['fase_id' => $faseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEntre($fechaInicio, $fechaFin)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin");
        $stmt->execute(['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (fase_id, fecha, escenario_id, equipo_local_id, equipo_visitante_id, puntos_local, puntos_visitante, estado) 
                VALUES (:fase_id, :fecha, :escenario_id, :equipo_local_id, :equipo_visitante_id, :puntos_local, :puntos_visitante, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function actualizar($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET fase_id = :fase_id, fecha = :fecha, escenario_id = :escenario_id, 
                    equipo_local_id = :equipo_local_id, equipo_visitante_id = :equipo_visitante_id, 
                    puntos_local = :puntos_local, puntos_visitante = :puntos_visitante, 
                    estado = :estado, actualizado_en = CURRENT_TIMESTAMP
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
