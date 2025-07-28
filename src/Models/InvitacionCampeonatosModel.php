<?php

namespace App\Models;

use PDO;
use PDOException;

class InvitacionCampeonatosModel
{
    private $db;
    private $table = "InvitacionCampeonatos";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function crear($data)
    {
        try {
            $query = "INSERT INTO {$this->table}
                (campeonato_id, equipo_id, de_usuario_id, para_usuario_id, mensaje, estado, fecha_envio, fecha_respuesta)
                VALUES (:campeonato_id, :equipo_id, :de_usuario_id, :para_usuario_id, :mensaje, :estado, :fecha_envio, :fecha_respuesta)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarEstado($id, $estado)
    {
        $query = "UPDATE {$this->table} SET estado = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    public function eliminarInvitacion($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function obtenerInvitacionPorParaUsuarioId($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE para_usuario_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
