<?php
// modulos/carrito/checkout.php

// 0) Permitir inclusión segura
define('ACCESO_PERMITIDO', true);

// 1) Carga configuración y helpers
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';

// 2) Obtener carrito y totales
session_start();
$items       = Carrito::obtener();
$total       = Carrito::calcularTotal($items);
$saldoActual = $_SESSION['wallet_saldo'] ?? 0;

include '../../includes/header.php';
?>

<div class="container">
  <h2>Resumen de tu pedido</h2>
  <!-- Aquí tu listado de productos… -->
  <p><strong>Total:</strong> <?= MONEDA . number_format($total, 2) ?></p>

  <h3>Detalles de pago</h3>
  <p>Tu saldo actual: <?= MONEDA . number_format($saldoActual, 2) ?></p>

  <?php if ($saldoActual >= $total): ?>
    <!-- Pagas con Wallet -->
    <form method="POST" action="<?= URL_SITIO ?>carrito/pagar_wallet">
      <button type="submit" class="btn btn-primary">Pagar con Wallet</button>
    </form>

  <?php else: ?>
    <!-- Ofreces recarga o Clip -->
    <p class="text-danger">
      Tu saldo es insuficiente. Necesitas <?= MONEDA . number_format($total - $saldoActual,2) ?> más.
    </p>
    <a href="<?= URL_SITIO ?>wallet/recargar" class="btn btn-secondary">Recargar Wallet</a>
    <hr>
    <button id="checkout-clip" class="btn btn-danger">
      Pagar <?= MONEDA . number_format($total,2) ?> con Clip
    </button>
  <?php endif; ?>

  <hr>
  <a href="<?= URL_SITIO ?>carrito/ver" class="btn btn-outline">Volver al carrito</a>
</div>

<?php include '../../includes/footer.php'; ?>

<?php if ($saldoActual < $total): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('checkout-clip');
  btn.addEventListener('click', async () => {
    const total = <?= json_encode($total) ?>;
    try {
      const res = await fetch('<?= URL_SITIO ?>carrito/crear-clip-session', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ total })
      });
      const json = await res.json();
      if (res.ok && json.paymentUrl) {
        window.location.href = json.paymentUrl;
      } else {
        alert('Error al iniciar pago: ' + (json.error || res.statusText));
      }
    } catch (e) {
      alert('Error de red: ' + e.message);
    }
  });
});
</script>
<?php endif; ?>
