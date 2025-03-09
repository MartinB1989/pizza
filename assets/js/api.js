/**
 * Módulo para manejar solicitudes a la API
 * Incluye funciones para realizar peticiones GET, POST, PUT y DELETE
 * con manejo de errores mediante try-catch
 */

// No importamos la configuración, asumimos que config.js se carga antes que este archivo

/**
 * Construye la URL completa para la petición
 * @param {string} endpoint - Endpoint de la API (sin la URL base)
 * @returns {string} - URL completa
 */
function buildUrl(endpoint) {
  // Si el endpoint ya comienza con http, asumimos que es una URL completa
  if (endpoint.startsWith('http')) {
    return endpoint;
  }
  
  // Aseguramos que el endpoint no comience con / si la URL base termina con /
  const baseUrl = API_CONFIG.BASE_URL.endsWith('/') 
    ? API_CONFIG.BASE_URL.slice(0, -1) 
    : API_CONFIG.BASE_URL;
    
  const formattedEndpoint = endpoint.startsWith('/') 
    ? endpoint 
    : `/${endpoint}`;
    
  return `${baseUrl}${formattedEndpoint}`;
}

/**
 * Realiza una petición GET a la API
 * @param {string} endpoint - Endpoint a consultar (sin la URL base)
 * @param {Object} headers - Cabeceras HTTP adicionales
 * @returns {Promise<any>} - Promesa con los datos de respuesta
 */
async function fetchGet(endpoint, headers = {}) {
  try {
    const url = buildUrl(endpoint);
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        ...AUTH_UTILS.getAuthHeaders(),
        ...headers
      },
      signal: AbortSignal.timeout(API_CONFIG.TIMEOUT)
    });
    
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error en la petición GET:', error.message);
    throw error;
  }
}

/**
 * Realiza una petición POST a la API
 * @param {string} endpoint - Endpoint (sin la URL base)
 * @param {Object} data - Datos a enviar en el cuerpo de la petición
 * @param {Object} headers - Cabeceras HTTP adicionales
 * @returns {Promise<any>} - Promesa con los datos de respuesta
 */
async function fetchPost(endpoint, data, headers = {}) {
  try {
    const url = buildUrl(endpoint);
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        ...AUTH_UTILS.getAuthHeaders(),
        ...headers
      },
      body: JSON.stringify(data),
      signal: AbortSignal.timeout(API_CONFIG.TIMEOUT)
    });
    
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error en la petición POST:', error.message);
    throw error;
  }
}

/**
 * Realiza una petición PUT a la API
 * @param {string} endpoint - Endpoint (sin la URL base)
 * @param {Object} data - Datos a enviar en el cuerpo de la petición
 * @param {Object} headers - Cabeceras HTTP adicionales
 * @returns {Promise<any>} - Promesa con los datos de respuesta
 */
async function fetchPut(endpoint, data, headers = {}) {
  try {
    const url = buildUrl(endpoint);
    const response = await fetch(url, {
      method: 'PUT',
      headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        ...AUTH_UTILS.getAuthHeaders(),
        ...headers
      },
      body: JSON.stringify(data),
      signal: AbortSignal.timeout(API_CONFIG.TIMEOUT)
    });
    
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error en la petición PUT:', error.message);
    throw error;
  }
}

/**
 * Realiza una petición DELETE a la API
 * @param {string} endpoint - Endpoint (sin la URL base)
 * @param {Object} headers - Cabeceras HTTP adicionales
 * @returns {Promise<any>} - Promesa con los datos de respuesta
 */
async function fetchDelete(endpoint, headers = {}) {
  try {
    const url = buildUrl(endpoint);
    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        ...AUTH_UTILS.getAuthHeaders(),
        ...headers
      },
      signal: AbortSignal.timeout(API_CONFIG.TIMEOUT)
    });
    
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error en la petición DELETE:', error.message);
    throw error;
  }
}

/**
 * Funciones específicas para la API de la pizzería
 */
const API = {
  // Autenticación
  auth: {
    login: async (username, password) => {
      const response = await fetchPost('auth/login', { username, password });
      if (response.token) {
        AUTH_UTILS.setToken(response.token);
      }
      return response;
    },
    
    register: async (username, email, password) => {
      const response = await fetchPost('auth/register', { username, email, password });
      if (response.token) {
        AUTH_UTILS.setToken(response.token);
      }
      return response;
    },
    
    logout: () => {
      AUTH_UTILS.removeToken();
    }
  },
  
  // Productos
  products: {
    getAll: () => fetchGet('products'),
    getOne: (id) => fetchGet(`products/${id}`),
    create: (productData) => fetchPost('products', productData),
    update: (id, productData) => fetchPut(`products/${id}`, productData),
    delete: (id) => fetchDelete(`products/${id}`)
  },
  
  // Categorías
  categories: {
    getAll: () => fetchGet('categories')
  },
  
  // Usuario
  user: {
    getProfile: () => fetchGet('user/profile'),
    updateProfile: (userData) => fetchPut('user/profile', userData)
  }
};

// No usamos export, estas funciones serán globales
// cuando se incluya este archivo con una etiqueta script 