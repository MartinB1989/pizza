/**
 * Archivo de configuración para la aplicación
 * Contiene variables de entorno y configuraciones globales
 */

// Configuración de la API
const API_CONFIG = {
  // URL base de la API
  BASE_URL: 'https://api.ejemplo.com',
  
  // Tiempo máximo de espera para las peticiones en milisegundos
  TIMEOUT: 30000,
  
  // Cabeceras por defecto para todas las peticiones
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
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

// No usamos export, estas variables serán globales
// cuando se incluya este archivo con una etiqueta script 