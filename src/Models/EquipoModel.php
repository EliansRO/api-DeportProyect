<?php
namespace App\Models;

use PDO;

class EquipoModel {
    private PDO $db;
    private string $table = 'Equipo';

    public function __construct(PDO $db){ $this->db = $db; }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $d): int {
        $stmt = $this->db->prepare("INSERT INTO {$this->table}
            (nombre, descripcion, anio_fundacion, estadio_local, ciudad, pais, url_logo, correo_contacto, telefono_contacto, url_web)
        VALUES (:nombre,:descripcion,:anio_fundacion,:estadio_local,:ciudad,:pais,:url_logo,:correo_contacto,:telefono_contacto,:url_web)");
        $stmt->execute([
          ':nombre'=>$d['nombre'], ':descripcion'=>$d['descripcion'] ?? null,
          ':anio_fundacion'=>$d['anio_fundacion'] ?? null,
          ':estadio_local'=>$d['estadio_local'] ?? null,
          ':ciudad'=>$d['ciudad'] ?? null, ':pais'=>$d['pais'] ?? null,
          ':url_logo'=>$d['url_logo'] ?? null,
          ':correo_contacto'=>$d['correo_contacto'] ?? null,
          ':telefono_contacto'=>$d['telefono_contacto'] ?? null,
          ':url_web'=>$d['url_web'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            nombre=:nombre, descripcion=:descripcion, anio_fundacion=:anio_fundacion,
            estadio_local=:estadio_local, ciudad=:ciudad, pais=:pais,
            url_logo=:url_logo, correo_contacto=:correo_contacto,
            telefono_contacto=:telefono_contacto, url_web=:url_web
          WHERE id=:id");
        return $stmt->execute(array_merge($d, [':id'=>$id]));
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id")->execute([':id'=>$id]);
    }
}
