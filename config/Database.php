<?php

// config/Database.php

require_once __DIR__ . '/../core/Response.php';

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private static $instance = null;
    public $conn;

    public function __construct()
    {
        // Asignar valores desde variables de entorno
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = (int)($_ENV['DB_PORT'] ?? 3307);
        $this->db_name = $_ENV['DB_NAME'] ?? 'legendary_quest';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';

        // Validar configuración
        if (empty($this->db_name)) {
            Response::error("El nombre de la base de datos no está configurado.", 500);
        }
    }

    // Implementación del patrón Singleton
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Configurar atributos de PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Log exception message instead of exposing it
            error_log($exception->getMessage());
            Response::error("Conexión fallida a la base de datos.", 500);
        }

        return $this->conn;
    }

    // Optional: Method to close the connection
    public function closeConnection()
    {
        $this->conn = null;
    }
}
