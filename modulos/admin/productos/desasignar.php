<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) {
  redireccionar('login');
}

// Recibimos categoría y producto
$catId  = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$prodId = isset($_GET['producto' ]) ? (int)$_GET['producto']  : 0;

if ($catId && $prodId) {
  // Quitamos apenas la relación con la categoría
  $stmt = $conexion->prepare("
    UPDATE productos
       SET categoria_id = NULL
     WHERE id = ?
  ");
  $stmt->bind_param('i', $prodId);
  $stmt->execute();
  $stmt->close();

  mostrarMensaje("Producto desasignado de la categoría.", 'success');
}

// Volvemos al listado de esta categoría
redireccionar("admin/productos/listar.php?categoria={$catId}");
