/**
 * Archivo de configuración para la aplicación
 * Contiene variables de entorno y configuraciones globales
 */

// Configuración de la API
const API_CONFIG = {
  // URL base de la API
  BASE_URL: '/api/v1',
  
  // Tiempo máximo de espera para las peticiones en milisegundos
  TIMEOUT: 30000,
  
  // Cabeceras por defecto para todas las peticiones
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  
  // Clave para almacenar el token en localStorage
  TOKEN_KEY: 'pizzeria_auth_token'
};

// Configuración general de la aplicación
const APP_CONFIG = {
  // Nombre de la aplicación
  APP_NAME: 'Pizzería App',
  
  // Versión de la aplicación
  VERSION: '1.0.0',
  
  // Modo de la aplicación (development, production, test)
  MODE: 'development'
};

// Funciones de utilidad para la autenticación
const AUTH_UTILS = {
  // Obtener token del localStorage
  getToken: function() {
    return localStorage.getItem(API_CONFIG.TOKEN_KEY);
  },
  
  // Guardar token en localStorage
  setToken: function(token) {
    localStorage.setItem(API_CONFIG.TOKEN_KEY, token);
  },
  
  // Eliminar token del localStorage
  removeToken: function() {
    localStorage.removeItem(API_CONFIG.TOKEN_KEY);
  },
  
  // Verificar si el usuario está autenticado
  isAuthenticated: function() {
    return !!this.getToken();
  },
  
  // Obtener cabeceras de autorización
  getAuthHeaders: function() {
    const token = this.getToken();
    return token ? { 'Authorization': `Bearer ${token}` } : {};
  }
};

// No usamos export, estas variables serán globales
// cuando se incluya este archivo con una etiqueta script 