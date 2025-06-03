<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) {
  redireccionar('login');
}

// 1) Traer todas las categorías con su imagen
$stmt = $conexion->prepare("
  SELECT id, nombre, imagen
  FROM categorias
  ORDER BY id DESC
");
$stmt->execute();
$cats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Título y header
$titulo = "Categorías";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-list">
  <!-- Botón para crear nueva categoría -->
  <a href="<?= URL_SITIO ?>modulos/admin/categorias/crear.php"
     class="btn btn-primary">
    Nueva categoría
  </a>

  <table class="table" style="width:100%; margin-top:1rem; border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left; padding:.5rem; border-bottom:1px solid #ddd;">ID</th>
        <th style="text-align:left; padding:.5rem; border-bottom:1px solid #ddd;">Imagen</th>
        <th style="text-align:left; padding:.5rem; border-bottom:1px solid #ddd;">Nombre</th>
        <th style="text-align:left; padding:.5rem; border-bottom:1px solid #ddd;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cats as $cat): ?>
      <tr>
        <td style="padding:.5rem; border-bottom:1px solid #eee;">
          <?= $cat['id'] ?>
        </td>
        <td style="padding:.5rem; border-bottom:1px solid #eee;">
          <?php if (!empty($cat['imagen'])): ?>
            <img src="<?= URL_SITIO . $cat['imagen'] ?>"
                 alt=""
                 style="width:50px; height:auto; border-radius:4px;">
          <?php else: ?>
            <span style="color:#999; font-size:.9rem;">—</span>
          <?php endif; ?>
        </td>
        <td style="padding:.5rem; border-bottom:1px solid #eee;">
          <?= htmlspecialchars($cat['nombre'], ENT_QUOTES) ?>
        </td>
        <td style="padding:.5rem; border-bottom:1px solid #eee;">
          <!-- Enlace “Editar” -->
          <a href="<?= URL_SITIO ?>modulos/admin/categorias/crear.php?id=<?= $cat['id'] ?>"
             title="Editar categoría"
             style="margin-right:8px;">
            <i class="fas fa-pencil-alt"></i>
          </a>
          <!-- Enlace “Eliminar” -->
          <a href="<?= URL_SITIO ?>modulos/admin/categorias/borrar.php?id=<?= $cat['id'] ?>"
             title="Eliminar categoría"
             onclick="return confirm('¿Eliminar esta categoría?');">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
