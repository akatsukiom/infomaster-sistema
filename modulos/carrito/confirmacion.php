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
    redireccionar('../usuarios/login.php');
}

// Verificar si se proporcionó un ID de pedido
if(!isset($_GET['id'])) {
    redireccionar('../../productos.php');
}

$pedido_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener información del pedido
$pedido = new Pedido($conexion);
$info_pedido = $pedido->obtenerPorId($pedido_id);

// Verificar que el pedido existe y pertenece al usuario actual
if(!$info_pedido || $info_pedido['usuario_id'] != $usuario_id) {
    mostrarMensaje('Pedido no encontrado', 'error');
    redireccionar('../../productos.php');
}

// Obtener detalles del pedido
$detalles = $pedido->obtenerDetalles($pedido_id);

// Obtener entregas del pedido
$entrega = new Entrega($conexion);
$entregas = $entrega->obtenerPorPedido($pedido_id);

// Incluir header
$titulo = "Confirmación de pedido";
include '../../includes/header.php';
?>

<!-- Delivery Section -->
<section class="delivery-section">
    <div class="container">
        <div class="confirmation-header">
            <i class="fas fa-check-circle success-icon"></i>
            <h1>¡Compra completada con éxito!</h1>
            <p>Tu pedido #<?= $pedido_id ?> ha sido procesado correctamente. A continuación encontrarás los detalles de tu compra y los códigos de acceso para tus productos.</p>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <h2>Detalles del pedido</h2>
            
            <div class="order-info">
                <div class="info-group">
                    <h3>Número de pedido</h3>
                    <div class="info-value">#<?= $pedido_id ?></div>
                </div>
                
                <div class="info-group">
                    <h3>Fecha</h3>
                    <div class="info-value"><?= date('d/m/Y - H:i', strtotime($info_pedido['fecha_pedido'])) ?></div>
                </div>
                
                <div class="info-group">
                    <h3>Estado</h3>
                    <div class="info-value success"><?= ucfirst($info_pedido['estado']) ?></div>
                </div>
                
                <div class="info-group">
                    <h3>Método de pago</h3>
                    <div class="info-value">Wallet</div>
                </div>
            </div>
            
            <div class="order-summary">
                <h3>Resumen de compra</h3>
                
                <?php foreach($detalles as $detalle): ?>
                <div class="summary-row">
                    <span class="summary-label"><?= $detalle['producto_nombre'] ?></span>
                    <span class="summary-value"><?= MONEDA . number_format($detalle['precio'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-total">
                    <span class="summary-total-label">Total</span>
                    <span class="summary-total-value"><?= MONEDA . number_format($info_pedido['total'], 2) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Delivery Products -->
        <h2 style="margin-bottom: 2rem; text-align: center; font-size: 1.8rem;">Tus productos</h2>
        
        <div class="delivery-grid">
            <?php foreach($entregas as $item): ?>
            <div class="delivery-card">
                <div class="delivery-header">
                    <div class="delivery-header-content">
                        <img src="<?= $item['imagen'] ? URL_SITIO . $item['imagen'] : URL_SITIO . 'img/producto-default.jpg' ?>" 
                             alt="<?= $item['producto_nombre'] ?>" class="delivery-icon">
                        <div class="delivery-title">
                            <h3><?= $item['producto_nombre'] ?></h3>
                            <div class="delivery-date">Entregado: <?= date('d/m/Y', strtotime($item['fecha_entrega'])) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="delivery-content">
                    <div class="access-code">
                        <h4>Código de acceso</h4>
                        <div class="code-container">
                            <div class="code-value"><?= $item['codigo_acceso'] ?></div>
                            <button class="copy-btn" data-code="<?= $item['codigo_acceso'] ?>">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        <?php if(isset($item['credenciales'])): ?>
                        <div class="code-container">
                            <div class="code-value"><?= $item['credenciales'] ?></div>
                            <button class="copy-btn" data-code="<?= $item['credenciales'] ?>">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="instructions">
                        <h4>Instrucciones de uso</h4>
                        <?php if(isset($item['instrucciones'])): ?>
                            <?= $item['instrucciones'] ?>
                        <?php else: ?>
                        <ol class="instructions-list">
                            <li>Ingresa a la página oficial del producto</li>
                            <li>Introduce el código de acceso proporcionado</li>
                            <li>Sigue las instrucciones en pantalla para activar tu producto</li>
                            <li>Si encuentras algún problema, contacta a soporte técnico</li>
                        </ol>
                        <?php endif; ?>
                    </div>
                    
                    <div class="delivery-actions">
                        <a href="<?= URL_SITIO ?>soporte.php?producto=<?= $item['producto_id'] ?>" class="btn">Ver tutorial</a>
                        <a href="<?= URL_SITIO ?>reportar.php?entrega=<?= $item['id'] ?>" class="btn btn-outline">Reportar problema</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="<?= URL_SITIO ?>modulos/usuarios/mis-entregas.php" class="btn btn-secondary">Ver mis entregas</a>
            <a href="<?= URL_SITIO ?>productos.php" class="btn">Volver a la tienda</a>
        </div>
    </div>
</section>

<script>
    // Copiar código de acceso
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-btn');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                
                // Crear elemento temporal
                const tempElement = document.createElement('textarea');
                tempElement.value = code;
                document.body.appendChild(tempElement);
                
                // Seleccionar y copiar texto
                tempElement.select();
                document.execCommand('copy');
                
                // Eliminar elemento temporal
                document.body.removeChild(tempElement);
                
                // Cambiar icono y estilo para indicar éxito
                const icon = this.querySelector('i');
                icon.classList.remove('far', 'fa-copy');
                icon.classList.add('fas', 'fa-check');
                this.classList.add('copied');
                
                // Mostrar notificación
                const notification = document.createElement('div');
                notification.textContent = 'Código copiado al portapapeles';
                notification.style.position = 'fixed';
                notification.style.bottom = '20px';
                notification.style.right = '20px';
                notification.style.backgroundColor = '#38b000';
                notification.style.color = 'white';
                notification.style.padding = '10px 20px';
                notification.style.borderRadius = '5px';
                notification.style.zIndex = '1000';
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                
                document.body.appendChild(notification);
                
                // Mostrar notificación con animación
                setTimeout(() => {
                    notification.style.opacity = '1';
                }, 10);
                
                // Ocultar después de 3 segundos
                setTimeout(() => {
                    notification.style.opacity = '0';
                    
                    // Restaurar icono después de 2 segundos
                    icon.classList.remove('fas', 'fa-check');
                    icon.classList.add('far', 'fa-copy');
                    this.classList.remove('copied');
                    
                    // Eliminar notificación después de la animación
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 3000);
            });
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>