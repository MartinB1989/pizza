<?php
/**
 * Script para ejecutar todas las pruebas de la API
 * 
 * Este script ejecuta todas las pruebas de la API en secuencia
 */

echo "=== EJECUTANDO PRUEBAS DE LA API ===" . PHP_EOL . PHP_EOL;

// 1. Crear datos de prueba
echo "Paso 1: Crear datos de prueba" . PHP_EOL;
include __DIR__ . '/create_test_data.php';

echo PHP_EOL . "Presiona Enter para continuar...";
fgets(STDIN);
echo PHP_EOL;

// 2. Ejecutar pruebas de la API
echo "Paso 2: Ejecutar pruebas de la API" . PHP_EOL;
include __DIR__ . '/api_test.php';

echo PHP_EOL . "Presiona Enter para continuar...";
fgets(STDIN);
echo PHP_EOL;

// 3. Eliminar datos de prueba
echo "Paso 3: Eliminar datos de prueba" . PHP_EOL;
include __DIR__ . '/delete_test_data.php';

echo PHP_EOL . "=== PRUEBAS COMPLETADAS ===" . PHP_EOL; 