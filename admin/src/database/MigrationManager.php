<?php
/**
 * Clase MigrationManager
 * 
 * Esta clase se encarga de gestionar las migraciones de la base de datos
 */

namespace App\Database;

class MigrationManager {
    private $db;
    private $migrationsDir;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsDir = __DIR__ . '/migrations';
        
        // Asegurarse de que la carpeta de migraciones existe
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
        }
    }
    
    /**
     * Crea la tabla de migraciones si no existe
     */
    private function createMigrationsTable() {
        $query = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->query($query);
    }
    
    /**
     * Verifica si una migración ya ha sido ejecutada
     * 
     * @param string $migrationName Nombre de la migración
     * @return bool True si la migración ya existe, false en caso contrario
     */
    private function migrationExists($migrationName) {
        $query = "SELECT COUNT(*) as count FROM migrations WHERE migration = ?";
        $result = $this->db->query($query, [$migrationName])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Registra una migración como ejecutada
     * 
     * @param string $migrationName Nombre de la migración
     */
    private function registerMigration($migrationName) {
        $query = "INSERT INTO migrations (migration) VALUES (?)";
        $this->db->query($query, [$migrationName]);
    }
    
    /**
     * Elimina una migración del registro
     * 
     * @param string $migrationName Nombre de la migración
     */
    private function unregisterMigration($migrationName) {
        $query = "DELETE FROM migrations WHERE migration = ?";
        $this->db->query($query, [$migrationName]);
    }
    
    /**
     * Carga una migración desde un archivo
     * 
     * @param string $migrationFile Ruta al archivo de migración
     * @return array Datos de la migración (up y down)
     */
    private function loadMigration($migrationFile) {
        $extension = pathinfo($migrationFile, PATHINFO_EXTENSION);
        
        if ($extension === 'php') {
            // Archivo PHP que devuelve un array con 'up' y 'down'
            return require $migrationFile;
        } else if ($extension === 'sql') {
            // Archivo SQL tradicional (solo up)
            return [
                'up' => file_get_contents($migrationFile),
                'down' => null // No hay instrucciones para revertir
            ];
        }
        
        throw new \Exception("Formato de migración no soportado: $extension");
    }
    
    /**
     * Ejecuta múltiples sentencias SQL
     * 
     * @param string $sql Consulta SQL con múltiples sentencias
     */
    private function executeMultipleQueries($sql) {
        // Dividir el SQL en sentencias individuales
        $queries = $this->splitSqlQueries($sql);
        
        foreach ($queries as $query) {
            if (trim($query) !== '') {
                $this->db->query($query);
            }
        }
    }
    
    /**
     * Divide una cadena SQL en sentencias individuales
     * 
     * @param string $sql Consulta SQL con múltiples sentencias
     * @return array Array de sentencias SQL
     */
    private function splitSqlQueries($sql) {
        // Eliminar comentarios
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Dividir por punto y coma, pero ignorar los que están dentro de comillas
        $queries = [];
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = ($i > 0) ? $sql[$i - 1] : '';
            
            // Manejar comillas
            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (!$inQuote) {
                    $inQuote = true;
                    $quoteChar = $char;
                } else if ($char === $quoteChar) {
                    $inQuote = false;
                }
            }
            
            // Si encontramos un punto y coma fuera de comillas, es el final de una sentencia
            if ($char === ';' && !$inQuote) {
                $queries[] = $current . ';';
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        // Añadir la última sentencia si no termina con punto y coma
        if (trim($current) !== '') {
            $queries[] = $current;
        }
        
        return $queries;
    }
    
    /**
     * Ejecuta todas las migraciones pendientes
     * 
     * @return array Información sobre las migraciones ejecutadas
     */
    public function runMigrations() {
        // Crear la tabla de migraciones si no existe
        $this->createMigrationsTable();
        
        $results = [
            'executed' => [],
            'skipped' => [],
            'errors' => []
        ];
        
        // Obtener todos los archivos de migración
        $migrationFiles = array_merge(
            glob($this->migrationsDir . '/*.sql'),
            glob($this->migrationsDir . '/*.php')
        );
        sort($migrationFiles); // Ordenar por nombre para ejecutar en orden
        
        foreach ($migrationFiles as $migrationFile) {
            $migrationName = basename($migrationFile);
            
            // Verificar si la migración ya ha sido ejecutada
            if ($this->migrationExists($migrationName)) {
                $results['skipped'][] = $migrationName;
                continue;
            }
            
            // Cargar la migración
            try {
                $migration = $this->loadMigration($migrationFile);
                $sql = $migration['up'];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'migration' => $migrationName,
                    'error' => $e->getMessage()
                ];
                continue;
            }
            
            // Ejecutar la migración
            try {
                // Iniciar transacción
                $this->db->beginTransaction();
                
                // Ejecutar las consultas SQL
                $this->executeMultipleQueries($sql);
                
                // Registrar la migración como ejecutada
                $this->registerMigration($migrationName);
                
                // Confirmar la transacción
                $this->db->commit();
                
                $results['executed'][] = $migrationName;
            } catch (\Exception $e) {
                // Verificar si hay una transacción activa antes de hacer rollback
                try {
                    $this->db->rollback();
                } catch (\PDOException $pdoEx) {
                    // Ignorar el error si no hay transacción activa
                }
                
                $results['errors'][] = [
                    'migration' => $migrationName,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Revierte migraciones
     * 
     * @param int $steps Número de migraciones a revertir (0 para todas)
     * @return array Información sobre las migraciones revertidas
     */
    public function rollbackMigrations($steps = 1) {
        $results = [
            'reverted' => [],
            'errors' => []
        ];
        
        // Obtener las migraciones ejecutadas en orden inverso (más recientes primero)
        $query = "SELECT migration FROM migrations ORDER BY executed_at DESC, id DESC";
        $migrations = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN);
        
        // Si no hay migraciones, no hay nada que revertir
        if (empty($migrations)) {
            return $results;
        }
        
        // Limitar el número de migraciones a revertir si se especifica
        if ($steps > 0) {
            $migrations = array_slice($migrations, 0, $steps);
        }
        
        // Revertir cada migración
        foreach ($migrations as $migrationName) {
            $migrationFile = $this->migrationsDir . '/' . $migrationName;
            
            // Verificar si el archivo existe
            if (!file_exists($migrationFile)) {
                $results['errors'][] = [
                    'migration' => $migrationName,
                    'error' => 'Archivo de migración no encontrado'
                ];
                continue;
            }
            
            // Cargar la migración
            try {
                $migration = $this->loadMigration($migrationFile);
                $sql = $migration['down'];
                
                // Si no hay instrucciones para revertir, mostrar error
                if ($sql === null) {
                    $results['errors'][] = [
                        'migration' => $migrationName,
                        'error' => 'No hay instrucciones para revertir esta migración'
                    ];
                    continue;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'migration' => $migrationName,
                    'error' => $e->getMessage()
                ];
                continue;
            }
            
            // Ejecutar la reversión
            try {
                // Iniciar transacción
                $this->db->beginTransaction();
                
                // Ejecutar las consultas SQL para revertir
                $this->executeMultipleQueries($sql);
                
                // Eliminar la migración del registro
                $this->unregisterMigration($migrationName);
                
                // Confirmar la transacción
                $this->db->commit();
                
                $results['reverted'][] = $migrationName;
            } catch (\Exception $e) {
                // Verificar si hay una transacción activa antes de hacer rollback
                try {
                    $this->db->rollback();
                } catch (\PDOException $pdoEx) {
                    // Ignorar el error si no hay transacción activa
                }
                
                $results['errors'][] = [
                    'migration' => $migrationName,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Obtiene el listado de todas las migraciones ejecutadas
     * 
     * @return array Lista de migraciones ejecutadas
     */
    public function getMigrationHistory() {
        $query = "SELECT migration, executed_at FROM migrations ORDER BY executed_at";
        return $this->db->query($query)->fetchAll();
    }
} 