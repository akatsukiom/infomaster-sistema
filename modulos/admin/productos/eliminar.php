<?php
// modulos/admin/productos/borrar.php

// 1) Permitir includes seguros
define('ACCESO_PERMITIDO', true);

// 2) Cargar configuración y funciones generales
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// 3) Verificar que el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    mostrarMensaje("Acceso denegado", 'error');
    redireccionar('login');
    exit;
}

// 4) Validar que venga un ID válido por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    mostrarMensaje("ID de producto no proporcionado o inválido", 'error');
    redireccionar('admin/productos/listar');
    exit;
}
$productoId = (int)$_GET['id'];

// 5) Cargar el modelo de Producto
require_once __DIR__ . '/modelo.php';
$model = new Producto($conexion);

// 6) Antes de borrar, obtener la fila para saber si tiene imagen
$producto = $model->obtenerPorId($productoId);
if (!$producto) {
    mostrarMensaje("Producto no encontrado", 'error');
    redireccionar('admin/productos/listar');
    exit;
}

// 7) Borrar archivo de imagen si existe
if (!empty($producto['imagen'])) {
    // Ajusta la ruta según dónde guardes tus imágenes
    $rutaImg = __DIR__ . '/../../../' . ltrim($producto['imagen'], '/');
    if (is_file($rutaImg)) {
        @unlink($rutaImg);
    }
}

// 8) Intentar borrar el registro de la BD
if ($model->eliminar($productoId)) {
    mostrarMensaje("Producto eliminado correctamente", 'success');
} else {
    mostrarMensaje("Error al eliminar el producto", 'error');
}

// 9) Redireccionar de vuelta al listado
redireccionar('admin/productos/listar');
