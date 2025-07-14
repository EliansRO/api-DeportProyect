<?php

namespace App\Models;

use PDO;
use PDOException;

class MiembrosEquipoModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // 1. Agregar nuevo miembro
    public function agregarMiembro($usuarioId, $equipoId, $rol = 'jugador')
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO miembrosequipo (usuario_id, equipo_id, rol_usuario)
                VALUES (:usuario_id, :equipo_id, :rol)
            ");
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':equipo_id' => $equipoId,
                ':rol' => $rol
            ]);
            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // 2. Verificar si ya es miembro del equipo
    public function esMiembro($usuarioId, $equipoId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM miembrosequipo
            WHERE usuario_id = :usuario_id AND equipo_id = :equipo_id AND activo = 1
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':equipo_id' => $equipoId
        ]);
        return $stmt->fetchColumn() > 0;
    }

    // 3. Listar todos los miembros de un equipo
    public function obtenerMiembrosPorEquipo($equipoId)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre, u.correo, u.url_foto_perfil
            FROM miembrosequipo m
            JOIN usuario u ON u.id = m.usuario_id
            WHERE m.equipo_id = :equipo_id AND m.activo = 1
            ORDER BY m.fecha_ingreso ASC
        ");
        $stmt->execute([':equipo_id' => $equipoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Desactivar miembro
    public function removerMiembro($usuarioId, $equipoId)
    {
        $stmt = $this->db->prepare("
            UPDATE miembrosequipo
            SET activo = 0
            WHERE usuario_id = :usuario_id AND equipo_id = :equipo_id
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':equipo_id' => $equipoId
        ]);
        return $stmt->rowCount() > 0;
    }

    // 5. Cambiar el rol del usuario (opcional)
    public function cambiarRol($usuarioId, $equipoId, $nuevoRol)
    {
        $stmt = $this->db->prepare("
            UPDATE miembrosequipo
            SET rol_usuario = :rol
            WHERE usuario_id = :usuario_id AND equipo_id = :equipo_id
        ");
        $stmt->execute([
            ':rol' => $nuevoRol,
            ':usuario_id' => $usuarioId,
            ':equipo_id' => $equipoId
        ]);
        return $stmt->rowCount() > 0;
    }
}
