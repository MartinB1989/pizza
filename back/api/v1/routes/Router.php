<?php
/**
 * Enrutador para la API REST
 * 
 * Este archivo maneja las rutas de la API y las dirige a los controladores correspondientes
 */

namespace Back\Api\Routes;

use Back\Api\Middlewares\AuthMiddleware;

class Router {
    // Rutas registradas
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Registrar rutas
        $this->registerRoutes();
    }
    
    /**
     * Registra todas las rutas de la API
     */
    private function registerRoutes() {
        // Rutas públicas
        $this->addRoute('GET', 'products', 'ProductController@getAll', true);
        $this->addRoute('GET', 'products/{id}', 'ProductController@getOne', true);
        $this->addRoute('GET', 'categories', 'CategoryController@getAll', true);
        
        // Rutas de autenticación (públicas)
        $this->addRoute('POST', 'auth/login', 'AuthController@login', true);
        $this->addRoute('POST', 'auth/register', 'AuthController@register', true);
        
        // Rutas protegidas (requieren autenticación)
        $this->addRoute('POST', 'products', 'ProductController@create', false);
        $this->addRoute('PUT', 'products/{id}', 'ProductController@update', false);
        $this->addRoute('DELETE', 'products/{id}', 'ProductController@delete', false);
        
        // Rutas de usuario (protegidas)
        $this->addRoute('GET', 'user/profile', 'UserController@getProfile', false);
        $this->addRoute('PUT', 'user/profile', 'UserController@updateProfile', false);
    }
    
    /**
     * Añade una ruta al enrutador
     * 
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $route Ruta de la API
     * @param string $handler Controlador y método (formato: ControllerName@methodName)
     * @param bool $public Si la ruta es pública o requiere autenticación
     */
    private function addRoute($method, $route, $handler, $public = false) {
        $this->routes[$method][$route] = [
            'handler' => $handler,
            'public' => $public
        ];
    }
    
    /**
     * Despacha la solicitud a la ruta correspondiente
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta solicitada
     */
    public function dispatch($method, $route) {
        // Verificar si el método es válido
        if (!isset($this->routes[$method])) {
            $this->sendResponse(405, ['error' => true, 'message' => 'Método no permitido']);
            return;
        }
        
        // Buscar coincidencia exacta
        if (isset($this->routes[$method][$route])) {
            $this->handleRequest($this->routes[$method][$route], []);
            return;
        }
        
        // Buscar rutas con parámetros
        foreach ($this->routes[$method] as $pattern => $routeData) {
            $params = $this->matchRoute($pattern, $route);
            if ($params !== false) {
                $this->handleRequest($routeData, $params);
                return;
            }
        }
        
        // Ruta no encontrada
        $this->sendResponse(404, ['error' => true, 'message' => 'Ruta no encontrada']);
    }
    
    /**
     * Compara una ruta con un patrón y extrae los parámetros
     * 
     * @param string $pattern Patrón de ruta (ej: 'products/{id}')
     * @param string $route Ruta solicitada (ej: 'products/123')
     * @return array|false Parámetros extraídos o false si no hay coincidencia
     */
    private function matchRoute($pattern, $route) {
        // Si no hay parámetros en el patrón, comparar directamente
        if (strpos($pattern, '{') === false) {
            return ($pattern === $route) ? [] : false;
        }
        
        // Convertir el patrón a expresión regular
        $patternParts = explode('/', $pattern);
        $routeParts = explode('/', $route);
        
        // Si tienen diferente número de partes, no hay coincidencia
        if (count($patternParts) !== count($routeParts)) {
            return false;
        }
        
        $params = [];
        
        // Comparar cada parte y extraer parámetros
        foreach ($patternParts as $index => $part) {
            if (preg_match('/^{([a-zA-Z0-9_]+)}$/', $part, $matches)) {
                // Es un parámetro, extraer su valor
                $paramName = $matches[1];
                $params[$paramName] = $routeParts[$index];
            } elseif ($part !== $routeParts[$index]) {
                // No es un parámetro y no coincide
                return false;
            }
        }
        
        return $params;
    }
    
    /**
     * Maneja la solicitud a una ruta
     * 
     * @param array $routeData Datos de la ruta
     * @param array $params Parámetros de la ruta
     */
    private function handleRequest($routeData, $params) {
        // Verificar si la ruta requiere autenticación
        if (!$routeData['public']) {
            $auth = new AuthMiddleware();
            $userData = $auth->verifyToken();
            // Añadir datos del usuario a los parámetros
            $params['user'] = $userData;
        }
        
        // Extraer controlador y método
        list($controllerName, $methodName) = explode('@', $routeData['handler']);
        
        // Cargar el controlador
        $controllerFile = __DIR__ . "/../controllers/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            $this->sendResponse(500, ['error' => true, 'message' => "Controlador no encontrado: {$controllerName}"]);
            return;
        }
        
        require_once $controllerFile;
        
        // Espacio de nombres completo para el controlador
        $controllerClass = "\\Back\\Api\\Controllers\\{$controllerName}";
        
        // Verificar si la clase existe
        if (!class_exists($controllerClass)) {
            $this->sendResponse(500, ['error' => true, 'message' => "Clase de controlador no encontrada: {$controllerClass}"]);
            return;
        }
        
        // Crear instancia del controlador
        $controller = new $controllerClass();
        
        // Verificar si el método existe
        if (!method_exists($controller, $methodName)) {
            $this->sendResponse(500, ['error' => true, 'message' => "Método no encontrado: {$methodName}"]);
            return;
        }
        
        // Ejecutar el método del controlador
        $controller->$methodName($params);
    }
    
    /**
     * Envía una respuesta JSON
     * 
     * @param int $statusCode Código de estado HTTP
     * @param array $data Datos a enviar
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
} 