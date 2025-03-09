<?php
/**
 * Test de migraciones
 * 
 * Este script verifica que las migraciones estén correctamente configuradas
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

use Back\Migrations\MigrationManager;

echo "=== TEST DE MIGRACIONES ===" . PHP_EOL;

try {
    // Crear instancia del gestor de migraciones
    $migrationManager = new MigrationManager();
    
    echo "✅ Gestor de migraciones inicializado correctamente" . PHP_EOL;
    
    // Verificar archivos de migración
    $migrationsDir = __DIR__ . '/../migrations/files';
    
    // Verificar que el directorio existe
    if (!is_dir($migrationsDir)) {
        echo "❌ El directorio de migraciones no existe: $migrationsDir" . PHP_EOL;
        exit(1);
    }
    
    $migrationFiles = array_merge(
        glob($migrationsDir . '/*.sql') ?: [],
        glob($migrationsDir . '/*.php') ?: []
    );
    sort($migrationFiles);
    
    echo "Archivos de migración encontrados: " . count($migrationFiles) . PHP_EOL;
    
    if (count($migrationFiles) === 0) {
        echo "❌ No se encontraron archivos de migración" . PHP_EOL;
    } else {
        foreach ($migrationFiles as $file) {
            echo "- " . basename($file) . PHP_EOL;
            
            // Verificar que el archivo sea legible
            if (!is_readable($file)) {
                echo "  ❌ El archivo no es legible" . PHP_EOL;
                continue;
            }
            
            // Verificar que el archivo tenga el formato correcto
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension === 'php') {
                try {
                    $migration = require $file;
                    if (!is_array($migration) || !isset($migration['up']) || !isset($migration['down'])) {
                        echo "  ❌ El archivo no tiene el formato correcto (debe tener 'up' y 'down')" . PHP_EOL;
                    } else {
                        echo "  ✅ Formato correcto" . PHP_EOL;
                    }
                } catch (Exception $e) {
                    echo "  ❌ Error al cargar el archivo: " . $e->getMessage() . PHP_EOL;
                }
            } else if ($extension === 'sql') {
                echo "  ✅ Archivo SQL" . PHP_EOL;
            } else {
                echo "  ❌ Extensión no soportada: " . $extension . PHP_EOL;
            }
        }
    }
    
    // Obtener historial de migraciones
    try {
        $history = $migrationManager->getMigrationHistory();
        
        echo PHP_EOL . "Historial de migraciones ejecutadas: " . count($history) . PHP_EOL;
        
        if (count($history) > 0) {
            foreach ($history as $migration) {
                echo "- " . $migration['migration'] . " (" . $migration['executed_at'] . ")" . PHP_EOL;
            }
        } else {
            echo "No hay migraciones ejecutadas. Ejecuta las migraciones con: php back/migrate.php" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "❌ Error al obtener el historial de migraciones: " . $e->getMessage() . PHP_EOL;
        echo "Es posible que la tabla 'migrations' no exista. Ejecuta las migraciones primero." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DEL TEST ===" . PHP_EOL; 