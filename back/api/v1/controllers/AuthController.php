<?php
/**
 * Controlador de autenticación
 * 
 * Este controlador maneja el registro e inicio de sesión de usuarios
 */

namespace Back\Api\Controllers;

use Back\Database\Database;
use Back\Api\Middlewares\AuthMiddleware;

class AuthController {
    private $db;
    private $auth;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener instancia de la base de datos
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
    }
    
    /**
     * Inicia sesión y genera un token JWT
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function login($params) {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->username) || !isset($data->password)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Buscar usuario
            $query = "SELECT id, username, password, role FROM users WHERE username = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->username);
            $stmt->execute();
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($data->password, $user['password'])) {
                // Generar token JWT
                $token = $this->auth->generateToken($user['id'], $user['username'], $user['role']);
                
                $this->sendResponse(200, [
                    'message' => "Inicio de sesión exitoso",
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                $this->sendError(401, "Credenciales inválidas");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al iniciar sesión: " . $e->getMessage());
        }
    }
    
    /**
     * Registra un nuevo usuario
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function register($params) {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->username) || !isset($data->password) || !isset($data->email)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Verificar si el usuario ya existe
            $query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->username);
            $stmt->bindParam(2, $data->email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendError(409, "El usuario o email ya existe");
            }
            
            // Hashear contraseña
            $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
            
            // Insertar usuario
            $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->username);
            $stmt->bindParam(2, $data->email);
            $stmt->bindParam(3, $passwordHash);
            
            if ($stmt->execute()) {
                $userId = $this->db->lastInsertId();
                
                // Generar token JWT
                $token = $this->auth->generateToken($userId, $data->username, 'user');
                
                $this->sendResponse(201, [
                    'message' => "Usuario registrado correctamente",
                    'token' => $token,
                    'user' => [
                        'id' => $userId,
                        'username' => $data->username,
                        'role' => 'user'
                    ]
                ]);
            } else {
                $this->sendError(500, "Error al registrar el usuario");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al registrar el usuario: " . $e->getMessage());
        }
    }
    
    /**
     * Envía una respuesta exitosa
     * 
     * @param int $statusCode Código de estado HTTP
     * @param array $data Datos a enviar
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
    
    /**
     * Envía una respuesta de error
     * 
     * @param int $statusCode Código de estado HTTP
     * @param string $message Mensaje de error
     */
    private function sendError($statusCode, $message) {
        http_response_code($statusCode);
        echo json_encode(['error' => true, 'message' => $message]);
        exit();
    }
} 