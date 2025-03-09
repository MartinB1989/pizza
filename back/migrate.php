<?php
/**
 * Script para ejecutar migraciones
 * 
 * Este script ejecuta todas las migraciones pendientes
 */

// Cargar autoloader
require_once __DIR__ . '/autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/config/dotenv.php';

use Back\Migrations\MigrationManager;

// Crear instancia del gestor de migraciones
$migrationManager = new MigrationManager();

// Ejecutar migraciones
$results = $migrationManager->runMigrations();

// Mostrar resultados
echo "=== Migraciones ejecutadas ===\n";
if (empty($results['executed'])) {
    echo "No se ejecutaron migraciones.\n";
} else {
    foreach ($results['executed'] as $migration) {
        echo "✓ $migration\n";
    }
}

echo "\n=== Migraciones omitidas (ya ejecutadas) ===\n";
if (empty($results['skipped'])) {
    echo "No se omitieron migraciones.\n";
} else {
    foreach ($results['skipped'] as $migration) {
        echo "- $migration\n";
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