<?php
/**
 * Autoloader para cargar clases automáticamente
 * 
 * Este archivo registra un autoloader que carga las clases automáticamente
 * según su espacio de nombres y ubicación en el sistema de archivos
 */

spl_autoload_register(function ($class) {
    // Prefijo del espacio de nombres del proyecto
    $prefix = 'Back\\';
    
    // Directorio base para el espacio de nombres
    $baseDir = __DIR__ . '/';
    
    // Verificar si la clase usa el prefijo
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, pasar al siguiente autoloader registrado
        return;
    }
    
    // Obtener el nombre relativo de la clase
    $relativeClass = substr($class, $len);
    
    // Reemplazar el separador de espacio de nombres por el separador de directorios
    // y añadir .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Si el archivo existe, cargarlo
    if (file_exists($file)) {
        require $file;
    }
}); 