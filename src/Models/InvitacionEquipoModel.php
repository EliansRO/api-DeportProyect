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

    // Crear una invitación
    public function crearInvitacion($deUsuarioId, $paraUsuarioId, $equipoId, $mensaje = '')
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO invitacionequipo (de_usuario_id, para_usuario_id, equipo_id, mensaje)
                VALUES (:de_usuario_id, :para_usuario_id, :equipo_id, :mensaje)
            ");
            $stmt->execute([
                ':de_usuario_id' => $deUsuarioId,
                ':para_usuario_id' => $paraUsuarioId,
                ':equipo_id' => $equipoId,
                ':mensaje' => $mensaje
            ]);
            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Obtener todas las invitaciones para un usuario
    public function obtenerInvitacionesParaUsuario($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT ie.*, e.nombre AS nombre_equipo, u.nombre AS nombre_remitente
            FROM invitacionequipo ie
            JOIN equipo e ON e.id = ie.equipo_id
            JOIN usuario u ON u.id = ie.de_usuario_id
            WHERE ie.para_usuario_id = :usuario_id
            ORDER BY ie.fecha_envio DESC
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener una invitación específica (opcional pero útil)
    public function obtenerPorId($invitacionId)
    {
        $stmt = $this->db->prepare("SELECT * FROM invitacionequipo WHERE id = :id");
        $stmt->execute([':id' => $invitacionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar estado de una invitación (aceptado o rechazado)
    public function actualizarEstadoInvitacion($invitacionId, $usuarioId, $estado)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE invitacionequipo
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

    // Eliminar una invitación (por quien la envió o quien la recibió)
    public function eliminarInvitacion($id, $usuarioId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM invitacionequipo
            WHERE id = :id AND (de_usuario_id = :usuario_id OR para_usuario_id = :usuario_id)
        ");
        $stmt->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId
        ]);

        return $stmt->rowCount() > 0;
    }
}
