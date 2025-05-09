<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) redireccionar('login');

// Traer todas las categorías
$rs = $conexion->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $rs->fetch_all(MYSQLI_ASSOC);

$titulo = "Listar categorías";
include __DIR__ . '/../../../includes/header.php';
?>
<div class="container admin-form">
  <h1>Categorías</h1>
  <p><a href="<?= URL_SITIO ?>admin/categorias/crear" class="btn">Nueva categoría</a></p>
  <table class="table">
    <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
    <?php foreach($categorias as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['nombre']) ?></td>
        <td>
          <a href="<?= URL_SITIO ?>admin/categorias/crear?id=<?= $c['id'] ?>">✏️</a>
          <a href="<?= URL_SITIO ?>admin/categorias/borrar?id=<?= $c['id'] ?>"
             onclick="return confirm('¿Borrar esta categoría?')">🗑️</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>