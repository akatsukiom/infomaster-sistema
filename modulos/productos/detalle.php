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
// Obtener productos aleatorios diferentes al actual y de categoría diferente
// para garantizar que se muestren productos distintos
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

<section class="product-detail">
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="<?= URL_SITIO ?>">Inicio</a>
      <span class="separator">›</span>
      <a href="<?= URL_SITIO ?>productos">Productos</a>
      <?php if (!empty($info['categoria'])): ?>
        <span class="separator">›</span>
        <a href="<?= URL_SITIO ?>productos?categoria=<?= (int)$info['categoria_id'] ?>">
          <?= htmlspecialchars($info['categoria']) ?>
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
          <span class="product-category"><?= htmlspecialchars($info['categoria']) ?></span>
        <?php endif; ?>
        <h1 class="product-title"><?= htmlspecialchars($info['nombre']) ?></h1>

        <!-- Precio dinámico -->
        <div class="product-price">
          <strong>Precio:</strong>
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
          <h3>Descripción</h3>
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
            <label for="tipo_plan">Tipo de plan:</label>
            <select name="tipo_plan" id="tipo_plan" class="form-control">
              <option value="individual">Individual</option>
              <option value="completo">Cuenta completa</option>
            </select>
          </div>

          <!-- Duración (cambiará dinámicamente según el tipo de plan) -->
          <div class="mb-3">
            <label for="duracion">Duración:</label>
            <select name="duracion" id="duracion" class="form-control">
              <!-- Se llenará con JavaScript -->
            </select>
          </div>

          <!-- Cantidad -->
          <div class="quantity-selector mb-4">
            <label for="cantidad">Cantidad:</label>
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
              Agregar al carrito
            </button>
            <button type="button" id="comprar-ahora" class="btn btn-secondary">
              Comprar ahora
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Productos relacionados -->
    <?php if (!empty($relacionados)): ?>
      <section class="related-products">
        <h2>Productos relacionados</h2>
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
                  <a href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $r['id'] ?>" class="btn">
                    Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $r['id'] ?>&redirigir=carrito/ver" class="btn btn-secondary">
                    Comprar
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

    <!-- Productos de interés (NUEVA SECCIÓN) -->
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
                    <span class="original-price" style="font-size: 14px; margin-left: 8px;">
                      <?= MONEDA . number_format($pi['precio_anterior'], 2) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="product-card-actions">
                  <a href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $pi['id'] ?>" class="btn">
                    Ver detalles
                  </a>
                  <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $pi['id'] ?>&redirigir=carrito/ver" class="btn btn-secondary">
                    Comprar
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
/* Estilos para la nueva sección de productos de interés */
.section-separator {
  height: 1px;
  background-color: #eee;
  margin: 40px 0;
}

.interest-products {
  margin-top: 60px;
}

.interest-products h2 {
  font-size: 24px;
  margin-bottom: 25px;
  color: #333;
  position: relative;
  padding-bottom: 10px;
}

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

.product-card {
  position: relative;
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

/* Estilos para selectores de plan y duración */
.form-control {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  color: #333;
  background-color: white;
  transition: all 0.2s;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 16px 12px;
  cursor: pointer;
}

.form-control:focus {
  border-color: #3b88ff;
  box-shadow: 0 0 0 3px rgba(59, 136, 255, 0.25);
  outline: none;
}

.form-control:disabled {
  background-color: #e9ecef;
  cursor: not-allowed;
  opacity: 0.7;
}

.mb-3 {
  margin-bottom: 20px;
}

.mb-4 {
  margin-bottom: 25px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #444;
}

/* Botones de cantidad */
.quantity-selector {
  margin-top: 20px;
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
  margin-top: 15px;
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

/* Estilos específicos para las tarjetas de productos en "Te podría interesar" */
.interest-products .product-card {
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
}

.interest-products .product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.interest-products .product-card-img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.interest-products .product-card-info {
  padding: 20px;
}

.interest-products .product-card-title {
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

.interest-products .product-card-price {
  color: #ff3366;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 15px;
}

.interest-products .product-card-actions {
  display: flex;
  gap: 10px;
}

.interest-products .product-card-actions .btn {
  padding: 8px 12px;
  font-size: 14px;
  flex: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Elementos del DOM
  const duracionSelect = document.getElementById('duracion');
  const tipoPlanSelect = document.getElementById('tipo_plan');
  const displayPrecio = document.getElementById('price-valor');
  
  // Moneda
  const MONEDA = '<?= MONEDA ?>';
  
  // Precios desde PHP - Asegurar valores
  const precios = {
    individual: {
      1: <?= $price['1'] ?? 0 ?>,
      3: <?= $price['3'] ?? 0 ?>,
      12: <?= $price['12'] ?? 0 ?>
    },
    completo: {
      1: <?= $price['completo'] ?? 0 ?>
    }
  };
  
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
    // Si no hay precio para cuenta completa, deshabilitar esa opción
    for (let i = 0; i < tipoPlanSelect.options.length; i++) {
      if (tipoPlanSelect.options[i].value === 'completo') {
        tipoPlanSelect.options[i].disabled = true;
      }
    }
  }
  
  // Función para actualizar las opciones de duración según el tipo de plan
  function actualizarOpcionesDuracion() {
    const tipoPlan = tipoPlanSelect.value;
    
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
                        
      if (precioValor <= 0) return; // No añadir opciones sin precio
      
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
    
    // Mostrar el precio
    displayPrecio.textContent = MONEDA + precio.toFixed(2);
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
    cantidadInput.value = Math.max(1, parseInt(cantidadInput.value) - 1);
  });
  
  btnMas.addEventListener('click', () => {
    cantidadInput.value = Math.min(10, parseInt(cantidadInput.value) + 1);
  });
  
  // Comprar ahora
  document.getElementById('comprar-ahora').addEventListener('click', () => {
    const form = document.querySelector('.producto-compra');
    form.action = '<?= URL_SITIO ?>modulos/carrito/agregar.php?redirigir=checkout';
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
      if (thumb.src.includes('thumb')) {
        mainImage.src = thumb.src.replace('thumb', 'default');
      } else {
        mainImage.src = thumb.src;
      }
    });
  });
  
  // Inicializar animaciones para productos de interés
  const productCards = document.querySelectorAll('.interest-products .product-card');
  
  // Añadir efecto sutil de highlight a productos de interés
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>