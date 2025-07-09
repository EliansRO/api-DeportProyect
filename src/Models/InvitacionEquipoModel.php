<?php

namespace App\Models;

use PDO;
use PDOException;

class InvitacionEquipoModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function crearInvitacion($deUsuarioId, $paraUsuarioId, $equipoId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO InvitacionEquipo (de_usuario_id, para_usuario_id, equipo_id)
                VALUES (:de_usuario_id, :para_usuario_id, :equipo_id)
            ");
            $stmt->execute([
                ':de_usuario_id' => $deUsuarioId,
                ':para_usuario_id' => $paraUsuarioId,
                ':equipo_id' => $equipoId
            ]);

            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function obtenerInvitacionesParaUsuario($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT ie.*, e.nombre AS nombre_equipo, u.nombre AS nombre_remitente
            FROM InvitacionEquipo ie
            JOIN Equipo e ON e.id = ie.equipo_id
            JOIN Usuario u ON u.id = ie.de_usuario_id
            WHERE ie.para_usuario_id = :usuario_id
            ORDER BY ie.fecha_envio DESC
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoInvitacion($invitacionId, $usuarioId, $estado)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE InvitacionEquipo
                SET estado = :estado, fecha_respuesta = NOW()
                WHERE id = :id AND para_usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':estado' => $estado,
                ':id' => $invitacionId,
                ':usuario_id' => $usuarioId
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function eliminarInvitacion($id, $usuarioId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM InvitacionEquipo
            WHERE id = :id AND (de_usuario_id = :usuario_id OR para_usuario_id = :usuario_id)
        ");
        $stmt->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId
        ]);

        return $stmt->rowCount() > 0;
    }
}
