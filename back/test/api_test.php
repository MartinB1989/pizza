<?php
/**
 * Test de la API
 * 
 * Este script realiza pruebas básicas a los endpoints de la API
 */

// Cargar autoloader
require_once __DIR__ . '/../autoload.php';

// Cargar variables de entorno
require_once __DIR__ . '/../config/dotenv.php';

echo "=== TEST DE LA API ===" . PHP_EOL;

// Función para realizar peticiones HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HEADER, true); // Incluir cabeceras en la respuesta
    
    // Añadir datos si es necesario
    if ($data !== null && ($method === 'POST' || $method === 'PUT')) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($jsonData);
    }
    
    // Añadir cabeceras
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    // Separar cabeceras y cuerpo
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'headers' => $responseHeaders,
        'response' => $responseBody ? json_decode($responseBody, true) : null,
        'raw_response' => $responseBody,
        'error' => $error
    ];
}

// Obtener la URL base de la API
$baseUrl = 'http://localhost/pizzeria/back/api';

// Añadir parámetro de depuración
$debugUrl = $baseUrl . '/products?debug=1';
echo "URL de depuración: " . $debugUrl . PHP_EOL;
$result = makeRequest($debugUrl);
echo "Respuesta de depuración: " . $result['raw_response'] . PHP_EOL . PHP_EOL;

// Prueba 1: Verificar que la API está en funcionamiento (endpoint público)
echo "Prueba 1: Verificar que la API está en funcionamiento" . PHP_EOL;
$result = makeRequest($baseUrl . '/products');

if ($result['error']) {
    echo "❌ Error: " . $result['error'] . PHP_EOL;
} else {
    echo "Código de respuesta: " . $result['code'] . PHP_EOL;
    
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo "✅ La API está en funcionamiento" . PHP_EOL;
        
        // Mostrar datos recibidos
        if (isset($result['response']['data'])) {
            echo "Productos encontrados: " . count($result['response']['data']) . PHP_EOL;
            
            // Mostrar detalles de los productos
            foreach ($result['response']['data'] as $product) {
                echo "  - " . $product['name'] . " ($" . $product['price'] . ")" . PHP_EOL;
            }
        } else {
            echo "No se recibieron datos de productos" . PHP_EOL;
            echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
            echo "Cabeceras: " . $result['headers'] . PHP_EOL;
        }
    } else {
        echo "❌ La API no está funcionando correctamente" . PHP_EOL;
        echo "Respuesta: " . print_r($result['response'], true) . PHP_EOL;
        echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
        echo "Cabeceras: " . $result['headers'] . PHP_EOL;
    }
}

echo PHP_EOL;

// Prueba 2: Intentar acceder a un endpoint protegido sin token
echo "Prueba 2: Intentar acceder a un endpoint protegido sin token" . PHP_EOL;
$result = makeRequest($baseUrl . '/user/profile');

if ($result['error']) {
    echo "❌ Error: " . $result['error'] . PHP_EOL;
} else {
    echo "Código de respuesta: " . $result['code'] . PHP_EOL;
    
    if ($result['code'] === 401) {
        echo "✅ El endpoint protegido requiere autenticación (como se esperaba)" . PHP_EOL;
    } else {
        echo "❌ El endpoint protegido no está configurado correctamente" . PHP_EOL;
        echo "Respuesta: " . print_r($result['response'], true) . PHP_EOL;
        echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
        echo "Cabeceras: " . $result['headers'] . PHP_EOL;
    }
}

echo PHP_EOL;

// Prueba 3: Intentar iniciar sesión con credenciales inválidas
echo "Prueba 3: Intentar iniciar sesión con credenciales inválidas" . PHP_EOL;
$result = makeRequest($baseUrl . '/auth/login', 'POST', [
    'username' => 'usuario_inexistente',
    'password' => 'contraseña_incorrecta'
]);

if ($result['error']) {
    echo "❌ Error: " . $result['error'] . PHP_EOL;
} else {
    echo "Código de respuesta: " . $result['code'] . PHP_EOL;
    
    if ($result['code'] === 401) {
        echo "✅ El inicio de sesión con credenciales inválidas fue rechazado (como se esperaba)" . PHP_EOL;
    } else {
        echo "❌ El endpoint de inicio de sesión no está configurado correctamente" . PHP_EOL;
        echo "Respuesta: " . print_r($result['response'], true) . PHP_EOL;
        echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
        echo "Cabeceras: " . $result['headers'] . PHP_EOL;
    }
}

echo PHP_EOL;

// Prueba 4: Iniciar sesión con credenciales válidas
echo "Prueba 4: Iniciar sesión con credenciales válidas" . PHP_EOL;
$result = makeRequest($baseUrl . '/auth/login', 'POST', [
    'username' => 'test_user',
    'password' => 'password123'
]);

if ($result['error']) {
    echo "❌ Error: " . $result['error'] . PHP_EOL;
} else {
    echo "Código de respuesta: " . $result['code'] . PHP_EOL;
    
    if ($result['code'] === 200 && isset($result['response']['token'])) {
        echo "✅ Inicio de sesión exitoso" . PHP_EOL;
        
        // Guardar el token para usarlo en la siguiente prueba
        $token = $result['response']['token'];
        
        // Prueba 5: Acceder a un endpoint protegido con token
        echo PHP_EOL . "Prueba 5: Acceder a un endpoint protegido con token" . PHP_EOL;
        $result = makeRequest($baseUrl . '/user/profile', 'GET', null, [
            'Authorization: Bearer ' . $token
        ]);
        
        if ($result['error']) {
            echo "❌ Error: " . $result['error'] . PHP_EOL;
        } else {
            echo "Código de respuesta: " . $result['code'] . PHP_EOL;
            
            if ($result['code'] === 200 && isset($result['response']['data'])) {
                echo "✅ Acceso al endpoint protegido exitoso" . PHP_EOL;
                echo "Datos del usuario: " . print_r($result['response']['data'], true) . PHP_EOL;
            } else {
                echo "❌ El endpoint protegido no está funcionando correctamente" . PHP_EOL;
                echo "Respuesta: " . print_r($result['response'], true) . PHP_EOL;
                echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
                echo "Cabeceras: " . $result['headers'] . PHP_EOL;
            }
        }
    } else {
        echo "❌ El inicio de sesión con credenciales válidas falló" . PHP_EOL;
        echo "Respuesta: " . print_r($result['response'], true) . PHP_EOL;
        echo "Respuesta completa: " . $result['raw_response'] . PHP_EOL;
        echo "Cabeceras: " . $result['headers'] . PHP_EOL;
    }
}

echo PHP_EOL . "=== FIN DEL TEST ===" . PHP_EOL; 