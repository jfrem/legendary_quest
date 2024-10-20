<?php

// config/Database.php

// Incluye el archivo de respuesta que gestiona la gestión de errores.
require_once __DIR__ . '/../core/Response.php';

/**
 * Clase Database
 * 
 * Esta clase gestiona la conexión a una base de datos MySQL utilizando el patrón Singleton.
 * Proporciona métodos para obtener y gestionar la conexión a la base de datos.
 */
class Database
{
    // Propiedades privadas para la configuración de la base de datos
    private $host;       // Host de la base de datos
    private $port;       // Puerto de la base de datos
    private $dbName;     // Nombre de la base de datos
    private $username;   // Nombre de usuario para la conexión
    private $password;   // Contraseña para la conexión
    private static $instance = null; // Instancia única de la clase
    private $conn;       // Conexión a la base de datos

    /**
     * Constructor de la clase.
     * 
     * Asigna valores a las propiedades de configuración de la base de datos 
     * y valida que el nombre de la base de datos esté configurado.
     */
    private function __construct()
    {
        // Asignar valores desde variables de entorno o valores por defecto
        $this->host = 'localhost';
        $this->port = 3307; // Puerto de MySQL
        $this->dbName = 'legendary_quest';
        $this->username = 'root';
        $this->password = '';

        // Validar que el nombre de la base de datos no esté vacío
        if (empty($this->dbName)) {
            Response::error("El nombre de la base de datos no está configurado.", 500);
        }
    }

    /**
     * Método estático para obtener la instancia única de la clase (Singleton).
     * 
     * @return Database Instancia de la clase Database.
     */
    public static function getInstance(): Database
    {
        // Crear una nueva instancia si no existe
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Método para obtener la conexión a la base de datos.
     * 
     * @return PDO Objeto de conexión a la base de datos.
     * @throws Exception En caso de fallo en la conexión.
     */
    public function getConnection(): PDO
    {
        // Si la conexión ya fue establecida, devolverla
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            // Construir el DSN para la conexión a MySQL
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";
            // Crear la conexión PDO
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Configurar atributos de PDO para manejo de errores y tipo de datos
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Registrar el mensaje de la excepción en los logs en caso de error
            error_log($exception->getMessage());
            Response::error("Conexión fallida a la base de datos.", 500);
        }

        return $this->conn; // Devolver la conexión
    }

    /**
     * Método para cerrar la conexión a la base de datos.
     */
    public function closeConnection(): void
    {
        $this->conn = null; // Cerrar la conexión
    }
}
