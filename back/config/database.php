<?php
/**
 * Configuración de la base de datos
 * 
 * Este archivo contiene la configuración para la conexión a MySQL
 * utilizando las variables de entorno definidas en el archivo .env
 */

// Cargar el archivo de variables de entorno si no se ha cargado
if (!function_exists('env')) {
    require_once __DIR__ . '/dotenv.php';
}

// Configuración de la base de datos desde variables de entorno
return [
    'host' => env('DB_HOST', 'localhost'),
    'name' => env('DB_NAME', 'pizzeria'),
    'user' => env('DB_USER', 'root'),
    'pass' => env('DB_PASS', ''),
    'port' => env('DB_PORT', 3306),
    'charset' => env('DB_CHARSET', 'utf8mb4')
]; 