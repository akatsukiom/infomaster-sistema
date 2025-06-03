<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php';

// Recuperar todas las duraciones
$result = $conexion->query("SELECT months, rate FROM duration_rates ORDER BY months ASC");

include __DIR__ . '/../../../includes/header.php';
?>

<h2>Tarifas por Duración</h2>
<a href="crear_editar.php" class="btn">Nueva duración</a>

<table class="table">
  <thead>
    <tr>
      <th>Meses</th>
      <th>Factor / Tasa</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['months'] ?></td>
        <td><?= $row['rate'] ?></td>
        <td>
          <a href="crear_editar.php?months=<?= $row['months'] ?>">Editar</a> |
          <a href="borrar.php?months=<?= $row['months'] ?>"
             onclick="return confirm('¿Eliminar <?= $row['months'] ?> mes(es)?')"
          >Borrar</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
