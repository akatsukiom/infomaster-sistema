<?php
// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

require_once 'modulos/productos/modelo.php';
require_once 'modulos/carrito/modelo.php';

// 1) Productos destacados
$productoModel = new Producto($conexion);
$destacados    = $productoModel->obtenerDestacados(8);

// 2) Categorías
$sql        = "SELECT * FROM categorias ORDER BY nombre";
$res_cat    = $conexion->query($sql);
$categorias = $res_cat ? $res_cat->fetch_all(MYSQLI_ASSOC) : [];

// 3) Carga el header
$titulo = "Inicio – InfoMaster";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <h1>Tu plataforma de productos digitales</h1>
    <p>Acceso inmediato a nuestro catálogo con entrega automática y sistema de wallet.</p>
    <div class="hero-buttons">
      <a href="<?= URL_SITIO ?>productos" class="btn btn-large">Ver productos</a>
      <?php if (!estaLogueado()): ?>
        <a href="<?= URL_SITIO ?>registro" class="btn-outline btn-large">Crear cuenta</a>
      <?php else: ?>
        <a href="<?= URL_SITIO ?>wallet/recargar" class="btn-outline btn-large">Recargar wallet</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="featured">
  <div class="container">
    <div class="section-title">
      <h2>Productos Destacados</h2>
      <p>Lo más vendido de nuestro catálogo</p>
    </div>
    <div class="products">
      <?php if (empty($destacados)): ?>
        <p>No hay productos destacados.</p>
      <?php else: foreach ($destacados as $p): ?>
        <div class="product">
          <img
            src="<?= !empty($p['imagen'])
                      ? URL_SITIO . $p['imagen']
                      : URL_SITIO . 'img/producto-default.jpg' ?>"
            alt="<?= htmlspecialchars($p['nombre']) ?>"
            class="product-img"
          >
          <div class="overlay">
            <!-- Ver detalle: apunta siempre a modulos/productos/detalle.php -->
            <a
              href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $p['id'] ?>"
              class="btn-small"
            >Ver</a>
            <!-- Comprar: redirige al carrito -->
            <a
              href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $p['id'] ?>&redirigir=carrito.php"
              class="btn-small btn-secondary"
            >Comprar</a>
          </div>
          <div class="product-info">
            <h3 class="product-title"><?= htmlspecialchars($p['nombre']) ?></h3>
            <div class="product-price"><?= MONEDA . number_format($p['precio'], 2) ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <div class="view-all">
      <a href="<?= URL_SITIO ?>productos" class="btn-outline">Ver todos los productos</a>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="categories">
  <div class="container">
    <div class="section-title">
      <h2>Categorías</h2>
      <p>Explora por categoría</p>
    </div>
    <div class="category-cards">
      <?php if (empty($categorias)): ?>
        <p>No hay categorías disponibles.</p>
      <?php else: foreach ($categorias as $c): ?>
        <div class="category-card">
          <img
            src="<?= !empty($c['imagen'])
                      ? URL_SITIO . $c['imagen']
                      : URL_SITIO . 'img/categoria-default.jpg' ?>"
            alt="<?= htmlspecialchars($c['nombre']) ?>"
            class="category-img"
          >
          <h3><?= htmlspecialchars($c['nombre']) ?></h3>
          <!-- Ver productos: ruta absoluta -->
          <a
            href="<?= URL_SITIO ?>productos?categoria=<?= (int)$c['id'] ?>"
            class="btn-outline"
          >Ver productos</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<!-- Resto del template (How it works, CTA…) sin cambios… -->

<?php include 'includes/footer.php'; ?>
