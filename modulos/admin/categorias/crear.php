<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) redireccionar('login');

$errors = [];
$datos = ['nombre'=>'', 'descripcion'=>''];
// Si viene ?id= editamos
$edit = !empty($_GET['id']);
if ($edit) {
  $stmt = $conexion->prepare("SELECT * FROM categorias WHERE id=?");
  $stmt->bind_param('i',$_GET['id']);
  $stmt->execute();
  $datos = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $datos['nombre']      = limpiarDato($_POST['nombre'] ?? '');
  $datos['descripcion'] = limpiarDato($_POST['descripcion'] ?? '');

  if ($datos['nombre']==='') $errors[] = "El nombre es obligatorio.";

  if (empty($errors)) {
    if ($edit) {
      $stmt = $conexion->prepare("UPDATE categorias SET nombre=?, descripcion=? WHERE id=?");
      $stmt->bind_param('ssi',$datos['nombre'],$datos['descripcion'],$_GET['id']);
      $ok = $stmt->execute();
    } else {
      $stmt = $conexion->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?,?)");
      $stmt->bind_param('ss',$datos['nombre'],$datos['descripcion']);
      $ok = $stmt->execute();
    }
    $stmt->close();
    if ($ok) {
      mostrarMensaje($edit ? "Categoría actualizada" : "Categoría creada",'success');
      redireccionar('admin/categorias/listar');
    } else {
      $errors[] = "Error en la base de datos.";
    }
  }
}

$titulo = $edit ? "Editar categoría" : "Crear categoría";
include __DIR__ . '/../../../includes/header.php';
?>
<div class="container admin-form">
  <h1><?= htmlspecialchars($titulo) ?></h1>
  <?php if($errors): ?>
    <div class="alert alert-error">
      <?php foreach($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Nombre</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>">
    </div>
    <div class="form-group">
      <label>Descripción</label>
      <textarea name="descripcion" rows="3"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
    </div>
    <button class="btn" type="submit"><?= $edit ? 'Actualizar' : 'Crear' ?></button>
  </form>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>