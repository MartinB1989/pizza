<?php
/**
 * Controlador de categorías
 * 
 * Este controlador maneja las operaciones CRUD para las categorías
 */

namespace Back\Api\Controllers;

use Back\Database\Database;

class CategoryController {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener instancia de la base de datos
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todas las categorías
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function getAll($params) {
        try {
            $query = "SELECT id, name, description FROM categories WHERE active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $categories = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $categories[] = $row;
            }
            
            $this->sendResponse(200, ['data' => $categories]);
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al obtener categorías: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene una categoría por ID
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function getOne($params) {
        try {
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de categoría no proporcionado");
            }
            
            $id = $params['id'];
            
            $query = "SELECT id, name, description FROM categories WHERE id = ? AND active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            
            $category = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($category) {
                $this->sendResponse(200, ['data' => $category]);
            } else {
                $this->sendError(404, "Categoría no encontrada");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al obtener la categoría: " . $e->getMessage());
        }
    }
    
    /**
     * Crea una nueva categoría
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function create($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para crear categorías");
            }
            
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->name)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Insertar categoría
            $query = "INSERT INTO categories (name, description, active) VALUES (?, ?, 1)";
            $stmt = $this->db->prepare($query);
            
            $description = isset($data->description) ? $data->description : '';
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $description);
            
            if ($stmt->execute()) {
                $categoryId = $this->db->lastInsertId();
                $this->sendResponse(201, [
                    'message' => "Categoría creada correctamente",
                    'id' => $categoryId
                ]);
            } else {
                $this->sendError(500, "Error al crear la categoría");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al crear la categoría: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza una categoría existente
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function update($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para actualizar categorías");
            }
            
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de categoría no proporcionado");
            }
            
            $id = $params['id'];
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->name)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Verificar si la categoría existe
            $checkQuery = "SELECT id FROM categories WHERE id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                $this->sendError(404, "Categoría no encontrada");
            }
            
            // Actualizar categoría
            $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            $description = isset($data->description) ? $data->description : '';
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $description);
            $stmt->bindParam(3, $id);
            
            if ($stmt->execute()) {
                $this->sendResponse(200, ['message' => "Categoría actualizada correctamente"]);
            } else {
                $this->sendError(500, "Error al actualizar la categoría");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al actualizar la categoría: " . $e->getMessage());
        }
    }
    
    /**
     * Elimina una categoría (eliminación lógica)
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function delete($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para eliminar categorías");
            }
            
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de categoría no proporcionado");
            }
            
            $id = $params['id'];
            
            // Verificar si la categoría existe
            $checkQuery = "SELECT id FROM categories WHERE id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                $this->sendError(404, "Categoría no encontrada");
            }
            
            // Eliminación lógica (cambiar estado a inactivo)
            $query = "UPDATE categories SET active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $id);
            
            if ($stmt->execute()) {
                $this->sendResponse(200, ['message' => "Categoría eliminada correctamente"]);
            } else {
                $this->sendError(500, "Error al eliminar la categoría");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al eliminar la categoría: " . $e->getMessage());
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