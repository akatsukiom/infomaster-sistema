<?php
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
+ require_once __DIR__ . '/../../../includes/config.php';
+ require_once __DIR__ . '/../../../includes/funciones.php';

// Verificar permisos
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Exportar productos
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="productos.csv"');

$out = fopen('php://output', 'w');
// Encabezados CSV
fputcsv($out, ['id', 'categoria_id', 'nombre', 'descripcion', 'precio', 'stock', 'imagen']);

// Obtener todos los productos
$rs = $conexion->query("SELECT * FROM productos");
while ($r = $rs->fetch_assoc()) {
    fputcsv($out, $r);
}

fclose($out);
exit;