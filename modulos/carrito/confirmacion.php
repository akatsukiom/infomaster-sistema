<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once '../productos/modelo.php';
require_once '../entregas/modelo.php';
require_once 'modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('modulos/usuarios/login.php');
}

// Verificar si se proporcionó un ID de pedido
if(!isset($_GET['id'])) {
    redireccionar('../../index.php');
}

$pedido_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener información del pedido
$pedido = new Pedido($conexion);
$info_pedido = $pedido->obtenerPorId($pedido_id);

// Verificar que el pedido existe y pertenece al usuario actual
if(!$info_pedido || $info_pedido['usuario_id'] != $usuario_id) {
    mostrarMensaje('Pedido no encontrado', 'error');
    redireccionar('../../index.php');
}

// Obtener detalles del pedido
$detalles = $pedido->obtenerDetalles($pedido_id);

// Obtener entregas del pedido
$entrega = new Entrega($conexion);
$entregas = $entrega->obtenerPorUsuario($usuario_id);

// Filtrar entregas solo de este pedido
$entregas_pedido = array_filter($entregas, function($e) use ($pedido_id) {
    return $e['pedido_id'] == $pedido_id;
});

// Incluir header
$titulo = "Confirmación de pedido";
include '../../includes/header.php';
?>

<div class="container">
    <div class="confirmacion-container">
        <div class="confirmacion-header">
            <h1>¡Pedido completado con éxito!</h1>
            <p>Tu pedido #<?php echo $pedido_id; ?> ha sido procesado correctamente.</p>
        </div>
        
        <div class="confirmacion-detalles">
            <div class="pedido-info">
                <h2>Información del pedido</h2>
                <ul>
                    <li><strong>Número de pedido:</strong> #<?php echo $pedido_id; ?></li>
                    <li><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($info_pedido['fecha_pedido'])); ?></li>
                    <li><strong>Total pagado:</strong> <?php echo MONEDA . number_format($info_pedido['total'], 2); ?></li>
                    <li><strong>Estado:</strong> <span class="estado-<?php echo $info_pedido['estado']; ?>"><?php echo ucfirst($info_pedido['estado']); ?></span></li>
                </ul>
            </div>
            
            <div class="productos-pedido">
                <h2>Productos adquiridos</h2>
                <table class="detalles-tabla">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($detalles as $detalle): ?>
                            <tr>
                                <td><?php echo $detalle['producto_nombre']; ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td><?php echo MONEDA . number_format($detalle['precio'], 2); ?></td>
                                <td><?php echo MONEDA . number_format($detalle['precio'] * $detalle['cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="total-label">Total:</td>
                            <td class="total-valor"><?php echo MONEDA . number_format($info_pedido['total'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="entregas-pedido">
                <h2>Detalles de acceso</h2>
                
                <?php if(empty($entregas_pedido)): ?>
                    <p>No hay entregas disponibles para este pedido.</p>
                <?php else: ?>
                    <div class="entregas-grid">
                        <?php foreach($entregas_pedido as $entrega): ?>
                            <div class="entrega-card">
                                <img src="<?php echo $entrega['imagen'] ? '../../' . $entrega['imagen'] : '/api/placeholder/100/100'; ?>" alt="<?php echo $entrega['producto_nombre']; ?>">
                                <h3><?php echo $entrega['producto_nombre']; ?></h3>
                                <div class="codigo-acceso">
                                    <p><strong>Código de acceso:</strong></p>
                                    <div class="codigo-container">
                                        <code><?php echo $entrega['codigo_acceso']; ?></code>
                                        <button class="btn-copiar" data-codigo="<?php echo $entrega['codigo_acceso']; ?>">Copiar</button>
                                    </div>
                                </div>
                                <p class="entrega-fecha">Entregado: <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmacion-acciones">
                <a href="../../index.php" class="btn">Volver al inicio</a>
                <a href="../usuarios/mis-compras.php" class="btn btn-secondary">Ver mis compras</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para copiar código de acceso al portapapeles
    document.querySelectorAll('.btn-copiar').forEach(button => {
        button.addEventListener('click', function() {
            const codigo = this.getAttribute('data-codigo');
            const tempInput = document.createElement('input');
            tempInput.value = codigo;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            this.textContent = '¡Copiado!';
            setTimeout(() => {
                this.textContent = 'Copiar';
            }, 2000);
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>