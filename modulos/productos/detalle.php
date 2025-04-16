<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';
require_once '../carrito/modelo.php';

// Verificar si se proporcionó un ID de producto
if(!isset($_GET['id'])) {
    redireccionar('../../productos.php');
}

$producto_id = (int)$_GET['id'];

// Obtener información del producto
$producto = new Producto($conexion);
$info_producto = $producto->obtenerPorId($producto_id);

// Verificar que el producto existe
if(!$info_producto) {
    mostrarMensaje('Producto no encontrado', 'error');
    redireccionar('../../productos.php');
}

// Obtener productos relacionados
$relacionados = $producto->obtenerRelacionados($producto_id, $info_producto['categoria_id'], 4);

// Incluir header
$titulo = $info_producto['nombre'] . " - Detalle del producto";
include '../../includes/header.php';
?>

<div class="container">
    <div class="producto-detalle">
        <div class="breadcrumb">
            <a href="../../index.php">Inicio</a> > 
            <a href="../../productos.php">Productos</a> > 
            <?php if($info_producto['categoria']): ?>
                <a href="../../productos.php?categoria=<?php echo $info_producto['categoria_id']; ?>"><?php echo $info_producto['categoria']; ?></a> > 
            <?php endif; ?>
            <span><?php echo $info_producto['nombre']; ?></span>
        </div>
        
        <div class="producto-grid">
            <div class="producto-imagen">
                <img src="<?php echo $info_producto['imagen'] ? '../../' . $info_producto['imagen'] : '../../img/producto-default.jpg'; ?>" alt="<?php echo $info_producto['nombre']; ?>">
            </div>
            
            <div class="producto-info">
                <span class="producto-categoria"><?php echo $info_producto['categoria']; ?></span>
                <h1><?php echo $info_producto['nombre']; ?></h1>
                
                <div class="producto-precio"><?php echo MONEDA . number_format($info_producto['precio'], 2); ?></div>
                
                <div class="producto-descripcion">
                    <h3>Descripción</h3>
                    <p><?php echo nl2br($info_producto['descripcion']); ?></p>
                </div>
                
                <form method="POST" action="../carrito/agregar.php" class="producto-compra">
                    <input type="hidden" name="id" value="<?php echo $info_producto['id']; ?>">
                    
                    <div class="form-group cantidad-grupo">
                        <label for="cantidad">Cantidad:</label>
                        <div class="cantidad-control">
                            <button type="button" class="cantidad-btn restar">-</button>
                            <input type="number" id="cantidad" name="cantidad" value="1" min="1" class="cantidad-input">
                            <button type="button" class="cantidad-btn sumar">+</button>
                        </div>
                    </div>
                    
                    <div class="producto-acciones">
                        <button type="submit" class="btn btn-secondary btn-agregar-carrito" data-id="<?php echo $info_producto['id']; ?>">
                            Agregar al carrito
                        </button>
                        
                        <button type="button" class="btn comprar-ahora" data-id="<?php echo $info_producto['id']; ?>">
                            Comprar ahora
                        </button>
                    </div>
                </form>
                
                <div class="producto-garantia">
                    <div class="garantia-item">
                        <i class="icon-check"></i>
                        <span>Entrega automática</span>
                    </div>
                    <div class="garantia-item">
                        <i class="icon-shield"></i>
                        <span>Garantía de funcionamiento</span>
                    </div>
                    <div class="garantia-item">
                        <i class="icon-support"></i>
                        <span>Soporte técnico</span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(!empty($relacionados)): ?>
            <div class="productos-relacionados">
                <h2>Productos relacionados</h2>
                
                <div class="products">
                    <?php foreach($relacionados as $rel): ?>
                        <div class="product">
                            <img src="<?php echo $rel['imagen'] ? '../../' . $rel['imagen'] : '../../img/producto-default.jpg'; ?>" alt="<?php echo $rel['nombre']; ?>" class="product-img">
                            <div class="product-info">
                                <span class="product-category"><?php echo $rel['categoria']; ?></span>
                                <h3 class="product-title"><?php echo $rel['nombre']; ?></h3>
                                <div class="product-price"><?php echo MONEDA . number_format($rel['precio'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="detalle.php?id=<?php echo $rel['id']; ?>" class="btn">Ver detalles</a>
                                    <a href="../carrito/agregar.php?id=<?php echo $rel['id']; ?>&redirigir=../../productos.php" class="btn btn-secondary">Comprar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Control de cantidad
    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInput = document.getElementById('cantidad');
        const restarBtn = document.querySelector('.cantidad-btn.restar');
        const sumarBtn = document.querySelector('.cantidad-btn.sumar');
        
        restarBtn.addEventListener('click', function() {
            let cantidad = parseInt(cantidadInput.value);
            if (cantidad > 1) {
                cantidadInput.value = cantidad - 1;
            }
        });
        
        sumarBtn.addEventListener('click', function() {
            let cantidad = parseInt(cantidadInput.value);
            cantidadInput.value = cantidad + 1;
        });
        
        // Comprar ahora
        const comprarAhoraBtn = document.querySelector('.comprar-ahora');
        comprarAhoraBtn.addEventListener('click', function() {
            const form = document.querySelector('.producto-compra');
            form.action = '../carrito/agregar.php?redirigir=../carrito/checkout.php';
            form.submit();
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>