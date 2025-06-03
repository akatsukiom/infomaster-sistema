<?php
// includes/header.php
if (!defined('ACCESO_PERMITIDO')) {
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo) ?></title>

  <!-- SEO Meta -->
  <meta name="description" content="InfoMaster - Tu plataforma de productos digitales con entrega inmediata">
  <meta name="keywords" content="productos digitales, gaming, streaming, software, cursos online">
  <meta name="author" content="InfoMaster">
  
  <!-- Open Graph Meta -->
  <meta property="og:title" content="<?= htmlspecialchars($titulo) ?>">
  <meta property="og:description" content="Tu plataforma de productos digitales con entrega inmediata">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= URL_SITIO ?>">
  <meta property="og:image" content="<?= URL_SITIO ?>assets/images/og-image.png">

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= URL_SITIO ?>assets/images/favicon.ico">
  <link rel="apple-touch-icon" href="<?= URL_SITIO ?>assets/images/apple-touch-icon.png">

  <!-- Fuentes & Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- CSS Variables principales para efectos -->
  <style>
    :root {
      --gradient-main: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
      --gradient-accent: linear-gradient(135deg, #10b981 0%, #059669 100%);
      --current-page: '<?= basename($_SERVER['PHP_SELF']) ?>';
    }
  </style>

  <!-- CSS Globales -->
  <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/variables.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/reset.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/base.css?v=<?= time() ?>">

  <!-- CSS del Header ULTRA MEJORADO -->
  <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/header.css?v=<?= time() ?>">
  
  <!-- CSS del Footer -->
  <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/footer.css?v=<?= time() ?>">

  <!-- Meta tags adicionales para PWA (opcional) -->
  <meta name="theme-color" content="#6366f1">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  
  <!-- Preload crítico para mejorar rendimiento -->
  <link rel="preload" href="<?= URL_SITIO ?>assets/css/header.css" as="style">
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
</head>
<body class="<?= estaLogueado() ? 'user-logged' : 'user-guest' ?>">
  
  <!-- ===== HEADER ULTRA MEJORADO ===== -->
  <header class="header" id="header" role="banner">
    <div class="header-container">
      
      <!-- ===== LOGO ULTRA MEJORADO ===== -->
      <a href="<?= URL_SITIO ?>index.php" class="logo" title="InfoMaster - Inicio">
        <div class="logo-img" aria-hidden="true">
          <!-- El símbolo ▶ se agrega automáticamente con CSS ::before -->
        </div>
        <span class="logo-text">InfoMaster</span>
      </a>

      <!-- ===== BÚSQUEDA ULTRA MEJORADA ===== -->
      <div class="search-section">
        <div class="search-container">
          <form class="search-form" method="GET" action="<?= URL_SITIO ?>modulos/productos/productos.php" role="search">
            <input
              type="search"
              name="buscar"
              class="search-input"
              placeholder="Buscar productos, cursos, servicios..."
              id="searchInput"
              value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>"
              autocomplete="off"
              aria-label="Buscar productos"
            >
            <button type="submit" class="search-btn" aria-label="Realizar búsqueda">
              <i class="fas fa-search" aria-hidden="true"></i>
              <span>Buscar</span>
            </button>
          </form>
        </div>
      </div>

      <!-- ===== NAVEGACIÓN PRINCIPAL ULTRA MEJORADA ===== -->
      <nav class="nav" id="nav" role="navigation" aria-label="Navegación principal">
        
        <!-- Inicio -->
        <div class="nav-item">
          <a href="<?= URL_SITIO ?>index.php" 
             class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>"
             <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'aria-current="page"' : '' ?>>
            <i class="fas fa-home" aria-hidden="true"></i>
            <span>Inicio</span>
          </a>
        </div>

        <!-- Categorías con Dropdown Ultra Mejorado -->
        <div class="nav-item">
          <a href="<?= URL_SITIO ?>modulos/productos/productos.php" 
             class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'productos.php') ? 'active' : '' ?>"
             <?= (basename($_SERVER['PHP_SELF']) == 'productos.php') ? 'aria-current="page"' : '' ?>
             aria-haspopup="true" 
             aria-expanded="false">
            <i class="fas fa-th-large" aria-hidden="true"></i>
            <span>Categorías</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
          </a>
          <div class="dropdown-menu" role="menu" aria-label="Categorías de productos">
            <div class="dropdown-content">
              <a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=games" class="dropdown-item" role="menuitem">
                <i class="fas fa-gamepad" aria-hidden="true"></i>
                <span>Gaming</span>
              </a>
              <a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=streaming" class="dropdown-item" role="menuitem">
                <i class="fas fa-video" aria-hidden="true"></i>
                <span>Streaming</span>
              </a>
              <a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=software" class="dropdown-item" role="menuitem">
                <i class="fas fa-laptop-code" aria-hidden="true"></i>
                <span>Software</span>
              </a>
              <a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=cursos" class="dropdown-item" role="menuitem">
                <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                <span>Cursos</span>
              </a>
              <a href="<?= URL_SITIO ?>modulos/productos/productos.php?categoria=libros" class="dropdown-item" role="menuitem">
                <i class="fas fa-book" aria-hidden="true"></i>
<span>Libros</span>

            </a>
            </div>
          </div>
        </div>

        <!-- Soporte con Dropdown Ultra Mejorado -->
<div class="nav-item dropdown">
  <a href="https://akatsukiom.github.io/faq-infomaster/" 
     class="nav-link"
     target="_blank" 
     aria-haspopup="true" 
     aria-expanded="false">
    <i class="fas fa-headset" aria-hidden="true"></i>
    <span>Soporte</span>
    <i class="fas fa-chevron-down" aria-hidden="true"></i>
  </a>
  <div class="dropdown-menu" role="menu" aria-label="Opciones de soporte">
    <div class="dropdown-content">
      <a href="https://akatsukiom.github.io/faq-infomaster/" class="dropdown-item" role="menuitem" target="_blank">
        <i class="fas fa-question-circle" aria-hidden="true"></i>
        <span>Preguntas Frecuentes</span>
      </a>
      <a href="https://akatsukiom.github.io/faq-infomaster/" class="dropdown-item" role="menuitem" target="_blank">
        <i class="fas fa-headset" aria-hidden="true"></i>
        <span>Soporte Técnico</span>
      </a>
      <a href="https://akatsukiom.github.io/faq-infomaster/" class="dropdown-item" role="menuitem" target="_blank">
        <i class="fas fa-envelope" aria-hidden="true"></i>
        <span>Contacto</span>
      </a>
      <a href="https://akatsukiom.github.io/faq-infomaster/" class="dropdown-item" role="menuitem" target="_blank">
        <i class="fas fa-file-alt" aria-hidden="true"></i>
        <span>Políticas</span>
      </a>
    </div>
  </div>
</div>
        </div>

        <!-- Cómo Funciona -->
        <div class="nav-item">
          <a href="<?= URL_SITIO ?>modulos/productos/como-funciona.php" 
             class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'modulos/productos/como-funciona.php') ? 'active' : '' ?>"
             <?= (basename($_SERVER['PHP_SELF']) == 'modulos/productos/como-funciona.php') ? 'aria-current="page"' : '' ?>>
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>Cómo Funciona</span>
          </a>
        </div>

      </nav>

      <!-- ===== MENÚ DE USUARIO ULTRA MEJORADO ===== -->
      <div class="user-menu">
        
        <?php if (estaLogueado()): ?>
          <!-- Usuario logueado -->
          
          <!-- Wallet/Saldo -->
          <a href="<?= URL_SITIO ?>wallet.php" class="wallet" title="Mi Wallet - Saldo disponible: $<?= number_format(obtenerSaldoUsuario(), 2) ?>">
            <i class="fas fa-wallet" aria-hidden="true"></i>
            <span aria-label="Saldo: <?= number_format(obtenerSaldoUsuario(), 2) ?> dólares">
              $<?= number_format(obtenerSaldoUsuario(), 2) ?>
            </span>
          </a>

          <!-- Carrito -->
          <a href="<?= URL_SITIO ?>modulos/carrito/ver.php" class="cart" title="Mi Carrito de Compras">
            <i class="fas fa-shopping-cart" aria-hidden="true"></i>
            <span>Carrito</span>
            <span class="cart-count hidden" id="cartCount" aria-label="Productos en carrito">0</span>
          </a>

          <!-- Menú de cuenta con dropdown -->
          <div class="user-actions">
            <div class="nav-item">
              <a href="<?= URL_SITIO ?>perfil.php" 
                 class="btn"
                 aria-haspopup="true" 
                 aria-expanded="false"
                 title="Mi Cuenta">
                <i class="fas fa-user" aria-hidden="true"></i>
                <span>Mi Cuenta</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
              </a>
              <div class="dropdown-menu" role="menu" aria-label="Opciones de cuenta">
                <div class="dropdown-content">
                  <a href="<?= URL_SITIO ?>perfil.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span>Mi Perfil</span>
                  </a>
                  <a href="<?= URL_SITIO ?>pedidos.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-shopping-bag" aria-hidden="true"></i>
                    <span>Mis Pedidos</span>
                  </a>
                  <a href="<?= URL_SITIO ?>favoritos.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-heart" aria-hidden="true"></i>
                    <span>Favoritos</span>
                  </a>
                  <a href="<?= URL_SITIO ?>configuracion.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                    <span>Configuración</span>
                  </a>
                  <a href="<?= URL_SITIO ?>transacciones.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                    <span>Transacciones</span>
                  </a>
                  <div class="dropdown-separator" aria-hidden="true"></div>
                  <a href="<?= URL_SITIO ?>logout.php" class="dropdown-item logout-item" role="menuitem">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Cerrar Sesión</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Usuario no logueado -->
          
          <!-- Carrito para invitados -->
          <a href="<?= URL_SITIO ?>modulos/carrito/ver.php" class="cart" title="Mi Carrito de Compras">
            <i class="fas fa-shopping-cart" aria-hidden="true"></i>
            <span>Carrito</span>
            <span class="cart-count hidden" id="cartCount" aria-label="Productos en carrito">0</span>
          </a>

          <!-- Botones de Login/Registro -->
          <div class="user-actions">
            <a href="<?= URL_SITIO ?>modulos/usuarios/login.php" class="btn" title="Iniciar Sesión">
              <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
              <span>Login</span>
            </a>
            <a href="<?= URL_SITIO ?>modulos/usuarios/registro.php" class="btn btn-primary" title="Crear Cuenta Nueva">
              <i class="fas fa-user-plus" aria-hidden="true"></i>
              <span>Registro</span>
            </a>
          </div>

        <?php endif; ?>
        
        <!-- ===== BOTÓN MÓVIL ULTRA MEJORADO ===== -->
        <button class="mobile-toggle" 
                id="mobileToggle" 
                aria-label="Abrir menú de navegación"
                aria-expanded="false"
                aria-controls="nav"
                type="button">
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </button>

      </div>
    </div>
  </header>

  <!-- Espaciador para header fijo -->
  <div class="header-spacer" aria-hidden="true"></div>

  <!-- Scripts de inicialización ultra mejorados -->
  <script>
    // ===== INICIALIZACIÓN ULTRA MEJORADA =====
    document.addEventListener('DOMContentLoaded', function() {
      const header = document.getElementById('header');
      const searchInput = document.getElementById('searchInput');
      const mobileToggle = document.getElementById('mobileToggle');
      const nav = document.getElementById('nav');
      
      console.log('🚀 Iniciando InfoMaster Header...');
      
      // Marcar header como cargado
      if (header) {
        header.classList.add('loaded');
        console.log('✅ Header cargado correctamente');
      }
      
      // ===== EFECTO SCROLL OPTIMIZADO =====
      if (header) {
        let ticking = false;
        
        function updateHeaderScroll() {
          if (window.scrollY > 20) {
            header.classList.add('scrolled');
          } else {
            header.classList.remove('scrolled');
          }
          ticking = false;
        }
        
        window.addEventListener('scroll', function() {
          if (!ticking) {
            requestAnimationFrame(updateHeaderScroll);
            ticking = true;
          }
        }, { passive: true });
      }
      
      // ===== PLACEHOLDER ANIMADO EN BÚSQUEDA =====
      if (searchInput) {
        const placeholders = [
          'Buscar productos digitales...',
          'Buscar cursos premium...',
          'Buscar software licenciado...',
          'Buscar cuentas gaming...',
          'Buscar servicios streaming...',
          'Buscar VPN y seguridad...',
          'Buscar almacenamiento cloud...'
        ];
        
        let currentPlaceholder = 0;
        let placeholderInterval;
        
        const changePlaceholder = () => {
          if (document.activeElement !== searchInput && !searchInput.value) {
            searchInput.placeholder = placeholders[currentPlaceholder];
            currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
          }
        };
        
        // Iniciar animación de placeholders
        placeholderInterval = setInterval(changePlaceholder, 3000);
        
        // Eventos de focus mejorados
        searchInput.addEventListener('focus', function() {
          clearInterval(placeholderInterval);
          this.placeholder = 'Escribe tu búsqueda...';
          this.parentElement.classList.add('focused');
        });
        
        searchInput.addEventListener('blur', function() {
          this.parentElement.classList.remove('focused');
          if (!this.value) {
            changePlaceholder();
            placeholderInterval = setInterval(changePlaceholder, 3000);
          }
        });
      }
      
      // ===== MOBILE TOGGLE MEJORADO =====
      if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const isExpanded = this.getAttribute('aria-expanded') === 'true';
          const newState = !isExpanded;
          
          // Actualizar estados
          this.classList.toggle('active');
          nav.classList.toggle('active');
          this.setAttribute('aria-expanded', newState);
          this.setAttribute('aria-label', newState ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
          
          // Controlar scroll del body
          if (nav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            document.body.style.height = '100vh';
          } else {
            document.body.style.overflow = '';
            document.body.style.height = '';
          }
          
          console.log('📱 Menú móvil:', newState ? 'abierto' : 'cerrado');
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
          if (nav.classList.contains('active') && 
              !nav.contains(e.target) && 
              !mobileToggle.contains(e.target)) {
            
            mobileToggle.classList.remove('active');
            nav.classList.remove('active');
            mobileToggle.setAttribute('aria-expanded', 'false');
            mobileToggle.setAttribute('aria-label', 'Abrir menú de navegación');
            document.body.style.overflow = '';
            document.body.style.height = '';
          }
        });
        
        // Cerrar menú al cambiar tamaño de ventana
        window.addEventListener('resize', function() {
          if (window.innerWidth > 768 && nav.classList.contains('active')) {
            mobileToggle.classList.remove('active');
            nav.classList.remove('active');
            mobileToggle.setAttribute('aria-expanded', 'false');
            mobileToggle.setAttribute('aria-label', 'Abrir menú de navegación');
            document.body.style.overflow = '';
            document.body.style.height = '';
          }
        });
      }
      
      // ===== DROPDOWN HOVER CORREGIDO - SOLUCIÓN AL PROBLEMA =====
      const dropdownItems = document.querySelectorAll('.nav-item');
      
      dropdownItems.forEach(navItem => {
        const trigger = navItem.querySelector('[aria-haspopup="true"]');
        const dropdown = navItem.querySelector('.dropdown-menu');
        
        if (!trigger || !dropdown) return;
        
        let hoverTimeout;
        let isHovering = false;
        
        // Función para mostrar dropdown
        function showDropdown() {
          clearTimeout(hoverTimeout);
          isHovering = true;
          
          // Solo en desktop
          if (window.innerWidth > 768) {
            trigger.setAttribute('aria-expanded', 'true');
            dropdown.style.display = 'block';
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'translateX(-50%) translateY(0) scale(1)';
            dropdown.style.pointerEvents = 'auto';
            
            console.log('📋 Dropdown mostrado');
          }
        }
        
        // Función para ocultar dropdown
        function hideDropdown(delay = 300) {
          isHovering = false;
          
          hoverTimeout = setTimeout(() => {
            if (!isHovering && window.innerWidth > 768) {
              trigger.setAttribute('aria-expanded', 'false');
              dropdown.style.opacity = '0';
              dropdown.style.visibility = 'hidden';
              dropdown.style.transform = 'translateX(-50%) translateY(-20px) scale(0.9)';
              dropdown.style.pointerEvents = 'none';
              
              // Ocultar completamente después de la transición
              setTimeout(() => {
                if (!isHovering) {
                  dropdown.style.display = 'none';
                }
              }, 300);
              
              console.log('📋 Dropdown ocultado');
            }
          }, delay);
        }
        
        // Eventos en el trigger (nav-link)
        trigger.addEventListener('mouseenter', function() {
          console.log('🖱️ Mouse enter en trigger');
          showDropdown();
        });
        
        trigger.addEventListener('mouseleave', function() {
          console.log('🖱️ Mouse leave en trigger');
          // Dar tiempo para moverse al dropdown
          setTimeout(() => {
            if (!isHovering) {
              hideDropdown(100);
            }
          }, 50);
        });
        
        // Eventos en todo el nav-item (contenedor)
        navItem.addEventListener('mouseenter', function() {
          console.log('🖱️ Mouse enter en nav-item');
          showDropdown();
        });
        
        navItem.addEventListener('mouseleave', function() {
          console.log('🖱️ Mouse leave en nav-item');
          hideDropdown(200);
        });
        
        // Eventos en el dropdown
        dropdown.addEventListener('mouseenter', function() {
          console.log('🖱️ Mouse enter en dropdown');
          clearTimeout(hoverTimeout);
          isHovering = true;
        });
        
        dropdown.addEventListener('mouseleave', function() {
          console.log('🖱️ Mouse leave en dropdown');
          hideDropdown(100);
        });
        
        // Click en móvil
        trigger.addEventListener('click', function(e) {
          if (window.innerWidth <= 768) {
            e.preventDefault();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            // Cerrar otros dropdowns primero
            dropdownItems.forEach(otherItem => {
              if (otherItem !== navItem) {
                const otherTrigger = otherItem.querySelector('[aria-haspopup="true"]');
                if (otherTrigger) {
                  otherTrigger.setAttribute('aria-expanded', 'false');
                  otherItem.classList.remove('mobile-open');
                }
              }
            });
            
            // Alternar el actual
            this.setAttribute('aria-expanded', newState);
            navItem.classList.toggle('mobile-open', newState);
            
            console.log('📱 Dropdown móvil:', newState ? 'abierto' : 'cerrado');
          }
        });
        
        // Navegación con teclado
        trigger.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (window.innerWidth <= 768) {
              this.click();
            } else {
              showDropdown();
            }
          } else if (e.key === 'Escape') {
            hideDropdown(0);
            this.focus();
          }
        });
        
        // Focus y blur para accesibilidad
        trigger.addEventListener('focus', function() {
          if (window.innerWidth > 768) {
            showDropdown();
          }
        });
        
        navItem.addEventListener('focusout', function(e) {
          // Solo ocultar si el focus sale completamente del nav-item
          setTimeout(() => {
            if (!navItem.contains(document.activeElement)) {
              hideDropdown(0);
            }
          }, 0);
        });
      });
      
      // ===== EFECTOS DE RIPPLE EN BOTONES =====
      const interactiveElements = document.querySelectorAll('.btn, .dropdown-item, .nav-link, .wallet, .cart');
      
      function createRippleEffect(element, event) {
        // Solo para clics, no para hover
        if (event.type !== 'click') return;
        
        // No crear ripple si ya hay uno
        if (element.querySelector('.ripple-effect')) return;
        
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.className = 'ripple-effect';
        ripple.style.cssText = `
          position: absolute;
          border-radius: 50%;
          background: rgba(255,255,255,0.3);
          width: ${size}px;
          height: ${size}px;
          left: ${x}px;
          top: ${y}px;
          transform: scale(0);
          animation: ripple 0.6s ease-out;
          pointer-events: none;
          z-index: 1;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
          if (ripple.parentNode) {
            ripple.remove();
          }
        }, 600);
      }
      
      interactiveElements.forEach(element => {
        element.addEventListener('click', function(e) {
          // Solo para elementos que no son enlaces externos
          if (!this.href || this.href.includes(window.location.hostname) || this.href.startsWith('/') || this.href.startsWith('#')) {
            createRippleEffect(this, e);
          }
        });
      });
      
      // ===== ACTUALIZAR CONTADOR DE CARRITO =====
      function updateCartDisplay() {
        const cartCount = document.getElementById('cartCount');
        
        if (cartCount) {
          // Intentar obtener del localStorage primero
          let totalItems = 0;
          
          try {
            const cartData = localStorage.getItem('infomaster_cart');
            if (cartData) {
              const cart = JSON.parse(cartData);
              totalItems = cart.reduce((total, item) => total + (item.quantity || 0), 0);
            }
          } catch (e) {
            console.warn('Error al leer carrito desde localStorage:', e);
          }
          
          // Si existe InfoMaster.cart, usarlo
          if (window.InfoMaster && window.InfoMaster.cart) {
            try {
              const cartItems = window.InfoMaster.cart.getItems();
              totalItems = cartItems.reduce((total, item) => total + (item.quantity || 0), 0);
            } catch (e) {
              console.warn('Error al obtener items del carrito:', e);
            }
          }
          
          cartCount.textContent = totalItems;
          
          if (totalItems > 0) {
            cartCount.classList.remove('hidden');
            cartCount.setAttribute('aria-label', `${totalItems} producto${totalItems !== 1 ? 's' : ''} en carrito`);
          } else {
            cartCount.classList.add('hidden');
            cartCount.setAttribute('aria-label', 'Carrito vacío');
          }
        }
      }
      
      // Actualizar carrito al cargar
      updateCartDisplay();
      
      // Actualizar carrito cada 2 segundos
      setInterval(updateCartDisplay, 2000);
      
      // Escuchar eventos de carrito personalizados
      window.addEventListener('cartUpdated', updateCartDisplay);
      window.addEventListener('storage', function(e) {
        if (e.key === 'infomaster_cart') {
          updateCartDisplay();
        }
      });
      
      console.log('✨ InfoMaster Header completamente inicializado');
    });

    // ===== UTILIDADES GLOBALES =====
    window.InfoMasterHeader = {
      showNotification: function(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `header-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
          position: fixed;
          top: calc(var(--header-height) + 10px);
          right: 20px;
          background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#6366f1'};
          color: white;
          padding: 12px 20px;
          border-radius: 8px;
          box-shadow: 0 4px 20px rgba(0,0,0,0.3);
          z-index: 1005;
          transform: translateX(100%);
          transition: transform 0.3s ease;
          font-weight: 500;
          font-size: 14px;
        `;
        
        document.body.appendChild(notification);
        
        // Mostrar notificación
        setTimeout(() => {
          notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Ocultar notificación
        setTimeout(() => {
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => {
            if (notification.parentNode) {
              notification.remove();
            }
          }, 300);
        }, duration);
      },
      
      setLoading: function(loading = true) {
        const header = document.getElementById('header');
        if (header) {
          if (loading) {
            header.classList.add('loading');
          } else {
            header.classList.remove('loading');
          }
        }
      },
      
      setError: function(error = true) {
        const header = document.getElementById('header');
        if (header) {
          if (error) {
            header.classList.add('error');
          } else {
            header.classList.remove('error');
          }
        }
      },
      
      updateCartCount: function(count) {
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
          cartCount.textContent = count;
          if (count > 0) {
            cartCount.classList.remove('hidden');
            cartCount.setAttribute('aria-label', `${count} producto${count !== 1 ? 's' : ''} en carrito`);
          } else {
            cartCount.classList.add('hidden');
            cartCount.setAttribute('aria-label', 'Carrito vacío');
          }
          
          // Disparar evento personalizado
          window.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count } }));
        }
      },
      
      highlightNavItem: function(href) {
        // Remover active de todos los nav-links
        document.querySelectorAll('.nav-link').forEach(link => {
          link.classList.remove('active');
          link.removeAttribute('aria-current');
        });
        
        // Agregar active al enlace correspondiente
        const targetLink = document.querySelector(`a[href="${href}"]`);
        if (targetLink && targetLink.classList.contains('nav-link')) {
          targetLink.classList.add('active');
          targetLink.setAttribute('aria-current', 'page');
        }
      }
    };

    // ===== CSS ADICIONAL PARA MEJORAR HOVER =====
    const hoverStyles = document.createElement('style');
    hoverStyles.textContent = `
      /* Mejoras específicas para hover de dropdowns */
      .nav-item {
        position: relative;
      }
      
      .dropdown-menu {
        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease !important;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-50%) translateY(-20px) scale(0.9);
      }
      
      /* Estados activos mejorados */
      .nav-item:hover .dropdown-menu,
      .nav-item:focus-within .dropdown-menu {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateX(-50%) translateY(0) scale(1) !important;
        pointer-events: auto !important;
      }
      
      /* Área de hover extendida para evitar que se cierre */
      .nav-item::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        height: 15px;
        background: transparent;
        z-index: 1001;
      }
      
      /* Indicador visual de hover */
      .nav-item:hover .nav-link .fa-chevron-down {
        transform: rotate(180deg);
        color: #10b981;
      }
      
      /* Efectos visuales adicionales */
      @keyframes ripple {
        to {
          transform: scale(2);
          opacity: 0;
        }
      }
      
      .search-container.focused {
        transform: translateY(-2px) scale(1.01);
      }
      
      .dropdown-separator {
        height: 1px;
        background: rgba(255,255,255,0.1);
        margin: 0.5rem 0;
      }
      
      .logout-item {
        color: #ef4444 !important;
      }
      
      .logout-item:hover {
        background: rgba(239, 68, 68, 0.1) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
      }
      
      /* Mejoras de accesibilidad */
      .nav-link:focus,
      .btn:focus,
      .search-input:focus,
      .mobile-toggle:focus {
        outline: 2px solid #10b981;
        outline-offset: 2px;
      }
      
      /* Estados de loading */
      .header.loading {
        pointer-events: none;
        opacity: 0.8;
      }
      
      .header.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #10b981, transparent);
        animation: loading 1.5s ease-in-out infinite;
      }
      
      @keyframes loading {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
      }
      
      /* Estados de error */
      .header.error {
        background: rgba(239, 68, 68, 0.1);
        border-bottom-color: rgba(239, 68, 68, 0.3);
      }
      
      .header.error::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #ef4444, transparent);
        animation: errorPulse 2s ease-in-out infinite;
      }
      
      @keyframes errorPulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
      }
      
      /* Mejoras para móvil */
      @media (max-width: 768px) {
        .nav-item::after {
          display: none;
        }
        
        .nav-item.mobile-open .dropdown-menu {
          display: block !important;
          opacity: 1 !important;
          visibility: visible !important;
          transform: none !important;
          pointer-events: auto !important;
        }
      }
      
      /* Animaciones de entrada */
      .header.loaded .nav-item {
        animation: slideInUp 0.8s ease-out backwards;
      }
      
      .header.loaded .nav-item:nth-child(1) { animation-delay: 0.1s; }
      .header.loaded .nav-item:nth-child(2) { animation-delay: 0.2s; }
      .header.loaded .nav-item:nth-child(3) { animation-delay: 0.3s; }
      .header.loaded .nav-item:nth-child(4) { animation-delay: 0.4s; }
      
      @keyframes slideInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      /* Efectos hover mejorados */
      .nav-link:hover .fas:not(.fa-chevron-down) {
        animation: bounce 0.6s ease-in-out;
      }
      
      @keyframes bounce {
        0%, 20%, 60%, 100% { transform: translateY(0) scale(1); }
        40% { transform: translateY(-8px) scale(1.1); }
        80% { transform: translateY(-4px) scale(1.05); }
      }
      
      /* Indicador de página activa */
      .nav-link[aria-current="page"]::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 10px #10b981;
      }
      
      /* Prevenir FOUC (Flash of Unstyled Content) */
      .header {
        visibility: hidden;
      }
      
      .header.loaded {
        visibility: visible;
      }
    `;
    
    document.head.appendChild(hoverStyles);
    
    // ===== MANEJO DE ERRORES GLOBALES =====
    window.addEventListener('error', function(e) {
      console.error('Error en header:', e.error);
      if (window.InfoMasterHeader) {
        window.InfoMasterHeader.setError(true);
        setTimeout(() => {
          window.InfoMasterHeader.setError(false);
        }, 5000);
      }
    });
    
    // ===== EVENTOS PERSONALIZADOS =====
    
    // Evento cuando se completa la carga del header
    window.dispatchEvent(new CustomEvent('headerLoaded', {
      detail: {
        version: '2.0.0',
        features: ['responsive', 'dropdowns', 'mobile-menu', 'accessibility', 'hover-fixed']
      }
    }));
    
    // Función para debug (solo en desarrollo)
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev') || window.location.hostname.includes('127.0.0.1')) {
      window.debugHeader = function() {
        console.group('🔧 InfoMaster Header Debug');
        console.log('📱 Mobile toggle:', document.getElementById('mobileToggle'));
        console.log('🧭 Navigation:', document.getElementById('nav'));
        console.log('🔍 Search input:', document.getElementById('searchInput'));
        console.log('🛒 Cart count:', document.getElementById('cartCount'));
        console.log('📋 Dropdowns:', document.querySelectorAll('.dropdown-menu').length);
        console.log('🎯 Active nav item:', document.querySelector('.nav-link.active'));
        console.log('📱 Is mobile menu open:', document.getElementById('nav').classList.contains('active'));
        console.log('🖱️ Hover events working:', window.innerWidth > 768 ? 'Yes' : 'Mobile mode');
        console.groupEnd();
      };
      
      console.log('🛠️ Debug disponible: window.debugHeader()');
    }
  </script>

  <!-- JSON-LD estructurado para SEO -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "InfoMaster",
    "description": "Tu plataforma de productos digitales con entrega inmediata",
    "url": "<?= URL_SITIO ?>",
    "potentialAction": {
      "@type": "SearchAction",
      "target": "<?= URL_SITIO ?>modulos/productos/productos.php?buscar={search_term_string}",
      "query-input": "required name=search_term_string"
    },
    "publisher": {
      "@type": "Organization",
      "name": "InfoMaster",
      "url": "<?= URL_SITIO ?>"
    }
  }
  </script>