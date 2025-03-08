<?php
/**
 * Script para ejecutar todas las pruebas
 */

// Definir el entorno como testing
define('APP_ENV', 'testing');

// Cargar el autoloader
require_once __DIR__ . '/../autoload.php';

echo "=== Ejecutando todas las pruebas ===\n\n";

// Ejecutar las pruebas de la base de datos
if (file_exists(__DIR__ . '/DatabaseTest.php')) {
    echo "Ejecutando pruebas de Database...\n";
    include __DIR__ . '/DatabaseTest.php';
    echo "\n";
}

// Ejecutar las pruebas del gestor de migraciones
if (file_exists(__DIR__ . '/MigrationManagerTest.php')) {
    echo "Ejecutando pruebas de MigrationManager...\n";
    include __DIR__ . '/MigrationManagerTest.php';
    echo "\n";
}

// Ejecutar las pruebas de reversión de migraciones
if (file_exists(__DIR__ . '/MigrationRollbackTest.php')) {
    echo "Ejecutando pruebas de reversión de migraciones...\n";
    include __DIR__ . '/MigrationRollbackTest.php';
    echo "\n";
}

echo "=== Todas las pruebas completadas ===\n"; 