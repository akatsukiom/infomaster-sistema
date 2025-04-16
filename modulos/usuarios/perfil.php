<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';
require_once '../wallet/modelo.php';
require_once '../entregas/modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('login.php');
}

$usuario = new Usuario($conexion);
$wallet = new Wallet($conexion);
$entrega = new Entrega($conexion);

$usuario_id = $_SESSION['usuario_id'];
$errores = [];
$exito = null;

// Obtener datos del usuario
$datos_usuario = $usuario->obtenerPorId($usuario_id);

// Obtener transacciones recientes
$transacciones = $wallet->obtenerHistorial($usuario_id, 5);

// Obtener entregas recientes
$entregas = $entrega->obtenerPorUsuario($usuario_id);

// Procesar actualización de perfil
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    if($accion == 'actualizar_perfil') {
        $nombre = limpiarDato($_POST['nombre'] ?? '');
        
        // Validaciones
        if(empty($nombre)) {
            $errores[] = "El nombre es obligatorio";
        }
        
        // Si no hay errores, actualizar
        if(empty($errores)) {
            $resultado = $usuario->actualizarPerfil($usuario_id, $nombre);
            
            if(isset($resultado['success'])) {
                $exito = $resultado['success'];
                // Actualizar datos en sesión
                $_SESSION['usuario_nombre'] = $nombre;
                $datos_usuario['nombre'] = $nombre;
            } else {
                $errores[] = $resultado['error'];
            }
        }
    } elseif($accion == 'cambiar_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validaciones
        if(empty($current_password)) {
            $errores[] = "La contraseña actual es obligatoria";
        }
        
        if(empty($new_password)) {
            $errores[] = "La nueva contraseña es obligatoria";
        } elseif(strlen($new_password) < 6) {
            $errores[] = "La nueva contraseña debe tener al menos 6 caracteres";
        }
        
        if($new_password != $confirm_password) {
            $errores[] = "Las contraseñas no coinciden";
        }
        
        // Si no hay errores, cambiar contraseña
        if(empty($errores)) {
            $resultado = $usuario->cambiarPassword($usuario_id, $current_password, $new_password);
            
            if(isset($resultado['success'])) {
                $exito = $resultado['success'];
            } else {
                $errores[] = $resultado['error'];
            }
        }
    }
}

// Incluir header
$titulo = "Mi Perfil";
include '../../includes/header.php';
?>

<div class="container">
    <div class="perfil-container">
        <div class="perfil-header">
            <img src="../../img/avatar-default.jpg" alt="<?php echo $datos_usuario['nombre']; ?>" class="perfil-avatar">
            <div class="perfil-info">
                <h1><?php echo $datos_usuario['nombre']; ?></h1>
                <p><?php echo $datos_usuario['email']; ?></p>
                <p>Miembro desde: <?php echo date('d/m/Y', strtotime($datos_usuario['fecha_registro'])); ?></p>
            </div>
        </div>
        
        <div class="perfil-tabs">
            <a href="#info" class="perfil-tab active">Información</a>
            <a href="#seguridad" class="perfil-tab">Seguridad</a>
            <a href="#entregas" class="perfil-tab">Mis Entregas</a>
            <a href="#transacciones" class="perfil-tab">Transacciones</a>
        </div>
        
        <div class="perfil-content">
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
            
            <!-- Sección de información -->
            <div id="info" class="perfil-section active">
                <h2>Información Personal</h2>
                
                <form method="POST" action="" class="editar-perfil-form">
                    <input type="hidden" name="accion" value="actualizar_perfil">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo $datos_usuario['nombre']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo $datos_usuario['email']; ?>" disabled>
                        <small>No es posible cambiar el email.</small>
                    </div>
                    
                    <button type="submit" class="btn">Guardar Cambios</button>
                </form>
            </div>
            
            <!-- Sección de seguridad -->
            <div id="seguridad" class="perfil-section">
                <h2>Cambiar Contraseña</h2>
                
                <form method="POST" action="" class="cambiar-password-form">
                    <input type="hidden" name="accion" value="cambiar_password">
                    
                    <div class="form-group">
                        <label for="current_password">Contraseña actual</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar nueva contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn">Cambiar Contraseña</button>
                </form>
            </div>
            
            <!-- Sección de entregas -->
            <div id="entregas" class="perfil-section">
                <h2>Mis Entregas</h2>
                
                <?php if(empty($entregas)): ?>
                    <p>No tienes entregas activas. <a href="../../productos.php">Explora nuestros productos</a></p>
                <?php else: ?>
                    <div class="entregas-grid">
                        <?php foreach($entregas as $item): ?>
                            <div class="entrega-card">
                                <img src="<?php echo $item['imagen'] ? '../../' . $item['imagen'] : '../../img/producto-default.jpg'; ?>" alt="<?php echo $item['producto_nombre']; ?>" class="entrega-icon">
                                <div class="entrega-info">
                                    <h3><?php echo $item['producto_nombre']; ?></h3>
                                    <p class="entrega-fecha">Entregado: <?php echo date('d/m/Y H:i', strtotime($item['fecha_entrega'])); ?></p>
                                    <a href="../entregas/detalle.php?id=<?php echo $item['id']; ?>" class="btn btn-small">Ver Detalles</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="ver-mas">
                        <a href="mis-entregas.php" class="btn btn-outline">Ver todas mis entregas</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de transacciones -->
            <div id="transacciones" class="perfil-section">
                <h2>Últimas Transacciones</h2>
                
                <?php if(empty($transacciones)): ?>
                    <p>No tienes transacciones recientes. <a href="../wallet/recargar.php">Recargar wallet</a></p>
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
                                    <td class="<?php echo $tx['tipo'] == 'recarga' ? 'amount-positive' : 'amount-negative'; ?>">
                                        <?php echo $tx['tipo'] == 'recarga' ? '+' : '-'; ?><?php echo MONEDA . number_format($tx['monto'], 2); ?>
                                    </td>
                                    <td><?php echo $tx['referencia']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="ver-mas">
                        <a href="../wallet/historial.php" class="btn btn-outline">Ver historial completo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Manejar pestañas
    document.querySelectorAll('.perfil-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Quitar clase activa de todas las pestañas
            document.querySelectorAll('.perfil-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            // Ocultar todas las secciones
            document.querySelectorAll('.perfil-section').forEach(s => {
                s.classList.remove('active');
            });
            
            // Activar pestaña actual
            this.classList.add('active');
            
            // Mostrar sección correspondiente
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.add('active');
        });
    });
    
    // Activar pestaña según hash URL
    if(window.location.hash) {
        const tab = document.querySelector(`.perfil-tab[href="${window.location.hash}"]`);
        if(tab) tab.click();
    }
</script>

<?php include '../../includes/footer.php'; ?>