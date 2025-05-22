<?php
// 1) Mostrar errores en desarrollo (quita luego)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
// 3) Cargar configuración y librerías globales
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// 4) Incluir el header
$titulo = "Cómo funciona";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
  <div class="como-funciona-container">
    <div class="page-header">
      <h1>Cómo funciona InfoMaster</h1>
      <p>Descubre cómo acceder a tus productos digitales de forma rápida y segura</p>
    </div>

    <div class="proceso-steps">
      <div class="proceso-step">
        <div class="paso-numero">1</div>
        <div class="paso-info">
          <h2>Crea una cuenta</h2>
          <p>Regístrate con tu email y contraseña para acceder a todas las funcionalidades de la plataforma.</p>
          <div class="paso-imagen">
            <img src="<?= URL_SITIO ?>img/paso-1.jpg" alt="Crear cuenta">
          </div>
          <?php if (!estaLogueado()): ?>
            <a href="<?= URL_SITIO ?>modulos/usuarios/registro.php" class="btn">Crear cuenta ahora</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="proceso-step">
        <div class="paso-numero">2</div>
        <div class="paso-info">
          <h2>Recarga tu wallet</h2>
          <p>Añade saldo a tu cuenta mediante cualquiera de nuestros métodos de pago seguros.</p>
          <div class="paso-imagen">
            <img src="<?= URL_SITIO ?>img/paso-2.jpg" alt="Recargar wallet">
          </div>
          <?php if (estaLogueado()): ?>
            <a href="<?= URL_SITIO ?>modulos/wallet/recargar.php" class="btn">Recargar wallet</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="proceso-step">
        <div class="paso-numero">3</div>
        <div class="paso-info">
          <h2>Selecciona tu producto</h2>
          <p>Explora nuestro catálogo y encuentra el producto que necesitas. Agrégalo al carrito o cómpralo directamente.</p>
          <div class="paso-imagen">
            <img src="<?= URL_SITIO ?>img/paso-3.jpg" alt="Seleccionar producto">
          </div>
          <a href="<?= URL_SITIO ?>productos.php" class="btn">Ver productos</a>
        </div>
      </div>

      <div class="proceso-step">
        <div class="paso-numero">4</div>
        <div class="paso-info">
          <h2>Recibe tu acceso inmediatamente</h2>
          <p>Una vez completada la compra, recibirás automáticamente el código de acceso o la información de descarga.</p>
          <div class="paso-imagen">
            <img src="<?= URL_SITIO ?>img/paso-4.jpg" alt="Recibir acceso">
          </div>
        </div>
      </div>
    </div>

    <div class="funcionamiento-info">
      <h2>¿Por qué elegir InfoMaster?</h2>
      <div class="ventajas-grid">
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-flash"></i></div>
          <h3>Entrega inmediata</h3>
          <p>Recibe tus productos digitales al instante después de realizar el pago, sin tiempos de espera.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-wallet"></i></div>
          <h3>Sistema de wallet</h3>
          <p>Recarga una vez y compra múltiples productos sin tener que ingresar tus datos de pago cada vez.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-shield"></i></div>
          <h3>Garantía de seguridad</h3>
          <p>Todos nuestros productos tienen garantía y soporte técnico para asegurar tu satisfacción.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-support"></i></div>
          <h3>Soporte 24/7</h3>
          <p>Nuestro equipo de soporte está disponible para resolver cualquier duda o problema que puedas tener.</p>
        </div>
      </div>
    </div>

    <div class="cta-section">
      <h2>¿Listo para empezar?</h2>
      <p>Únete a miles de clientes satisfechos y accede a nuestros productos digitales ahora mismo.</p>
      <?php if (!estaLogueado()): ?>
        <a href="<?= URL_SITIO ?>modulos/usuarios/registro.php" class="btn btn-large">Crear cuenta gratis</a>
      <?php else: ?>
        <a href="<?= URL_SITIO ?>productos.php" class="btn btn-large">Explorar productos</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
// 6) Incluir el pie (footer)
include __DIR__ . '/../../includes/footer.php';
