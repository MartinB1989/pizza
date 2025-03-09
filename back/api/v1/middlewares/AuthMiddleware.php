<?php
/**
 * Middleware de autenticación
 * 
 * Este middleware se encarga de verificar la autenticación de los usuarios
 * mediante tokens JWT para proteger rutas de la API
 */

namespace Back\Api\Middlewares;

class AuthMiddleware {
    private $jwtSecret;
    private $jwtExpiration;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->jwtSecret = env('JWT_SECRET', 'clave_secreta_por_defecto');
        $this->jwtExpiration = (int)env('JWT_EXPIRATION', 3600);
    }
    
    /**
     * Verifica si el token JWT es válido
     * 
     * @return array Datos del usuario si el token es válido
     * @throws \Exception Si el token no es válido
     */
    public function verifyToken() {
        // Obtener headers
        $headers = $this->getAuthorizationHeader();
        
        // Verificar si existe el header de autorización
        if (empty($headers)) {
            $this->sendError(401, 'Token de acceso no proporcionado');
        }
        
        // Extraer el token
        if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $this->sendError(401, 'Formato de token inválido');
        }
        
        $jwt = $matches[1];
        
        try {
            // Decodificar token
            $tokenParts = explode('.', $jwt);
            if (count($tokenParts) != 3) {
                throw new \Exception("Formato de token inválido");
            }
            
            $header = $this->base64UrlDecode($tokenParts[0]);
            $payload = $this->base64UrlDecode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];
            
            // Verificar expiración
            $payload = json_decode($payload, true);
            if (!isset($payload['exp']) || $payload['exp'] < time()) {
                throw new \Exception("Token expirado");
            }
            
            // Verificar firma
            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
            $signature = hash_hmac('SHA256', $base64UrlHeader . "." . $base64UrlPayload, $this->jwtSecret, true);
            $base64UrlSignature = $this->base64UrlEncode($signature);
            
            if ($base64UrlSignature !== $signatureProvided) {
                throw new \Exception("Firma inválida");
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            $this->sendError(401, $e->getMessage());
        }
    }
    
    /**
     * Genera un token JWT
     * 
     * @param int $userId ID del usuario
     * @param string $username Nombre de usuario
     * @param string $role Rol del usuario
     * @return string Token JWT
     */
    public function generateToken($userId, $username, $role = 'user') {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->jwtExpiration;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'username' => $username,
            'role' => $role
        ];
        
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('SHA256', $headerEncoded . "." . $payloadEncoded, $this->jwtSecret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }
    
    /**
     * Obtiene el header de autorización
     * 
     * @return string Header de autorización o cadena vacía
     */
    private function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * Codifica en base64 URL-safe
     * 
     * @param string $data Datos a codificar
     * @return string Datos codificados
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodifica base64 URL-safe
     * 
     * @param string $data Datos a decodificar
     * @return string Datos decodificados
     */
    private function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/');
        $padLength = 4 - strlen($base64) % 4;
        
        if ($padLength < 4) {
            $base64 .= str_repeat('=', $padLength);
        }
        
        return base64_decode($base64);
    }
    
    /**
     * Envía un error y termina la ejecución
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