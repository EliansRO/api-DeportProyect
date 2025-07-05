<?php
namespace App\Config;

use PDO;
use Dotenv\Dotenv;

class Database {
    private PDO $conn;

    public function __construct(){
        // Carga variables de entorno desde el .env en la raíz
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        // Conexión PDO a MySQL
        $this->conn = new PDO(
            "mysql:host={$host};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
        );
    }

    /**
     * Devuelve la conexión PDO
     *
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->conn;
    }
}

