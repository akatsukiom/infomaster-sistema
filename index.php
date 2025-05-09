<?php
// Asegurar acceso y cargar dependencias
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';
require_once 'modulos/productos/modelo.php';
require_once 'modulos/carrito/modelo.php';

// 1) Productos destacados
$productoModel = new Producto($conexion);
$destacados    = $productoModel->obtenerDestacados(8);

// 2) Categorías
$sql  = "SELECT * FROM categorias ORDER BY nombre";
$rs   = $conexion->query($sql);
$cats = $rs->fetch_all(MYSQLI_ASSOC);

// 3) Incluir header
$titulo = "Inicio - Productos Digitales";
include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Tu plataforma de productos digitales</h1>
      <p>Acceso inmediato y seguro a nuestro catálogo de productos con entrega automática y wallet integrado.</p>
      <div class="hero-buttons">
        <a href="<?= URL_SITIO ?>productos" class="btn">Ver productos</a>
        <?php if (!estaLogueado()): ?>
          <a href="<?= URL_SITIO ?>usuarios/registro" class="btn btn-outline">Crear cuenta</a>
        <?php else: ?>
          <a href="<?= URL_SITIO ?>wallet/recargar" class="btn btn-outline">Recargar wallet</a>
        <?php endif; ?>
      </div>
      <div class="hero-cards">
        <div class="hero-card">
          <i class="fas fa-rocket"></i>
          <h3>Entrega inmediata</h3>
          <p>Recibe tu producto al instante</p>
        </div>
        <div class="hero-card">
          <i class="fas fa-lock"></i>
          <h3>100% Seguro</h3>
          <p>Transacciones protegidas</p>
        </div>
        <div class="hero-card">
          <i class="fas fa-headset"></i>
          <h3>Soporte 24/7</h3>
          <p>Estamos para ayudarte</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DESTACADOS -->
<section class="featured">
  <div class="container">
    <div class="section-title">
      <h2>Productos Destacados</h2>
      <p>Los más vendidos de nuestro catálogo</p>
    </div>
    <div class="products">
      <?php if (empty($destacados)): ?>
        <p class="no-products">No hay productos destacados disponibles.</p>
      <?php else: ?>
        <?php foreach ($destacados as $prod):
          $rutaProdImg = !empty($prod['imagen'])
            ? URL_SITIO . ltrim($prod['imagen'], '/')
            : URL_SITIO . 'img/producto-default.jpg';
        ?>
        <div class="product">
          <?php if ($prod['destacado']): ?>
            <span class="badge">Destacado</span>
          <?php endif; ?>
          <img src="<?= htmlspecialchars($rutaProdImg) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="product-img">
          <div class="info">
            <span class="category"><?= htmlspecialchars($prod['categoria']) ?></span>
            <h3 class="title"><?= htmlspecialchars($prod['nombre']) ?></h3>
            <p class="description"><?= htmlspecialchars(substr($prod['descripcion'], 0, 60)) ?><?= strlen($prod['descripcion']) > 60 ? '…' : '' ?></p>
            <div class="price"><?= MONEDA . number_format($prod['precio'], 2) ?></div>
            <div class="actions">
              <a href="<?= URL_SITIO ?>productos/<?= $prod['id'] ?>" class="btn">Ver detalles</a>
              <a href="<?= URL_SITIO ?>carrito/agregar/<?= $prod['id'] ?>?redirigir=/" class="btn btn-secondary">Comprar</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="view-all">
      <a href="<?= URL_SITIO ?>productos" class="btn btn-outline">Ver todos los productos</a>
    </div>
  </div>
</section>

<!-- CATEGORÍAS -->
<section class="categories">
  <div class="container">
    <div class="section-title">
      <h2>Categorías</h2>
      <p>Explora por categoría</p>
    </div>
    <div class="cards">
      <?php if (empty($cats)): ?>
        <p>No hay categorías disponibles.</p>
      <?php else: ?>
        <?php foreach ($cats as $cat):
          $rutaCatImg = !empty($cat['imagen'])
            ? URL_SITIO . ltrim($cat['imagen'], '/')
            : URL_SITIO . 'img/categoria-default.jpg';
        ?>
        <div class="card">
          <img src="<?= htmlspecialchars($rutaCatImg) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>">
          <h3><?= htmlspecialchars($cat['nombre']) ?></h3>
          <p><?= htmlspecialchars(substr($cat['descripcion'], 0, 50)) ?><?= strlen($cat['descripcion']) > 50 ? '…' : '' ?></p>
          <a href="<?= URL_SITIO ?>productos?categoria=<?= $cat['id'] ?>" class="btn btn-outline">Ver productos</a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- CÓMO FUNCIONA -->
<section class="how-it-works">
  <div class="container">
    <div class="section-title">
      <h2>Cómo funciona</h2>
      <p>Compra fácil y rápida en 4 sencillos pasos</p>
    </div>
    <div class="steps">
      <?php
      $steps = [
        ['icon' => '1', 'title' => 'Crea tu cuenta', 'desc' => 'Regístrate y accede a tu panel.'],
        ['icon' => '2', 'title' => 'Recarga tu wallet', 'desc' => 'Añade saldo con nuestros métodos.'],
        ['icon' => '3', 'title' => 'Elige tu producto', 'desc' => 'Selecciona lo que necesitas.'],
        ['icon' => '4', 'title' => 'Acceso inmediato', 'desc' => 'Disfruta tu compra al instante.'],
      ];
      foreach ($steps as $s): ?>
        <div class="step">
          <div class="step-icon"><?= $s['icon'] ?></div>
          <h3><?= $s['title'] ?></h3>
          <p><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIOS -->
<section class="testimonials">
  <div class="container">
    <div class="section-title">
      <h2>Testimonios</h2>
      <p>Lo que dicen nuestros clientes</p>
    </div>
    <div class="testimonial-slider">
      <?php
      $testigos = [
        ['text' => 'Excelente servicio... plataforma muy fácil de usar.', 'name' => 'José Rodríguez', 'when' => '2023', 'img' => 'img/testimonial-1.jpg'],
        ['text' => 'La mejor plataforma... soporte técnico responde rápido.', 'name' => 'María Gómez', 'when' => '2024', 'img' => 'img/testimonial-2.jpg'],
        ['text' => 'Sin duda la mejor. Precios competitivos y entrega inmediata.', 'name' => 'Carlos Mendoza', 'when' => '2022', 'img' => 'img/testimonial-3.jpg'],
      ];
      foreach ($testigos as $t):
        $imgTest = URL_SITIO . ltrim($t['img'], '/');
      ?>
        <div class="testimonial">
          <p>"<?= htmlspecialchars($t['text']) ?>"</p>
          <div class="author">
            <img src="<?= htmlspecialchars($imgTest) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
            <div>
              <h4><?= htmlspecialchars($t['name']) ?></h4>
              <span>Cliente desde <?= $t['when'] ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="slider-indicators">
      <?php foreach (array_keys($testigos) as $i): ?>
        <span data-index="<?= $i ?>"<?= $i === 0 ? ' class="active"' : '' ?>></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta">
  <div class="container">
    <h2>¿Listo para empezar?</h2>
    <p>Únete a miles de clientes satisfechos hoy mismo.</p>
    <div class="cta-buttons">
      <?php if (!estaLogueado()): ?>
        <a href="<?= URL_SITIO ?>usuarios/registro" class="btn">Crear cuenta ahora</a>
      <?php else: ?>
        <a href="<?= URL_SITIO ?>productos" class="btn">Explorar productos</a>
      <?php endif; ?>
      <a href="<?= URL_SITIO ?>como-funciona" class="btn btn-outline">Saber más</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- JS SLIDER -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const slider = document.querySelector('.testimonial-slider');
    const items  = slider.children;
    const dots   = document.querySelectorAll('.slider-indicators span');
    let idx      = 0;

    function show(i) {
      slider.style.transform = `translateX(-${i * 100}%)`;
      dots.forEach((d,j) => d.classList.toggle('active', i===j));
      idx = i;
    }

    dots.forEach(d => d.addEventListener('click', () => show(+d.dataset.index)));
    let auto = setInterval(() => show((idx+1)%items.length), 5000);
    slider.addEventListener('mouseenter', () => clearInterval(auto));
    slider.addEventListener('mouseleave', () => auto = setInterval(() => show((idx+1)%items.length), 5000));
  });
</script>
