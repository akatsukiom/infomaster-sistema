<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once '../wallet/modelo.php';
require_once '../productos/modelo.php';
require_once '../entregas/modelo.php';
require_once 'modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    // Guardar página actual para redirigir después del login
    $_SESSION['redirigir_despues_login'] = 'modulos/carrito/checkout.php';
    redireccionar('modulos/usuarios/login.php');
}

// Verificar si hay productos en el carrito
if(Carrito::contar() == 0) {
    mostrarMensaje('No hay productos en el carrito', 'error');
    redireccionar('../../productos.php');
}

$total = Carrito::calcularTotal();
$usuario_id = $_SESSION['usuario_id'];

// Obtener saldo del usuario
$usuario = new Usuario($conexion);
$saldo = $usuario->obtenerSaldo($usuario_id);

$errores = [];
$exito = null;

// Procesar formulario de pago
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar saldo suficiente
    if($saldo < $total) {
        $errores[] = "Saldo insuficiente. Por favor recarga tu wallet.";
    } else {
        // Procesar pedido
        $pedido = new Pedido($conexion);
        $resultado = $pedido->procesarPedido($usuario_id);
        
        if(isset($resultado['success'])) {
            $exito = $resultado['success'];
            // Redirigir a página de confirmación
            redireccionar('confirmacion.php?id=' . $resultado['id']);
        } else {
            $errores[] = $resultado['error'];
        }
    }
}

// Obtener información completa de productos en el carrito
$carrito = Carrito::obtener();
$productos_carrito = [];

if(!empty($carrito)) {
    $producto = new Producto($conexion);
    
    foreach($carrito as $id => $item) {
        $info_producto = $producto->obtenerPorId($id);
        
        if($info_producto) {
            $productos_carrito[] = [
                'id' => $id,
                'nombre' => $info_producto['nombre'],
                'imagen' => $info_producto['imagen'],
                'precio' => $item['precio'],
                'cantidad' => $item['cantidad'],
                'subtotal' => $item['precio'] * $item['cantidad']
            ];
        }
    }
}

// Incluir header
$titulo = "Checkout";
include '../../includes/header.php';
?>

<div class="container">
    <div class="checkout-container">
        <h1>Resumen de tu pedido</h1>
        
        <?php if(!empty($errores)): ?>
            <div class="errores">
                <?php foreach($errores as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if($exito): ?>
            <div class="exito">
                <p><?php echo $exito; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="resumen-pedido">
                <h2>Productos</h2>
                
                <table class="resumen-tabla">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos_carrito as $producto): ?>
                            <tr>
                                <td><?php echo $producto['nombre']; ?></td>
                                <td><?php echo $producto['cantidad']; ?></td>
                                <td><?php echo MONEDA . number_format($producto['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="total-label">Total:</td>
                            <td class="total-valor"><?php echo MONEDA . number_format($total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="detalle-pago">
                <h2>Detalles de pago</h2>
                
                <div class="wallet-info">
                    <p>Tu saldo actual: <strong><?php echo MONEDA . number_format($saldo, 2); ?></strong></p>
                    
                    <?php if($saldo < $total): ?>
                        <div class="saldo-insuficiente">
                            <p>Tu saldo es insuficiente. Necesitas <?php echo MONEDA . number_format($total - $saldo, 2); ?> más.</p>
                            <a href="../wallet/recargar.php" class="btn">Recargar wallet</a>
                        </div>
                    <?php else: ?>
                        <div class="saldo-suficiente">
                            <p>Saldo después de la compra: <?php echo MONEDA . number_format($saldo - $total, 2); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group check-terms">
                        <input type="checkbox" id="terminos" name="terminos" required>
                        <label for="terminos">Acepto los términos y condiciones</label>
                    </div>
                    
                    <div class="checkout-actions">
                        <a href="ver.php" class="btn btn-outline">Volver al carrito</a>
                        <button type="submit" class="btn btn-secondary" <?php echo $saldo < $total ? 'disabled' : ''; ?>>Confirmar compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>