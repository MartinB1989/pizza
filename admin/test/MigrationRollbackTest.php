<?php
/**
 * Test para la reversión de migraciones
 * 
 * Este archivo contiene pruebas para verificar el funcionamiento del sistema de reversión de migraciones
 */

require_once __DIR__ . '/../autoload.php';

// Establecer el entorno de pruebas si no está definido
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}

use App\Database\Database;
use App\Database\MigrationManager;

// Función para ejecutar tests
function rollbackTest($name, $callback) {
    echo "Test: $name... ";
    try {
        $callback();
        echo "✓ PASÓ\n";
    } catch (Exception $e) {
        echo "✗ FALLÓ: " . $e->getMessage() . "\n";
    }
}

// Función para crear una migración de prueba
function createTestMigration() {
    $migrationDir = __DIR__ . '/../src/database/migrations';
    $testMigrationFile = $migrationDir . '/999_test_reversible.php';
    
    // Crear una migración reversible
    $content = '<?php
/**
 * Migración de prueba para reversión
 */
return [
    "up" => "CREATE TABLE IF NOT EXISTS test_reversible (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO test_reversible (name) VALUES (\'Test 1\'), (\'Test 2\');",
    "down" => "DROP TABLE IF EXISTS test_reversible;"
];';
    
    file_put_contents($testMigrationFile, $content);
    
    return $testMigrationFile;
}

// Función para limpiar después de las pruebas
function rollbackCleanup($testMigrationFile) {
    if (file_exists($testMigrationFile)) {
        unlink($testMigrationFile);
    }
    
    // Eliminar la tabla de prueba si existe
    $db = Database::getInstance();
    try {
        $db->query("DROP TABLE IF EXISTS test_reversible");
    } catch (Exception $e) {
        // Ignorar errores al eliminar
    }
}

echo "=== Iniciando pruebas de reversión de migraciones ===\n\n";

// Test 1: Verificar que se puede aplicar y revertir una migración
rollbackTest('Aplicar y revertir una migración', function() {
    // Crear una migración de prueba
    $testMigrationFile = createTestMigration();
    
    try {
        $db = Database::getInstance();
        $manager = new MigrationManager();
        
        // Aplicar la migración
        $manager->runMigrations();
        
        // Verificar que la tabla existe
        $result = $db->query("SHOW TABLES LIKE 'test_reversible'")->rowCount();
        if ($result === 0) {
            throw new Exception("La tabla test_reversible no se creó correctamente");
        }
        
        // Verificar que los datos se insertaron
        $count = $db->query("SELECT COUNT(*) as count FROM test_reversible")->fetch()['count'];
        if ($count != 2) {
            throw new Exception("No se insertaron los datos correctamente");
        }
        
        // Revertir la migración
        $manager->rollbackMigrations(1);
        
        // Verificar que la tabla ya no existe
        $result = $db->query("SHOW TABLES LIKE 'test_reversible'")->rowCount();
        if ($result > 0) {
            throw new Exception("La tabla test_reversible no se eliminó correctamente");
        }
        
    } finally {
        // Limpiar después de la prueba
        rollbackCleanup($testMigrationFile);
    }
});

// Test 2: Verificar que se puede revertir un número específico de migraciones
rollbackTest('Revertir un número específico de migraciones', function() {
    // Este test es más conceptual, ya que necesitaríamos múltiples migraciones de prueba
    // En un entorno real, se probaría con migraciones existentes
    
    $manager = new MigrationManager();
    
    // Obtener el número actual de migraciones
    $history = $manager->getMigrationHistory();
    $initialCount = count($history);
    
    // Crear una migración de prueba
    $testMigrationFile = createTestMigration();
    
    try {
        // Aplicar la migración
        $manager->runMigrations();
        
        // Verificar que se añadió una migración
        $history = $manager->getMigrationHistory();
        $newCount = count($history);
        
        if ($newCount != $initialCount + 1) {
            throw new Exception("No se registró correctamente la migración");
        }
        
        // Revertir la migración
        $results = $manager->rollbackMigrations(1);
        
        // Verificar que se revirtió una migración
        if (count($results['reverted']) != 1) {
            throw new Exception("No se revirtió correctamente la migración");
        }
        
        // Verificar que volvimos al número inicial de migraciones
        $history = $manager->getMigrationHistory();
        $finalCount = count($history);
        
        if ($finalCount != $initialCount) {
            throw new Exception("No se actualizó correctamente el historial de migraciones");
        }
        
    } finally {
        // Limpiar después de la prueba
        rollbackCleanup($testMigrationFile);
    }
});

echo "\n=== Pruebas de reversión completadas ===\n"; 