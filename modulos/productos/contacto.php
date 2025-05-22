<?php
// Mostrar errores en desarrollo (quita en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Permitir el include de config.php
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
// 2) Cargar configuración y funciones
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// 3) Variables de estado
$enviado = false;
$errores = [];

// 4) Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = limpiarDato($_POST['nombre']  ?? '');
    $email   = limpiarDato($_POST['email']   ?? '');
    $asunto  = limpiarDato($_POST['asunto']  ?? '');
    $mensaje = limpiarDato($_POST['mensaje'] ?? '');

    // Validaciones
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    if (empty($email) || !validarEmail($email)) {
        $errores[] = "Por favor ingresa un email válido";
    }
    if (empty($asunto)) {
        $errores[] = "El asunto es obligatorio";
    }
    if (empty($mensaje)) {
        $errores[] = "El mensaje es obligatorio";
    }

    // Si no hay errores, simulamos envío
    if (empty($errores)) {
        mostrarMensaje("Tu mensaje ha sido enviado. Te responderemos a la brevedad.", 'success');
        $enviado = true;
        $nombre = $email = $asunto = $mensaje = '';
    }
}

// 5) Incluir header
$titulo = "Contacto";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
  <div class="contacto-container">
    <div class="page-header">
      <h1>Contáctanos</h1>
      <p>Estamos aquí para ayudarte. Envíanos tu consulta y te responderemos a la brevedad.</p>
    </div>

    <div class="contacto-grid">
      <div class="contacto-form">
        <?php if ($enviado): ?>
          <div class="form-enviado">
            <div class="enviado-icon"><i class="icon-check"></i></div>
            <h2>¡Mensaje enviado!</h2>
            <p>Gracias por contactarnos. Hemos recibido tu mensaje y te responderemos a la brevedad.</p>
            <a href="<?= URL_SITIO ?>" class="btn">Volver al inicio</a>
          </div>
        <?php else: ?>
          <?php if (!empty($errores)): ?>
            <div class="errores">
              <?php foreach ($errores as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($nombre ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email"
                     value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="asunto">Asunto</label>
              <input type="text" id="asunto" name="asunto"
                     value="<?= htmlspecialchars($asunto ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="mensaje">Mensaje</label>
              <textarea id="mensaje" name="mensaje" rows="6" required><?= htmlspecialchars($mensaje ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Enviar mensaje</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="contacto-info">
        <div class="info-card">
          <div class="info-icon"><i class="icon-mail"></i></div>
          <h3>Email</h3>
          <p>info@infomaster.com.mx</p>
          <p>soporte@infomaster.com.mx</p>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="icon-phone"></i></div>
          <h3>Teléfono</h3>
          <p>+52 496 126 05 97</p>
          <p>Lun - Dom: 8:00 - 00:00</p>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="icon-message"></i></div>
          <h3>Chat en vivo</h3>
          <p>Disponible de Lunes a Dom</p>
          <p>8:00 AM - 00:00 PM</p>
          <button class="btn btn-outline iniciar-chat">Iniciar chat</button>
        </div>

        <div class="redes-sociales">
          <h3>Síguenos</h3>
          <div class="redes-icons">
            <a href="#" class="red-social"><i class="icon-facebook"></i></a>
            <a href="#" class="red-social"><i class="icon-twitter"></i></a>
            <a href="#" class="red-social"><i class="icon-instagram"></i></a>
            <a href="#" class="red-social"><i class="icon-youtube"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// 6) Incluir footer
include __DIR__ . '/../../includes/footer.php';
