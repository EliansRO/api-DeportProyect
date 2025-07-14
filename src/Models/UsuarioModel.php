<?php
namespace App\Models;

use PDO;

class UsuarioModel {
    private PDO $db;
    private string $table = 'Usuario';

    public function __construct(PDO $db){
        $this->db = $db;
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data)
    {
        $sql = "INSERT INTO Usuario (nombre, cedula, sexo, fecha_nacimiento, correo, contraseña)
                VALUES (:nombre, :cedula, :sexo, :fecha_nacimiento, :correo, :contraseña)";
        
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre'           => $data['nombre'],
            ':cedula'           => $data['cedula'],
            ':sexo'             => $data['sexo'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':correo'           => $data['correo'],
            ':contraseña'       => password_hash($data['contraseña'], PASSWORD_BCRYPT),
        ]);

        return true;
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET
            nombre = :nombre,
            cedula = :cedula,
            sexo = :sexo,
            fecha_nacimiento = :fecha_nacimiento,
            estado_salud = :estado_salud,
            correo = :correo,
            contraseña = :contraseña,
            telefono = :telefono,
            direccion = :direccion,
            ciudad = :ciudad,
            pais = :pais,
            url_foto_perfil = :url_foto_perfil,
            rol = :rol,
            ultimo_login = :ultimo_login
          WHERE id = :id");
        return $stmt->execute([
            ':nombre'            => $data['nombre'],
            ':cedula'            => $data['cedula'],
            ':sexo'              => $data['sexo'],
            ':fecha_nacimiento'  => $data['fecha_nacimiento'],
            ':estado_salud'      => $data['estado_salud'] ?? null,
            ':correo'            => $data['correo'],
            ':contraseña'        => password_hash($data['contraseña'], PASSWORD_BCRYPT),
            ':telefono'          => $data['telefono'] ?? null,
            ':direccion'         => $data['direccion'] ?? null,
            ':ciudad'            => $data['ciudad'] ?? null,
            ':pais'              => $data['pais'] ?? null,
            ':url_foto_perfil'   => $data['url_foto_perfil'] ?? null,
            ':rol'               => $data['rol'] ?? 'player',
            ':ultimo_login'      => $data['ultimo_login'] ?? null,
            ':id'                => $id
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id")->execute([':id' => $id]);
    }
        /**
     * Busca un usuario por correo
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE correo = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

}
