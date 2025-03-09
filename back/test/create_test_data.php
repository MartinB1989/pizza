<?php
/**
 * Script para crear datos de prueba
 * 
 * Este script inserta datos de prueba en la base de datos
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

use Back\Database\Database;

echo "=== CREANDO DATOS DE PRUEBA ===" . PHP_EOL;

// Obtener conexión a la base de datos
$db = Database::getInstance()->getConnection();

try {
    // Iniciar transacción
    $db->beginTransaction();
    
    // 1. Crear usuario de prueba
    echo "Creando usuario de prueba..." . PHP_EOL;
    
    // Verificar si el usuario ya existe
    $checkUserQuery = "SELECT id FROM users WHERE username = 'test_user'";
    $checkUserStmt = $db->query($checkUserQuery);
    $userExists = $checkUserStmt->rowCount() > 0;
    
    if (!$userExists) {
        // Crear usuario
        $createUserQuery = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $createUserStmt = $db->prepare($createUserQuery);
        
        $username = 'test_user';
        $email = 'test@example.com';
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $role = 'admin';
        
        $createUserStmt->bindParam(1, $username);
        $createUserStmt->bindParam(2, $email);
        $createUserStmt->bindParam(3, $password);
        $createUserStmt->bindParam(4, $role);
        
        $createUserStmt->execute();
        
        $userId = $db->lastInsertId();
        echo "✅ Usuario creado con ID: $userId" . PHP_EOL;
    } else {
        echo "⚠️ El usuario de prueba ya existe" . PHP_EOL;
    }
    
    // 2. Crear categoría de prueba
    echo "Creando categoría de prueba..." . PHP_EOL;
    
    // Verificar si la categoría ya existe
    $checkCategoryQuery = "SELECT id FROM categories WHERE name = 'Test Category'";
    $checkCategoryStmt = $db->query($checkCategoryQuery);
    $categoryExists = $checkCategoryStmt->rowCount() > 0;
    
    $categoryId = 0;
    
    if (!$categoryExists) {
        // Crear categoría
        $createCategoryQuery = "INSERT INTO categories (name, description, active) VALUES (?, ?, ?)";
        $createCategoryStmt = $db->prepare($createCategoryQuery);
        
        $name = 'Test Category';
        $description = 'This is a test category';
        $active = 1;
        
        $createCategoryStmt->bindParam(1, $name);
        $createCategoryStmt->bindParam(2, $description);
        $createCategoryStmt->bindParam(3, $active);
        
        $createCategoryStmt->execute();
        
        $categoryId = $db->lastInsertId();
        echo "✅ Categoría creada con ID: $categoryId" . PHP_EOL;
    } else {
        $categoryId = $checkCategoryStmt->fetch(\PDO::FETCH_ASSOC)['id'];
        echo "⚠️ La categoría de prueba ya existe con ID: $categoryId" . PHP_EOL;
    }
    
    // 3. Crear productos de prueba
    echo "Creando productos de prueba..." . PHP_EOL;
    
    // Verificar si los productos ya existen
    $checkProductQuery = "SELECT id FROM products WHERE name = 'Test Product 1'";
    $checkProductStmt = $db->query($checkProductQuery);
    $productExists = $checkProductStmt->rowCount() > 0;
    
    if (!$productExists) {
        // Crear productos
        $createProductQuery = "INSERT INTO products (name, description, price, image, category_id, active) VALUES (?, ?, ?, ?, ?, ?)";
        $createProductStmt = $db->prepare($createProductQuery);
        
        // Producto 1
        $name = 'Test Product 1';
        $description = 'This is test product 1';
        $price = 9.99;
        $image = 'test1.jpg';
        $active = 1;
        
        $createProductStmt->bindParam(1, $name);
        $createProductStmt->bindParam(2, $description);
        $createProductStmt->bindParam(3, $price);
        $createProductStmt->bindParam(4, $image);
        $createProductStmt->bindParam(5, $categoryId);
        $createProductStmt->bindParam(6, $active);
        
        $createProductStmt->execute();
        
        $productId = $db->lastInsertId();
        echo "✅ Producto 1 creado con ID: $productId" . PHP_EOL;
        
        // Producto 2
        $name = 'Test Product 2';
        $description = 'This is test product 2';
        $price = 19.99;
        $image = 'test2.jpg';
        
        $createProductStmt->execute();
        
        $productId = $db->lastInsertId();
        echo "✅ Producto 2 creado con ID: $productId" . PHP_EOL;
    } else {
        echo "⚠️ Los productos de prueba ya existen" . PHP_EOL;
    }
    
    // Confirmar transacción
    $db->commit();
    
    echo "✅ Datos de prueba creados correctamente" . PHP_EOL;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $db->rollback();
    
    echo "❌ Error al crear datos de prueba: " . $e->getMessage() . PHP_EOL;
}

echo "=== FIN DE LA CREACIÓN DE DATOS DE PRUEBA ===" . PHP_EOL; 