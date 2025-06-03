<?php
// development only: mostrar errores
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// permitir includes
define('ACCESO_PERMITIDO', true);

// cargar config y helpers
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// restringir a admin
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login.php');
}

$titulo = "Panel Admin";
include __DIR__ . '/../../includes/header.php';
?>
<div class="container">
  <h1>🔧 Panel Admin</h1>
  <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></p>
  <div class="admin-buttons">
    <a href="<?= URL_SITIO ?>admin/productos/listar" class="btn">Listar productos</a>
    <a href="<?= URL_SITIO ?>admin/productos/crear"  class="btn">Crear producto</a>
    <a href="<?= URL_SITIO ?>admin/categorias/listar" class="btn">Listar categorías</a>
    <a href="<?= URL_SITIO ?>admin/categorias/crear"  class="btn">Crear categoría</a>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
