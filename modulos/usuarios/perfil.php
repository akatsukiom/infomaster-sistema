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
$mostrarModalAvatar = false;

// Recuperar mensaje de éxito de la sesión (si existe)
if (isset($_SESSION['perfil_exito'])) {
    $exito = $_SESSION['perfil_exito'];
    unset($_SESSION['perfil_exito']);
}

// 6) Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, nombre, email, fecha_registro, avatar FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$datos_usuario = $result->fetch_assoc();
$stmt->close();

// Si hay un avatar actualizado en la sesión, actualizar los datos del usuario
if (isset($_SESSION['perfil_avatar'])) {
    $datos_usuario['avatar'] = $_SESSION['perfil_avatar'];
    unset($_SESSION['perfil_avatar']);
}

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
                $_SESSION['perfil_exito'] = $res['success'];
                $_SESSION['usuario_nombre'] = $nombre;
                // Redirigir para evitar reenvío de formulario
                header('Location: ' . URL_SITIO . 'modulos/usuarios/perfil.php#info');
                exit;
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
                $_SESSION['perfil_exito'] = $res['success'];
                // Redirigir para evitar reenvío de formulario
                header('Location: ' . URL_SITIO . 'modulos/usuarios/perfil.php#seguridad');
                exit;
            } else {
                $errores[] = $res['error'];
            }
        }
    } elseif ($accion === 'cambiar_avatar') {
        // Procesar cambio de avatar
        if (isset($_FILES) && !empty($_FILES) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['avatar']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $errores[] = "Tipo de archivo no permitido. Use JPG, PNG o GIF";
                $mostrarModalAvatar = true;
            } else {
                $res = $usuarioService->procesarAvatar($usuario_id, $_FILES);
                if (isset($res['success'])) {
                    // Guardamos el éxito en sesión para mostrarlo tras el redirect
                    $_SESSION['perfil_exito'] = $res['success'];
                    
                    // También actualizamos el avatar en sesión
                    if (isset($res['avatar_path'])) {
                        $_SESSION['perfil_avatar'] = $res['avatar_path'];
                    }
                    
                    // REDIRECT para limpiar el POST y prevenir reenvío
                    header('Location: ' . URL_SITIO . 'modulos/usuarios/perfil.php#info');
                    exit;
                } else {
                    $errores[] = $res['error'];
                    $mostrarModalAvatar = true;
                }
            }
        } else {
            $errores[] = "No se ha seleccionado ninguna imagen o hubo un error al subirla";
            $mostrarModalAvatar = true;
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
      <div class="avatar-container" onclick="openImageModal()">
        <img src="<?= !empty($datos_usuario['avatar']) 
                    ? htmlspecialchars(URL_SITIO . $datos_usuario['avatar'])
                    : htmlspecialchars(URL_SITIO . 'img/avatar-default.jpg') ?>"
             alt="<?= htmlspecialchars($datos_usuario['nombre']) ?>"
             class="perfil-avatar">
        <div class="perfil-avatar-overlay">
          <i class="fas fa-camera"></i>
        </div>
      </div>
      <div class="perfil-info">
        <h1><?= htmlspecialchars($datos_usuario['nombre']) ?></h1>
        <p><?= htmlspecialchars($datos_usuario['email']) ?></p>
        <p class="member-since">Miembro desde: <?= htmlspecialchars(date('d/m/Y', strtotime($datos_usuario['fecha_registro']))) ?></p>
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
      <a href="#info" class="perfil-tab active" onclick="cambiarTab(this, 'info')">Información</a>
      <a href="#seguridad" class="perfil-tab" onclick="cambiarTab(this, 'seguridad')">Seguridad</a>
      <a href="#entregas" class="perfil-tab" onclick="cambiarTab(this, 'entregas')">Mis Entregas</a>
      <a href="#transacciones" class="perfil-tab" onclick="cambiarTab(this, 'transacciones')">Transacciones</a>
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
            <small>La contraseña debe tener al menos 6 caracteres.</small>
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
          <p>No tienes entregas activas. <a href="<?= htmlspecialchars(URL_SITIO) ?>productos">Explorar productos</a></p>
        <?php else: ?>
          <div class="entregas-grid">
            <?php foreach ($entregas as $item): ?>
              <div class="entrega-card">
                <img src="<?= isset($item['imagen']) && $item['imagen']
                            ? htmlspecialchars(URL_SITIO . $item['imagen'])
                            : htmlspecialchars(URL_SITIO . 'img/producto-default.jpg') ?>"
                     alt="<?= htmlspecialchars($item['producto_nombre']) ?>"
                     class="entrega-icon">
                <div class="entrega-info">
                  <h3><?= htmlspecialchars($item['producto_nombre']) ?></h3>
                  <p class="entrega-fecha">
                    Entregado: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['fecha_entrega']))) ?>
                  </p>
                  <a href="<?= htmlspecialchars(URL_SITIO) ?>modulos/usuarios/mis-entregas?id=<?= (int)$item['id'] ?>"
                     class="btn btn-small">Ver Detalles</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="ver-mas">
            <a href="<?= htmlspecialchars(URL_SITIO) ?>modulos/usuarios/mis-entregas" class="btn btn-outline">Ver todas mis entregas</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Transacciones -->
      <div id="transacciones" class="perfil-section">
        <h2>Últimas Transacciones</h2>
        <?php if (empty($transacciones)): ?>
          <p>No tienes transacciones recientes. <a href="<?= htmlspecialchars(URL_SITIO) ?>modulos/wallet/recargar">Recargar wallet</a></p>
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
                  <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($tx['fecha']))) ?></td>
                  <td>
                    <span class="transaction-type type-<?= htmlspecialchars($tx['tipo']) ?>">
                      <?= htmlspecialchars(ucfirst($tx['tipo'])) ?>
                    </span>
                  </td>
                  <td class="<?= $tx['tipo']=='recarga'?'amount-positive':'amount-negative' ?>">
                    <?= htmlspecialchars(($tx['tipo']=='recarga'?'+':'-') . MONEDA . number_format($tx['monto'],2)) ?>
                  </td>
                  <td><?= htmlspecialchars($tx['referencia']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="ver-mas">
            <a href="<?= htmlspecialchars(URL_SITIO) ?>modulos/wallet/historial" class="btn btn-outline">Ver historial completo</a>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- Modal para cambiar imagen de perfil -->
<div id="avatar-modal" class="modal">
  <div class="modal-content">
    <span class="modal-close">&times;</span>
    <h3>Cambiar foto de perfil</h3>
    
    <div class="image-preview-container">
      <div class="image-preview-placeholder">
        <span>Vista previa de la imagen</span>
      </div>
      <img class="image-preview" style="display: none;" alt="Vista previa">
    </div>
    
    <form class="image-upload-form" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="accion" value="cambiar_avatar">
      
      <div class="file-input-wrapper">
        <button type="button" class="file-input-button">Seleccionar imagen</button>
        <input type="file" name="avatar" id="avatar-file" accept="image/jpeg,image/png,image/gif">
      </div>
      <span class="file-name"></span>
      <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
      
      <div class="modal-buttons">
        <button type="button" class="btn btn-cancel">Cancelar</button>
        <button type="submit" class="btn" id="btn-guardar-avatar">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Incluir Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* Estilos adicionales específicos para solucionar problemas */
.perfil-avatar-overlay {
  pointer-events: none !important;
}

.perfil-tabs {
  position: relative;
  z-index: 30 !important;
}

.perfil-tab {
  position: relative;
  z-index: 30 !important;
}
</style>

<script>
// Enfoque simplificado con enfoque directo en lugar de delegación de eventos
document.addEventListener('DOMContentLoaded', function() {
  // 1. Activar la pestaña inicial según el hash de la URL
  activarTabInicial();
  
  // 2. Configurar eventos de cierre para el modal
  setupModal();
  
  // 3. Mostrar modal si hay errores
  <?php if ($mostrarModalAvatar): ?>
    openImageModal();
  <?php endif; ?>
});

// Función para activar la pestaña inicial basada en el hash de la URL
function activarTabInicial() {
  if (window.location.hash) {
    const targetId = window.location.hash.substring(1);
    const tab = document.querySelector(`.perfil-tab[href="#${targetId}"]`);
    if (tab) {
      cambiarTab(tab, targetId);
    }
  }
}

// Función simplificada para cambiar de pestaña (llamada directamente desde el HTML)
function cambiarTab(tabElement, targetId) {
  // Desactivar todas las pestañas y secciones
  document.querySelectorAll('.perfil-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.perfil-section').forEach(s => s.classList.remove('active'));
  
  // Activar la pestaña clicada y su sección correspondiente
  tabElement.classList.add('active');
  
  const targetSection = document.getElementById(targetId);
  if (targetSection) {
    targetSection.classList.add('active');
    // Actualizar URL con hash
    window.history.pushState(null, null, `#${targetId}`);
  }
}

// Función para configurar modal
function setupModal() {
  const modal = document.getElementById('avatar-modal');
  if (!modal) return;
  
  // Cerrar con el botón X o Cancelar
  const closeButtons = modal.querySelectorAll('.modal-close, .btn-cancel');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', closeImageModal);
  });
  
  // Cerrar al hacer clic fuera del contenido
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      closeImageModal();
    }
  });
  
  // Previsualizar imagen
  const fileInput = modal.querySelector('#avatar-file');
  const fileName = modal.querySelector('.file-name');
  const imagePreview = modal.querySelector('.image-preview');
  const imagePlaceholder = modal.querySelector('.image-preview-placeholder');
  
  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        
        if (fileName) {
          fileName.textContent = file.name;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
          if (imagePreview) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
          }
          if (imagePlaceholder) {
            imagePlaceholder.style.display = 'none';
          }
        };
        reader.readAsDataURL(file);
      }
    });
  }
  
  // Botón para seleccionar archivo
  const fileButton = modal.querySelector('.file-input-button');
  if (fileButton && fileInput) {
    fileButton.addEventListener('click', function() {
      fileInput.click();
    });
  }
  
  // Validación del formulario
  const submitBtn = modal.querySelector('button[type="submit"]');
  if (submitBtn && fileInput) {
    submitBtn.addEventListener('click', function(e) {
      if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Por favor, selecciona una imagen');
        return false;
      }
      
      const file = fileInput.files[0];
      // Validar tipo
      const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
      if (!validTypes.includes(file.type)) {
        e.preventDefault();
        alert('Formato de archivo no válido. Por favor usa JPG, PNG o GIF');
        return false;
      }
      
      // Validar tamaño (2MB máximo)
      const maxSize = 2 * 1024 * 1024; // 2MB
      if (file.size > maxSize) {
        e.preventDefault();
        alert('La imagen es demasiado grande. El tamaño máximo es 2MB');
        return false;
      }
    });
  }
}

// Función para abrir el modal de imagen - invocada directamente desde el HTML
function openImageModal() {
  const modal = document.getElementById('avatar-modal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevenir scroll
  }
}

// Función para cerrar el modal de imagen
function closeImageModal() {
  const modal = document.getElementById('avatar-modal');
  if (!modal) return;
  
  modal.classList.remove('active');
  document.body.style.overflow = ''; // Restaurar scroll
  
  // Resetear formulario
  const form = modal.querySelector('form');
  if (form) form.reset();
  
  // Resetear vista previa
  const imagePreview = modal.querySelector('.image-preview');
  const imagePlaceholder = modal.querySelector('.image-preview-placeholder');
  const fileName = modal.querySelector('.file-name');
  
  if (imagePreview) imagePreview.style.display = 'none';
  if (imagePlaceholder) imagePlaceholder.style.display = 'flex';
  if (fileName) fileName.textContent = '';
}
</script>

<?php
// 10) Incluir footer
include __DIR__ . '/../../includes/footer.php';
?>