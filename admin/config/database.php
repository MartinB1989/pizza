<?php
/**
 * Configuración de la base de datos
 * 
 * Este archivo contiene las constantes de configuración para la conexión a MySQL
 */

// Entorno de la aplicación (development, testing, production)
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// Configuración para el entorno de desarrollo
if (APP_ENV === 'development') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'pizzeria');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
}

// Configuración para el entorno de pruebas
if (APP_ENV === 'testing') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'pizzeria');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
}

// Configuración para el entorno de producción
if (APP_ENV === 'production') {
    define('DB_HOST', 'servidor_produccion');
    define('DB_NAME', 'nombre_db_prod');
    define('DB_USER', 'usuario_prod');
    define('DB_PASS', 'contraseña_prod');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
} 