<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar acceso
if (!estaLogueado() || !esAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado']);
    exit;
}

// Verificar parámetro
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'mensaje' => 'ID inválido']);
    exit;
}

$item_id = (int)$_GET['id'];
$menuModel = new Menu($conexion);

// Obtener datos del item
$sql = "SELECT * FROM menu_items WHERE id = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

// Responder
header('Content-Type: application/json');
if ($item) {
    echo json_encode(['status' => 'success', 'item' => $item]);
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Item no encontrado']);
}