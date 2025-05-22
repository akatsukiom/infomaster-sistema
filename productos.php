<?php
// productos.php

// 0) Permitir acceso directo
define('ACCESO_PERMITIDO', true);

// 1) Cargar configuración y modelos
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/modulos/admin/categorias/modelo.php';
require_once __DIR__ . '/modulos/admin/productos/modelo.php';

// 2) Leer y sanitizar ID de categoría
$categoriaId = (isset($_GET['categoria']) && is_numeric($_GET['categoria']))
    ? (int) $_GET['categoria']
    : 0;

// 3) Instanciar los modelos
$catModel  = new Categoria($conexion);
$prodModel = new Producto($conexion);

// 4) Obtener datos de la categoría y sus productos
$categoria = $catModel->obtenerPorId($categoriaId);
$titulo    = 'Productos en «' . htmlspecialchars($categoria['nombre'] ?? 'Categoría desconocida') . '»';
$productos = $prodModel->obtenerTodos($categoriaId);

// 5) Incluir el header de tu sitio
include __DIR__ . '/includes/header.php';
?>

<section class="featured">
  <div class="container py-5">
    <div class="section-title">
      <h2><?= $titulo ?></h2>
    </div>

    <?php if (empty($productos)): ?>
      <div class="alert alert-info">No hay productos en esta categoría.</div>
    <?php else: ?>
      <div class="products">
        <?php foreach ($productos as $p): ?>
          <div class="product">
            <?php if (!empty($p['imagen'])): ?>
              <img
                src="<?= URL_SITIO . ltrim($p['imagen'], '/') ?>"
                alt="<?= htmlspecialchars($p['nombre']) ?>"
                class="product-img"
              >
            <?php else: ?>
              <div class="no-image">Sin imagen</div>
            <?php endif; ?>

            <div class="overlay">
              <!-- Ver detalles -->
              <a
  href="<?= URL_SITIO ?>detalle_producto.php?id=<?= $p['id'] ?>"
                class="btn-small btn-view"
                title="Ver detalles"
              >
                <i class="fas fa-eye"></i> Ver
              </a>

              <!-- Añadir al carrito, redirigiendo de vuelta a esta página -->
              <a
                href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $p['id'] ?>&redirigir=<?= urlencode("productos.php?categoria={$categoriaId}") ?>"
                class="btn-small btn-cart"
                title="Añadir al carrito"
              >
                <i class="fas fa-shopping-cart"></i>
              </a>
            </div>

            <div class="product-info">
              <h3 class="product-title"><?= htmlspecialchars($p['nombre']) ?></h3>
              <div class="product-price">
                <?= MONEDA . number_format($p['precio_base'], 2) ?>
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


<!-- Agrega este CSS en tu archivo de estilos o en el header -->
<style>
  .products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
  }
  
  .product {
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
    position: relative;
  }
  
  .product:hover {
    transform: translateY(-5px);
  }
  
  .product-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
  }
  
  .no-image {
    width: 100%;
    height: 200px;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
  }
  
  .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 200px;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .product:hover .overlay {
    opacity: 1;
  }
  
  .btn-small {
    padding: 8px 15px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  
  .btn-view {
    background-color: #fff;
    color: #333;
  }
  
  .btn-view:hover {
    background-color: #f0f0f0;
  }
  
  .btn-cart {
    background-color: #4caf50;
    color: white;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    padding: 0;
  }
  
  .btn-cart:hover {
    background-color: #3e8e41;
  }
  
  .product-info {
    padding: 15px;
  }
  
  .product-title {
    font-size: 16px;
    margin: 0 0 10px;
    height: 40px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
  
  .product-price {
    font-weight: bold;
    color: #4caf50;
  }
  
  .alert-info {
    background-color: #e3f2fd;
    color: #0288d1;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }
  
  .btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 10px 20px;
    border: 1px solid #333;
    border-radius: 30px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
  }
  
  .btn-outline:hover {
    background-color: #333;
    color: #fff;
  }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>