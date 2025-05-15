<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Instanciar modelo
$menuModel = new Menu($conexion);
$menus = $menuModel->obtenerTodos();

// Título de la página
$titulo = "Listar Menús";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form">
  <h1>Gestión de Menús</h1>

  <p>
    <a href="<?= URL_SITIO ?>modulos/admin/menus/crear.php" class="btn btn-primary">
      + Nuevo menú
    </a>
    <a href="<?= URL_SITIO ?>modulos/admin/menus/exportar.php" class="btn btn-outline">
      Exportar menús
    </a>
    <a href="#" onclick="document.getElementById('importForm').style.display='block'; return false;" class="btn btn-outline">
      Importar menús
    </a>
  </p>
  
  <!-- Formulario oculto para importación -->
  <div id="importForm" style="display:none; margin-bottom: 20px;">
    <form action="<?= URL_SITIO ?>modulos/admin/menus/importar.php" method="post" enctype="multipart/form-data">
      <p>Selecciona un archivo CSV:</p>
      <input type="file" name="archivo_csv" required accept=".csv">
      <button type="submit" class="btn">Importar</button>
      <button type="button" class="btn btn-outline" onclick="document.getElementById('importForm').style.display='none';">Cancelar</button>
    </form>
  </div>

  <?php if (empty($menus)): ?>
    <div class="alert alert-info">
      No hay menús para mostrar.
    </div>
  <?php else: ?>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Fecha de creación</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($menus as $m): ?>
          <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['nombre']) ?></td>
            <td><?= htmlspecialchars($m['descripcion']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($m['fecha_creacion'])) ?></td>
            <td>
              <a href="<?= URL_SITIO ?>modulos/admin/menus/editar.php?id=<?= $m['id'] ?>"
                 class="btn btn-sm btn-warning" title="Editar">✏️</a>
              <a href="<?= URL_SITIO ?>modulos/admin/menus/items.php?menu_id=<?= $m['id'] ?>"
                 class="btn btn-sm btn-info" title="Gestionar elementos">📋</a>
              <a href="<?= URL_SITIO ?>modulos/admin/menus/eliminar.php?id=<?= $m['id'] ?>"
                 class="btn btn-sm btn-danger" title="Eliminar"
                 onclick="return confirm('¿Estás seguro de que deseas eliminar este menú?');">🗑️</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>