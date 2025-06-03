<?php
// Permitir carga directa de esta página
define('ACCESO_PERMITIDO', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/modulos/admin/categorias/modelo.php';

$catModel   = new Categoria($conexion);
$categorias = $catModel->obtenerTodas();

include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <h1>Nuestras Categorías</h1>
  <div class="row">
    <?php foreach($categorias as $cat): ?>
      <div class="col-md-4">
        <div class="card mb-4">
          <?php if($cat['imagen']): ?>
            <img src="<?= URL_SITIO . ltrim($cat['imagen'],'/') ?>" class="card-img-top">
          <?php endif; ?>
          <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($cat['nombre']) ?></h5>
<a href="<?= URL_SITIO ?>productos.php?categoria=<?= $cat['id'] ?>" …>Ver productos</a>
              Ver productos
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
