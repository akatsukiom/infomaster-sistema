<?php
// modulos/admin/productos/listar.php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// Verificar que el usuario esté logueado y sea administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar(URL_SITIO . 'login');
}

// Cargar el modelo y obtener todos los productos
require_once __DIR__ . '/modelo.php';
$model = new Producto($conexion);
$productos = $model->obtenerTodos();

$titulo = 'Listado de Productos';
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form">
  <h1><?= $titulo ?></h1>

  <p>
    <a href="<?= URL_SITIO ?>modulos/admin/productos/crear_editar.php" class="btn btn-primary">
      + Nuevo producto
    </a>
  </p>

  <?php if (empty($productos)): ?>
    <div class="alert alert-info">
      No hay productos para mostrar.
    </div>
  <?php else: ?>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Precio base</th>
          <th>Categoría</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= MONEDA . number_format($p['precio_base'], 2) ?></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
            <td>
              <a
                href="<?= URL_SITIO ?>modulos/admin/productos/crear_editar.php?id=<?= $p['id'] ?>"
                class="btn btn-sm btn-warning"
                title="Editar"
              >✏️</a>

              <a
                href="<?= URL_SITIO ?>modulos/admin/productos/eliminar.php?id=<?= $p['id'] ?>"
                class="btn btn-sm btn-danger"
                title="Eliminar"
                onclick="return confirm('¿Eliminar producto “<?= htmlspecialchars(addslashes($p['nombre'])) ?>”?');"
              >🗑️</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
