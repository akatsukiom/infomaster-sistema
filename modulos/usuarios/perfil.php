<?php
// Mostrar errores en desarrollo (quita en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

// 2) Cargar config y funciones
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// 3) Cargar modelos
require_once __DIR__ . '/modelo.php';               // Usuario
require_once __DIR__ . '/../wallet/modelo.php';     // Wallet
require_once __DIR__ . '/../entregas/modelo.php';   // Entrega

// 4) Verificar si el usuario está logueado
if (!estaLogueado()) {
    redireccionar('login');
}

// 5) Instanciar servicios
$usuarioService = new Usuario($conexion);
$walletService  = new Wallet($conexion);
$entregaService = new Entrega($conexion);

$usuario_id = $_SESSION['usuario_id'];
$errores    = [];
$exito      = null;

// 6) Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, nombre, email, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$datos_usuario = $result->fetch_assoc();
$stmt->close();

// 7) Obtener transacciones y entregas
$transacciones = $walletService->obtenerHistorial($usuario_id, 5);
$entregas      = $entregaService->obtenerPorUsuario($usuario_id);

// 8) Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'actualizar_perfil') {
        $nombre = limpiarDato($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            $errores[] = "El nombre es obligatorio";
        }
        if (empty($errores)) {
            $res = $usuarioService->actualizarPerfil($usuario_id, $nombre);
            if (isset($res['success'])) {
                $exito = $res['success'];
                $_SESSION['usuario_nombre'] = $nombre;
                $datos_usuario['nombre'] = $nombre;
            } else {
                $errores[] = $res['error'];
            }
        }

    } elseif ($accion === 'cambiar_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current))    $errores[] = "La contraseña actual es obligatoria";
        if (empty($new))        $errores[] = "La nueva contraseña es obligatoria";
        elseif (strlen($new) < 6) $errores[] = "Debe tener al menos 6 caracteres";
        if ($new !== $confirm)   $errores[] = "Las contraseñas no coinciden";

        if (empty($errores)) {
            $res = $usuarioService->cambiarPassword($usuario_id, $current, $new);
            if (isset($res['success'])) {
                $exito = $res['success'];
            } else {
                $errores[] = $res['error'];
            }
        }
    }
}

// 9) Incluir header
$titulo = "Mi Perfil";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
  <div class="perfil-container">

    <!-- Cabecera de Perfil -->
    <div class="perfil-header">
      <img src="<?= URL_SITIO ?>img/avatar-default.jpg"
           alt="<?= htmlspecialchars($datos_usuario['nombre']) ?>"
           class="perfil-avatar">
      <div class="perfil-info">
        <h1><?= htmlspecialchars($datos_usuario['nombre']) ?></h1>
        <p><?= htmlspecialchars($datos_usuario['email']) ?></p>
        <p>Miembro desde: <?= date('d/m/Y', strtotime($datos_usuario['fecha_registro'])) ?></p>
      </div>
    </div>

    <!-- Mensajes -->
    <?php if (!empty($errores)): ?>
      <div class="errores">
        <?php foreach ($errores as $error): ?>
          <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($exito): ?>
      <div class="exito">
        <p><?= htmlspecialchars($exito) ?></p>
      </div>
    <?php endif; ?>

    <!-- Pestañas de Perfil -->
    <div class="perfil-tabs">
      <a href="#info" class="perfil-tab active">Información</a>
      <a href="#seguridad" class="perfil-tab">Seguridad</a>
      <a href="#entregas" class="perfil-tab">Mis Entregas</a>
      <a href="#transacciones" class="perfil-tab">Transacciones</a>
    </div>

    <div class="perfil-content">
      <!-- Información Personal -->
      <div id="info" class="perfil-section active">
        <h2>Información Personal</h2>
        <form method="POST" class="editar-perfil-form">
          <input type="hidden" name="accion" value="actualizar_perfil">
          <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($datos_usuario['nombre']) ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email"
                   value="<?= htmlspecialchars($datos_usuario['email']) ?>" disabled>
            <small>No es posible cambiar el email.</small>
          </div>
          <button type="submit" class="btn">Guardar Cambios</button>
        </form>
      </div>

      <!-- Cambiar Contraseña -->
      <div id="seguridad" class="perfil-section">
        <h2>Cambiar Contraseña</h2>
        <form method="POST" class="cambiar-password-form">
          <input type="hidden" name="accion" value="cambiar_password">
          <div class="form-group">
            <label for="current_password">Contraseña actual</label>
            <input type="password" id="current_password" name="current_password" required>
          </div>
          <div class="form-group">
            <label for="new_password">Nueva contraseña</label>
            <input type="password" id="new_password" name="new_password" required>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirmar nueva contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn">Cambiar Contraseña</button>
        </form>
      </div>

      <!-- Mis Entregas -->
      <div id="entregas" class="perfil-section">
        <h2>Mis Entregas</h2>
        <?php if (empty($entregas)): ?>
          <p>No tienes entregas activas. <a href="<?= URL_SITIO ?>productos">Explorar productos</a></p>
        <?php else: ?>
          <div class="entregas-grid">
            <?php foreach ($entregas as $item): ?>
              <div class="entrega-card">
                <img src="<?= $item['imagen']
                            ? URL_SITIO . $item['imagen']
                            : URL_SITIO . 'img/producto-default.jpg' ?>"
                     alt="<?= htmlspecialchars($item['producto_nombre']) ?>"
                     class="entrega-icon">
                <div class="entrega-info">
                  <h3><?= htmlspecialchars($item['producto_nombre']) ?></h3>
                  <p class="entrega-fecha">
                    Entregado: <?= date('d/m/Y H:i', strtotime($item['fecha_entrega'])) ?>
                  </p>
                  <a href="<?= URL_SITIO ?>modulos/usuarios/mis-entregas?id=<?= (int)$item['id'] ?>"
                     class="btn btn-small">Ver Detalles</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="ver-mas">
            <a href="<?= URL_SITIO ?>modulos/usuarios/mis-entregas" class="btn btn-outline">Ver todas mis entregas</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Transacciones -->
      <div id="transacciones" class="perfil-section">
        <h2>Últimas Transacciones</h2>
        <?php if (empty($transacciones)): ?>
          <p>No tienes transacciones recientes. <a href="<?= URL_SITIO ?>modulos/wallet/recargar">Recargar wallet</a></p>
        <?php else: ?>
          <table class="transaction-table">
            <thead>
              <tr>
                <th>Fecha</th><th>Tipo</th><th>Monto</th><th>Referencia</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transacciones as $tx): ?>
                <tr>
                  <td><?= date('d/m/Y H:i', strtotime($tx['fecha'])) ?></td>
                  <td>
                    <span class="transaction-type type-<?= htmlspecialchars($tx['tipo']) ?>">
                      <?= ucfirst(htmlspecialchars($tx['tipo'])) ?>
                    </span>
                  </td>
                  <td class="<?= $tx['tipo']=='recarga'?'amount-positive':'amount-negative' ?>">
                    <?= ($tx['tipo']=='recarga'?'+':'-') . MONEDA . number_format($tx['monto'],2) ?>
                  </td>
                  <td><?= htmlspecialchars($tx['referencia']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="ver-mas">
            <a href="<?= URL_SITIO ?>modulos/wallet/historial" class="btn btn-outline">Ver historial completo</a>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script>
// Control de pestañas
document.querySelectorAll('.perfil-tab').forEach(tab => {
  tab.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('.perfil-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.perfil-section').forEach(s => s.classList.remove('active'));
    this.classList.add('active');
    document.getElementById(this.getAttribute('href').substring(1)).classList.add('active');
  });
});
if (window.location.hash) {
  let tab = document.querySelector(`.perfil-tab[href="${window.location.hash}"]`);
  if (tab) tab.click();
}
</script>

<?php
// 10) Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>