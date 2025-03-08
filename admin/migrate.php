<?php
/**
 * Script para ejecutar migraciones
 * 
 * Este script ejecuta todas las migraciones pendientes en la base de datos
 */

// Cargar el autoloader
require_once __DIR__ . '/autoload.php';

// Definir el entorno si no está definido
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

use App\Database\MigrationManager;

// Crear una instancia del gestor de migraciones
$migrationManager = new MigrationManager();

// Ejecutar las migraciones
$results = $migrationManager->runMigrations();

// Mostrar resultados
echo "=== Resultados de las migraciones ===\n\n";

if (!empty($results['executed'])) {
    echo "Migraciones ejecutadas:\n";
    foreach ($results['executed'] as $migration) {
        echo "✓ " . $migration . "\n";
    }
    echo "\n";
} else {
    echo "No se ejecutaron nuevas migraciones.\n\n";
}

if (!empty($results['skipped'])) {
    echo "Migraciones omitidas (ya ejecutadas):\n";
    foreach ($results['skipped'] as $migration) {
        echo "- " . $migration . "\n";
    }
    echo "\n";
}

if (!empty($results['errors'])) {
    echo "Errores en migraciones:\n";
    foreach ($results['errors'] as $error) {
        echo "✗ " . $error['migration'] . ": " . $error['error'] . "\n";
    }
    echo "\n";
}

// Mostrar historial de migraciones
$history = $migrationManager->getMigrationHistory();

echo "=== Historial de migraciones ===\n\n";
if (!empty($history)) {
    echo "ID\tMigración\t\t\tFecha de ejecución\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($history as $record) {
        echo $record['migration'] . "\t" . $record['executed_at'] . "\n";
    }
} else {
    echo "No hay migraciones ejecutadas.\n";
} 