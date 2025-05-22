<?php
// Permitir includes
define('ACCESO_PERMITIDO', true);

// Cargar configuración y funciones
+ require_once __DIR__ . '/../../../includes/config.php';
+ require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar que el usuario está logueado y es admin
if (!estaLogueado() || !esAdmin()) {
    mostrarMensaje("Acceso denegado", 'error');
    redireccionar('modulos/usuarios/login.php');
    exit;
}

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    mostrarMensaje("ID de producto no proporcionado", 'error');
    redireccionar('admin/productos/listar');
    exit;
}

$producto_id = (int)$_GET['id'];

// Instanciar el modelo
$adminProd = new AdminProducto($conexion);

// Intentar borrar el producto
if ($adminProd->borrar($producto_id)) {
    mostrarMensaje("Producto eliminado correctamente", 'success');
} else {
    mostrarMensaje("Error al eliminar el producto", 'error');
}

// IMPORTANTE: Redireccionar de vuelta al listado de productos
// Usamos la URL completa directamente para evitar cualquier problema con la función redireccionar
header("Location: " . URL_SITIO . "admin/productos/listar");
exit;