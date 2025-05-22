<?php
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// Verificar permisos
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Exportar categorías
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="categorias.csv"');

$out = fopen('php://output', 'w');
// Encabezados CSV
fputcsv($out, ['id', 'nombre', 'descripcion']);

// Obtener todas las categorías
$rs = $conexion->query("SELECT * FROM categorias");
while ($r = $rs->fetch_assoc()) {
    fputcsv($out, $r);
}

fclose($out);
exit;