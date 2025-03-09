<?php
/**
 * Clase Database
 * 
 * Esta clase maneja la conexión a la base de datos MySQL de forma segura
 * Implementa el patrón Singleton para evitar múltiples conexiones
 */

namespace Back\Database;

class Database {
    // Instancia única de la clase (patrón Singleton)
    private static $instance = null;
    
    // Objeto PDO para la conexión
    private $connection;
    
    // Opciones de PDO para una conexión segura
    private $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    /**
     * Constructor privado para evitar la creación directa de objetos
     */
    private function __construct() {
        // Cargar la configuración de la base de datos
        $config = require_once __DIR__ . '/../config/database.php';
        
        $dsn = "mysql:host=" . $config['host'] . 
               ";dbname=" . $config['name'] . 
               ";port=" . $config['port'] . 
               ";charset=" . $config['charset'];
        
        try {
            $this->connection = new \PDO($dsn, $config['user'], $config['pass'], $this->options);
        } catch (\PDOException $e) {
            // Registrar el error pero no mostrar detalles sensibles
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            throw new \Exception('Error al conectar con la base de datos');
        }
    }
    
    /**
     * Método para obtener la instancia única de la clase
     * 
     * @return Database Instancia de la clase Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtiene la conexión PDO
     * 
     * @return \PDO Objeto PDO de conexión
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Ejecuta una consulta preparada
     * 
     * @param string $query Consulta SQL con marcadores de posición
     * @param array $params Parámetros para la consulta preparada
     * @return \PDOStatement Resultado de la consulta
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log('Error en la consulta: ' . $e->getMessage());
            throw new \Exception('Error al ejecutar la consulta: ' . $e->getMessage());
        }
    }
    
    /**
     * Inicia una transacción
     * 
     * @return bool True si la transacción se inició correctamente
     */
    public function beginTransaction() {
        // Verificar si ya hay una transacción activa
        if ($this->connection->inTransaction()) {
            return true; // Ya hay una transacción activa
        }
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     * 
     * @return bool True si la transacción se confirmó correctamente
     */
    public function commit() {
        // Verificar si hay una transacción activa
        if (!$this->connection->inTransaction()) {
            return false; // No hay transacción activa
        }
        return $this->connection->commit();
    }
    
    /**
     * Revierte una transacción
     * 
     * @return bool True si la transacción se revirtió correctamente
     */
    public function rollback() {
        // Verificar si hay una transacción activa
        if (!$this->connection->inTransaction()) {
            return false; // No hay transacción activa
        }
        return $this->connection->rollBack();
    }
    
    /**
     * Obtiene el último ID insertado
     * 
     * @return string El último ID insertado
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Evitar la clonación del objeto (parte del patrón Singleton)
     */
    private function __clone() {}
    
    /**
     * Evitar la deserialización del objeto (parte del patrón Singleton)
     */
    public function __wakeup() {
        throw new \Exception("No se puede deserializar una instancia de " . get_class($this));
    }
} 