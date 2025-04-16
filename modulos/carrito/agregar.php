<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../productos/modelo.php';
require_once 'modelo.php';

// Verificar si se proporcionó un ID de producto
if(!isset($_GET['id'])) {
    redireccionar('../../productos.php');
}

$producto_id = (int)$_GET['id'];
$cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;

// Verificar que la cantidad sea al menos 1
if($cantidad < 1) {
    $cantidad = 1;
}

// Obtener información del producto
$producto = new Producto($conexion);
$info_producto = $producto->obtenerPorId($producto_id);

// Verificar que el producto existe
if(!$info_producto) {
    mostrarMensaje('Producto no encontrado', 'error');
    redireccionar('../../productos.php');
}

// Agregar producto al carrito
Carrito::agregar($producto_id, $cantidad, $info_producto['precio']);

// Mostrar mensaje y redirigir
mostrarMensaje($info_producto['nombre'] . ' agregado al carrito', 'success');

// Redirigir a donde vino o al carrito
$redirigir = isset($_GET['redirigir']) ? $_GET['redirigir'] : 'ver.php';
redireccionar($redirigir);
?>