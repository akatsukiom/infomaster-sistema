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

<div class="container">
    <div class="wallet-container">
        <!-- Sidebar -->
        <div class="wallet-sidebar">
            <div class="wallet-balance">
                <div class="balance-title">Tu saldo disponible</div>
                <div class="balance-amount"><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></div>
            </div>
            
            <div class="wallet-actions">
                <a href="recargar.php" class="btn">Recargar wallet</a>
            </div>
            
            <div class="wallet-menu">
                <h3>Menú wallet</h3>
                <ul>
                    <li><a href="wallet.php" class="active">Resumen</a></li>
                    <li><a href="historial.php">Historial</a></li>
                    <li><a href="recargar.php">Recargar</a></li>
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
                    <div class="stat-title">Total recargado</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($estadisticas['total_recargas'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total gastado</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($estadisticas['total_compras'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Saldo actual</div>
                    <div class="stat-value"><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Movimientos este mes</div>
                    <div class="stat-value"><?php echo $estadisticas['ultimo_mes']['total_transacciones']; ?></div>
                </div>
            </div>
            
            <!-- Gráfico de actividad - En un entorno real, usarías JavaScript para crear un gráfico -->
            <div class="stats-chart">
                <h3>Actividad reciente</h3>
                <div class="chart-placeholder">
                    <p>El gráfico de actividad se mostrará aquí.</p>
                </div>
            </div>
            
            <!-- Últimas transacciones -->
            <div class="ultimas-transacciones">
                <h3>Últimas transacciones</h3>
                
                <?php if(empty($ultimas_transacciones)): ?>
                    <p>No hay transacciones recientes.</p>
                <?php else: ?>
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Referencia</th>
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
                        <a href="historial.php" class="btn btn-outline">Ver historial completo</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Métodos de recarga -->
            <div class="metodos-recarga">
                <h3>Métodos de recarga disponibles</h3>
                
                <div class="payment-methods">
                    <div class="payment-method">
                        <img src="../../img/payment-transfer.png" alt="Transferencia">
                        <h4>Transferencia bancaria</h4>
                        <a href="recargar.php?metodo=transferencia" class="btn btn-small">Recargar</a>
                    </div>
                    
                    <div class="payment-method">
                        <img src="../../img/payment-deposit.png" alt="Depósito">
                        <h4>Depósito bancario</h4>
                        <a href="recargar.php?metodo=deposito" class="btn btn-small">Recargar</a>
                    </div>
                    
                    <div class="payment-method">
                        <img src="../../img/payment-paypal.png" alt="PayPal">
                        <h4>PayPal</h4>
                        <a href="recargar.php?metodo=paypal" class="btn btn-small">Recargar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>