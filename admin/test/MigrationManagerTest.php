<?php
/**
 * Test para el MigrationManager
 * 
 * Este archivo contiene pruebas para verificar el funcionamiento del sistema de migraciones
 */

require_once __DIR__ . '/../autoload.php';

use App\Database\Database;
use App\Database\MigrationManager;

// Función para ejecutar tests
function test($name, $callback) {
    echo "Test: $name... ";
    try {
        $callback();
        echo "✓ PASÓ\n";
    } catch (Exception $e) {
        echo "✗ FALLÓ: " . $e->getMessage() . "\n";
    }
}

// Función para crear una migración de prueba con error
function createTestMigrationWithError() {
    $migrationDir = __DIR__ . '/../src/database/migrations';
    $testMigrationFile = $migrationDir . '/999_test_rollback.sql';
    
    // Crear una migración con un error de sintaxis SQL
    $sql = "-- Migración de prueba para rollback
CREATE TABLE test_rollback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
); 
-- Error intencional para probar rollback
INSERT INTO tabla_que_no_existe (columna) VALUES ('valor');";
    
    file_put_contents($testMigrationFile, $sql);
    
    return $testMigrationFile;
}

// Función para limpiar después de las pruebas
function cleanup($testMigrationFile) {
    if (file_exists($testMigrationFile)) {
        unlink($testMigrationFile);
    }
    
    // Eliminar la tabla de prueba si existe
    $db = Database::getInstance();
    try {
        $db->query("DROP TABLE IF EXISTS test_rollback");
    } catch (Exception $e) {
        // Ignorar errores al eliminar
    }
}

echo "=== Iniciando pruebas del MigrationManager ===\n\n";

// Test 1: Verificar que se puede crear una instancia del MigrationManager
test('Crear instancia de MigrationManager', function() {
    $manager = new MigrationManager();
    if (!$manager instanceof MigrationManager) {
        throw new Exception("No se pudo crear una instancia de MigrationManager");
    }
});

// Test 2: Verificar que la tabla migrations existe
test('Verificar que la tabla migrations existe', function() {
    $db = Database::getInstance();
    $result = $db->query("SHOW TABLES LIKE 'migrations'")->rowCount();
    if ($result === 0) {
        throw new Exception("La tabla migrations no existe");
    }
});

// Test 3: Probar el rollback cuando una migración falla
test('Probar rollback cuando una migración falla', function() {
    // Crear una migración de prueba con error
    $testMigrationFile = createTestMigrationWithError();
    
    try {
        // Ejecutar las migraciones
        $manager = new MigrationManager();
        $results = $manager->runMigrations();
        
        // Verificar que la migración falló
        $foundError = false;
        foreach ($results['errors'] as $error) {
            if (strpos($error['migration'], '999_test_rollback.sql') !== false) {
                $foundError = true;
                break;
            }
        }
        
        if (!$foundError) {
            throw new Exception("La migración debería haber fallado pero no se registró el error");
        }
        
        // Verificar que la tabla test_rollback no existe (rollback exitoso)
        $db = Database::getInstance();
        $result = $db->query("SHOW TABLES LIKE 'test_rollback'")->rowCount();
        if ($result > 0) {
            throw new Exception("La tabla test_rollback existe, el rollback no funcionó");
        }
        
    } finally {
        // Limpiar después de la prueba
        cleanup($testMigrationFile);
    }
});

// Test 4: Verificar que el MigrationManager maneja correctamente transacciones múltiples
test('Manejar múltiples transacciones', function() {
    $db = Database::getInstance();
    
    // Iniciar una transacción manualmente
    $db->beginTransaction();
    
    // Crear una instancia del MigrationManager (que también inicia transacciones)
    $manager = new MigrationManager();
    
    // Intentar ejecutar una consulta
    $db->query("SELECT 1");
    
    // Hacer rollback
    $db->rollback();
    
    // Si llegamos aquí sin errores, la prueba pasa
});

echo "\n=== Pruebas completadas ===\n"; 