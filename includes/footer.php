<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}
?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h3>Info<span>Master</span></h3>
                    <p>Tu plataforma confiable para la adquisición de productos digitales con entrega automática y sistema de wallet.</p>
                    <div class="contact">
                        <p><i class="icon-envelope"></i> info@infomaster.com.mx</p>
                        <p><i class="icon-phone"></i> +52 123 456 7890</p>
                    </div>
                    <div class="socials">
                        <a href="#"><i class="icon-facebook"></i></a>
                        <a href="#"><i class="icon-twitter"></i></a>
                        <a href="#"><i class="icon-instagram"></i></a>
                        <a href="#"><i class="icon-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section links">
                    <h3>Enlaces rápidos</h3>
                    <ul>
                        <li><a href="<?php echo URL_SITIO; ?>">Inicio</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>productos.php">Productos</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>categorias.php">Categorías</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>como-funciona.php">Cómo funciona</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>terminos.php">Términos y condiciones</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>privacidad.php">Política de privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-section account">
                    <h3>Mi cuenta</h3>
                    <ul>
                        <?php if(estaLogueado()): ?>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/perfil.php">Mi perfil</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/wallet/recargar.php">Recargar wallet</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/mis-compras.php">Mis compras</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/mis-entregas.php">Mis entregas</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/logout.php">Cerrar sesión</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/login.php">Iniciar sesión</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/registro.php">Registrarse</a></li>
                            <li><a href="<?php echo URL_SITIO; ?>modulos/usuarios/recuperar.php">Recuperar contraseña</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section support">
                    <h3>Atención al cliente</h3>
                    <ul>
                        <li><a href="<?php echo URL_SITIO; ?>contacto.php">Contacto</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>faq.php">Preguntas frecuentes</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>soporte.php">Soporte técnico</a></li>
                        <li><a href="<?php echo URL_SITIO; ?>metodos-pago.php">Métodos de pago</a></li>
                    </ul>
                    <p>Horario de atención:<br>Lunes a Viernes de 9:00 a 18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> InfoMaster. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Script para cerrar mensajes
        document.addEventListener('DOMContentLoaded', function() {
            const cerrarBotones = document.querySelectorAll('.cerrar-mensaje');
            
            cerrarBotones.forEach(boton => {
                boton.addEventListener('click', function() {
                    this.closest('.mensaje-container').style.display = 'none';
                });
            });
            
            // Auto-cerrar mensajes después de 5 segundos
            setTimeout(function() {
                const mensajes = document.querySelectorAll('.mensaje-container');
                mensajes.forEach(mensaje => {
                    mensaje.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>