# Proyecto PIZZA

Este proyecto implementa una estructura base para aplicaciones web, con un sistema de comunicación con APIs REST que puede adaptarse a cualquier tipo de proyecto. PIZZA (Platform for Integrated Zero-setup API / Plataforma para API Integrada de Configuración Zero) proporciona una base sólida para comenzar rápidamente el desarrollo de aplicaciones web con comunicación a APIs.

## Estructura del Proyecto

```
proyecto/
├── assets/
│   ├── js/
│   │   ├── api.js       # Funciones para comunicación con la API
│   │   └── config.js    # Configuración global de la aplicación
│   ├── css/
│   └── img/
├── admin/               # Sección de administración (opcional)
└── index.html           # Página principal
```

## Módulo de API

Hemos implementado un sistema de comunicación con APIs REST que incluye:

### Archivo de Configuración (`assets/js/config.js`)

Este archivo contiene las configuraciones globales de la aplicación:

- **API_CONFIG**: Configuración relacionada con la API
  - `BASE_URL`: URL base para todas las peticiones a la API
  - `TIMEOUT`: Tiempo máximo de espera para las peticiones (en milisegundos)
  - `DEFAULT_HEADERS`: Cabeceras HTTP por defecto para todas las peticiones

- **APP_CONFIG**: Configuración general de la aplicación
  - `APP_NAME`: Nombre de la aplicación
  - `VERSION`: Versión actual
  - `MODE`: Modo de ejecución (development, production, test)

### Módulo de Comunicación con la API (`assets/js/api.js`)

Este archivo contiene funciones para realizar peticiones HTTP a la API:

- **buildUrl(endpoint)**: Construye la URL completa combinando la URL base con el endpoint
- **fetchGet(endpoint, headers)**: Realiza peticiones GET
- **fetchPost(endpoint, data, headers)**: Realiza peticiones POST
- **fetchPut(endpoint, data, headers)**: Realiza peticiones PUT
- **fetchDelete(endpoint, headers)**: Realiza peticiones DELETE

Todas las funciones incluyen:
- Manejo de errores mediante bloques try-catch
- Verificación de respuestas HTTP exitosas
- Timeout para evitar peticiones infinitas
- Combinación de cabeceras por defecto con cabeceras personalizadas

## Cómo Usar

Para utilizar las funciones de API en tu HTML:

1. Incluye los archivos JavaScript en el orden correcto:

```html
<!-- Primero el archivo de configuración -->
<script src="assets/js/config.js"></script>

<!-- Después el archivo de la API -->
<script src="assets/js/api.js"></script>
```

2. Usa las funciones en tu código:

```html
<script>
  // Ejemplo de petición GET
  fetchGet('recursos')
    .then(data => {
      console.log('Recursos:', data);
      // Hacer algo con los datos...
    })
    .catch(error => {
      console.error('Error:', error);
    });

  // Ejemplo de petición POST
  const nuevoRecurso = {
    nombre: "Nuevo recurso",
    descripcion: "Descripción del recurso",
    atributos: {
      color: "azul",
      tamaño: "mediano"
    }
  };

  fetchPost('recursos', nuevoRecurso)
    .then(respuesta => {
      console.log('Recurso creado:', respuesta);
    })
    .catch(error => {
      console.error('Error al crear recurso:', error);
    });
</script>
```

## Características Importantes

- **Manejo de URLs**: Las funciones aceptan endpoints relativos (ej: 'recursos') y los combinan automáticamente con la URL base definida en la configuración.
- **Manejo de Errores**: Todas las peticiones incluyen manejo de errores mediante try-catch.
- **Timeout**: Las peticiones tienen un tiempo máximo de espera configurable.
- **Cabeceras Personalizables**: Puedes añadir cabeceras HTTP adicionales a cada petición.

## Documentación

El código utiliza comentarios JSDoc para documentar cada función y parámetro, lo que facilita su comprensión y uso.

## Configuración

Para adaptar este proyecto a tus necesidades:

1. Modifica el archivo `assets/js/config.js` para establecer la URL base de tu API y otras configuraciones.
2. Personaliza la estructura de carpetas según las necesidades de tu proyecto.
3. Extiende las funciones de API si necesitas comportamientos específicos adicionales.