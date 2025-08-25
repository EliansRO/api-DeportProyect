<?php

namespace App\Models;

use PDO;
use PDOException;

class EscenarioDeportivoModel
{
    private $db;
    private $table = "escenarios_deportivos";

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

    public function obtenerPorNombre($nombre)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nombre LIKE :nombre");
        $stmt->execute(['nombre' => $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (nombre, capacidad_espectadores, tamano, activa, horario_operacion) 
                VALUES (:nombre, :capacidad_espectadores, :tamano, :activa, :horario_operacion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function actualizar($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET nombre = :nombre, capacidad_espectadores = :capacidad_espectadores, 
                    tamano = :tamano, activa = :activa, horario_operacion = :horario_operacion, 
                    actualizado_en = CURRENT_TIMESTAMP
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
