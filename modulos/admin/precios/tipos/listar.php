<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php'; // tu control de acceso

$result = $conexion->query("SELECT * FROM plan_types ORDER BY profile_factor DESC");
include __DIR__ . '/../../../includes/header.php';
?>

<h2>Tipos de Plan</h2>
<a href="crear_editar.php" class="btn">Nuevo tipo</a>
<table class="table">
  <thead><tr>
    <th>ID</th><th>Slug</th><th>Nombre</th><th>Factor</th><th>Acciones</th>
  </tr></thead>
  <tbody>
  <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= htmlspecialchars($row['slug']) ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= $row['profile_factor'] ?></td>
      <td>
        <a href="crear_editar.php?id=<?= $row['id'] ?>">Editar</a> |
        <a href="borrar.php?id=<?= $row['id'] ?>" onclick="return confirm('Borrar?')">Borrar</a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
