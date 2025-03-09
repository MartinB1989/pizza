<?php
/**
 * Punto de entrada principal para la API REST
 * 
 * Este archivo maneja todas las solicitudes a la API y las dirige a los controladores correspondientes
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

// Incluir archivos necesarios
require_once __DIR__ . '/v1/routes/Router.php';
require_once __DIR__ . '/v1/middlewares/AuthMiddleware.php';

// Configuración de cabeceras para CORS
header("Access-Control-Allow-Origin: " . env('CORS_ALLOW_ORIGIN', '*'));
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: " . env('CORS_ALLOW_METHODS', 'GET, POST, PUT, DELETE, OPTIONS'));
header("Access-Control-Allow-Headers: " . env('CORS_ALLOW_HEADERS', 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'));

// Manejo de preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Obtener la URI solicitada
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$basePath = '/api/';

// Extraer la ruta relativa a la API
$position = strpos($requestUri, $basePath);
if ($position !== false) {
    $route = substr($requestUri, $position + strlen($basePath));
} else {
    $route = $requestUri;
}

// Eliminar parámetros de consulta si existen
if (($queryPosition = strpos($route, '?')) !== false) {
    $route = substr($route, 0, $queryPosition);
}

// Eliminar barras diagonales al inicio y final
$route = trim($route, '/');

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Inicializar el enrutador y procesar la solicitud
$router = new \Back\Api\Routes\Router();
$router->dispatch($method, $route); 