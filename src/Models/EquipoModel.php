<?php

namespace App\Models;

use PDO;

class EquipoModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos()
    {
        $stmt = $this->db->query("SELECT * FROM Equipo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Equipo WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO Equipo (
                nombre, descripcion, anio_fundacion, estadio_local,
                ciudad, pais, url_logo, correo_contacto,
                telefono_contacto, url_web, propietario_id
            ) VALUES (
                :nombre, :descripcion, :anio_fundacion, :estadio_local,
                :ciudad, :pais, :url_logo, :correo_contacto,
                :telefono_contacto, :url_web, :propietario_id
            )
        ");

        $stmt->execute([
            ':nombre'            => $data['nombre'],
            ':descripcion'       => $data['descripcion'] ?? null,
            ':anio_fundacion'    => $data['anio_fundacion'],
            ':estadio_local'     => $data['estadio_local'] ?? null,
            ':ciudad'            => $data['ciudad'] ?? null,
            ':pais'              => $data['pais'] ?? null,
            ':url_logo'          => $data['url_logo'] ?? null,
            ':correo_contacto'   => $data['correo_contacto'] ?? null,
            ':telefono_contacto' => $data['telefono_contacto'] ?? null,
            ':url_web'           => $data['url_web'] ?? null,
            ':propietario_id'    => $data['propietario_id']
        ]);

        return $this->obtenerPorId($this->db->lastInsertId());
    }


    public function actualizar(int $id, array $data)
    {
        $stmt = $this->db->prepare("
            UPDATE Equipo SET
                nombre = :nombre,
                descripcion = :descripcion,
                anio_fundacion = :anio_fundacion,
                estadio_local = :estadio_local,
                ciudad = :ciudad,
                pais = :pais,
                url_logo = :url_logo,
                correo_contacto = :correo_contacto,
                telefono_contacto = :telefono_contacto,
                url_web = :url_web
            WHERE id = :id
        ");

        $stmt->execute([
            ':id'                => $id,
            ':nombre'            => $data['nombre'],
            ':descripcion'       => $data['descripcion'] ?? null,
            ':anio_fundacion'    => $data['anio_fundacion'],
            ':estadio_local'     => $data['estadio_local'] ?? null,
            ':ciudad'            => $data['ciudad'] ?? null,
            ':pais'              => $data['pais'] ?? null,
            ':url_logo'          => $data['url_logo'] ?? null,
            ':correo_contacto'   => $data['correo_contacto'] ?? null,
            ':telefono_contacto' => $data['telefono_contacto'] ?? null,
            ':url_web'           => $data['url_web'] ?? null
        ]);

        return $this->obtenerPorId($id);
    }

    public function eliminar(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM Equipo WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}