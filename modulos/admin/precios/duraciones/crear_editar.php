<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php';

$months = isset($_GET['months']) ? (int)$_GET['months'] : 0;
$rate   = '';

if ($months > 0) {
    // Editar: cargar datos existentes
    $stmt = $conexion->prepare("SELECT rate FROM duration_rates WHERE months = ?");
    $stmt->bind_param("i", $months);
    $stmt->execute();
    $stmt->bind_result($rate);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger POST y sanitizar
    $m = isset($_POST['months']) ? (int)$_POST['months'] : 0;
    $r = isset($_POST['rate'])   ? floatval($_POST['rate']) : 0;
    
    if ($m <= 0 || $r <= 0) {
        mostrarMensaje('Meses y tasa deben ser valores positivos', 'error');
    } else {
        if ($months > 0) {
            // Actualizar
            $stmt = $conexion->prepare("
                UPDATE duration_rates 
                SET months = ?, rate = ?, updated_at = NOW() 
                WHERE months = ?
            ");
            $stmt->bind_param("idi", $m, $r, $months);
        } else {
            // Insertar
            $stmt = $conexion->prepare("
                INSERT INTO duration_rates (months, rate) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("id", $m, $r);
        }
        $stmt->execute();
        $stmt->close();

        header("Location: listar.php");
        exit;
    }
}

include __DIR__ . '/../../../includes/header.php';
?>

<h2><?= $months ? 'Editar' : 'Nueva' ?> Duración</h2>
<form method="POST">
  <div class="form-group">
    <label for="months">Meses</label>
    <input
      type="number"
      id="months"
      name="months"
      value="<?= $months ?>"
      min="1"
      class="form-control"
      <?= $months ? 'readonly' : '' /* no permitir cambiar PK en edición */ ?>
      required
    >
  </div>
  <div class="form-group">
    <label for="rate">Tasa (por ejemplo 1.00, 0.95, 0.85)</label>
    <input
      type="text"
      id="rate"
      name="rate"
      value="<?= htmlspecialchars($rate) ?>"
      class="form-control"
      required
      pattern="^\d+(\.\d{1,2})?$"
    >
  </div>
  <button type="submit" class="btn btn-primary">Guardar</button>
  <a href="listar.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
