<?php
/**
 * Autoloader para las clases del proyecto
 * 
 * Este archivo se encarga de cargar automáticamente las clases del proyecto
 * siguiendo la convención PSR-4
 */

spl_autoload_register(function ($class) {
    // Prefijo del namespace del proyecto
    $prefix = 'App\\';
    
    // Directorio base para el namespace
    $base_dir = __DIR__ . '/src/';
    
    // Verificar si la clase usa el prefijo
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, muévete al siguiente autoloader registrado
        return;
    }
    
    // Obtener el nombre relativo de la clase
    $relative_class = substr($class, $len);
    
    // Reemplazar el namespace por directorios, \ por / y añadir .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si el archivo existe, cargarlo
    if (file_exists($file)) {
        require $file;
    }
}); 