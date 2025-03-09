<?php
/**
 * Migración: Crear tabla de migraciones
 */
return [
    'up' => "CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);",
    'down' => "DROP TABLE IF EXISTS migrations;"
]; 