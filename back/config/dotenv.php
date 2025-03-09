<?php
/**
 * Cargador de variables de entorno
 * 
 * Este archivo carga las variables de entorno desde un archivo .env
 * y las hace disponibles a través de getenv(), $_ENV y $_SERVER
 */

/**
 * Carga las variables de entorno desde un archivo .env
 * 
 * @param string $path Ruta al archivo .env (opcional)
 * @return bool True si el archivo fue cargado correctamente
 */
function loadEnv($path = null) {
    // Si no se especifica una ruta, usar la ruta por defecto
    if ($path === null) {
        $path = __DIR__ . '/../../.env';
    }
    
    // Verificar si el archivo existe
    if (!file_exists($path)) {
        error_log("Archivo .env no encontrado en: $path");
        return false;
    }
    
    // Leer el archivo línea por línea
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Dividir la línea en nombre y valor
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Eliminar comillas si existen
            if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                $value = substr($value, 1, -1);
            }
            
            // Establecer la variable de entorno
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    
    return true;
}

// Cargar variables de entorno automáticamente
loadEnv();

/**
 * Obtiene el valor de una variable de entorno con un valor por defecto
 * 
 * @param string $key Nombre de la variable
 * @param mixed $default Valor por defecto si la variable no existe
 * @return mixed Valor de la variable o el valor por defecto
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Convertir valores especiales
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }
    
    return $value;
} 