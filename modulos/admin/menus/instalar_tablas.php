<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    die("Acceso no autorizado");
}

// SQL para crear las tablas
$sql = "
-- Verificar si la tabla ya existe
CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Verificar si la tabla ya existe
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    parent_id INT DEFAULT 0,
    titulo VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    orden INT DEFAULT 0,
    clase VARCHAR(100),
    target VARCHAR(20) DEFAULT '_self',
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

-- Añadir índices para mejorar el rendimiento de consultas jerárquicas
ALTER TABLE menu_items ADD INDEX idx_parent_id (parent_id);
ALTER TABLE menu_items ADD INDEX idx_menu_id (menu_id);

-- Crear la nueva tabla menu_positions para gestionar la asignación de menús a posiciones
CREATE TABLE IF NOT EXISTS menu_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    position VARCHAR(50) NOT NULL,
    parent_menu_id INT DEFAULT 0,
    menu_order INT DEFAULT 0,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    INDEX idx_position (position),
    INDEX idx_parent_menu_id (parent_menu_id)
);
";

// Ejecutar SQL
if ($conexion->multi_query($sql)) {
    echo "<p>Tablas creadas correctamente</p>";
    
    // Limpiar los resultados pendientes
    while ($conexion->more_results() && $conexion->next_result()) {
        // Consumir los resultados de las consultas adicionales
        if ($result = $conexion->store_result()) {
            $result->free();
        }
    }
    
    echo "<p><a href='" . URL_SITIO . "modulos/admin/menus/listar.php'>Ir a gestión de menús</a></p>";
} else {
    echo "<p>Error al crear las tablas: " . $conexion->error . "</p>";
}