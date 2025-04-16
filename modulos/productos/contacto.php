<?php
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$enviado = false;
$errores = [];

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDato($_POST['nombre'] ?? '');
    $email = limpiarDato($_POST['email'] ?? '');
    $asunto = limpiarDato($_POST['asunto'] ?? '');
    $mensaje = limpiarDato($_POST['mensaje'] ?? '');
    
    // Validaciones
    if(empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    
    if(empty($email) || !validarEmail($email)) {
        $errores[] = "Por favor ingresa un email válido";
    }
    
    if(empty($asunto)) {
        $errores[] = "El asunto es obligatorio";
    }
    
    if(empty($mensaje)) {
        $errores[] = "El mensaje es obligatorio";
    }
    
    // Si no hay errores, procesar mensaje
    if(empty($errores)) {
        // En un entorno real, aquí enviarías el correo electrónico
        // Para este ejemplo, solo simulamos el envío exitoso
        
        $enviado = true;
        mostrarMensaje("Tu mensaje ha sido enviado. Te responderemos a la brevedad.", 'success');
        
        // Limpiar campos después del envío
        $nombre = $email = $asunto = $mensaje = '';
    }
}

// Incluir header
$titulo = "Contacto";
include 'includes/header.php';
?>

<div class="container">
    <div class="contacto-container">
        <div class="page-header">
            <h1>Contáctanos</h1>
            <p>Estamos aquí para ayudarte. Envíanos tu consulta y te responderemos a la brevedad.</p>
        </div>
        
        <div class="contacto-grid">
            <div class="contacto-form">
                <?php if($enviado): ?>
                    <div class="form-enviado">
                        <div class="enviado-icon">
                            <i class="icon-check"></i>
                        </div>
                        <h2>¡Mensaje enviado!</h2>
                        <p>Gracias por contactarnos. Hemos recibido tu mensaje y te responderemos a la brevedad.</p>
                        <a href="index.php" class="btn">Volver al inicio</a>
                    </div>
                <?php else: ?>
                    <?php if(!empty($errores)): ?>
                        <div class="errores">
                            <?php foreach($errores as $error): ?>
                                <p><?php echo $error; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo $nombre ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $email ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <input type="text" id="asunto" name="asunto" value="<?php echo $asunto ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensaje">Mensaje</label>
                            <textarea id="mensaje" name="mensaje" rows="6" required><?php echo $mensaje ?? ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="contacto-info">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-mail"></i>
                    </div>
                    <h3>Email</h3>
                    <p>info@infomaster.com.mx</p>
                    <p>soporte@infomaster.com.mx</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-phone"></i>
                    </div>
                    <h3>Teléfono</h3>
                    <p>+52 123 456 7890</p>
                    <p>Lun - Vie: 9:00 - 18:00</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-message"></i>
                    </div>
                    <h3>Chat en vivo</h3>
                    <p>Disponible de Lunes a Viernes</p>
                    <p>9:00 AM - 6:00 PM</p>
                    <button class="btn btn-outline iniciar-chat">Iniciar chat</button>
                </div>
                
                <div class="redes-sociales">
                    <h3>Síguenos</h3>
                    <div class="redes-icons">
                        <a href="#" class="red-social"><i class="icon-facebook"></i></a>
                        <a href="#" class="red-social"><i class="icon-twitter"></i></a>
                        <a href="#" class="red-social"><i class="icon-instagram"></i></a>
                        <a href="#" class="red-social"><i class="icon-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>