<?php
/**
 * Test para la clase Database
 * 
 * Este archivo contiene pruebas para verificar la conexión a la base de datos
 */

// Cargar el autoloader
require_once __DIR__ . '/../autoload.php';

// Establecer el entorno de pruebas
define('APP_ENV', 'testing');

// Importar la clase Database
use App\Database\Database;

// Función simple para ejecutar pruebas
function test($name, $callback) {
    echo "Ejecutando prueba: $name... ";
    try {
        $callback();
        echo "✓ PASÓ\n";
    } catch (Exception $e) {
        echo "✗ FALLÓ: " . $e->getMessage() . "\n";
    }
}

// Prueba 1: Verificar que se puede obtener una instancia de Database
test('Obtener instancia de Database', function() {
    $db = Database::getInstance();
    if (!($db instanceof Database)) {
        throw new Exception('No se pudo obtener una instancia de Database');
    }
});

// Prueba 2: Verificar que se puede obtener una conexión PDO
test('Obtener conexión PDO', function() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if (!($conn instanceof PDO)) {
        throw new Exception('No se pudo obtener una conexión PDO');
    }
});

// Prueba 3: Verificar que se puede ejecutar una consulta simple
test('Ejecutar consulta simple', function() {
    $db = Database::getInstance();
    $result = $db->query('SELECT 1 as test');
    $row = $result->fetch();
    if ($row['test'] != 1) {
        throw new Exception('La consulta no devolvió el resultado esperado');
    }
});

// Prueba 4: Verificar que se manejan correctamente los errores de consulta
test('Manejar errores de consulta', function() {
    $db = Database::getInstance();
    try {
        $db->query('SELECT * FROM tabla_que_no_existe');
        throw new Exception('No se lanzó una excepción para una consulta inválida');
    } catch (Exception $e) {
        // Se espera que se lance una excepción
        if ($e->getMessage() !== 'Error al ejecutar la consulta') {
            throw $e;
        }
    }
});

// Prueba 5: Verificar que se pueden usar transacciones
test('Usar transacciones', function() {
    $db = Database::getInstance();
    $result = $db->beginTransaction();
    if (!$result) {
        throw new Exception('No se pudo iniciar una transacción');
    }
    $result = $db->rollback();
    if (!$result) {
        throw new Exception('No se pudo revertir una transacción');
    }
});

echo "\nPruebas completadas.\n"; 