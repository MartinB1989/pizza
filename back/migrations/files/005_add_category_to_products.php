<?php
/**
 * Migración: Añadir columna de categoría a la tabla de productos
 */
return [
    'up' => "ALTER TABLE products
    ADD COLUMN category_id INT NULL,
    ADD CONSTRAINT fk_product_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL;",
    'down' => "ALTER TABLE products
    DROP FOREIGN KEY fk_product_category,
    DROP COLUMN category_id;"
]; 