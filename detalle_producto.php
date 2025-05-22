<?php
// detalle_producto.php
// 0) Permitimos que se cargue el header.php sin morir
define('ACCESO_PERMITIDO', true);

// 1) Configuración global y modelo
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/modulos/admin/productos/modelo.php';

// 2) Leer y sanear el id
$id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int) $_GET['id']
    : 0;

// 3) Obtener datos del producto
$prodModel = new Producto($conexion);
$producto = $prodModel->obtenerPorId($id);

if (!$producto) {
    // Si no existe, redirigimos o mostramos un 404 custom
    header('HTTP/1.1 404 Not Found');
    echo "<h1>Producto no encontrado</h1>";
    exit;
}

// 4) Obtener productos relacionados (de la misma categoría)
$relacionados = [];
if (!empty($producto['categoria_id'])) {
    $todos = $prodModel->obtenerTodos((int)$producto['categoria_id']);
    $relacionados = array_filter($todos, fn($p) => $p['id'] !== $id);
    $relacionados = array_slice($relacionados, 0, 4);
}

// 5) Productos de interés - implementación similar al archivo original
// Obtener productos aleatorios diferentes al actual y de categoría diferente
$sql = "SELECT 
            p.*, 
            c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id != ? AND p.categoria_id != ?
        ORDER BY RAND()
        LIMIT 4";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $id, $producto['categoria_id']);
$stmt->execute();
$res = $stmt->get_result();
$productosInteres = $res->fetch_all(MYSQLI_ASSOC);

// 6) Incluir cabecera
$titulo = htmlspecialchars($producto['nombre']) . ' – Detalle';
include __DIR__ . '/includes/header.php';
?>

<section class="product-detail">
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="<?= URL_SITIO ?>">Inicio</a>
      <span class="separator">›</span>
      <a href="<?= URL_SITIO ?>productos.php">Productos</a>
      <?php if (!empty($producto['categoria_id'])): ?>
        <span class="separator">›</span>
        <a href="<?= URL_SITIO ?>productos.php?categoria=<?= (int)$producto['categoria_id'] ?>">
          <?= htmlspecialchars($producto['categoria'] ?? 'Categoría') ?>
        </a>
      <?php endif; ?>
      <span class="separator">›</span>
      <span class="current"><?= htmlspecialchars($producto['nombre']) ?></span>
    </nav>

    <div class="product-grid">
      <!-- Galería -->
      <div class="product-gallery">
        <img
          src="<?= $producto['imagen']
                  ? URL_SITIO . ltrim($producto['imagen'], '/')
                  : URL_SITIO . 'img/producto-default.jpg' ?>"
          alt="<?= htmlspecialchars($producto['nombre']) ?>"
          class="main-image"
        >
        <div class="gallery-thumbnails">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <img
              src="<?= $i === 1
                      ? ($producto['imagen']
                          ? URL_SITIO . ltrim($producto['imagen'], '/')
                          : URL_SITIO . 'img/producto-default.jpg')
                      : URL_SITIO . 'img/producto-thumb-' . $i . '.jpg' ?>"
              alt="Thumbnail <?= $i ?>"
              class="gallery-thumbnail <?= $i === 1 ? 'active' : '' ?>"
            >
          <?php endfor; ?>
        </div>
      </div>

      <!-- Información -->
      <div class="product-info">
        <?php if (!empty($producto['categoria'])): ?>
          <span class="product-category"><?= htmlspecialchars($producto['categoria'] ?? '') ?></span>
        <?php endif; ?>
        <h1 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h1>

        <!-- Precio -->
        <div class="product-price">
          <strong>Precio:</strong>
          <span id="price-valor"><?= MONEDA . number_format($producto['precio_base'], 2) ?></span>
          <?php if (!empty($producto['precio_anterior']) && $producto['precio_anterior'] > $producto['precio_base']): ?>
            <span class="original-price">
              <?= MONEDA . number_format($producto['precio_anterior'], 2) ?>
            </span>
            <span class="discount-badge">
              <?= round((1 - $producto['precio_base'] / $producto['precio_anterior']) * 100) ?>% OFF
            </span>
          <?php endif; ?>
        </div>

        <div class="product-description">
          <h3>Descripción</h3>
          <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
          <ul>
            <li>Entrega inmediata tras confirmación de pago</li>
            <li>Soporte técnico incluido</li>
            <li>Garantía de funcionamiento</li>
            <li>Tutoriales disponibles</li>
          </ul>
        </div>

<form method="GET" action="<?= URL_SITIO ?>modulos/carrito/agregar.php" class="producto-compra">
  <input type="hidden" name="id"        value="<?= $id ?>">
  <input type="hidden" name="redirigir" value="detalle_producto.php?id=<?= $id ?>">
  
  <!-- Selector de cantidad ... -->
  <div class="quantity-selector mb-4">
    <label for="cantidad">Cantidad:</label>
    <div class="quantity-control">
      <button type="button" id="btn-menos" class="quantity-btn">−</button>
      <input type="number" id="cantidad" name="cantidad"
             value="1" min="1" max="10" class="quantity-input">
      <button type="button" id="btn-mas" class="quantity-btn">+</button>
    </div>
  </div>
  
  <div class="action-buttons">
    <!-- 1) Añadir al carrito y volver aquí -->
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-shopping-cart"></i> Añadir al carrito
    </button>
    <!-- 2) Comprar ahora → llama al checkout real -->
    <button
      type="submit"
      formaction="<?= URL_SITIO ?>modulos/carrito/checkout.php"
      class="btn btn-secondary"
    >
      <i class="fas fa-bolt"></i> Comprar ahora
    </button>
  </div>
</form>

    <!-- Productos relacionados -->
    <?php if (!empty($relacionados)): ?>
      <section class="related-products">
        <h2>Productos relacionados</h2>
        <div class="products-grid">
          <?php foreach ($relacionados as $r): ?>
            <div class="product-card">
              <img
                src="<?= $r['imagen']
                        ? URL_SITIO . ltrim($r['imagen'], '/')
                        : URL_SITIO . 'img/producto-default.jpg' ?>"
                alt="<?= htmlspecialchars($r['nombre']) ?>"
                class="product-card-img"
              >
              <div class="product-card-info">
                <h3 class="product-card-title"><?= htmlspecialchars($r['nombre']) ?></h3>
                <div class="product-card-price">
                  <?= MONEDA . number_format($r['precio_base'] ?? 0, 2) ?>
                </div>
                <div class="product-card-actions">
                  <a href="<?= URL_SITIO ?>detalle_producto.php?id=<?= $r['id'] ?>" class="btn">
                    <i class="fas fa-eye"></i> Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $r['id'] ?>&redirigir=<?= urlencode("detalle_producto.php?id={$r['id']}") ?>" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Comprar
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Separador -->
    <div class="section-separator"></div>

    <!-- Productos de interés -->
    <?php if (!empty($productosInteres)): ?>
      <section class="interest-products">
        <h2>Te podría interesar</h2>
        <div class="products-grid">
          <?php foreach ($productosInteres as $pi): ?>
            <div class="product-card">
              <?php if (!empty($pi['destacado']) && $pi['destacado']): ?>
                <span class="oferta-badge">Oferta</span>
              <?php endif; ?>
              <img
                src="<?= $pi['imagen']
                        ? URL_SITIO . ltrim($pi['imagen'], '/')
                        : URL_SITIO . 'img/producto-default.jpg' ?>"
                alt="<?= htmlspecialchars($pi['nombre']) ?>"
                class="product-card-img"
              >
              <div class="product-card-info">
                <h3 class="product-card-title"><?= htmlspecialchars($pi['nombre']) ?></h3>
                <div class="product-card-price">
                  <?= MONEDA . number_format($pi['precio_base'] ?? 0, 2) ?>
                  <?php if (!empty($pi['precio_anterior']) && $pi['precio_anterior'] > $pi['precio_base']): ?>
                    <span class="original-price" style="font-size: 14px; margin-left: 8px;">
                      <?= MONEDA . number_format($pi['precio_anterior'], 2) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="product-card-actions">
                  <a href="<?= URL_SITIO ?>detalle_producto.php?id=<?= $pi['id'] ?>" class="btn">
                    <i class="fas fa-eye"></i> Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $pi['id'] ?>&redirigir=<?= urlencode("detalle_producto.php?id={$pi['id']}") ?>" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Comprar
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</section>

<style>
/* Estilos generales de la página de detalle */
.product-detail {
  padding: 40px 0;
}

.breadcrumb {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  font-size: 14px;
}

.breadcrumb a {
  color: #555;
  text-decoration: none;
}

.breadcrumb a:hover {
  color: #3b88ff;
}

.separator {
  margin: 0 8px;
  color: #999;
}

.current {
  color: #3b88ff;
  font-weight: 600;
}

.product-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 60px;
}

@media (max-width: 768px) {
  .product-grid {
    grid-template-columns: 1fr;
  }
}

/* Galería */
.product-gallery {
  border-radius: 8px;
  overflow: hidden;
  background-color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.main-image {
  width: 100%;
  height: 400px;
  object-fit: cover;
  display: block;
}

.gallery-thumbnails {
  display: flex;
  gap: 10px;
  padding: 15px;
  border-top: 1px solid #f2f2f2;
}

.gallery-thumbnail {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
  opacity: 0.6;
  transition: all 0.2s;
}

.gallery-thumbnail:hover {
  opacity: 0.9;
}

.gallery-thumbnail.active {
  opacity: 1;
  border: 2px solid #3b88ff;
}

/* Información del producto */
.product-info {
  display: flex;
  flex-direction: column;
}

.product-category {
  display: inline-block;
  background-color: #f2f7ff;
  color: #3b88ff;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 14px;
  margin-bottom: 10px;
}

.product-title {
  font-size: 32px;
  margin: 0 0 20px;
  color: #222;
  line-height: 1.2;
}

.product-price {
  font-size: 24px;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.product-price strong {
  font-weight: 500;
  color: #555;
}

#price-valor {
  font-size: 28px;
  font-weight: 700;
  color: #3b88ff;
}

.original-price {
  text-decoration: line-through;
  color: #999;
  font-size: 18px;
}

.discount-badge {
  background-color: #ff3366;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 14px;
}

.product-description {
  margin-bottom: 30px;
}

.product-description h3 {
  font-size: 20px;
  margin-bottom: 15px;
  color: #333;
}

.product-description p {
  margin-bottom: 15px;
  line-height: 1.6;
  color: #555;
}

.product-description ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.product-description li {
  padding: 8px 0 8px 25px;
  position: relative;
  line-height: 1.5;
}

.product-description li:before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #3b88ff;
  font-weight: bold;
}

/* Formulario de compra */
.producto-compra {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  margin-top: auto;
}

/* Cantidad */
.quantity-selector {
  margin-bottom: 20px;
}

.quantity-control {
  display: flex;
  align-items: center;
  width: 140px;
}

.quantity-btn {
  width: 40px;
  height: 40px;
  border: 1px solid #ddd;
  background-color: white;
  color: #333;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.quantity-btn:first-child {
  border-radius: 6px 0 0 6px;
}

.quantity-btn:last-child {
  border-radius: 0 6px 6px 0;
}

.quantity-btn:hover {
  background-color: #f1f1f1;
}

.quantity-input {
  flex-grow: 1;
  height: 40px;
  border: 1px solid #ddd;
  border-left: none;
  border-right: none;
  text-align: center;
  font-size: 16px;
  padding: 0 5px;
  -moz-appearance: textfield;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Botones de acción */
.action-buttons {
  display: flex;
  gap: 15px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  line-height: 1.5;
  gap: 8px;
}

.btn-primary {
  background-color: #3b88ff;
  color: white;
  flex: 1;
}

.btn-primary:hover {
  background-color: #2a75eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 136, 255, 0.3);
}

.btn-secondary {
  background-color: #ff3366;
  color: white;
  flex: 1;
}

.btn-secondary:hover {
  background-color: #e62c59;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(230, 44, 89, 0.3);
}

/* Productos relacionados y de interés */
.related-products,
.interest-products {
  margin-top: 60px;
}

.related-products h2,
.interest-products h2 {
  font-size: 24px;
  margin-bottom: 25px;
  color: #333;
  position: relative;
  padding-bottom: 10px;
}

.related-products h2::after,
.interest-products h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background-color: #3b88ff;
  border-radius: 2px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.product-card {
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
  position: relative;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.product-card-img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.product-card-info {
  padding: 20px;
}

.product-card-title {
  font-size: 18px;
  margin: 0 0 15px;
  color: #333;
  line-height: 1.4;
  height: 50px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-card-price {
  color: #ff3366;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 15px;
}

.product-card-actions {
  display: flex;
  gap: 10px;
}

.product-card-actions .btn {
  padding: 8px 12px;
  font-size: 14px;
  flex: 1;
}

.oferta-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #3b88ff;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 14px;
  z-index: 2;
}

.section-separator {
  height: 1px;
  background-color: #eee;
  margin: 40px 0;
}

/* Responsividad */
@media (max-width: 992px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .product-title {
    font-size: 26px;
  }
  
  .main-image {
    height: 300px;
  }
}

@media (max-width: 576px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .gallery-thumbnails {
    overflow-x: auto;
    padding-bottom: 10px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Control de cantidad
  const cantidadInput = document.getElementById('cantidad');
  const btnMenos = document.getElementById('btn-menos');
  const btnMas = document.getElementById('btn-mas');
  
  btnMenos.addEventListener('click', () => {
    const currentValue = parseInt(cantidadInput.value) || 1;
    cantidadInput.value = Math.max(1, currentValue - 1);
  });
  
  btnMas.addEventListener('click', () => {
    const currentValue = parseInt(cantidadInput.value) || 1;
    cantidadInput.value = Math.min(10, currentValue + 1);
  });
  
  // Comprar ahora
  document.getElementById('comprar-ahora').addEventListener('click', () => {
    const form = document.querySelector('.producto-compra');
    form.querySelector('input[name="redirigir"]').value = 'checkout.php';
    form.submit();
  });
  
  // Galería de imágenes
  const thumbnails = document.querySelectorAll('.gallery-thumbnail');
  const mainImage = document.querySelector('.main-image');
  
  thumbnails.forEach(thumb => {
    thumb.addEventListener('click', () => {
      // Quitar clase active de todos los thumbnails
      thumbnails.forEach(t => t.classList.remove('active'));
      
      // Añadir clase active al thumbnail seleccionado
      thumb.classList.add('active');
      
      // Actualizar imagen principal
      mainImage.src = thumb.src;
      
      // Si es necesario, ajustar la ruta para mostrar imagen en tamaño completo
      if (thumb.src.includes('thumb')) {
        mainImage.src = thumb.src.replace('thumb', 'default');
      }
    });
  });
  
  // Efecto visual para productos de interés
  const productCards = document.querySelectorAll('.interest-products .product-card');
  
  if (productCards.length) {
    setTimeout(() => {
      productCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.transition = 'all 0.5s ease';
          card.style.boxShadow = '0 8px 25px rgba(59, 136, 255, 0.2)';
          
          setTimeout(() => {
            card.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
          }, 1000);
        }, index * 200);
      });
    }, 2000);
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>