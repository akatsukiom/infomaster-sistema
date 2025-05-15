<?php
// public_html/modulos/productos/productos.php

// Mostrar errores en desarrollo (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Permitir el acceso (evitar accesos directos)
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

// 2) Cargar configuración, utilidades y modelo
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// 3) Instanciar y obtener todos los productos
$modelo    = new Producto($conexion);
$productos = $modelo->obtenerTodos();

// 4) Título de la página
$titulo = "Nuestros Productos";
include __DIR__ . '/../../includes/header.php';
?>

<!-- Estilos específicos para el grid y tarjetas -->
<style>
  .productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 30px;
    margin: 30px 0;
  }

  .tarjeta-producto {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .tarjeta-contenido {
    position: relative;
    width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    background-color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .tarjeta-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .btn-comprar {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(255,255,255,0.9);
    color: #000;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 14px;
    text-decoration: none;
  }

  .btn-agregar-chico {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: rgba(255,255,255,0.8);
    color: #000;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    text-decoration: none;
  }

  .producto-nombre {
    font-weight: bold;
    font-size: 1.1rem;
    margin: 10px 0 5px;
    color: #333;
  }

  .producto-precio {
    font-size: 1.2rem;
    font-weight: bold;
    color: #000;
  }
</style>

<div class="container">
  <h1><?= htmlspecialchars($titulo) ?></h1>

  <?php if (empty($productos)): ?>
    <p>No hay productos para mostrar.</p>
  <?php else: ?>
    <div class="productos-grid">
      <?php foreach ($productos as $p):
        // Determinar color de fondo según nombre
        $bgColor = '#000';
        if (stripos($p['nombre'], 'spotify') !== false)     $bgColor = '#1DB954';
        elseif (stripos($p['nombre'], 'netflix') !== false) $bgColor = '#E50914';
        elseif (stripos($p['nombre'], 'amazon') !== false)  $bgColor = '#00A8E1';
        elseif (stripos($p['nombre'], 'youtube') !== false) $bgColor = '#FF0000';
        elseif (stripos($p['nombre'], 'disney') !== false)  $bgColor = '#0E0B16';
        elseif (stripos($p['nombre'], 'vix') !== false)     $bgColor = '#FFA500';
      ?>
        <div class="tarjeta-producto">
          <div class="tarjeta-contenido" style="background-color: <?= $bgColor ?>;">
            <!-- Imagen -->
            <img
              src="<?= URL_SITIO . ($p['imagen'] ?: 'img/producto-default.jpg') ?>"
              alt="<?= htmlspecialchars($p['nombre']) ?>"
              class="tarjeta-imagen"
            >

            <!-- Botón pequeño Agregar al carrito -->
            <a href="<?= URL_SITIO ?>modulos/carrito/agregar.php?id=<?= $p['id'] ?>&redirigir=carrito/ver"
               class="btn-agregar-chico"
               title="Agregar al carrito">
               🛒
            </a>

            <!-- Botón grande Comprar -->
            <a href="<?= URL_SITIO ?>detalle?id=<?= $p['id'] ?>"
               class="btn-comprar">
               Comprar
            </a>
          </div>

          <!-- Nombre y precio -->
          <h3 class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></h3>
          <div class="producto-precio">
            <?= MONEDA . number_format($p['precio'], 2) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
