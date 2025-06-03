<?php
// includes/footer.php
if (!defined('ACCESO_PERMITIDO')) {
    exit;
}
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">

            <div class="footer-section about">
                <h3>Info<span>Master</span></h3>
                <p>Tu mejor lugar para contratar servicios digitales premium con entrega inmediata y calidad garantizada.</p>
                <div class="contact">
                    <p><i class="fas fa-envelope"></i> infomaster@infomaster.com.mx</p>
                    <p><i class="fas fa-phone"></i> +52 4961260597</p>
                </div>
                <div class="social-links">
                    <a href="https://facebook.com/infomaster" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/infomaster" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="https://instagram.com/infomaster" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://discord.gg/infomaster" target="_blank"><i class="fab fa-discord"></i></a>
                    <a href="https://t.me/infomaster" target="_blank"><i class="fab fa-telegram"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-th-large"></i> Productos</h3>
                <ul>
                    <li><a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=games"><i class="fas fa-gamepad"></i> Gaming</a></li>
                    <li><a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=streaming"><i class="fas fa-tv"></i> Streaming</a></li>
                    <li><a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=software"><i class="fas fa-laptop"></i> Software</a></li>
                    <li><a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=cursos"><i class="fas fa-graduation-cap"></i> Cursos Online</a></li>
                    <li><a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=libros"><i class="fas fa-book"></i> Libros</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-headset"></i> Soporte</h3>
                <ul>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#preguntas-frecuentes" target="_blank"><i class="fas fa-question-circle"></i> Centro de Ayuda</a></li>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#preguntas-frecuentes" target="_blank"><i class="fas fa-question"></i> Preguntas Frecuentes</a></li>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#contacto" target="_blank"><i class="fas fa-envelope"></i> Contacto</a></li>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#como-funciona" target="_blank"><i class="fas fa-cogs"></i> Cómo Funciona</a></li>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#terminos" target="_blank"><i class="fas fa-file-contract"></i> Términos de Servicio</a></li>
                    <li><a href="https://akatsukiom.github.io/faq-infomaster/#privacidad" target="_blank"><i class="fas fa-shield-alt"></i> Política de Privacidad</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-user-circle"></i> Mi Cuenta</h3>
                <ul>
                    <?php if (estaLogueado()): ?>
                        <li><a href="<?= URL_SITIO ?>perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <li><a href="<?= URL_SITIO ?>pedidos.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
                        <li><a href="<?= URL_SITIO ?>wallet.php"><i class="fas fa-wallet"></i> Mi Wallet</a></li>
                        <li><a href="<?= URL_SITIO ?>favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                        <li><a href="<?= URL_SITIO ?>logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?= URL_SITIO ?>modulos/usuarios/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                        <li><a href="<?= URL_SITIO ?>modulos/usuarios/registro.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                        <li><a href="<?= URL_SITIO ?>modulos/usuarios/login.php"><i class="fas fa-wallet"></i> Mi Wallet</a></li>
                        <li><a href="<?= URL_SITIO ?>modulos/usuarios/login.php"><i class="fas fa-shopping-cart"></i> Mi Carrito</a></li>
                        <li><a href="<?= URL_SITIO ?>recuperar.php"><i class="fas fa-key"></i> Recuperar Contraseña</a></li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
        
        <div class="footer-bottom">
            <div class="social-links">
                <a href="https://t.me/infomaster" target="_blank"><i class="fab fa-telegram"></i></a>
                <a href="https://discord.gg/infomaster" target="_blank"><i class="fab fa-discord"></i></a>
                <a href="https://wa.me/524961260597" target="_blank"><i class="fab fa-whatsapp"></i></a>
                <a href="https://instagram.com/infomaster" target="_blank"><i class="fab fa-instagram"></i></a>
            </div>
            <p>&copy; <?= date('Y') ?> InfoMaster. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Botón flotante de WhatsApp -->
<div class="whatsapp-popup">
    <a href="https://wa.me/524961260597" target="_blank" class="whatsapp-link" aria-label="Chat en WhatsApp">
        <div class="whatsapp-tooltip">¿Necesitas ayuda? ¡Chatea con nosotros!</div>
        <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" alt="WhatsApp" class="whatsapp-icon">
    </a>
</div>

<!-- Scripts -->
<script src="<?= URL_SITIO ?>assets/js/site.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?= URL_SITIO ?>assets/js/hero-slider.js"></script>
<script src="<?= URL_SITIO ?>assets/js/testimonials.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>
</html>
