<?php
define('ACCESO_PERMITIDO', true);

// 1) Cargar configuración y modelos
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/modulos/admin/categorias/modelo.php';
require_once __DIR__ . '/modulos/admin/productos/modelo.php';

$catModel  = new Categoria($conexion);
$prodModel = new Producto($conexion);

// 2) Inicializar variables
$titulo    = 'Todos los productos';
$productos = [];

// 3) Buscar por texto
if (isset($_GET['buscar']) && trim($_GET['buscar']) !== '') {
$termino = $_GET['buscar'];
    $productos = $prodModel->buscarPorNombre($termino);
    $titulo = "Resultados para «" . htmlspecialchars($termino) . "»";
}
// 4) Filtrar por categoría
elseif (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoriaId = (int) $_GET['categoria'];
    $categoria   = $catModel->obtenerPorId($categoriaId);
    $titulo      = 'Productos en «' . htmlspecialchars($categoria['nombre'] ?? 'Categoría desconocida') . '»';
    $productos   = $prodModel->obtenerTodos($categoriaId);
}
// 5) Mostrar todos
else {
    $productos = $prodModel->obtenerTodos();
}

include __DIR__ . '/includes/header.php';
?>

<section class="featured">
  <div class="container py-5">
    <div class="section-title">
      <h2><?= $titulo ?></h2>
    </div>

    <?php if (empty($productos)): ?>
      <div class="alert alert-info">No hay productos en esta categoría o búsqueda.</div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($productos as $p): ?>
          <div class="product-card">
            <img
              src="<?= $p['imagen'] ? URL_SITIO . ltrim($p['imagen'], '/') : URL_SITIO . 'img/producto-default.jpg' ?>"
              alt="<?= htmlspecialchars($p['nombre']) ?>"
              class="product-card-img"
            >
            <div class="product-card-info">
              <h3 class="product-card-title"><?= htmlspecialchars($p['nombre']) ?></h3>
              <div class="product-card-price">
                <?= MONEDA . number_format($p['precio_base'], 2) ?>
              </div>
              <div class="product-card-actions">
                <a href="<?= URL_SITIO ?>modulos/productos/detalle.php?id=<?= $p['id'] ?>" class="btn btn-primary">
                  Ver detalles
                </a>
                <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $p['id'] ?>&redirigir=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-secondary">
                  Comprar
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="view-all mt-4">
      <a href="<?= URL_SITIO ?>categorias" class="btn-outline">
        <i class="fas fa-arrow-left"></i> Volver a categorías
      </a>
    </div>
  </div>
</section>

<style>
/* Variables para personalización */
:root {
  --primary-color: #2563eb;
  --primary-hover: #1d4ed8;
  --secondary-color: #10b981;
  --secondary-hover: #059669;
  --card-bg: #f8f9fa;
  --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --card-shadow-hover: 0 12px 24px rgba(0, 0, 0, 0.15);
  --border-radius: 20px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Grid de productos mejorado */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 25px;
  margin-top: 40px;
}

/* Tarjeta de producto estilo streaming */
.product-card {
  background-color: var(--card-bg);
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: var(--transition);
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100%;
  text-align: center;
}

.product-card:hover {
  transform: translateY(-10px);
  box-shadow: var(--card-shadow-hover);
}

/* Imagen del producto con efecto hover */
.product-card-img {
  width: 100%;
  height: 200px;
  object-fit: contain;
  display: block;
  transition: var(--transition);
  background-color: white;
  padding: 20px;
}

.product-card:hover .product-card-img {
  transform: scale(1.05);
}

/* Información del producto */
.product-card-info {
  padding: 20px 15px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  background-color: white;
}

/* Título del producto mejorado */
.product-card-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 10px;
  color: #2c3e50;
  line-height: 1.3;
  min-height: 30px;
}

/* Precio con mejor estilo */
.product-card-price {
  color: #2c3e50;
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 20px;
  margin-top: auto;
}

/* Botones de acción estilo streaming */
.product-card-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: auto;
}

.product-card-actions .btn {
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  border-radius: 25px;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Botón primario estilo streaming */
.btn-primary {
  background-color: white;
  color: #333;
  border: 2px solid #333;
}

.btn-primary:hover {
  background-color: #333;
  color: white;
  transform: scale(1.05);
}

/* Botón secundario estilo streaming */
.btn-secondary {
  background-color: #333;
  color: white;
  border: 2px solid #333;
}

.btn-secondary:hover {
  background-color: #555;
  border-color: #555;
  transform: scale(1.05);
}

/* Alerta mejorada */
.alert-info {
  background: #f8f9fa;
  color: #495057;
  padding: 40px;
  border-radius: var(--border-radius);
  margin-bottom: 30px;
  font-weight: 500;
  text-align: center;
  box-shadow: var(--card-shadow);
}

/* Botón de volver estilo minimalista */
.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border: 2px solid #333;
  border-radius: 25px;
  color: #333;
  text-decoration: none;
  transition: var(--transition);
  font-weight: 600;
  background-color: transparent;
  text-transform: uppercase;
  font-size: 14px;
  letter-spacing: 0.5px;
}

.btn-outline:hover {
  background-color: #333;
  color: white;
  transform: scale(1.05);
}

/* Título de sección estilo streaming */
.section-title {
  text-align: center;
  margin-bottom: 40px;
}

.section-title h2 {
  font-size: 36px;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
  letter-spacing: -0.5px;
}

/* Responsive mejorado */
@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 15px;
  }
  
  .product-card-img {
    height: 140px;
    padding: 15px;
  }
  
  .product-card-info {
    padding: 15px 10px;
  }
  
  .product-card-title {
    font-size: 16px;
  }
  
  .product-card-price {
    font-size: 18px;
    margin-bottom: 15px;
  }
  
  .product-card-actions .btn {
    padding: 10px 15px;
    font-size: 12px;
  }
  
  .section-title h2 {
    font-size: 28px;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}

/* Animaciones de entrada */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.product-card {
  animation: fadeInUp 0.6s ease-out forwards;
}

.product-card:nth-child(1) { animation-delay: 0.05s; }
.product-card:nth-child(2) { animation-delay: 0.1s; }
.product-card:nth-child(3) { animation-delay: 0.15s; }
.product-card:nth-child(4) { animation-delay: 0.2s; }
.product-card:nth-child(5) { animation-delay: 0.25s; }
.product-card:nth-child(6) { animation-delay: 0.3s; }

/* Efectos de hover adicionales */
.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.product-card:hover::before {
  transform: scaleX(1);
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>