<!-- Footer -->
<footer>
  <div class="container">
    <div class="footer-content">

      <div class="footer-section about">
        <h3>Info<span>Master</span></h3>
        <p>Tu Mjor lugar para contratar servicios digitales.</p>
        <div class="contact">
          <p><i class="fas fa-envelope"></i> infomaster@infomaster.com.mx</p>
          <p><i class="fas fa-phone"></i> +52 4961260597</p>
        </div>
        <div class="socials">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      
      <div class="footer-section links">
        <h3>Enlaces rápidos</h3>
        <ul>
          <li><a href="<?= URL_SITIO ?>">Inicio</a></li>
          <li><a href="<?= URL_SITIO ?>productos">Productos</a></li>
          <li><a href="<?= URL_SITIO ?>categorias">Categorías</a></li>
          <li><a href="<?= URL_SITIO ?>como-funciona">Cómo funciona</a></li>
          <li><a href="https://akatsukiom.github.io/faq-infomaster/">Términos y condiciones</a></li>
   <!-- Aquí tu nueva línea: -->
    <li>
      <a href="https://akatsukiom.github.io/faq-infomaster/"
         target="_blank"
         rel="noopener">
        Política de privacidad
      </a>
    </li>
  </ul>
</div>
      
      <div class="footer-section account">
        <h3>Mi cuenta</h3>
        <ul>
          <?php if (estaLogueado()): ?>
            <li><a href="<?= URL_SITIO ?>mi-perfil">Mi perfil</a></li>
            <li><a href="<?= URL_SITIO ?>wallet/recargar">Recargar wallet</a></li>
            <li><a href="<?= URL_SITIO ?>compras">Mis compras</a></li>
            <li><a href="<?= URL_SITIO ?>entregas">Mis entregas</a></li>
            <li><a href="<?= URL_SITIO ?>logout">Cerrar sesión</a></li>
          <?php else: ?>
            <li><a href="<?= URL_SITIO ?>login">Iniciar sesión</a></li>
            <li><a href="<?= URL_SITIO ?>registro">Registrarse</a></li>
            <li><a href="<?= URL_SITIO ?>recuperar">Recuperar contraseña</a></li>
          <?php endif; ?>
        </ul>
      </div>
      
      <div class="footer-section support">
        <h3>Atención al cliente</h3>
        <ul>
          <li><a href="<?= URL_SITIO ?>contacto">Contacto</a></li>
          <li><a href="<?= URL_SITIO ?>faq">Preguntas frecuentes</a></li>
          <li><a href="https://akatsukiom.github.io/faq-infomaster/">Soporte técnico</a></li>
          <li><a href="https://akatsukiom.github.io/faq-infomaster/" target="_blank" rel="noopener">
  Métodos de pago
</a></li>

        </ul>
        <p>Horario de atención:<br>Lunes a Domingo de 8:00 a 00:00</p>
      </div>

    </div>
    
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> InfoMaster. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>

<!-- Scripts necesarios -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script src="<?= URL_SITIO ?>js/main.js"></script>

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
