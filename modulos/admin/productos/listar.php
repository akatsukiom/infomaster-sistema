<?php
// modulos/admin/productos/listar.php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

if (!estaLogueado() || !esAdmin()) {
    redireccionar(URL_SITIO . 'login');
}

// 1) Recoger posible filtro de categoría
$categoriaId   = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$categoriaName = '';

if ($categoriaId > 0) {
    $stmt = $conexion->prepare("SELECT nombre FROM categorias WHERE id = ?");
    $stmt->bind_param('i', $categoriaId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $categoriaName = $row['nombre'] ?? '';
    $stmt->close();
}

// 2) Cargar productos (filtrados o no)
require_once __DIR__ . '/modelo.php';
$model     = new Producto($conexion);
$productos = $model->obtenerTodos($categoriaId);

// 3) Título dinámico
$titulo = $categoriaId
    ? "Productos en «" . htmlspecialchars($categoriaName, ENT_QUOTES) . "»"
    : "Listado de Productos";

include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-list">
  <h1><?= $titulo ?></h1>

  <!-- Nuevo producto -->
  <p>
    <a
      href="<?= URL_SITIO ?>modulos/admin/productos/crear_editar.php<?= $categoriaId ? '?categoria='.$categoriaId : '' ?>"
      class="btn btn-primary">
      + Nuevo producto
    </a>
  </p>

  <?php if (empty($productos)): ?>
    <div class="alert alert-info">
      <?php if ($categoriaId): ?>
        No hay productos en esta categoría.
      <?php else: ?>
        No hay productos para mostrar.
      <?php endif; ?>
    </div>
  <?php else: ?>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <?php if (!$categoriaId): ?>
            <th>Categoría</th>
          <?php endif; ?>
          <th>Precio base</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?></td>
            <?php if (!$categoriaId): ?>
              <td><?= htmlspecialchars($p['categoria'], ENT_QUOTES) ?></td>
            <?php endif; ?>
            <td><?= MONEDA . number_format($p['precio_base'], 2) ?></td>
            <td>
              <!-- Editar -->
              <a
                href="<?= URL_SITIO ?>modulos/admin/productos/crear_editar.php?id=<?= $p['id'] ?>"
                class="btn btn-sm btn-warning"
                title="Editar">
                ✏️
              </a>

              <?php if ($categoriaId): ?>
                <!-- Desasignar de esta categoría -->
                <a
                  href="<?= URL_SITIO ?>modulos/admin/productos/desasignar.php?categoria=<?= $categoriaId ?>&producto=<?= $p['id'] ?>"
                  class="btn btn-sm btn-outline-secondary"
                  title="Quitar de esta categoría"
                  onclick="return confirm('¿Quitar “<?= htmlspecialchars(addslashes($p['nombre']), ENT_QUOTES) ?>” de la categoría?');">
                  🔗
                </a>
              <?php else: ?>
                <!-- Borrar definitivamente -->
                <a
                  href="<?= URL_SITIO ?>modulos/admin/productos/eliminar.php?id=<?= $p['id'] ?>"
                  class="btn btn-sm btn-danger"
                  title="Eliminar"
                  onclick="return confirm('¿Eliminar “<?= htmlspecialchars(addslashes($p['nombre']), ENT_QUOTES) ?>”?');">
                  🗑️
                </a>
              <?php endif; ?>

            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <p style="margin-top:1rem;">
    <a
      href="<?= URL_SITIO ?>modulos/admin/categorias/listar.php"
      class="btn btn-outline">
      ← Volver a categorías
    </a>
  </p>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
