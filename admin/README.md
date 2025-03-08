# Backend del Proyecto

Este es el backend del proyecto, desarrollado en PHP con conexión a MySQL.

## Estructura del Proyecto

```
back/
├── config/             # Archivos de configuración
│   └── database.php    # Configuración de la base de datos
├── src/                # Código fuente
│   ├── database/       # Capa de acceso a datos
│   │   └── Database.php # Clase para la conexión a la base de datos
│   ├── models/         # Modelos de datos
│   ├── controllers/    # Controladores
│   └── services/       # Servicios de negocio
├── test/               # Pruebas
│   └── DatabaseTest.php # Pruebas para la conexión a la base de datos
└── autoload.php        # Cargador automático de clases
```

## Configuración

Para configurar la conexión a la base de datos, edita el archivo `config/database.php` con los datos de tu entorno:

```php
// Configuración para el entorno de desarrollo
if (APP_ENV === 'development') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'nombre_db');
    define('DB_USER', 'usuario');
    define('DB_PASS', 'contraseña');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
}
```

## Uso de la Conexión a la Base de Datos

Para utilizar la conexión a la base de datos en cualquier parte del proyecto:

```php
use App\Database\Database;

// Obtener la instancia de la base de datos
$db = Database::getInstance();

// Ejecutar una consulta
$result = $db->query("SELECT * FROM usuarios WHERE id = ?", [1]);
$usuario = $result->fetch();

// Usar transacciones
$db->beginTransaction();
try {
    $db->query("INSERT INTO usuarios (nombre, email) VALUES (?, ?)", ["Juan", "juan@ejemplo.com"]);
    $db->query("INSERT INTO perfiles (usuario_id, bio) VALUES (?, ?)", [$db->lastInsertId(), "Bio de Juan"]);
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Ejecutar Pruebas

Para ejecutar las pruebas de la conexión a la base de datos:

```bash
php test/DatabaseTest.php
```

Asegúrate de configurar correctamente los datos de conexión para el entorno de pruebas en `config/database.php`. 