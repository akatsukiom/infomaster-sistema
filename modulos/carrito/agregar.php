<?php
// modulos/carrito/agregar.php

// Evitar acceso directo
define('ACCESO_PERMITIDO', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../productos/modelo.php';
require_once __DIR__ . '/modelo.php';

session_start();

// 1) Recoger datos del formulario o query string
$producto_id = isset($_REQUEST['id'])       ? (int)  $_REQUEST['id']       : 0;
$cantidad    = isset($_REQUEST['cantidad']) ? max(1, (int) $_REQUEST['cantidad']) : 1;
$duracion    = isset($_REQUEST['duracion']) && is_numeric($_REQUEST['duracion'])
               ? (int) $_REQUEST['duracion']
               : 1;
$tipo_plan   = isset($_REQUEST['tipo_plan']) && in_array($_REQUEST['tipo_plan'], ['individual','completo'])
               ? $_REQUEST['tipo_plan']
               : 'individual';
// Ruta a la que redirigir tras agregar (sin barra inicial)
$redirigir   = isset($_REQUEST['redirigir'])
               ? ltrim($_REQUEST['redirigir'], '/')
               : 'carrito/ver';

// 2) Validar producto
if ($producto_id < 1) {
    mostrarMensaje('Producto no válido', 'error');
    header('Location: ' . URL_SITIO . 'productos');
    exit;
}

$producto      = new Producto($conexion);
$info_producto = $producto->obtenerPorId($producto_id);
if (! $info_producto) {
    mostrarMensaje('Producto no encontrado', 'error');
    header('Location: ' . URL_SITIO . 'productos');
    exit;
}

// 3) Calcular precios dinámicos

// 3.1) Factor de plan desde plan_types
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

// 3.2) Tasa por duración desde duration_rates
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

// 3.3) Precio total = unitario × meses × factor de plan × tasa de duración
$precio_unitario = (float) $info_producto['precio'];
$precio_total    = $precio_unitario
                  * $duracion
                  * $planFactor
                  * $duracionFactor;

// 4) Agregar al carrito, incluyendo las opciones
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

// 5) Redirigir al usuario a la ruta configurada
header('Location: ' . URL_SITIO . $redirigir);
exit;
