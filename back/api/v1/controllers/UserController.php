<?php
/**
 * Controlador de usuarios
 * 
 * Este controlador maneja las operaciones relacionadas con los usuarios
 */

namespace Back\Api\Controllers;

use Back\Database\Database;

class UserController {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener instancia de la base de datos
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene el perfil del usuario autenticado
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function getProfile($params) {
        try {
            // Verificar que el usuario está autenticado
            if (!isset($params['user'])) {
                $this->sendError(401, "No autenticado");
            }
            
            $userId = $params['user']['user_id'];
            
            // Obtener datos del usuario
            $query = "SELECT id, username, email, role FROM users WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->sendResponse(200, ['data' => $user]);
            } else {
                $this->sendError(404, "Usuario no encontrado");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al obtener el perfil: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza el perfil del usuario autenticado
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function updateProfile($params) {
        try {
            // Verificar que el usuario está autenticado
            if (!isset($params['user'])) {
                $this->sendError(401, "No autenticado");
            }
            
            $userId = $params['user']['user_id'];
            
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->email)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Verificar si el email ya existe para otro usuario
            $query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->email);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->sendError(409, "El email ya está en uso");
            }
            
            // Actualizar usuario
            $query = "UPDATE users SET email = ?";
            $params = [$data->email];
            
            // Si se proporciona una nueva contraseña, actualizarla
            if (isset($data->password) && !empty($data->password)) {
                $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
                $query .= ", password = ?";
                $params[] = $passwordHash;
            }
            
            $query .= " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $this->db->prepare($query);
            
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindParam($i + 1, $params[$i]);
            }
            
            if ($stmt->execute()) {
                $this->sendResponse(200, ['message' => "Perfil actualizado correctamente"]);
            } else {
                $this->sendError(500, "Error al actualizar el perfil");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al actualizar el perfil: " . $e->getMessage());
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