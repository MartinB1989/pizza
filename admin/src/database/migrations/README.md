# Sistema de Migraciones

Este directorio contiene los archivos para las migraciones de la base de datos.

## ¿Qué son las migraciones?

Las migraciones son archivos que contienen cambios en la estructura de la base de datos. Cada migración representa un cambio específico, como crear una tabla, añadir una columna, etc.

## Tipos de archivos de migración

El sistema soporta dos tipos de archivos de migración:

1. **Archivos SQL (.sql)**: Contienen solo instrucciones para aplicar cambios (no se pueden revertir)
2. **Archivos PHP (.php)**: Contienen instrucciones tanto para aplicar como para revertir cambios

## Convenciones de nomenclatura

Los archivos de migración deben seguir el siguiente formato:

```
NNN_nombre_descriptivo.sql
```

o

```
NNN_nombre_descriptivo.php
```

Donde:
- `NNN` es un número secuencial de tres dígitos (001, 002, 003, etc.)
- `nombre_descriptivo` es una descripción breve de lo que hace la migración

## Cómo crear una nueva migración

### Migración SQL (solo aplicar)

Crea un archivo .sql con las instrucciones SQL:

```sql
-- Migración: Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE
);
```

### Migración PHP (aplicar y revertir)

Crea un archivo .php que devuelva un array con las claves 'up' y 'down':

```php
<?php
/**
 * Migración: Crear tabla de usuarios
 */
return [
    'up' => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE
        );
    ",
    'down' => "
        DROP TABLE IF EXISTS users;
    "
];
```

## Cómo ejecutar las migraciones

Para aplicar todas las migraciones pendientes:

```bash
php migrate.php
```

Para revertir migraciones:

```bash
php rollback.php [número de migraciones a revertir]
```

Ejemplos:
- `php rollback.php` - Revierte la última migración
- `php rollback.php 3` - Revierte las últimas 3 migraciones
- `php rollback.php 0` - Revierte TODAS las migraciones (pide confirmación)

## Recomendaciones

- Cada migración debe ser atómica (hacer una sola cosa)
- Las migraciones deben ser idempotentes (poder ejecutarse varias veces sin causar errores)
- Usa `IF NOT EXISTS` al crear tablas y `IF EXISTS` al eliminarlas
- Incluye comentarios para explicar lo que hace la migración
- Todos los nombres de tablas y columnas deben estar en inglés 