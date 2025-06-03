<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $name = '';
$factor = 1;

if ($id) {
  $stmt = $conexion->prepare("SELECT slug,name,profile_factor FROM plan_types WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  $stmt->bind_result($slug,$name,$factor);
  $stmt->fetch();
  $stmt->close();
}

if ($_POST) {
  $slug   = $conexion->real_escape_string($_POST['slug']);
  $name   = $conexion->real_escape_string($_POST['name']);
  $factor = (int)$_POST['profile_factor'];

  if ($id) {
    $sql = "UPDATE plan_types SET slug='$slug',name='$name',profile_factor=$factor WHERE id=$id";
  } else {
    $sql = "INSERT INTO plan_types (slug,name,profile_factor)
            VALUES ('$slug','$name',$factor)";
  }
  $conexion->query($sql);
  header("Location: listar.php");
  exit;
}

include __DIR__ . '/../../../includes/header.php';
?>
<h2><?= $id ? 'Editar' : 'Nuevo' ?> Tipo de Plan</h2>
<form method="POST">
  <label>Slug:<input name="slug" value="<?= htmlspecialchars($slug) ?>"></label><br>
  <label>Nombre:<input name="name" value="<?= htmlspecialchars($name) ?>"></label><br>
  <label>Factor perfiles:<input type="number" name="profile_factor" value="<?= $factor ?>" min="1"></label><br>
  <button>Guardar</button>
</form>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
