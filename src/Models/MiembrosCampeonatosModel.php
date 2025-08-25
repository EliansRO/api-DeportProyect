<?php

namespace App\Models;

use PDO;
use PDOException;

class MiembrosCampeonatosModel
{
    private $db;
    private $table = "MiembrosCampeonatos";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function agregarMiembro($campeonato_id, $equipo_id)
    {
        try {
            $query = "INSERT INTO {$this->table} (campeonato_id, equipo_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$campeonato_id, $equipo_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerMiembrosPorCampeonato($campeonato_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE campeonato_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$campeonato_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMiembrosPorNombreCampeonato($nombre)
    {
        $query = "SELECT * FROM {$this->table} WHERE campeonato_id IN (SELECT id FROM Campeonatos WHERE nombre LIKE ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['%' . $nombre . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function desactivarMiembro($campeonato_id, $equipo_id)
    {
        $query = "UPDATE {$this->table} SET activo = false WHERE campeonato_id = ? AND equipo_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$campeonato_id, $equipo_id]);
    }

    public function eliminar($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
