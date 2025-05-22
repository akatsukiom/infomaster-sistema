<?php
// modulos/admin/productos/asignar.php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) {
    redireccionar(URL_SITIO . 'login');
    exit;
}

// 1) Leer y validar categoría
$categoriaId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
if ($categoriaId <= 0) {
    mostrarMensaje('Categoría no válida', 'error');
    redireccionar('admin/categorias/listar');
    exit;
}

// 2) Nombre de la categoría (opcional, para el título)
$stmt = $conexion->prepare("SELECT nombre FROM categorias WHERE id = ?");
$stmt->bind_param('i', $categoriaId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$tituloCat = $row['nombre'] ?? '—';
$stmt->close();

// 3) Procesar el POST: asignar los productos marcados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toAssign = $_POST['productos'] ?? [];
    if (count($toAssign) > 0) {
        // sanea e implode
        $ids = implode(',', array_map('intval', $toAssign));
        // Nota: mysqli no permite bind_param en IN(), por eso inyectamos directo tras sanitizar.
        $sql = "UPDATE productos SET categoria_id = ? WHERE id IN ($ids)";
        $upd = $conexion->prepare($sql);
        $upd->bind_param('i', $categoriaId);
        $upd->execute();
        $upd->close();
    }
    mostrarMensaje('Productos asignados correctamente', 'success');
    redireccionar("admin/productos/listar.php?categoria=$categoriaId");
    exit;
}

// 4) Cargar todos los productos que NO estén ya en esta categoría
$stmt = $conexion->prepare("
    SELECT id, nombre 
      FROM productos 
     WHERE categoria_id != ? OR categoria_id IS NULL
  ORDER BY nombre
");
$stmt->bind_param('i', $categoriaId);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5) Pintar la vista
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form">
  <h1>Asignar productos a «<?= htmlspecialchars($tituloCat) ?>»</h1>

  <?php if (empty($productos)): ?>
    <div class="alert alert-info">
      No hay productos disponibles para asignar.
    </div>
  <?php else: ?>
    <form method="POST">
      <table class="table">
        <thead>
          <tr><th>✔</th><th>ID</th><th>Nombre</th></tr>
        </thead>
        <tbody>
          <?php foreach ($productos as $p): ?>
          <tr>
            <td><input type="checkbox" name="productos[]" value="<?= $p['id'] ?>"></td>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button class="btn btn-primary">Asignar seleccionados</button>
      <a href="<?= URL_SITIO ?>modulos/admin/productos/listar.php?categoria=<?= $categoriaId ?>"
         class="btn btn-outline">← Volver al listado</a>
    </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
