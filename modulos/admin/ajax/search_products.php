<?php
// search_products.php
header('Content-Type: application/json; charset=utf-8');

// Evitar accesos directos
define('ACCESO_PERMITIDO', true);

// Incluir config y funciones
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// Solo admin puede usarlo
if (!estaLogueado() || !esAdmin()) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// Parámetro de búsqueda
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Busca hasta 10 productos cuyo nombre contenga el query
$stmt = $conexion->prepare("
    SELECT id, nombre AS name, slug
    FROM productos
    WHERE nombre LIKE ?
    ORDER BY nombre
    LIMIT 10
");
$stmt->execute(["%{$q}%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver solo los campos necesarios
echo json_encode(array_map(function($r){
    return [
        'name' => $r['name'],
        'slug' => $r['slug']
    ];
}, $rows));
