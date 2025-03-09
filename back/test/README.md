# Pruebas del Backend

Esta carpeta contiene scripts para probar diferentes componentes del backend de la aplicación.

## Pruebas disponibles

### 1. Prueba de conexión a la base de datos
- **Archivo**: `connection_test.php`
- **Descripción**: Prueba simple para verificar la conexión a la base de datos.
- **Uso**: `php connection_test.php`

### 2. Prueba detallada de la base de datos
- **Archivo**: `database_connection_test.php`
- **Descripción**: Prueba detallada que muestra información sobre la base de datos, tablas y columnas.
- **Uso**: `php database_connection_test.php`

### 3. Prueba de migraciones
- **Archivo**: `migration_test.php`
- **Descripción**: Verifica que las migraciones estén correctamente configuradas y muestra el historial de migraciones.
- **Uso**: `php migration_test.php`

### 4. Prueba de la API
- **Archivo**: `api_test.php`
- **Descripción**: Realiza pruebas básicas a los endpoints de la API.
- **Uso**: `php api_test.php`

### 5. Crear datos de prueba
- **Archivo**: `create_test_data.php`
- **Descripción**: Crea datos de prueba en la base de datos para las pruebas de la API.
- **Uso**: `php create_test_data.php`

### 6. Eliminar datos de prueba
- **Archivo**: `delete_test_data.php`
- **Descripción**: Elimina los datos de prueba creados por `create_test_data.php`.
- **Uso**: `php delete_test_data.php`

### 7. Ejecutar todas las pruebas de la API
- **Archivo**: `run_api_tests.php`
- **Descripción**: Ejecuta todas las pruebas de la API en secuencia (crear datos, probar API, eliminar datos).
- **Uso**: `php run_api_tests.php`

### 8. Ejecutar todas las pruebas
- **Archivo**: `run_all_tests.php`
- **Descripción**: Ejecuta todas las pruebas disponibles en secuencia.
- **Uso**: `php run_all_tests.php`

## Requisitos

- PHP 7.4 o superior
- Extensión PDO habilitada
- Extensión cURL habilitada (para las pruebas de API)
- Servidor MySQL en ejecución
- Base de datos configurada en el archivo `.env`

## Solución de problemas

Si alguna prueba falla, verifica lo siguiente:

1. El servidor MySQL está en ejecución
2. Las credenciales en el archivo `.env` son correctas
3. La base de datos existe
4. El usuario tiene permisos para acceder a la base de datos
5. Las migraciones se han ejecutado correctamente (`php back/migrate.php`)
6. El servidor web está en ejecución (para las pruebas de API)

## Notas

- Las pruebas de API requieren que el servidor web esté en ejecución
- Las pruebas de base de datos requieren que el servidor MySQL esté en ejecución
- Las pruebas de migraciones requieren que las migraciones se hayan ejecutado correctamente
- Las pruebas de API crean y eliminan datos de prueba automáticamente 