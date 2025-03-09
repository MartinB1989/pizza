<?php
/**
 * Prueba de conexión a la base de datos
 * 
 * Este script verifica la conexión a la base de datos y muestra información sobre la misma
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

use Back\Database\Database;

// Función para mostrar mensajes con formato
function printMessage($message, $type = 'info') {
    $colors = [
        'success' => "\033[0;32m", // Verde
        'error' => "\033[0;31m",   // Rojo
        'info' => "\033[0;34m",    // Azul
        'warning' => "\033[0;33m"  // Amarillo
    ];
    
    $reset = "\033[0m";
    
    // Si estamos en un entorno web, usar HTML
    if (php_sapi_name() !== 'cli') {
        $htmlColors = [
            'success' => 'color: green;',
            'error' => 'color: red;',
            'info' => 'color: blue;',
            'warning' => 'color: orange;'
        ];
        
        echo "<div style=\"{$htmlColors[$type]}\">{$message}</div>";
    } else {
        // Estamos en la línea de comandos
        echo $colors[$type] . $message . $reset . PHP_EOL;
    }
}

// Título
printMessage("=== PRUEBA DE CONEXIÓN A LA BASE DE DATOS ===", 'info');
echo PHP_EOL;

try {
    // Obtener instancia de la base de datos
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // Verificar la conexión
    printMessage("✓ Conexión establecida correctamente", 'success');
    
    // Mostrar información del servidor
    $serverInfo = $connection->getAttribute(PDO::ATTR_SERVER_VERSION);
    printMessage("Información del servidor: " . $serverInfo, 'info');
    
    // Mostrar información de la base de datos usando variables de entorno
    $dbName = env('DB_NAME', 'No configurado');
    $dbHost = env('DB_HOST', 'No configurado');
    $dbPort = env('DB_PORT', 'No configurado');
    
    printMessage("Base de datos: " . $dbName, 'info');
    printMessage("Host: " . $dbHost . ":" . $dbPort, 'info');
    
    // Verificar tablas existentes
    $query = "SHOW TABLES";
    $stmt = $connection->query($query);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    printMessage("Tablas encontradas: " . count($tables), 'info');
    
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            printMessage("- " . $table, 'info');
            
            // Mostrar estructura de la tabla
            $query = "DESCRIBE " . $table;
            $stmt = $connection->query($query);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "  Columnas:" . PHP_EOL;
            foreach ($columns as $column) {
                $type = $column['Type'];
                $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
                $key = $column['Key'] ? " ({$column['Key']})" : '';
                $default = $column['Default'] ? " DEFAULT '{$column['Default']}'" : '';
                
                printMessage("  - {$column['Field']}: {$type} {$null}{$key}{$default}", 'info');
            }
            
            echo PHP_EOL;
        }
    } else {
        printMessage("No se encontraron tablas en la base de datos", 'warning');
        printMessage("Ejecuta las migraciones con: php back/migrate.php", 'info');
    }
    
} catch (Exception $e) {
    printMessage("✗ Error de conexión: " . $e->getMessage(), 'error');
    
    // Verificar configuración
    printMessage("Verificando configuración...", 'info');
    
    // Verificar archivo .env
    if (!file_exists(__DIR__ . '/../../.env')) {
        printMessage("✗ No se encontró el archivo .env", 'error');
    } else {
        printMessage("✓ Archivo .env encontrado", 'success');
    }
    
    // Mostrar configuración de la base de datos desde variables de entorno
    printMessage("Configuración de la base de datos:", 'info');
    printMessage("- Host: " . env('DB_HOST', 'No configurado'), 'info');
    printMessage("- Base de datos: " . env('DB_NAME', 'No configurado'), 'info');
    printMessage("- Usuario: " . env('DB_USER', 'No configurado'), 'info');
    printMessage("- Puerto: " . env('DB_PORT', 'No configurado'), 'info');
    
    // Sugerencias
    printMessage("Sugerencias:", 'warning');
    printMessage("1. Verifica que el servidor MySQL esté en ejecución", 'info');
    printMessage("2. Verifica que las credenciales en el archivo .env sean correctas", 'info');
    printMessage("3. Verifica que la base de datos exista", 'info');
    printMessage("4. Verifica que el usuario tenga permisos para acceder a la base de datos", 'info');
}

echo PHP_EOL;
printMessage("=== FIN DE LA PRUEBA ===", 'info'); 