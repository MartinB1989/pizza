<?php
/**
 * Controlador de productos
 * 
 * Este controlador maneja las operaciones CRUD para los productos
 */

namespace Back\Api\Controllers;

use Back\Database\Database;

class ProductController {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener instancia de la base de datos
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todos los productos
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function getAll($params) {
        try {
            $query = "SELECT id, name, description, price, image FROM products WHERE active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $products = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $products[] = $row;
            }
            
            $this->sendResponse(200, ['data' => $products]);
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al obtener productos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un producto por ID
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function getOne($params) {
        try {
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de producto no proporcionado");
            }
            
            $id = $params['id'];
            
            $query = "SELECT id, name, description, price, image FROM products WHERE id = ? AND active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($product) {
                $this->sendResponse(200, ['data' => $product]);
            } else {
                $this->sendError(404, "Producto no encontrado");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al obtener el producto: " . $e->getMessage());
        }
    }
    
    /**
     * Crea un nuevo producto
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function create($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para crear productos");
            }
            
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->name) || !isset($data->price)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Insertar producto
            $query = "INSERT INTO products (name, description, price, image, active) VALUES (?, ?, ?, ?, 1)";
            $stmt = $this->db->prepare($query);
            
            $description = isset($data->description) ? $data->description : '';
            $image = isset($data->image) ? $data->image : '';
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $description);
            $stmt->bindParam(3, $data->price);
            $stmt->bindParam(4, $image);
            
            if ($stmt->execute()) {
                $productId = $this->db->lastInsertId();
                $this->sendResponse(201, [
                    'message' => "Producto creado correctamente",
                    'id' => $productId
                ]);
            } else {
                $this->sendError(500, "Error al crear el producto");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al crear el producto: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un producto existente
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function update($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para actualizar productos");
            }
            
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de producto no proporcionado");
            }
            
            $id = $params['id'];
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar datos
            if (!isset($data->name) || !isset($data->price)) {
                $this->sendError(400, "Datos incompletos");
            }
            
            // Verificar si el producto existe
            $checkQuery = "SELECT id FROM products WHERE id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                $this->sendError(404, "Producto no encontrado");
            }
            
            // Actualizar producto
            $query = "UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            $description = isset($data->description) ? $data->description : '';
            $image = isset($data->image) ? $data->image : '';
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $description);
            $stmt->bindParam(3, $data->price);
            $stmt->bindParam(4, $image);
            $stmt->bindParam(5, $id);
            
            if ($stmt->execute()) {
                $this->sendResponse(200, ['message' => "Producto actualizado correctamente"]);
            } else {
                $this->sendError(500, "Error al actualizar el producto");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al actualizar el producto: " . $e->getMessage());
        }
    }
    
    /**
     * Elimina un producto (eliminación lógica)
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function delete($params) {
        try {
            // Verificar permisos
            if (!isset($params['user']) || $params['user']['role'] !== 'admin') {
                $this->sendError(403, "No tienes permisos para eliminar productos");
            }
            
            if (!isset($params['id'])) {
                $this->sendError(400, "ID de producto no proporcionado");
            }
            
            $id = $params['id'];
            
            // Verificar si el producto existe
            $checkQuery = "SELECT id FROM products WHERE id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                $this->sendError(404, "Producto no encontrado");
            }
            
            // Eliminación lógica (cambiar estado a inactivo)
            $query = "UPDATE products SET active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $id);
            
            if ($stmt->execute()) {
                $this->sendResponse(200, ['message' => "Producto eliminado correctamente"]);
            } else {
                $this->sendError(500, "Error al eliminar el producto");
            }
            
        } catch (\Exception $e) {
            $this->sendError(500, "Error al eliminar el producto: " . $e->getMessage());
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