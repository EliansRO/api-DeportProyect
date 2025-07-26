<?php

namespace App\Models;

use PDO;
use PDOException;

class SolicitudAmistad
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function crear($deUsuarioId, $paraUsuarioId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO SolicitudAmistad (
                    de_usuario_id,
                    para_usuario_id,
                    estado,
                    fecha_envio
                ) VALUES (
                    :de_usuario_id,
                    :para_usuario_id,
                    'pendiente',
                    NOW()
                )
            ");

            $stmt->execute([
                ':de_usuario_id'   => $deUsuarioId,
                ':para_usuario_id' => $paraUsuarioId,
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Error al crear solicitud de amistad: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPendientesPara($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM SolicitudAmistad
            WHERE para_usuario_id = :usuario_id AND estado = 'pendiente'
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudesParaUsuario($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT sa.*, u.nombre AS nombre_remitente
            FROM SolicitudAmistad sa
            JOIN Usuario u ON u.id = sa.de_usuario_id
            WHERE sa.para_usuario_id = :id
            ORDER BY sa.fecha_envio DESC
        ");
        $stmt->execute([':id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id, $nuevoEstado)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE SolicitudAmistad
                SET estado = :estado, fecha_respuesta = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id'     => $id,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de solicitud: " . $e->getMessage());
            return false;
        }
    }

    public function existeSolicitud($deUsuarioId, $paraUsuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM SolicitudAmistad
            WHERE de_usuario_id = :de AND para_usuario_id = :para
               OR de_usuario_id = :para AND para_usuario_id = :de
        ");
        $stmt->execute([
            ':de'   => $deUsuarioId,
            ':para' => $paraUsuarioId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
