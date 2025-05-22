<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) redireccionar('login');

$errors      = [];
$datos       = ['nombre'=>'', 'descripcion'=>''];
$rutaAntigua = null;
$edit        = !empty($_GET['id']);

// 1) Si viene ?id=... cargamos la categoría para editar
if ($edit) {
  $stmt = $conexion->prepare("SELECT * FROM categorias WHERE id=?");
  $stmt->bind_param('i', $_GET['id']);
  $stmt->execute();
  $datos = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

// **AQUÍ**: Aseguramos que descripción siempre sea string
//          Si vino NULL o no existe, la dejamos como cadena vacía.
$datos['descripcion'] = $datos['descripcion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 2) Limpiamos y guardamos nombre/descripcion (ya sin tags HTML)
  $datos['nombre']      = limpiarDato($_POST['nombre'] ?? '');
  // Usamos strip_tags() para eliminar cualquier etiqueta HTML
  $rawDesc             = $_POST['descripcion'] ?? '';
  $datos['descripcion'] = limpiarDato(strip_tags($rawDesc));

  // 3) Procesar imagen si se sube
  if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $tmp     = $_FILES['imagen']['tmp_name'];
    $destDir = __DIR__ . '/../../../img/categorias/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $nombreUnico         = uniqid('cat_', true) . '-' . basename($_FILES['imagen']['name']);
    $dest                = $destDir . $nombreUnico;
    if (move_uploaded_file($tmp, $dest)) {
      $rutaAntigua       = $datos['imagen'] ?? null;
      $datos['imagen']   = "img/categorias/$nombreUnico";
    }
  }

  // 4) Validaciones
  if ($datos['nombre'] === '') {
    $errors[] = "El nombre es obligatorio.";
  }

  // 5) Si no hay errores, INSERT o UPDATE
  if (empty($errors)) {
    if ($edit) {
      // UPDATE
      $sql = "UPDATE categorias SET nombre=?, descripcion=?";
      if (!empty($datos['imagen'])) {
        $sql .= ", imagen=?";
      }
      $sql .= " WHERE id=?";
      $stmt = $conexion->prepare($sql);

      if (!empty($datos['imagen'])) {
        $stmt->bind_param(
          'sssi',
          $datos['nombre'],
          $datos['descripcion'],
          $datos['imagen'],
          $_GET['id']
        );
      } else {
        $stmt->bind_param(
          'ssi',
          $datos['nombre'],
          $datos['descripcion'],
          $_GET['id']
        );
      }
      $ok = $stmt->execute();

    } else {
      // INSERT
      if (!empty($datos['imagen'])) {
        $stmt = $conexion->prepare(
          "INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?,?,?)"
        );
        $stmt->bind_param(
          'sss',
          $datos['nombre'],
          $datos['descripcion'],
          $datos['imagen']
        );
      } else {
        $stmt = $conexion->prepare(
          "INSERT INTO categorias (nombre, descripcion) VALUES (?,?)"
        );
        $stmt->bind_param(
          'ss',
          $datos['nombre'],
          $datos['descripcion']
        );
      }
      $ok = $stmt->execute();
    }

    $stmt->close();

    // 6) Si editamos y subimos nueva imagen, borramos la anterior
    if ($ok && $edit && !empty($rutaAntigua)) {
      @unlink(__DIR__ . '/../../../' . ltrim($rutaAntigua, '/'));
    }

    if ($ok) {
      mostrarMensaje($edit ? "Categoría actualizada" : "Categoría creada", 'success');
      redireccionar('admin/categorias/listar');
    } else {
      $errors[] = "Error en la base de datos.";
    }
  }
}

// Título de la página
$titulo = $edit ? "Editar categoría" : "Crear categoría";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form">
  <h1><?= htmlspecialchars($titulo) ?></h1>

  <?php if ($errors): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Nombre</label>
      <input
        type="text"
        name="nombre"
        value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES) ?>">
    </div>

    <div class="form-group">
      <label>Descripción</label>
      <textarea
        name="descripcion"
        rows="3"><?= htmlspecialchars($datos['descripcion'], ENT_QUOTES) ?></textarea>
    </div>

    <div class="form-group">
      <label>Imagen de categoría</label>
      <?php if ($edit && !empty($datos['imagen'])): ?>
        <div style="margin-bottom:.5rem;">
          <img
            src="<?= URL_SITIO . ltrim($datos['imagen'], '/') ?>"
            alt="Actual"
            style="max-width:120px;border:1px solid #ccc;">
        </div>
      <?php endif; ?>
      <input type="file" name="imagen" accept="image/*">
    </div>

    <button class="btn" type="submit">
      <?= $edit ? 'Actualizar' : 'Crear' ?>
    </button>
  </form>
  
<?php if (!empty($_GET['id'])): // estamos en edición ?>
  <div style="margin-top:1rem;">
    <a 
      href="<?= URL_SITIO ?>modulos/admin/productos/asignar.php?categoria=<?= (int)$_GET['id'] ?>"
      class="btn btn-outline">
      Agregar productos existentes
    </a>
  </div>
<?php endif; ?>
</div><!-- /.container -->



<?php include __DIR__ . '/../../../includes/footer.php'; ?>
