<?php
// modulos/carrito/agregar.php

// 0) Permitir acceso directo
define('ACCESO_PERMITIDO', true);

session_start();

// 1) Cargar configuración y clases
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../productos/modelo.php';
require_once __DIR__ . '/modelo.php';

// 2) Recoger id, cantidad, opciones y ruta de redirección
$producto_id = isset($_REQUEST['id'])       ? (int) $_REQUEST['id'] : 0;
$cantidad    = isset($_REQUEST['cantidad']) ? max(1, (int) $_REQUEST['cantidad']) : 1;
$duracion    = isset($_REQUEST['duracion']) && is_numeric($_REQUEST['duracion'])
               ? (int) $_REQUEST['duracion']
               : 1;
$tipo_plan   = isset($_REQUEST['tipo_plan']) && in_array($_REQUEST['tipo_plan'], ['individual','completo'])
               ? $_REQUEST['tipo_plan']
               : 'individual';
// aquí recogemos lo que le pasaste en el enlace productos.php?categoria=XX
$redirigir   = isset($_REQUEST['redirigir'])
               ? ltrim($_REQUEST['redirigir'], '/')
               : 'carrito/ver';

// 3) Validar que el producto exista
if ($producto_id < 1) {
    mostrarMensaje('Producto no válido', 'error');
    header('Location: ' . URL_SITIO . 'productos');
    exit;
}
$productoModel   = new Producto($conexion);
$info_producto   = $productoModel->obtenerPorId($producto_id);
if (! $info_producto) {
    mostrarMensaje('Producto no encontrado', 'error');
    header('Location: ' . URL_SITIO . 'productos');
    exit;
}

// 4) Calcular precio total según plan y duración
//    (tu lógica de plan_types y duration_rates queda igual)
$stmt = $conexion->prepare("
    SELECT profile_factor
      FROM plan_types
     WHERE slug = ?
");
$stmt->bind_param("s", $tipo_plan);
$stmt->execute();
$stmt->bind_result($planFactor);
$stmt->fetch();
$stmt->close();
$planFactor = $planFactor ?: 1;

$stmt = $conexion->prepare("
    SELECT rate
      FROM duration_rates
     WHERE months = ?
");
$stmt->bind_param("i", $duracion);
$stmt->execute();
$stmt->bind_result($duracionFactor);
$stmt->fetch();
$stmt->close();
$duracionFactor = $duracionFactor ?: 1.0;

$precio_unitario = (float) $info_producto['precio_base'];
$precio_total    = $precio_unitario * $duracion * $planFactor * $duracionFactor;

// 5) Agregar al carrito
Carrito::agregar(
    $producto_id,
    $precio_total,
    $cantidad,
    [
      'duracion'  => $duracion,
      'tipo_plan' => $tipo_plan
    ]
);

mostrarMensaje($info_producto['nombre'] . ' agregado al carrito', 'success');

// 6) Redirigir de vuelta a la lista de productos de esa categoría
header('Location: ' . URL_SITIO . $redirigir);
exit;
