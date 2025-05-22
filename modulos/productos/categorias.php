
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
$sql = "SELECT c.*, COUNT(p.id) AS total_productos
        FROM categorias c
        LEFT JOIN productos p ON c.id = p.categoria_id
        GROUP BY c.id
        ORDER BY c.nombre";
$res = $conexion->query($sql);
$titulo = "Categorías";
include __DIR__ . '/../../includes/header.php';
?>
<div class="container">
  <h1>Explora nuestras categorías</h1>
  <?php if (!$res || $res->num_rows === 0): ?>
    <p>No hay categorías disponibles.</p>
    <a href="<?= URL_SITIO ?>productos" class="btn">Ver todos los productos</a>
  <?php else: ?>
    <div class="categories-container">
      <?php while ($c = $res->fetch_assoc()): ?>
        <?php
          $rutaImg = !empty($c['imagen'])
              ? URL_SITIO . $c['imagen']
              : URL_SITIO . 'img/categoria-default.jpg';
        ?>
        <div class="category-card">
          <div class="categoria-img-container">
            <img src="<?= $rutaImg ?>"
                 alt="<?= htmlspecialchars($c['nombre']) ?>"
                 class="categoria-img">
          </div>
         <div class="category-info">
  <h2 class="categoria-title"><?= htmlspecialchars($c['nombre']) ?></h2>
  <p class="categoria-description"><?= (int)$c['total_productos'] ?> productos</p>
  <!-- Único <a> bien formado -->
  <a 
    href="<?= URL_SITIO ?>productos.php?categoria=<?= (int)$c['id'] ?>" 
    class="categoria-btn"
  >
    Ver productos
  </a>
</div>

        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>