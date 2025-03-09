<?php
/**
 * Script para eliminar datos de prueba
 * 
 * Este script elimina los datos de prueba de la base de datos
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

use Back\Database\Database;

echo "=== ELIMINANDO DATOS DE PRUEBA ===" . PHP_EOL;

// Obtener conexión a la base de datos
$db = Database::getInstance()->getConnection();

try {
    // Iniciar transacción
    $db->beginTransaction();
    
    // 1. Eliminar productos de prueba
    echo "Eliminando productos de prueba..." . PHP_EOL;
    
    $deleteProductsQuery = "DELETE FROM products WHERE name LIKE 'Test Product%'";
    $deleteProductsStmt = $db->prepare($deleteProductsQuery);
    $deleteProductsStmt->execute();
    
    $productsDeleted = $deleteProductsStmt->rowCount();
    echo "✅ Productos eliminados: $productsDeleted" . PHP_EOL;
    
    // 2. Eliminar categoría de prueba
    echo "Eliminando categoría de prueba..." . PHP_EOL;
    
    $deleteCategoryQuery = "DELETE FROM categories WHERE name = 'Test Category'";
    $deleteCategoryStmt = $db->prepare($deleteCategoryQuery);
    $deleteCategoryStmt->execute();
    
    $categoriesDeleted = $deleteCategoryStmt->rowCount();
    echo "✅ Categorías eliminadas: $categoriesDeleted" . PHP_EOL;
    
    // 3. Eliminar usuario de prueba
    echo "Eliminando usuario de prueba..." . PHP_EOL;
    
    $deleteUserQuery = "DELETE FROM users WHERE username = 'test_user'";
    $deleteUserStmt = $db->prepare($deleteUserQuery);
    $deleteUserStmt->execute();
    
    $usersDeleted = $deleteUserStmt->rowCount();
    echo "✅ Usuarios eliminados: $usersDeleted" . PHP_EOL;
    
    // Confirmar transacción
    $db->commit();
    
    echo "✅ Datos de prueba eliminados correctamente" . PHP_EOL;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $db->rollback();
    
    echo "❌ Error al eliminar datos de prueba: " . $e->getMessage() . PHP_EOL;
}

echo "=== FIN DE LA ELIMINACIÓN DE DATOS DE PRUEBA ===" . PHP_EOL; 