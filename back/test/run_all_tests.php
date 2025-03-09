<?php
/**
 * Ejecuta todas las pruebas
 * 
 * Este script ejecuta todas las pruebas disponibles en la carpeta test
 */

echo "=== EJECUTANDO TODAS LAS PRUEBAS ===" . PHP_EOL . PHP_EOL;

// Definir las pruebas a ejecutar
$tests = [
    'connection_test.php' => 'Prueba de conexión a la base de datos',
    'database_connection_test.php' => 'Prueba detallada de la base de datos',
    'migration_test.php' => 'Prueba de migraciones',
    'run_api_tests.php' => 'Pruebas de la API (incluye crear y eliminar datos de prueba)'
];

// Ejecutar cada prueba
foreach ($tests as $file => $description) {
    echo "=== EJECUTANDO: $description ===" . PHP_EOL;
    echo "Archivo: $file" . PHP_EOL . PHP_EOL;
    
    // Ejecutar la prueba
    if ($file === 'run_api_tests.php') {
        // Para las pruebas de API, ejecutamos el script directamente
        system('php ' . __DIR__ . '/' . $file);
    } else {
        // Para las demás pruebas, incluimos el archivo
        include __DIR__ . '/' . $file;
    }
    
    echo PHP_EOL . "=== FIN DE: $description ===" . PHP_EOL . PHP_EOL;
    echo "----------------------------------------------" . PHP_EOL . PHP_EOL;
    
    // Pausa entre pruebas
    if ($file !== array_key_last($tests)) {
        echo "Presiona Enter para continuar con la siguiente prueba...";
        fgets(STDIN);
        echo PHP_EOL;
    }
}

echo "=== TODAS LAS PRUEBAS COMPLETADAS ===" . PHP_EOL; 