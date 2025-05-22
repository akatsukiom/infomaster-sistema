<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Exportar menús
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="menus.csv"');

// Abrir output stream
$output = fopen('php://output', 'w');

// Instanciar modelo
$menuModel = new Menu($conexion);

// Exportar menús
$menus = $menuModel->obtenerTodos();

// Escribir headers
fputcsv($output, ['id', 'nombre', 'descripcion', 'fecha_creacion']);

// Escribir datos
foreach ($menus as $menu) {
    fputcsv($output, $menu);
}

// Exportar items de menús
fputcsv($output, []);  // Línea en blanco como separador
fputcsv($output, ['menu_id', 'id', 'parent_id', 'titulo', 'url', 'orden', 'clase', 'target']);

foreach ($menus as $menu) {
    $items = $menuModel->obtenerItems($menu['id']);
    
    foreach ($items as $item) {
        $row = [
            $menu['id'],  // Agregamos el ID del menú para facilitar la importación
            $item['id'],
            $item['parent_id'],
            $item['titulo'],
            $item['url'],
            $item['orden'],
            $item['clase'],
            $item['target']
        ];
        
        fputcsv($output, $row);
    }
}

// Cerrar output stream
fclose($output);
exit;