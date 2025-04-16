<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once 'modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('../usuarios/login.php');
}

$usuario_id = $_SESSION['usuario_id'];
$wallet = new Wallet($conexion);

// Filtros
$tipo = isset($_GET['tipo']) ? limpiarDato($_GET['tipo']) : '';
$desde = isset($_GET['desde']) && !empty($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) && !empty($_GET['hasta']) ? $_GET['hasta'] : '';

// Obtener transacciones con filtros
$transacciones = $wallet->obtenerHistorialFiltrado($usuario_id, $tipo, $desde, $hasta);

// Incluir header
$titulo = "Historial de Transacciones";
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
                    <li><a href="wallet.php">Resumen</a></li>
                    <li><a href="historial.php" class="active">Historial</a></li>
                    <li><a href="recargar.php">Recargar</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="wallet-content">
            <div class="content-header">
                <h2>Historial de Transacciones</h2>
            </div>
            
            <!-- Filtros -->
            <div class="historial-filtros">
                <form method="GET" action="">
                    <div class="filtro-grupo">
                        <label for="filtro-tipo">Tipo:</label>
                        <select name="tipo" id="filtro-tipo">
                            <option value="">Todos</option>
                            <option value="recarga" <?php echo $tipo == 'recarga' ? 'selected' : ''; ?>>Recargas</option>
                            <option value="compra" <?php echo $tipo == 'compra' ? 'selected' : ''; ?>>Compras</option>
                            <option value="reembolso" <?php echo $tipo == 'reembolso' ? 'selected' : ''; ?>>Reembolsos</option>
                        </select>
                    </div>
                    
                    <div class="filtro-grupo">
                        <label for="filtro-desde">Desde:</label>
                        <input type="date" name="desde" id="filtro-desde" value="<?php echo $desde; ?>">
                    </div>
                    
                    <div class="filtro-grupo">
                        <label for="filtro-hasta">Hasta:</label>
                        <input type="date" name="hasta" id="filtro-hasta" value="<?php echo $hasta; ?>">
                    </div>
                    
                    <button type="button" class="btn-limpiar">Limpiar filtros</button>
                </form>
            </div>
            
            <!-- Tabla de transacciones -->
            <?php if(empty($transacciones)): ?>
                <div class="no-transacciones">
                    <p>No se encontraron transacciones que coincidan con los filtros seleccionados.</p>
                </div>
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
                        <?php foreach($transacciones as $tx): ?>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../js/wallet.js"></script>

<?php include '../../includes/footer.php'; ?>