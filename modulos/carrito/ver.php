<?php
// modulos/carrito/ver.php

define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/../productos/modelo.php';
require_once __DIR__ . '/modelo.php';

// 1) Procesar acciones: eliminar, vaciar o actualizar
if (isset($_GET['accion'])) {
    $accion     = $_GET['accion'];
    $productoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    switch ($accion) {
        case 'eliminar':
            Carrito::eliminar($productoId);
            mostrarMensaje('Producto eliminado del carrito', 'success');
            break;

        case 'vaciar':
            Carrito::vaciar();
            mostrarMensaje('Carrito vaciado correctamente', 'success');
            break;

        case 'actualizar':
            if (isset($_POST['cantidad']) && is_array($_POST['cantidad'])) {
                foreach ($_POST['cantidad'] as $id => $cant) {
                    Carrito::actualizar((int) $id, (int) $cant);
                }
                mostrarMensaje('Carrito actualizado correctamente', 'success');
            }
            break;
    }

    // Redirigir a la misma página sin parámetros
    redireccionar('carrito/ver');
    exit;
}

// 2) Cargar contenido del carrito y enriquecer con datos de producto
$sessionItems      = Carrito::obtener();
$productos_carrito = [];

if (!empty($sessionItems)) {
    $productoModel = new Producto($conexion);
    foreach ($sessionItems as $id => $item) {
        $info = $productoModel->obtenerPorId($id);
        if ($info) {
            $productos_carrito[] = [
                'id'        => $id,
                'nombre'    => $info['nombre'],
                'imagen'    => $info['imagen'],
                'precio'    => $item['precio'],
                'cantidad'  => $item['cantidad'],
                'subtotal'  => $item['precio'] * $item['cantidad'],
                'categoria' => $info['categoria'],
                'opciones'  => isset($item['opciones']) ? $item['opciones'] : []
            ];
        }
    }
}

// 3) Calcular total
$total = Carrito::calcularTotal();

// 4) Incluir header
$titulo = "Mi Carrito";
include __DIR__ . '/../../includes/header.php';
?>

<section class="cart-section">
  <div class="container">
    <div class="section-header">
      <h1>Mi Carrito</h1>
      <p>Revisa tus productos antes de completar la compra</p>
    </div>

    <?php if (empty($productos_carrito)): ?>
      <div class="cart-empty">
        <img src="<?= URL_SITIO ?>img/empty-cart.png" alt="Carrito vacío">
        <h2>Tu carrito está vacío</h2>
        <p>Parece que aún no has agregado productos a tu carrito. Explora nuestro catálogo para encontrar lo que necesitas.</p>
        <a href="<?= URL_SITIO ?>productos" class="btn">Ver productos</a>
      </div>
    <?php else: ?>
      <div class="cart-container">
        <div class="cart-items">
          <h2>Productos (<?= count($productos_carrito) ?>)</h2>

          <?php foreach ($productos_carrito as $p): ?>
            <div class="cart-item">
              <img
                src="<?= $p['imagen'] ? URL_SITIO . $p['imagen'] : URL_SITIO . 'img/producto-default.jpg' ?>"
                alt="<?= htmlspecialchars($p['nombre']) ?>"
                class="cart-item-image"
              >
              <div class="cart-item-info">
                <span class="cart-item-category"><?= htmlspecialchars($p['categoria']) ?></span>
                <h3 class="cart-item-title"><?= htmlspecialchars($p['nombre']) ?></h3>
                <div class="cart-item-price">
                  <?= MONEDA . number_format($p['precio'], 2) ?>
                </div>

                <!-- Mostrar opciones personalizadas -->
                <?php if (!empty($p['opciones'])): ?>
                  <div class="cart-item-options">
                    <small>Duración: <?= (int)$p['opciones']['duracion'] ?> mes(es)</small><br>
                    <small>
                      Plan:
                      <?= $p['opciones']['tipo_plan'] === 'completo'
                          ? 'Completo (7 perfiles)'
                          : 'Individual' ?>
                    </small>
                  </div>
                <?php endif; ?>

                <div class="cart-item-actions">
                  <div class="item-quantity">
                    <button class="quantity-btn btn-menos" data-id="<?= $p['id'] ?>">−</button>
                    <input
                      type="number"
                      class="quantity-input"
                      data-id="<?= $p['id'] ?>"
                      value="<?= $p['cantidad'] ?>"
                      min="1"
                      max="10"
                    >
                    <button class="quantity-btn btn-mas" data-id="<?= $p['id'] ?>">+</button>
                  </div>
                  <a
                    href="<?= URL_SITIO ?>carrito/ver?accion=eliminar&id=<?= $p['id'] ?>"
                    class="remove-item"
                  >
                    <i class="fas fa-trash"></i> Eliminar
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="cart-actions">
            <a href="<?= URL_SITIO ?>productos" class="continue-shopping">
              <i class="fas fa-arrow-left"></i> Continuar comprando
            </a>
            <a
              href="<?= URL_SITIO ?>carrito/ver?accion=vaciar"
              class="clear-cart"
              onclick="return confirm('¿Estás seguro de que deseas vaciar el carrito?')"
            >
              <i class="fas fa-trash"></i> Vaciar carrito
            </a>
          </div>
        </div>

        <div class="cart-summary">
          <h2>Resumen de Compra</h2>

          <div class="summary-item">
            <span class="summary-label">Subtotal</span>
            <span class="summary-value"><?= MONEDA . number_format($total, 2) ?></span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Descuento</span>
            <span class="summary-value">-<?= MONEDA ?>0.00</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">IVA</span>
            <span class="summary-value"><?= MONEDA ?>0.00</span>
          </div>
          <div class="summary-total">
            <span class="summary-total-label">Total</span>
            <span class="summary-total-value"><?= MONEDA . number_format($total, 2) ?></span>
          </div>

          <?php if (estaLogueado()): ?>
            <div class="wallet-balance">
              <span class="balance-label">Tu saldo:</span>
              <span class="balance-value"><?= MONEDA . number_format($_SESSION['usuario_saldo'], 2) ?></span>
            </div>
            <a href="<?= URL_SITIO ?>carrito/checkout" class="checkout-btn">
              Proceder al pago
            </a>
          <?php else: ?>
            <div class="wallet-balance">
              <span class="balance-label">Inicia sesión para continuar</span>
            </div>
            <a
              href="<?= URL_SITIO ?>login?redirigir=carrito/checkout"
              class="checkout-btn"
            >
              Iniciar sesión
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Disminuir cantidad
  document.querySelectorAll('.btn-menos').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const input = document.querySelector(`.quantity-input[data-id="${id}"]`);
      let val = parseInt(input.value, 10);
      if (val > 1) {
        actualizarCantidad(id, val - 1);
      }
    });
  });

  // Aumentar cantidad
  document.querySelectorAll('.btn-mas').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const input = document.querySelector(`.quantity-input[data-id="${id}"]`);
      let val = parseInt(input.value, 10);
      if (val < 10) {
        actualizarCantidad(id, val + 1);
      }
    });
  });

  // Cambio manual
  document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', () => {
      const id = input.dataset.id;
      let val = parseInt(input.value, 10) || 1;
      val = Math.min(Math.max(val, 1), 10);
      actualizarCantidad(id, val);
    });
  });

  function actualizarCantidad(id, cantidad) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= URL_SITIO ?>carrito/ver?accion=actualizar';
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `cantidad[${id}]`;
    input.value = cantidad;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
  }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
