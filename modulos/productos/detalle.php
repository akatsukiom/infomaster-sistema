<?php
// modulos/productos/detalle.php

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Evitar accesos directos
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

// 1) Cargar configuración y funciones
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// 2) Cargar modelo de Producto y Carrito
require_once __DIR__ . '/modelo.php';
require_once __DIR__ . '/../carrito/modelo.php';

// 3) Validar parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    mostrarMensaje('ID de producto no válido', 'error');
    redireccionar(URL_SITIO . 'productos');
}
$producto_id = (int) $_GET['id'];

// 4) Obtener información del producto
$producto = new Producto($conexion);
$info = $producto->obtenerPorId($producto_id);
if (!$info) {
    mostrarMensaje('Producto no encontrado', 'error');
    redireccionar(URL_SITIO . 'productos');
}

// 5) Productos relacionados
$relacionados = [];
if (!empty($info['categoria_id'])) {
    $todos = $producto->obtenerTodos((int)$info['categoria_id']);
    $relacionados = array_filter($todos, fn($p) => $p['id'] !== $producto_id);
    $relacionados = array_slice($relacionados, 0, 4);
}

// 6) Precios desde BD
$price = [
  'base'      => (float)($info['precio_base']    ?? 0),
  '1'         => (float)($info['precio_1_mes']   ?? 0),
  '3'         => (float)($info['precio_3_meses'] ?? 0),
  '12'        => (float)($info['precio_12_meses'] ?? 0),
  'completo'  => (float)($info['precio_completo'] ?? 0),
];

// 7) Productos de interés - NUEVA IMPLEMENTACIÓN
$sql = "SELECT 
            p.*, 
            c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id != ? AND p.categoria_id != ?
        ORDER BY RAND()
        LIMIT 4";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $producto_id, $info['categoria_id']);
$stmt->execute();
$res = $stmt->get_result();
$productosInteres = $res->fetch_all(MYSQLI_ASSOC);

// Verificar disponibilidad de precios
$tienePrecioIndividual = $price['1'] > 0;
$tienePrecioCompleto = $price['completo'] > 0;

$titulo = htmlspecialchars($info['nombre']) . ' – Detalle';
include __DIR__ . '/../../includes/header.php';
?>

<style>
/* ===== CSS GLASSMORPHISM PARA DETALLE DE PRODUCTO ===== */

/* Corregir espaciado del header */
body {
    padding-top: 140px !important;
}

.container {
    margin-top: 2rem !important;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
    padding: 0 1rem;
}

/* Breadcrumb */
.breadcrumb {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.breadcrumb a,
.breadcrumb span {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 500;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.breadcrumb a:hover {
    color: white;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

.breadcrumb .separator {
    margin: 0 0.5rem;
    color: rgba(255, 255, 255, 0.6);
}

.breadcrumb .current {
    color: #4ade80;
    font-weight: 700;
}

/* Sección principal */
.product-detail {
    min-height: 80vh;
}

/* Grid principal del producto */
.product-grid {
    display: grid;
    grid-template-columns: 500px 1fr;
    gap: 3rem;
    align-items: start;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.product-grid::before {
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

/* Galería de imágenes */
.product-gallery {
    position: relative;
    z-index: 1;
}

.main-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease;
    margin-bottom: 1rem;
}

.main-image:hover {
    transform: scale(1.02);
}

.gallery-thumbnails {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.gallery-thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 12px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0.7;
}

.gallery-thumbnail.active,
.gallery-thumbnail:hover {
    opacity: 1;
    border-color: rgba(255, 255, 255, 0.5);
    transform: scale(1.1);
}

/* Información del producto */
.product-info {
    position: relative;
    z-index: 1;
}

.product-category {
    background: rgba(102, 126, 234, 0.8);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    width: fit-content;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
    display: inline-block;
}

.product-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    color: white;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.product-price {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.product-price strong {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin-right: 1rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

#price-valor {
    font-size: 2rem;
    font-weight: 900;
    color: #4ade80;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.original-price {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: line-through;
    font-size: 1.2rem;
    margin-left: 1rem;
}

.discount-badge {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    margin-left: 1rem;
}

/* Descripción */
.product-description {
    background: rgba(255, 255, 255, 0.1);
    padding: 2rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    margin-bottom: 2rem;
}

.product-description h3 {
    color: white;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.product-description p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.product-description ul {
    list-style: none;
    padding: 0;
}

.product-description li {
    color: rgba(255, 255, 255, 0.8);
    padding: 0.5rem 0;
    position: relative;
    padding-left: 2rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.product-description li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #4ade80;
    font-weight: 700;
    font-size: 1.2rem;
}

/* Formulario de compra */
.producto-compra {
    background: rgba(255, 255, 255, 0.1);
    padding: 2rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.mb-3 {
    margin-bottom: 1.5rem;
}

.mb-4 {
    margin-bottom: 2rem;
}

label {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.form-control {
    width: 100%;
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px 12px;
    cursor: pointer;
}

.form-control:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.form-control option {
    background: #1e293b;
    color: white;
}

/* Control de cantidad */
.quantity-selector {
    margin-bottom: 2rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    width: fit-content;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    padding: 0.3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    font-weight: 700;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.quantity-input {
    width: 80px !important;
    text-align: center;
    border: none !important;
    background: transparent !important;
    font-weight: 700;
    margin: 0 0.5rem;
    background-image: none !important;
}

/* Botones de acción */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    flex: 1;
    padding: 1.2rem 2rem;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white !important;
    box-shadow: 0 8px 20px rgba(67, 233, 123, 0.3);
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
}

.btn-primary:hover {
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.btn-secondary:hover {
    box-shadow: 0 15px 35px rgba(67, 233, 123, 0.4);
}

/* Secciones de productos */
.related-products,
.interest-products {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    margin-bottom: 3rem;
}

.related-products h2,
.interest-products h2 {
    color: white;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 2rem;
    text-align: center;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.product-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.product-card::before {
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

.product-card:hover::before {
    opacity: 1;
}

.product-card:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    border-color: rgba(255, 255, 255, 0.25);
}

.product-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 1;
}

.product-card-info {
    position: relative;
    z-index: 1;
}

.product-card-title {
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.3;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.product-card-price {
    color: #4ade80;
    font-size: 1.3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.product-card-actions {
    display: flex;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
}

.product-card-actions .btn {
    padding: 0.8rem 1rem;
    font-size: 0.9rem;
}

/* Badges */
.oferta-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    z-index: 2;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Section separator */
.section-separator {
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    margin: 3rem 0;
    border-radius: 1px;
}

/* Responsive */
@media (max-width: 1200px) {
    .product-grid {
        grid-template-columns: 450px 1fr;
        gap: 2rem;
    }
}

@media (max-width: 992px) {
    .product-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .main-image {
        height: 350px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

@media (max-width: 768px) {
    body {
        padding-top: 160px !important;
    }
    
    .container {
        padding: 0 0.5rem;
    }
    
    .product-grid,
    .related-products,
    .interest-products {
        padding: 2rem;
    }
    
    .product-title {
        font-size: 2rem;
    }
    
    #price-valor {
        font-size: 1.5rem;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .main-image {
        height: 300px;
    }
}

@media (max-width: 480px) {
    body {
        padding-top: 180px !important;
    }
    
    .product-grid,
    .related-products,
    .interest-products {
        padding: 1.5rem;
    }
    
    .product-title {
        font-size: 1.8rem;
    }
    
    .quantity-control {
        justify-content: center;
        margin: 0 auto;
    }
    
    .gallery-thumbnails {
        flex-wrap: wrap;
    }
}
</style>

<section class="product-detail">
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="<?= URL_SITIO ?>">🏠 Inicio</a>
      <span class="separator">›</span>
      <a href="<?= URL_SITIO ?>productos">📦 Productos</a>
      <?php if (!empty($info['categoria'])): ?>
        <span class="separator">›</span>
        <a href="<?= URL_SITIO ?>productos?categoria=<?= (int)$info['categoria_id'] ?>">
          🏷️ <?= htmlspecialchars($info['categoria']) ?>
        </a>
      <?php endif; ?>
      <span class="separator">›</span>
      <span class="current"><?= htmlspecialchars($info['nombre']) ?></span>
    </nav>

    <div class="product-grid">
      <!-- Galería -->
      <div class="product-gallery">
        <img
          src="<?= $info['imagen']
                    ? URL_SITIO . $info['imagen']
                    : URL_SITIO . 'img/producto-default.jpg' ?>"
          alt="<?= htmlspecialchars($info['nombre']) ?>"
          class="main-image"
        >
        <div class="gallery-thumbnails">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <img
              src="<?= $i === 1
                        ? ($info['imagen']
                            ? URL_SITIO . $info['imagen']
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
        <?php if (!empty($info['categoria'])): ?>
          <span class="product-category">🏷️ <?= htmlspecialchars($info['categoria']) ?></span>
        <?php endif; ?>
        <h1 class="product-title"><?= htmlspecialchars($info['nombre']) ?></h1>

        <!-- Precio dinámico -->
        <div class="product-price">
          <strong>💰 Precio:</strong>
          <span id="price-valor"><?= MONEDA . number_format($price['1'], 2) ?></span>
          <?php if (!empty($info['precio_anterior']) && $info['precio_anterior'] > $info['precio_base']): ?>
            <span class="original-price">
              <?= MONEDA . number_format($info['precio_anterior'], 2) ?>
            </span>
            <span class="discount-badge">
              <?= round((1 - $info['precio_base'] / $info['precio_anterior']) * 100) ?>% OFF
            </span>
          <?php endif; ?>
        </div>

        <div class="product-description">
          <h3>📋 Descripción</h3>
          <p><?= nl2br(htmlspecialchars($info['descripcion'])) ?></p>
          <ul>
            <li>Entrega inmediata tras confirmación de pago</li>
            <li>Soporte técnico incluido</li>
            <li>Garantía de funcionamiento</li>
            <li>Tutoriales disponibles</li>
          </ul>
        </div>

        <form method="GET"
              action="<?= URL_SITIO ?>modulos/carrito/agregar.php"
              class="producto-compra"
        >
          <input type="hidden" name="id" value="<?= $producto_id ?>">
          <input type="hidden" name="redirigir" value="carrito/ver">

          <!-- Tipo de plan -->
          <div class="mb-3">
            <label for="tipo_plan">📋 Tipo de plan:</label>
            <select name="tipo_plan" id="tipo_plan" class="form-control">
              <option value="individual">👤 Individual</option>
              <option value="completo">👥 Cuenta completa (7 perfiles)</option>
            </select>
          </div>

          <!-- Duración -->
          <div class="mb-3">
            <label for="duracion">⏰ Duración:</label>
            <select name="duracion" id="duracion" class="form-control">
              <!-- Se llenará con JavaScript -->
            </select>
          </div>

          <!-- Cantidad -->
          <div class="quantity-selector mb-4">
            <label for="cantidad">🔢 Cantidad:</label>
            <div class="quantity-control">
              <button type="button" id="btn-menos" class="quantity-btn">−</button>
              <input type="number" id="cantidad" name="cantidad"
                     value="1" min="1" max="10" class="quantity-input">
              <button type="button" id="btn-mas" class="quantity-btn">+</button>
            </div>
          </div>

          <!-- Botones -->
          <div class="action-buttons">
            <button type="submit" name="agregar" class="btn btn-primary">
              🛒 Agregar al carrito
            </button>
            <button type="button" id="comprar-ahora" class="btn btn-secondary">
              🚀 Comprar ahora
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Productos relacionados -->
    <?php if (!empty($relacionados)): ?>
      <section class="related-products">
        <h2>🔗 Productos relacionados</h2>
        <div class="products-grid">
          <?php foreach ($relacionados as $r): ?>
            <div class="product-card">
              <img
                src="<?= $r['imagen']
                          ? URL_SITIO . $r['imagen']
                          : URL_SITIO . 'img/producto-default.jpg' ?>"
                alt="<?= htmlspecialchars($r['nombre']) ?>"
                class="product-card-img"
              >
              <div class="product-card-info">
                <h3 class="product-card-title"><?= htmlspecialchars($r['nombre']) ?></h3>
                <div class="product-card-price">
                  <?= MONEDA . number_format($r['precio_1_mes'] ?? $r['precio_base'] ?? 0, 2) ?>
                </div>
                <div class="product-card-actions">
                  <a href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $r['id'] ?>" class="btn btn-primary">
                    👁️ Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $r['id'] ?>&redirigir=carrito/ver" class="btn btn-secondary">
                    🛒 Comprar
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
        <h2>💡 Te podría interesar</h2>
        <div class="products-grid">
          <?php foreach ($productosInteres as $pi): ?>
            <div class="product-card">
              <?php if (!empty($pi['destacado']) && $pi['destacado']): ?>
                <span class="oferta-badge">🔥 Oferta</span>
              <?php endif; ?>
              <img
                src="<?= $pi['imagen']
                          ? URL_SITIO . $pi['imagen']
                          : URL_SITIO . 'img/producto-default.jpg' ?>"
                alt="<?= htmlspecialchars($pi['nombre']) ?>"
                class="product-card-img"
              >
              <div class="product-card-info">
                <h3 class="product-card-title"><?= htmlspecialchars($pi['nombre']) ?></h3>
                <div class="product-card-price">
                  <?= MONEDA . number_format($pi['precio_1_mes'] ?? $pi['precio_base'] ?? 0, 2) ?>
                  <?php if (!empty($pi['precio_anterior']) && $pi['precio_anterior'] > ($pi['precio_1_mes'] ?? $pi['precio_base'])): ?>
                    <span class="original-price" style="font-size: 0.9rem; margin-left: 8px;">
                      <?= MONEDA . number_format($pi['precio_anterior'], 2) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="product-card-actions">
                  <a href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $pi['id'] ?>" class="btn btn-primary">
                    👁️ Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $pi['id'] ?>&redirigir=carrito/ver" class="btn btn-secondary">
                    🛒 Comprar
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

<script>
document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 Detalle de producto - Inicializando');
  
  // Elementos del DOM
  const duracionSelect = document.getElementById('duracion');
  const tipoPlanSelect = document.getElementById('tipo_plan');
  const displayPrecio = document.getElementById('price-valor');
  
  // Moneda
  const MONEDA = '<?= MONEDA ?>';
  
  // Precios desde PHP - Asegurar valores
  const precios = {
    individual: {
      1: <?= $price['1'] > 0 ? $price['1'] : $price['base'] ?>,
      3: <?= $price['3'] ?? 0 ?>,
      12: <?= $price['12'] ?? 0 ?>
    },
    completo: {
      1: <?= $price['completo'] ?? 0 ?>
    }
  };
  
  console.log('💰 Precios cargados:', precios);
  
  // Opciones de duración para cada tipo de plan
  const duracionesPorPlan = {
    individual: [
      { valor: '1', texto: '1 mes (' + MONEDA + precios.individual[1].toFixed(2) + ')' },
      { valor: '3', texto: '3 meses (' + MONEDA + precios.individual[3].toFixed(2) + ')' },
      { valor: '12', texto: '12 meses (' + MONEDA + precios.individual[12].toFixed(2) + ')' }
    ],
    completo: [
      { valor: 'completo', texto: '1 mes (' + MONEDA + precios.completo[1].toFixed(2) + ')' }
    ]
  };
  
  // Verificar si el precio completo está disponible
  if (precios.completo[1] <= 0) {
    console.log('⚠️ Precio completo no disponible, deshabilitando opción');
    for (let i = 0; i < tipoPlanSelect.options.length; i++) {
      if (tipoPlanSelect.options[i].value === 'completo') {
        tipoPlanSelect.options[i].disabled = true;
        tipoPlanSelect.options[i].textContent += ' (No disponible)';
      }
    }
  }
  
  // Función para actualizar las opciones de duración según el tipo de plan
  function actualizarOpcionesDuracion() {
    const tipoPlan = tipoPlanSelect.value;
    console.log('🔄 Actualizando opciones para:', tipoPlan);
    
    // Limpiar opciones actuales
    duracionSelect.innerHTML = '';
    
    // Añadir opciones según el tipo de plan
    const opciones = duracionesPorPlan[tipoPlan] || [];
    let opcionesAgregadas = 0;
    
    opciones.forEach(opcion => {
      // Solo añadir si hay precio para esa opción
      const precioValor = tipoPlan === 'completo' ? 
                         precios.completo[1] : 
                         precios.individual[opcion.valor];
                        
      if (precioValor <= 0) {
        console.log('⚠️ Precio no disponible para:', opcion.valor);
        return; // No añadir opciones sin precio
      }
      
      const option = document.createElement('option');
      option.value = opcion.valor;
      option.textContent = opcion.texto;
      duracionSelect.appendChild(option);
      opcionesAgregadas++;
    });
    
    // Si no hay opciones, añadir una opción por defecto
    if (opcionesAgregadas === 0) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'No hay opciones disponibles';
      option.disabled = true;
      duracionSelect.appendChild(option);
    }
    
    // Actualizar precio mostrado
    actualizarPrecio();
  }
  
  // Función para actualizar el precio mostrado
  function actualizarPrecio() {
    const tipoPlan = tipoPlanSelect.value;
    const duracion = duracionSelect.value;
    
    let precio = 0;
    if (tipoPlan === 'completo') {
      precio = precios.completo[1];
    } else {
      precio = precios.individual[duracion] || 0;
    }
    
    console.log('💰 Precio actualizado:', precio);
    
    // Mostrar el precio con animación
    displayPrecio.style.transform = 'scale(0.8)';
    setTimeout(() => {
      displayPrecio.textContent = MONEDA + precio.toFixed(2);
      displayPrecio.style.transform = 'scale(1)';
    }, 150);
  }
  
  // Eventos
  tipoPlanSelect.addEventListener('change', actualizarOpcionesDuracion);
  duracionSelect.addEventListener('change', actualizarPrecio);
  
  // Inicializar las opciones de duración
  actualizarOpcionesDuracion();
  
  // Control de cantidad
  const cantidadInput = document.getElementById('cantidad');
  const btnMenos = document.getElementById('btn-menos');
  const btnMas = document.getElementById('btn-mas');
  
  btnMenos.addEventListener('click', () => {
    const valorActual = parseInt(cantidadInput.value);
    if (valorActual > 1) {
      cantidadInput.value = valorActual - 1;
      console.log('➖ Cantidad:', cantidadInput.value);
    }
  });
  
  btnMas.addEventListener('click', () => {
    const valorActual = parseInt(cantidadInput.value);
    if (valorActual < 10) {
      cantidadInput.value = valorActual + 1;
      console.log('➕ Cantidad:', cantidadInput.value);
    }
  });
  
  // Comprar ahora
  document.getElementById('comprar-ahora').addEventListener('click', () => {
    console.log('🚀 Comprar ahora clicked');
    const form = document.querySelector('.producto-compra');
    const originalAction = form.action;
    form.action = '<?= URL_SITIO ?>modulos/carrito/agregar.php?redirigir=checkout';
    form.submit();
  });
  
  // Galería de imágenes
  const thumbnails = document.querySelectorAll('.gallery-thumbnail');
  const mainImage = document.querySelector('.main-image');
  
  thumbnails.forEach((thumb, index) => {
    thumb.addEventListener('click', () => {
      console.log('🖼️ Thumbnail clicked:', index);
      
      // Quitar clase active de todos los thumbnails
      thumbnails.forEach(t => t.classList.remove('active'));
      
      // Añadir clase active al thumbnail seleccionado
      thumb.classList.add('active');
      
      // Actualizar imagen principal con efecto
      mainImage.style.opacity = '0.7';
      setTimeout(() => {
        if (thumb.src.includes('thumb')) {
          mainImage.src = thumb.src.replace('thumb', 'default');
        } else {
          mainImage.src = thumb.src;
        }
        mainImage.style.opacity = '1';
      }, 200);
    });
  });
  
  // Inicializar animaciones para productos de interés
  const productCards = document.querySelectorAll('.interest-products .product-card');
  
  // Añadir efecto sutil de highlight a productos de interés
  if (productCards.length) {
    console.log('✨ Inicializando animaciones para', productCards.length, 'productos');
    setTimeout(() => {
      productCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.transition = 'all 0.5s ease';
          card.style.transform = 'translateY(-5px)';
          card.style.boxShadow = '0 15px 30px rgba(67, 233, 123, 0.2)';
          
          setTimeout(() => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 25px 50px rgba(0, 0, 0, 0.25)';
          }, 1000);
        }, index * 200);
      });
    }, 2000);
  }
  
  console.log('✅ Detalle de producto - Inicializado correctamente');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>