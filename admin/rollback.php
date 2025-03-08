<?php
/**
 * Script para revertir migraciones
 * 
 * Este script revierte migraciones previamente aplicadas
 * 
 * Uso:
 * php rollback.php [número de migraciones a revertir]
 */

// Cargar el autoloader
require_once __DIR__ . '/autoload.php';

// Definir el entorno si no está definido
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

use App\Database\MigrationManager;

// Obtener el número de migraciones a revertir
$steps = 1; // Por defecto, revertir solo la última migración
if (isset($argv[1]) && is_numeric($argv[1])) {
    $steps = (int)$argv[1];
}

// Si se especifica 0, revertir todas las migraciones
if ($steps === 0) {
    echo "¡ADVERTENCIA! Estás a punto de revertir TODAS las migraciones.\n";
    echo "Esto eliminará todas las tablas y datos de la base de datos.\n";
    echo "¿Estás seguro? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    if (strtolower($line) !== 's') {
        echo "Operación cancelada.\n";
        exit;
    }
}

// Crear una instancia del gestor de migraciones
$migrationManager = new MigrationManager();

// Revertir las migraciones
$results = $migrationManager->rollbackMigrations($steps);

// Mostrar resultados
echo "=== Resultados de la reversión de migraciones ===\n\n";

if (!empty($results['reverted'])) {
    echo "Migraciones revertidas:\n";
    foreach ($results['reverted'] as $migration) {
        echo "✓ " . $migration . "\n";
    }
    echo "\n";
} else {
    echo "No se revirtió ninguna migración.\n\n";
}

if (!empty($results['errors'])) {
    echo "Errores al revertir migraciones:\n";
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