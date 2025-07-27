<?php

namespace App\Models;

use PDO;
use PDOException;

class CampeonatoModel
{
    private $db;
    private $table = "Campeonato";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function crear($data)
    {
        try {
            $query = "INSERT INTO {$this->table} 
                (nombre, descripcion, telefono_contacto, estado, inscripciones_abiertas, fecha_inicio, fecha_fin, deporte, propietario_id, tipo)
                VALUES (:nombre, :descripcion, :telefono_contacto, :estado, :inscripciones_abiertas, :fecha_inicio, :fecha_fin, :deporte, :propietario_id, :tipo)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTodos()
    {
        $query = "SELECT * FROM {$this->table}";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    // Actualizar un campeonato existente
    public function actualizar($id, $data)
    {
        try {
            $query = "UPDATE {$this->table} SET 
                nombre = :nombre, 
                descripcion = :descripcion, 
                telefono_contacto = :telefono_contacto, 
                estado = :estado, 
                inscripciones_abiertas = :inscripciones_abiertas, 
                fecha_inicio = :fecha_inicio, 
                fecha_fin = :fecha_fin, 
                deporte = :deporte, 
                propietario_id = :propietario_id, 
                tipo = :tipo 
                WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $data['id'] = $id; // Aseguramos que el ID se incluya en los datos
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }
}
