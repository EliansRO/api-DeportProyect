<?php

namespace App\Models;

use PDO;
use PDOException;

class AmistadModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Verifica si ya existe amistad entre dos usuarios
    public function existeAmistad($usuarioA, $usuarioB)
    {
        $u1 = min($usuarioA, $usuarioB);
        $u2 = max($usuarioA, $usuarioB);

        $stmt = $this->db->prepare("
            SELECT id FROM Amistad
            WHERE usuario1_id = :u1 AND usuario2_id = :u2 AND activo = 1
        ");
        $stmt->execute([
            ':u1' => $u1,
            ':u2' => $u2
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // Crear nueva amistad
    public function crearAmistad($usuarioA, $usuarioB)
    {
        $u1 = min($usuarioA, $usuarioB);
        $u2 = max($usuarioA, $usuarioB);

        if ($this->existeAmistad($u1, $u2)) {
            return ['error' => 'La amistad ya existe'];
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO Amistad (usuario1_id, usuario2_id)
                VALUES (:u1, :u2)
            ");
            $stmt->execute([
                ':u1' => $u1,
                ':u2' => $u2
            ]);

            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Listar amigos de un usuario (solo amistades activas)
    public function obtenerAmigos($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nombre, u.correo, u.url_foto_perfil
            FROM Amistad a
            JOIN Usuario u ON (
                (a.usuario1_id = :id AND u.id = a.usuario2_id)
                OR
                (a.usuario2_id = :id AND u.id = a.usuario1_id)
            )
            WHERE a.activo = 1
        ");
        $stmt->execute([':id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar equipos de amigos de un usuario (sin duplicados)
    public function obtenerEquiposDeAmigos($usuarioId){
        $stmt = $this->db->prepare("
            SELECT DISTINCT e.id, e.nombre, e.descripcion, e.url_imagen
            FROM Amistad a
            JOIN Usuario u ON (
                (a.usuario1_id = :id AND u.id = a.usuario2_id)
                OR
                (a.usuario2_id = :id AND u.id = a.usuario1_id)
            )
            JOIN Equipo e ON u.id = e.usuario_id
            WHERE a.activo = 1
        ");
        $stmt->execute([':id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar amistad (desactivar)
    public function eliminarAmistad($usuarioA, $usuarioB)
    {
        $u1 = min($usuarioA, $usuarioB);
        $u2 = max($usuarioA, $usuarioB);

        $stmt = $this->db->prepare("
            UPDATE Amistad
            SET activo = 0
            WHERE usuario1_id = :u1 AND usuario2_id = :u2
        ");
        $stmt->execute([
            ':u1' => $u1,
            ':u2' => $u2
        ]);

        return $stmt->rowCount() > 0;
    }
}
