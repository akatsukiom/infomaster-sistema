<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once 'modelo.php';
require_once '../carrito/modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('../usuarios/login.php');
}

$usuario_id = $_SESSION['usuario_id'];
$wallet = new Wallet($conexion);

// Obtener estadísticas de la wallet
$estadisticas = [
    'total_recargas' => $wallet->obtenerTotalRecargas($usuario_id),
    'total_compras' => $wallet->obtenerTotalCompras($usuario_id),
    'ultimo_mes' => $wallet->obtenerEstadisticasMes($usuario_id)
];

// Obtener últimas transacciones
$ultimas_transacciones = $wallet->obtenerHistorial($usuario_id, 5);

// Incluir header
$titulo = "Mi Wallet";
include '../../includes/header.php';
?>

<style>
/* ========== ESTILOS WALLET MEJORADOS ========== */

.wallet-container {
  max-width: 1400px;
  margin: 2rem auto;
  padding: 0 1rem;
  display: grid;
  grid-template-columns: 350px 1fr;
  gap: 2rem;
  min-height: 80vh;
}

/* ========== SIDEBAR WALLET ========== */
.wallet-sidebar {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 24px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  height: fit-content;
  position: sticky;
  top: 2rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.wallet-balance {
  text-align: center;
  margin-bottom: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  position: relative;
  overflow: hidden;
}

.wallet-balance::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: spin 8s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

.balance-title {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 1;
}

.balance-amount {
  color: white;
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  font-weight: 900;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 1;
}

.wallet-actions {
  margin-bottom: 2rem;
}

.wallet-actions .btn {
  width: 100%;
  display: block;
  text-align: center;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
  color: white !important;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1.1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
  position: relative;
  overflow: hidden;
}

.wallet-actions .btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.wallet-actions .btn:hover::before {
  left: 100%;
}

.wallet-actions .btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(34, 197, 94, 0.4);
}

.wallet-menu h3 {
  color: white;
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.wallet-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.wallet-menu ul li {
  margin-bottom: 0.5rem;
}

.wallet-menu ul li a {
  display: block;
  padding: 0.8rem 1rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.wallet-menu ul li a:hover,
.wallet-menu ul li a.active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(5px);
}

/* ========== CONTENIDO PRINCIPAL ========== */
.wallet-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.content-header {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.content-header h2 {
  color: white;
  font-size: clamp(2rem, 5vw, 2.5rem);
  font-weight: 900;
  margin: 0;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ========== GRID DE ESTADÍSTICAS ========== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
  text-align: center;
}

.stat-card::before {
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

.stat-card:hover::before {
  opacity: 1;
}

.stat-card:hover {
  transform: translateY(-10px);
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.25);
}

.stat-title {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.95rem;
  font-weight: 500;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.stat-value {
  color: white;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 900;
  position: relative;
  z-index: 1;
}

/* ========== GRÁFICO DE ACTIVIDAD ========== */
.stats-chart {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.stats-chart h3 {
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.chart-placeholder {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 3rem;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-placeholder::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

.chart-placeholder p {
  color: white;
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0;
  position: relative;
  z-index: 1;
}

/* ========== TRANSACCIONES ========== */
.ultimas-transacciones {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.ultimas-transacciones h3 {
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.transaction-table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  overflow: hidden;
}

.transaction-table thead th {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
}

.transaction-table tbody td {
  padding: 1rem;
  color: rgba(255, 255, 255, 0.9);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.9rem;
}

.transaction-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.05);
}

.transaction-type {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.type-recarga {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.type-compra {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.type-reembolso {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.amount-positive {
  color: #4ade80 !important;
  font-weight: 700;
}

.amount-negative {
  color: #f87171 !important;
  font-weight: 700;
}

.ver-mas {
  text-align: center;
  margin-top: 1.5rem;
}

.btn-outline {
  background: transparent !important;
  border: 2px solid rgba(255, 255, 255, 0.3) !important;
  color: white !important;
}

.btn-outline:hover {
  background: rgba(255, 255, 255, 0.1) !important;
  border-color: rgba(255, 255, 255, 0.5) !important;
}

/* ========== MÉTODOS DE RECARGA ========== */
.metodos-recarga {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(25px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.metodos-recarga h3 {
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.payment-methods {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.payment-method {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.5rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.payment-method::before {
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

.payment-method:hover::before {
  opacity: 1;
}

.payment-method:hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.12);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.payment-method img {
  width: 60px;
  height: 60px;
  margin-bottom: 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);
  padding: 1rem;
  position: relative;
  z-index: 1;
}

.payment-method h4 {
  color: white;
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.btn-small {
  padding: 0.5rem 1.5rem !important;
  font-size: 0.9rem !important;
  position: relative;
  z-index: 1;
}

/* BOTONES GENERALES */
.btn {
  display: inline-block;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white !important;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
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
  box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 1024px) {
  .wallet-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .wallet-sidebar {
    position: relative;
    top: auto;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 768px) {
  .wallet-container {
    margin: 1rem auto;
    padding: 0 0.5rem;
  }
  
  .wallet-sidebar,
  .content-header,
  .stats-chart,
  .ultimas-transacciones,
  .metodos-recarga {
    padding: 1.5rem;
  }
  
  .balance-amount {
    font-size: 2rem;
  }
  
  .transaction-table {
    font-size: 0.8rem;
  }
  
  .transaction-table thead th,
  .transaction-table tbody td {
    padding: 0.8rem 0.5rem;
  }
  
  .payment-methods {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 1.5rem;
  }
  
  .wallet-balance {
    padding: 1.5rem;
  }
  
  .balance-amount {
    font-size: 1.8rem;
  }
  
  .transaction-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>

<div class="container">
    <div class="wallet-container">
        <!-- Sidebar -->
        <div class="wallet-sidebar">
            <div class="wallet-balance">
                <div class="balance-title">Tu saldo disponible</div>
                <div class="balance-amount"><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></div>
            </div>
            
            <div class="wallet-actions">
                <a href="recargar.php" class="btn">💳 Recargar wallet</a>
            </div>
            
            <div class="wallet-menu">
                <h3>Menú wallet</h3>
                <ul>
                    <li><a href="wallet.php" class="active">📊 Resumen</a></li>
                    <li><a href="historial.php">📋 Historial</a></li>
                    <li><a href="recargar.php">💰 Recargar</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="wallet-content">
            <div class="content-header">
                <h2>Resumen de tu Wallet</h2>
            </div>
            
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">💸 Total recargado</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($estadisticas['total_recargas'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">🛒 Total gastado</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($estadisticas['total_compras'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">💰 Saldo actual</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">📈 Movimientos este mes</div>
                    <div class="stat-value"><?php echo $estadisticas['ultimo_mes']['total_transacciones']; ?></div>
                </div>
            </div>
            
            <!-- Gráfico de actividad -->
            <div class="stats-chart">
                <h3>📊 Actividad reciente</h3>
                <div class="chart-placeholder">
                    <p>📈 El gráfico de actividad se mostrará aquí próximamente</p>
                </div>
            </div>
            
            <!-- Últimas transacciones -->
            <div class="ultimas-transacciones">
                <h3>🕒 Últimas transacciones</h3>
                
                <?php if(empty($ultimas_transacciones)): ?>
                    <div style="text-align: center; padding: 3rem; color: rgba(255, 255, 255, 0.7);">
                        <p style="font-size: 3rem; margin-bottom: 1rem;">📝</p>
                        <p style="font-size: 1.2rem;">No hay transacciones recientes.</p>
                    </div>
                <?php else: ?>
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>📅 Fecha</th>
                                <th>🏷️ Tipo</th>
                                <th>💵 Monto</th>
                                <th>🔗 Referencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ultimas_transacciones as $tx): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($tx['fecha'])); ?></td>
                                    <td>
                                        <span class="transaction-type type-<?php echo $tx['tipo']; ?>">
                                            <?php echo ucfirst($tx['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="<?php echo $tx['tipo'] == 'recarga' || $tx['tipo'] == 'reembolso' ? 'amount-positive' : 'amount-negative'; ?>">
                                        <?php echo $tx['tipo'] == 'recarga' || $tx['tipo'] == 'reembolso' ? '+' : '-'; ?><?php echo MONEDA . number_format($tx['monto'], 2); ?>
                                    </td>
                                    <td><?php echo $tx['referencia']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="ver-mas">
                        <a href="historial.php" class="btn btn-outline">📋 Ver historial completo</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Métodos de recarga -->
            <div class="metodos-recarga">
                <h3>💳 Métodos de recarga disponibles</h3>
                
                <div class="payment-methods">
                    <div class="payment-method">
                        <div style="width: 60px; height: 60px; margin: 0 auto 1rem auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🏦</div>
                        <h4>Transferencia bancaria</h4>
                        <a href="recargar.php?metodo=transferencia" class="btn btn-small">Recargar</a>
                    </div>
                    
                    <div class="payment-method">
                        <div style="width: 60px; height: 60px; margin: 0 auto 1rem auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">💵</div>
                        <h4>Depósito bancario</h4>
                        <a href="recargar.php?metodo=deposito" class="btn btn-small">Recargar</a>
                    </div>
                    
                    <div class="payment-method">
                        <div style="width: 60px; height: 60px; margin: 0 auto 1rem auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">💎</div>
                        <h4>PayPal</h4>
                        <a href="recargar.php?metodo=paypal" class="btn btn-small">Recargar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>