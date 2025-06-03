<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../productos/modelo.php';
require_once 'modelo.php';

// Verificar si es una petición AJAX
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // No es AJAX, redirigir
    header('Location: ../../index.php');
    exit;
}

// Respuesta por defecto
$response = [
    'success' => false,
    'message' => 'Ocurrió un error al procesar la solicitud',
    'total_items' => 0,
    'redirigir' => 'false'
];

// Verificar si se proporcionaron datos
if(isset($_POST['id']) && isset($_POST['cantidad'])) {
    $producto_id = (int)$_POST['id'];
    $cantidad = (int)$_POST['cantidad'];
    $redirigir = isset($_POST['redirigir']) ? $_POST['redirigir'] : 'false';
    
    // Validar cantidad
    if($cantidad < 1) {
        $cantidad = 1;
    }
    
    // Obtener información del producto
    $producto = new Producto($conexion);
    $info_producto = $producto->obtenerPorId($producto_id);
    
    // Verificar que el producto existe
    if($info_producto) {
        // Agregar al carrito
     Carrito::agregar($producto_id, $info_producto['precio'], $cantidad);

        
        $response = [
            'success' => true,
            'message' => $info_producto['nombre'] . ' agregado al carrito',
            'total_items' => Carrito::contar(),
            'redirigir' => $redirigir
        ];
    } else {
        $response['message'] = 'Producto no encontrado';
    }
} else {
    $response['message'] = 'Datos incompletos';
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>