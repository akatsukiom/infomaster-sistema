<?php
// ——— Solo mientras depuras ———
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// modulos/carrito/checkout.php
// 0) Permitir inclusión segura
define('ACCESO_PERMITIDO', true);
// 1) Carga configuración y helpers
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';
// Helper function para manejar valores nulos en htmlspecialchars
function safe_html($str) {
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
// 2) Obtener carrito y totales
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$items = Carrito::obtener();
$total = Carrito::calcularTotal($items);

// AÑADIR AQUÍ: Inicializar y calcular el saldo del usuario
global $conexion;
$userId = $_SESSION['usuario_id'] ?? null;
$saldoActual = 0; // Inicializar con un valor predeterminado para evitar errores

if ($userId) {
    $stmtW = $conexion->prepare("
        SELECT IFNULL(SUM(monto), 0)
        FROM transacciones_wallet
        WHERE usuario_id = ?
    ");
    
    if ($stmtW) {
        $stmtW->bind_param("i", $userId);
        $stmtW->execute();
        $stmtW->bind_result($saldoActual);
        $stmtW->fetch();
        $stmtW->close();
    }
}

// Desactivar notificación de errores para producción
// Comenta estas líneas durante el desarrollo si necesitas ver advertencias
// ini_set('display_errors', 0);
// error_reporting(E_ERROR);
include '../../includes/header.php';
?>
<div class="container">
  <h2 class="checkout-title">Resumen de tu pedido</h2>
  
  <?php if (!empty($items)): ?>
  <!-- Tabla de productos -->
  <div class="table-responsive mb-4">
    <table class="table checkout-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): 
              // ———> Obtén el ID real  
              $prodId    = (int)$item['id'];
              $nombreImg = '';
              $nombreProd= 'Producto';

              if ($prodId) {
                  global $conexion;
                  $stmtP = $conexion->prepare("
                    SELECT nombre, imagen 
                      FROM productos 
                     WHERE id = ?
                  ");
                  if ($stmtP) {
                      $stmtP->bind_param("i", $prodId);
                      $stmtP->execute();
                      $stmtP->bind_result($nombreProd, $nombreImg);
                      $stmtP->fetch();
                      $stmtP->close();
                  }
              }
        ?>
          <tr>
            <td class="product-cell">
              <?php if ($nombreImg): ?>
                <img 
                  src="<?= URL_SITIO . ltrim($nombreImg, '/') ?>" 
                  alt="<?= safe_html($nombreProd) ?>" 
                  class="product-thumbnail"
                >
              <?php endif; ?>
              <span class="product-name"><?= safe_html($nombreProd) ?></span>
            </td>
            <td class="price-cell"><?= MONEDA . number_format($item['precio'],2) ?></td>
            <td class="quantity-cell"><?= (int)$item['cantidad'] ?></td>
            <td class="subtotal-cell">
              <?= MONEDA . number_format($item['precio'] * $item['cantidad'],2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="text-right"><strong>Total:</strong></td>
          <td class="total-cell"><strong><?= MONEDA . number_format($total, 2) ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-cart">
      <div class="empty-cart-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <p>No hay productos en tu carrito</p>
      <a href="<?= URL_SITIO ?>productos.php" class="btn btn-primary">Ver productos</a>
    </div>
  <?php endif; ?>
  <h3 class="payment-title">Detalles de pago</h3>
  
  <div class="wallet-balance <?= $saldoActual >= $total ? 'balance-ok' : 'balance-insufficient' ?>">
    <div class="balance-icon">
      <i class="fas fa-wallet"></i>
    </div>
    <div class="balance-info">
      <span class="balance-label">Tu saldo actual:</span>
      <span class="balance-amount"><?= MONEDA . number_format($saldoActual, 2) ?></span>
    </div>
  </div>
  <!-- Siempre mostramos ambas opciones de pago -->
  <div class="payment-action">
    <?php if ($saldoActual >= $total): ?>
      <!-- Pagas con Wallet (si hay suficiente saldo) -->
      <p class="payment-note">Tu saldo es suficiente para completar esta compra.</p>
      <div class="payment-options">
        <form method="POST" action="<?= URL_SITIO ?>carrito/pagar_wallet" class="mb-3">
          <button type="submit" class="btn btn-primary btn-pay">
            <i class="fas fa-wallet"></i> Pagar con Wallet
          </button>
        </form>
        
        <div class="payment-divider">o</div>
        
        <button id="checkout-clip" class="btn btn-danger">
          <i class="fas fa-credit-card"></i> Pagar con medios alternativos
        </button>
      </div>
    <?php else: ?>
      <!-- Si no hay saldo suficiente -->
      <div class="insufficient-balance">
        <p>Tu saldo es insuficiente. Necesitas <span class="missing-amount"><?= MONEDA . number_format($total - $saldoActual, 2) ?></span> más.</p>
      </div>
      
      <div class="payment-options">
        <a href="<?= URL_SITIO ?>wallet/recargar" class="btn btn-secondary">
          <i class="fas fa-plus-circle"></i> Recargar Wallet
        </a>
        
        <div class="payment-divider">o</div>
        
        <button id="checkout-clip" class="btn btn-danger">
          <i class="fas fa-credit-card"></i> Pagar con medios alternativos
        </button>
      </div>
    <?php endif; ?>
  </div>
  
  <hr class="checkout-divider">
  
  <div class="checkout-footer">
    <a href="<?= URL_SITIO ?>carrito/ver" class="btn btn-outline">
      <i class="fas fa-arrow-left"></i> Volver al carrito
    </a>
  </div>
</div>
<style>
/* Estilos para la página de checkout */
.checkout-title {
  margin-bottom: 25px;
  color: #3b88ff;
  font-weight: 600;
  font-size: 24px;
}
.payment-title {
  margin: 30px 0 20px;
  font-size: 20px;
  font-weight: 600;
  color: #343a40;
}
/* Tabla de productos */
.checkout-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  overflow: hidden;
}
.checkout-table thead {
  background-color: #f8f9fa;
}
.checkout-table th {
  padding: 15px;
  border-bottom: 2px solid #e9ecef;
  font-weight: 600;
  color: #495057;
}
.checkout-table td {
  padding: 15px;
  border-bottom: 1px solid #e9ecef;
}
.checkout-table tbody tr:hover {
  background-color: #f8f9fa;
}
.checkout-table tfoot {
  background-color: #f8f9fa;
  font-weight: bold;
}
.checkout-table tfoot td {
  padding: 15px;
}
.product-cell {
  display: flex;
  align-items: center;
}
.product-thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
  margin-right: 15px;
  border: 1px solid #e9ecef;
}
.product-name {
  font-weight: 500;
}
.price-cell, .quantity-cell {
  color: #6c757d;
}
.subtotal-cell {
  font-weight: 600;
  color: #3b88ff;
}
.total-cell {
  font-size: 18px;
  color: #3b88ff;
}
.text-right {
  text-align: right;
}
/* Estilo para carrito vacío */
.empty-cart {
  padding: 60px 20px;
  text-align: center;
  background-color: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 30px;
}
.empty-cart-icon {
  font-size: 60px;
  color: #dee2e6;
  margin-bottom: 20px;
}
.empty-cart p {
  font-size: 18px;
  color: #6c757d;
  margin-bottom: 25px;
}
/* Sección de saldo y pago */
.wallet-balance {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}
.balance-ok {
  background-color: #e3f9e5;
}
.balance-insufficient {
  background-color: #fef5f5;
}
.balance-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 18px;
}
.balance-ok .balance-icon {
  background-color: #38c172;
  color: white;
}
.balance-insufficient .balance-icon {
  background-color: #ff5252;
  color: white;
}
.balance-info {
  display: flex;
  flex-direction: column;
}
.balance-label {
  font-size: 14px;
  color: #6c757d;
  margin-bottom: 5px;
}
.balance-amount {
  font-size: 24px;
  font-weight: 700;
}
.balance-ok .balance-amount {
  color: #38c172;
}
.balance-insufficient .balance-amount {
  color: #ff5252;
}
.payment-action {
  margin-top: 25px;
}
.payment-note {
  color: #38c172;
  font-weight: 500;
  margin-bottom: 15px;
}
.insufficient-balance p {
  color: #ff5252;
  font-weight: 500;
  margin-bottom: 20px;
}
.missing-amount {
  font-weight: 700;
}
.payment-options {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}
.payment-divider {
  color: #6c757d;
  margin: 0 10px;
}
/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  gap: 8px;
}
.btn-primary {
  background-color: #3b88ff;
  color: white;
}
.btn-primary:hover {
  background-color: #2a75eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 136, 255, 0.3);
}
.btn-secondary {
  background-color: #6c757d;
  color: white;
}
.btn-secondary:hover {
  background-color: #5a6268;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}
.btn-danger {
  background-color: #ff3366;
  color: white;
}
.btn-danger:hover {
  background-color: #e62c59;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 51, 102, 0.3);
}
.btn-outline {
  background-color: transparent;
  border: 1px solid #6c757d;
  color: #6c757d;
}
.btn-outline:hover {
  background-color: #f8f9fa;
  color: #5a6268;
}
.btn-pay {
  padding: 12px 24px;
}
/* Separadores y footer */
.checkout-divider {
  height: 1px;
  background-color: #e9ecef;
  margin: 30px 0;
  border: none;
}
.checkout-footer {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
}
/* Responsividad */
@media (max-width: 768px) {
  .checkout-table thead {
    display: none;
  }
  
  .checkout-table, 
  .checkout-table tbody, 
  .checkout-table tr, 
  .checkout-table td {
    display: block;
    width: 100%;
  }
  
  .checkout-table tr {
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
  }
  
  .checkout-table td {
    text-align: right;
    padding: 10px;
    position: relative;
    border-bottom: 1px solid #f2f2f2;
  }
  
  .checkout-table td:before {
    content: attr(data-label);
    float: left;
    font-weight: 600;
    color: #495057;
  }
  
  .product-cell {
    justify-content: flex-end;
    text-align: right;
  }
  
  .product-thumbnail {
    margin-right: 0;
    margin-left: 15px;
  }
  
  .product-name {
    text-align: right;
  }
  
  .payment-options {
    flex-direction: column;
    align-items: stretch;
  }
  
  .payment-divider {
    text-align: center;
    margin: 10px 0;
  }
}
</style>
<?php include '../../includes/footer.php'; ?>
<?php if (!empty($items)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('checkout-clip');
  if (btn) {
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
      
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
          btn.disabled = false;
          btn.innerHTML = originalText;
          alert('Error al iniciar pago: ' + (json.error || res.statusText));
        }
      } catch (e) {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error de red: ' + e.message);
      }
    });
  }
});
</script>
<?php endif; ?>