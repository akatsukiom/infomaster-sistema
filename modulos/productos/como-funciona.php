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

<style>
/* ========== ESTILOS MEJORADOS PARA CÓMO FUNCIONA ========== */

.como-funciona-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

/* HEADER DE LA PÁGINA */
.page-header {
  text-align: center;
  margin-bottom: 4rem;
  padding: 3rem 0;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.page-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
  animation: rotateGradient 20s linear infinite;
}

@keyframes rotateGradient {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.page-header h1 {
  font-size: clamp(2.5rem, 6vw, 4rem);
  font-weight: 900;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.page-header p {
  font-size: clamp(1.1rem, 2.5vw, 1.4rem);
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  max-width: 600px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* PASOS DEL PROCESO */
.proceso-steps {
  margin-bottom: 5rem;
}

.proceso-step {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 3rem;
  align-items: center;
  margin-bottom: 4rem;
  padding: 3rem;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
}

.proceso-step::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transition: left 0.6s ease;
}

.proceso-step:hover::before {
  left: 100%;
}

.proceso-step:hover {
  transform: translateY(-10px);
  background: rgba(255, 255, 255, 0.12);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.25);
}

.proceso-step:nth-child(even) {
  grid-template-columns: 1fr auto;
}

.proceso-step:nth-child(even) .paso-info {
  order: 1;
}

.proceso-step:nth-child(even) .paso-numero {
  order: 2;
}

/* NÚMERO DEL PASO */
.paso-numero {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 900;
  color: white;
  box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
  position: relative;
  overflow: hidden;
}

.paso-numero::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: spin 3s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* INFORMACIÓN DEL PASO */
.paso-info {
  color: white;
}

.paso-info h2 {
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  font-weight: 800;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.paso-info p {
  font-size: 1.2rem;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 2rem;
  font-weight: 400;
}

/* IMAGEN ILUSTRATIVA CON PLACEHOLDER */
.paso-imagen {
  margin: 2rem 0;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
  height: 250px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.paso-imagen::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><rect width="400" height="250" fill="%23667eea"/><circle cx="200" cy="125" r="30" fill="white" opacity="0.3"/><rect x="160" y="160" width="80" height="20" rx="10" fill="white" opacity="0.2"/><rect x="140" y="190" width="120" height="15" rx="7" fill="white" opacity="0.15"/></svg>') center/cover;
  transition: transform 0.4s ease;
}

.proceso-step:hover .paso-imagen::before {
  transform: scale(1.05);
}

.paso-imagen .imagen-icon {
  font-size: 4rem;
  color: white;
  opacity: 0.8;
  z-index: 1;
  position: relative;
}

/* BOTONES */
.btn {
  display: inline-block;
  padding: 1rem 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white !important;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1.1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.btn:hover::before {
  left: 100%;
}

.btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.btn-large {
  padding: 1.5rem 3rem;
  font-size: 1.3rem;
}

/* INFORMACIÓN DE FUNCIONAMIENTO */
.funcionamiento-info {
  margin-bottom: 5rem;
  text-align: center;
}

.funcionamiento-info h2 {
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 900;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 3rem;
}

/* GRID DE VENTAJAS */
.ventajas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.ventaja-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2.5rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  transition: all 0.4s ease;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.ventaja-card::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.ventaja-card:hover::before {
  opacity: 1;
}

.ventaja-card:hover {
  transform: translateY(-15px);
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.25);
}

.ventaja-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 2rem auto;
  font-size: 2rem;
  color: white;
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
  position: relative;
  z-index: 1;
}

.ventaja-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.ventaja-card p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  font-size: 1rem;
  position: relative;
  z-index: 1;
}

/* SECCIÓN CTA */
.cta-section {
  text-align: center;
  padding: 4rem 2rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.cta-section::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 0deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1), rgba(102, 126, 234, 0.1));
  animation: rotateConic 30s linear infinite;
}

@keyframes rotateConic {
  100% { transform: rotate(360deg); }
}

.cta-section h2 {
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 900;
  color: white;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.cta-section p {
  font-size: 1.3rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 2.5rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  position: relative;
  z-index: 1;
}

/* ICONOS CON EMOJIS */
.icon-flash::before { content: "⚡"; }
.icon-wallet::before { content: "💳"; }
.icon-shield::before { content: "🛡️"; }
.icon-support::before { content: "🎧"; }

/* RESPONSIVE DESIGN */
@media (max-width: 1024px) {
  .proceso-step {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 2rem;
  }

  .proceso-step:nth-child(even) {
    grid-template-columns: 1fr;
  }

  .proceso-step:nth-child(even) .paso-info,
  .proceso-step:nth-child(even) .paso-numero {
    order: initial;
  }

  .paso-numero {
    width: 100px;
    height: 100px;
    font-size: 2.5rem;
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .como-funciona-container {
    padding: 1rem;
  }

  .proceso-step {
    padding: 2rem;
    margin-bottom: 2rem;
  }

  .paso-numero {
    width: 80px;
    height: 80px;
    font-size: 2rem;
  }

  .ventajas-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .ventaja-card {
    padding: 2rem;
  }

  .ventaja-icon {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }

  .page-header {
    padding: 2rem 1rem;
    margin-bottom: 2rem;
  }

  .cta-section {
    padding: 3rem 1.5rem;
  }

  .paso-imagen {
    height: 200px;
  }

  .paso-imagen .imagen-icon {
    font-size: 3rem;
  }
}

@media (max-width: 480px) {
  .btn {
    padding: 0.8rem 2rem;
    font-size: 1rem;
  }

  .btn-large {
    padding: 1.2rem 2.5rem;
    font-size: 1.1rem;
  }

  .ventaja-icon {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }

  .paso-imagen {
    height: 180px;
  }

  .paso-imagen .imagen-icon {
    font-size: 2.5rem;
  }
}
</style>

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
          <p>Regístrate con tu email y contraseña para acceder a todas las funcionalidades de la plataforma. El proceso es rápido, seguro y completamente gratuito.</p>
          <div class="paso-imagen">
            <div class="imagen-icon">👤</div>
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
          <p>Añade saldo a tu cuenta mediante cualquiera de nuestros métodos de pago seguros. Tu dinero estará disponible al instante para futuras compras.</p>
          <div class="paso-imagen">
            <div class="imagen-icon">💰</div>
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
          <p>Explora nuestro extenso catálogo y encuentra exactamente lo que necesitas. Agrégalo al carrito o cómpralo directamente con un solo clic.</p>
          <div class="paso-imagen">
            <div class="imagen-icon">🛒</div>
          </div>
          <a href="<?= URL_SITIO ?>productos.php" class="btn">Ver productos</a>
        </div>
      </div>

      <div class="proceso-step">
        <div class="paso-numero">4</div>
        <div class="paso-info">
          <h2>Recibe tu acceso inmediatamente</h2>
          <p>Una vez completada la compra, recibirás automáticamente el código de acceso, credenciales o información de descarga. ¡Sin esperas!</p>
          <div class="paso-imagen">
            <div class="imagen-icon">🚀</div>
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
          <p>Recibe tus productos digitales al instante después de realizar el pago, sin tiempos de espera ni demoras.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-wallet"></i></div>
          <h3>Sistema de wallet</h3>
          <p>Recarga una vez y compra múltiples productos sin tener que ingresar tus datos de pago cada vez.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-shield"></i></div>
          <h3>Garantía de seguridad</h3>
          <p>Todos nuestros productos tienen garantía y soporte técnico para asegurar tu satisfacción completa.</p>
        </div>
        <div class="ventaja-card">
          <div class="ventaja-icon"><i class="icon-support"></i></div>
          <h3>Soporte 24/7</h3>
          <p>Nuestro equipo de soporte está disponible las 24 horas para resolver cualquier duda o problema.</p>
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
?>