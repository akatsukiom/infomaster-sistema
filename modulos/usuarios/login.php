<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// Si ya está logueado, ir al perfil
if (estaLogueado()) {
    redireccionar('perfil');
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = limpiarDato($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1) Validar campos
    if (empty($email))    $errores[] = "El email es obligatorio";
    if (empty($password)) $errores[] = "La contraseña es obligatoria";

    // 2) Si no hay errores, buscar usuario
    if (empty($errores)) {
        $stmt = $conexion->prepare(
            "SELECT id, nombre, password, rol, saldo 
               FROM usuarios 
              WHERE email = ? 
              LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $usuario = $res->fetch_assoc();
            // 3) Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // 4) Guardar datos en la sesión, ¡incluyendo el rol!
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol']    = $usuario['rol'];  // <- Esto es clave
                $_SESSION['usuario_saldo']   = (float) $usuario['saldo'];  // <— Esto es clave

                // 5) Redirigir al panel o perfil
                redireccionar('perfil');
            } else {
                $errores[] = "Contraseña incorrecta";
            }
        } else {
            $errores[] = "No existe ningún usuario con ese email";
        }
        $stmt->close();
    }
}

// Incluir tu header y mostrar el form de login
$titulo = "Iniciar sesión";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
  <h1>Iniciar sesión</h1>
  <?php if ($errores): ?>
    <div class="errores">
      <?php foreach ($errores as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn">Iniciar sesión</button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>