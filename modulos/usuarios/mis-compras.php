<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';
require_once '../carrito/modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('login.php');
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener pedidos del usuario
$pedido = new Pedido($conexion);
$mis_pedidos = $pedido->obtenerPorUsuario($usuario_id);

// Incluir header
$titulo = "Mis Compras";
include '../../includes/header.php';
?>

<div class="container">
    <div class="mis-compras-container">
        <h1>Mis Compras</h1>
        
        <?php if(empty($mis_pedidos)): ?>
            <div class="no-compras">
                <p>No tienes compras realizadas todavía.</p>
                <a href="../../productos.php" class="btn">Explorar productos</a>
            </div>
        <?php else: ?>
            <div class="compras-container">
                <?php foreach($mis_pedidos as $pedido): ?>
                    <div class="compra-card">
                        <div class="compra-header">
                            <div>
                                <span class="compra-numero">Pedido #<?php echo $pedido['id']; ?></span>
                                <span class="compra-fecha"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                            </div>
                            <div class="compra-total"><?php echo MONEDA . number_format($pedido['total'], 2); ?></div>
                        </div>
                        
                        <?php 
                        // Obtener detalles del pedido
                        $detalles = $pedido_obj->obtenerDetalles($pedido['id']);
                        ?>
                        
                        <div class="compra-productos">
                            <?php foreach($detalles as $detalle): ?>
                                <div class="compra-producto">
                                    <div class="compra-producto-info">
                                        <div class="compra-producto-nombre"><?php echo $detalle['producto_nombre']; ?></div>
                                        <div class="compra-producto-detalle">
                                            <?php echo $detalle['cantidad']; ?> x <?php echo MONEDA . number_format($detalle['precio'], 2); ?>
                                        </div>
                                    </div>
                                    <div class="compra-producto-precio">
                                        <?php echo MONEDA . number_format($detalle['precio'] * $detalle['cantidad'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="compra-footer">
                            <div class="compra-estado">
                                Estado: <span class="estado-<?php echo $pedido['estado']; ?>"><?php echo ucfirst($pedido['estado']); ?></span>
                            </div>
                            <div class="compra-acciones">
                                <a href="../carrito/confirmacion.php?id=<?php echo $pedido['id']; ?>" class="btn btn-small">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>