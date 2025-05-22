<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    mostrarMensaje('ID de menú no válido', 'error');
    redireccionar('admin/menus/listar');
    exit;
}

$menu_id = (int)$_GET['id'];
$menuModel = new Menu($conexion);

// Eliminar el menú
$ok = $menuModel->eliminar($menu_id);

if ($ok) {
    mostrarMensaje('Menú eliminado correctamente', 'success');
} else {
    mostrarMensaje('Error al eliminar el menú', 'error');
}

// Redireccionar de vuelta al listado
redireccionar('admin/menus/listar');