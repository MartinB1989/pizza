<?php
/**
 * Test simple de conexión a la base de datos
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

use Back\Database\Database;

echo "=== TEST DE CONEXIÓN A LA BASE DE DATOS ===" . PHP_EOL;

try {
    // Obtener instancia de la base de datos
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "✅ Conexión exitosa a la base de datos" . PHP_EOL;
    
    // Mostrar información del servidor
    echo "Servidor MySQL: " . $connection->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
    
    // Mostrar información de la base de datos
    $config = require_once __DIR__ . '/../config/database.php';
    echo "Base de datos: " . $config['name'] . PHP_EOL;
    
    // Ejecutar una consulta simple
    $stmt = $connection->query("SELECT 1 AS test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['test'] === '1') {
        echo "✅ Consulta de prueba ejecutada correctamente" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . PHP_EOL;
}

echo "=== FIN DEL TEST ===" . PHP_EOL; 