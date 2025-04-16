<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../productos/modelo.php';
require_once 'modelo.php';

// Si se envió una acción
if(isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    switch($accion) {
        case 'eliminar':
            Carrito::eliminar($producto_id);
            mostrarMensaje('Producto eliminado del carrito', 'success');
            break;
            
        case 'vaciar':
            Carrito::vaciar();
            mostrarMensaje('Carrito vaciado correctamente', 'success');
            break;
            
        case 'actualizar':
            if(isset($_POST['cantidad']) && is_array($_POST['cantidad'])) {
                foreach($_POST['cantidad'] as $id => $cantidad) {
                    Carrito::actualizar($id, $cantidad);
                }
                mostrarMensaje('Carrito actualizado correctamente', 'success');
            }
            break;
    }
    
    redireccionar('modulos/carrito/ver.php');
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

// Calcular total
$total = Carrito::calcularTotal();

// Incluir header
$titulo = "Mi Carrito";
include '../../includes/header.php';
?>

<div class="container">
    <div class="carrito-container">
        <h1>Mi Carrito</h1>
        
        <?php if(empty($productos_carrito)): ?>
            <div class="carrito-vacio">
                <p>Tu carrito está vacío</p>
                <a href="../../productos.php" class="btn">Ver productos</a>
            </div>
        <?php else: ?>
            <form method="POST" action="ver.php?accion=actualizar">
                <table class="carrito-tabla">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos_carrito as $producto): ?>
                            <tr>
                                <td class="producto-info">
                                    <img src="<?php echo $producto['imagen'] ? '../../' . $producto['imagen'] : '/api/placeholder/100/100'; ?>" alt="<?php echo $producto['nombre']; ?>">
                                    <span><?php echo $producto['nombre']; ?></span>
                                </td>
                                <td><?php echo MONEDA . number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <input type="number" name="cantidad[<?php echo $producto['id']; ?>]" value="<?php echo $producto['cantidad']; ?>" min="1" class="cantidad-input">
                                </td>
                                <td><?php echo MONEDA . number_format($producto['subtotal'], 2); ?></td>
                                <td>
                                    <a href="ver.php?accion=eliminar&id=<?php echo $producto['id']; ?>" class="btn-eliminar">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="total-label">Total:</td>
                            <td colspan="2" class="total-valor"><?php echo MONEDA . number_format($total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="carrito-acciones">
                    <button type="submit" class="btn">Actualizar carrito</button>
                    <a href="ver.php?accion=vaciar" class="btn btn-outline">Vaciar carrito</a>
                    <a href="checkout.php" class="btn btn-secondary">Proceder al pago</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>