<?php
/**
 * Script para revertir migraciones
 * 
 * Este script revierte migraciones ejecutadas
 */

// Cargar autoloader
require_once __DIR__ . '/autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/config/dotenv.php';

use Back\Migrations\MigrationManager;

// Obtener el número de pasos a revertir
$steps = isset($argv[1]) ? (int)$argv[1] : 1;

// Crear instancia del gestor de migraciones
$migrationManager = new MigrationManager();

// Mostrar mensaje de confirmación
echo "Se revertirán " . ($steps > 0 ? $steps : "TODAS") . " migraciones.\n";
echo "¿Está seguro? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 's') {
    echo "Operación cancelada.\n";
    exit;
}

// Revertir migraciones
$results = $migrationManager->rollbackMigrations($steps);

// Mostrar resultados
echo "\n=== Migraciones revertidas ===\n";
if (empty($results['reverted'])) {
    echo "No se revirtieron migraciones.\n";
} else {
    foreach ($results['reverted'] as $migration) {
        echo "✓ $migration\n";
    }
}

echo "\n=== Errores ===\n";
if (empty($results['errors'])) {
    echo "No se produjeron errores.\n";
} else {
    foreach ($results['errors'] as $error) {
        echo "✗ {$error['migration']}: {$error['error']}\n";
    }
}

// Mostrar historial de migraciones
$history = $migrationManager->getMigrationHistory();

echo "\n=== Historial de migraciones ===\n";
if (empty($history)) {
    echo "No hay migraciones ejecutadas.\n";
} else {
    foreach ($history as $migration) {
        echo "- {$migration['migration']} ({$migration['executed_at']})\n";
    }
}

echo "\n"; 