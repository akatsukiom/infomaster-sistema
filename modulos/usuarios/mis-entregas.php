<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';
require_once '../entregas/modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('login.php');
}

$usuario_id = $_SESSION['usuario_id'];

// Filtros
$estado = isset($_GET['estado']) ? limpiarDato($_GET['estado']) : '';

// Obtener entregas del usuario
$entrega = new Entrega($conexion);
$mis_entregas = $entrega->obtenerPorUsuario($usuario_id, $estado);

// Incluir header
$titulo = "Mis Entregas";
include '../../includes/header.php';
?>

<div class="container">
    <div class="mis-entregas-container">
        <div class="entregas-header">
            <h1>Mis Entregas</h1>
            
            <div class="entregas-filtros">
                <form method="GET" action="">
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="expirado" <?php echo $estado == 'expirado' ? 'selected' : ''; ?>>Expirado</option>
                        <option value="cancelado" <?php echo $estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                    
                    <button type="button" class="filtros-reset">Limpiar filtros</button>
                </form>
            </div>
        </div>
        
        <?php if(empty($mis_entregas)): ?>
            <div class="no-entregas">
                <img src="../../img/no-entregas.svg" alt="Sin entregas">
                <h2>No tienes entregas disponibles</h2>
                <p>Aún no has realizado compras o no tienes entregas que coincidan con los filtros seleccionados.</p>
                <a href="../../productos.php" class="btn">Explorar productos</a>
            </div>
        <?php else: ?>
            <div class="entregas-grid">
                <?php foreach($mis_entregas as $entrega): ?>
                    <div class="entrega-card">
                        <div class="entrega-header">
                            <img src="<?php echo $entrega['imagen'] ? '../../' . $entrega['imagen'] : '../../img/producto-default.jpg'; ?>" alt="<?php echo $entrega['producto_nombre']; ?>" class="entrega-icon">
                            <div class="entrega-title">
                                <h3><?php echo $entrega['producto_nombre']; ?></h3>
                                <span class="entrega-estado estado-<?php echo $entrega['estado']; ?>"><?php echo ucfirst($entrega['estado']); ?></span>
                                <span class="entrega-fecha">Entregado: <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="entrega-content">
                            <div class="entrega-codigo">
                                <h4>Código de acceso</h4>
                                <div class="codigo-container">
                                    <code class="codigo-valor"><?php echo $entrega['codigo_acceso']; ?></code>
                                    <button class="btn-copiar" data-codigo="<?php echo $entrega['codigo_acceso']; ?>">Copiar</button>
                                </div>
                            </div>
                            
                            <div class="entrega-acciones">
                                <a href="#" class="btn btn-small entrega-accion" data-action="descargar" data-id="<?php echo $entrega['id']; ?>">Descargar</a>
                                <?php if($entrega['estado'] == 'activo'): ?>
                                    <a href="#" class="btn btn-small btn-outline entrega-accion" data-action="renovar" data-id="<?php echo $entrega['id']; ?>">Renovar</a>
                                <?php endif; ?>
                                <a href="#" class="btn btn-small btn-outline entrega-accion" data-action="reportar" data-id="<?php echo $entrega['id']; ?>">Reportar problema</a>
                            </div>
                        </div>
                        
                        <a href="#" class="entrega-toggle">Ver detalles</a>
                        
                        <div class="entrega-detalles">
                            <div class="entrega-info-adicional">
                                <h4>Detalles de la entrega</h4>
                                <div class="info-item">
                                    <div class="info-label">Pedido:</div>
                                    <div class="info-valor">#<?php echo $entrega['pedido_id']; ?></div>
                                </div>
                                <?php if($entrega['fecha_expiracion']): ?>
                                <div class="info-item">
                                    <div class="info-label">Expira:</div>
                                    <div class="info-valor"><?php echo date('d/m/Y H:i', strtotime($entrega['fecha_expiracion'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para reportar problema -->
<div class="reporte-modal">
    <div class="reporte-content">
        <div class="reporte-header">
            <h3>Reportar problema</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="reporte-form">
            <form id="form-reporte">
                <input type="hidden" id="reporte_entrega_id" name="id">
                <div class="form-group">
                    <label for="reporte_descripcion">Describe el problema que estás experimentando:</label>
                    <textarea id="reporte_descripcion" name="descripcion" rows="6" required></textarea>
                </div>
                <div class="reporte-actions">
                    <button type="button" class="btn btn-outline" id="cancelar-reporte">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../js/entregas.js"></script>

<?php include '../../includes/footer.php'; ?>