<?php
// Asegurar acceso y cargar dependencias
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';
require_once 'modulos/productos/modelo.php';
require_once 'modulos/carrito/modelo.php';

// 1) Productos destacados
$productoModel = new Producto($conexion);
$destacados    = $productoModel->obtenerDestacados(8);

// 2) Categorías
$sql  = "SELECT * FROM categorias ORDER BY nombre";
$rs   = $conexion->query($sql);
$cats = $rs->fetch_all(MYSQLI_ASSOC);

// 3) Incluir header
$titulo = "Inicio - Productos Digitales";
include 'includes/header.php';
?>

<style>
/* CARRUSEL HERO MEJORADO */
.hero-slider {
  position: relative;
  height: 500px;
  overflow: hidden;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  margin-bottom: 3rem;
}

.swiper-container {
  width: 100%;
  height: 100%;
}

.swiper-slide {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.swiper-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.swiper-slide:hover img {
  transform: scale(1.05);
}

/* Overlay gradiente para mejor legibilidad */
.swiper-slide::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg, 
    rgba(67, 97, 238, 0.8) 0%, 
    rgba(131, 56, 236, 0.6) 50%,
    rgba(255, 0, 110, 0.4) 100%
  );
  z-index: 1;
}

/* Contenido del slide */
.slide-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 3rem 2rem;
  color: white;
  z-index: 2;
  background: linear-gradient(transparent, rgba(0,0,0,0.3));
  opacity: 0;
  transform: translateY(50px);
  animation: slideUp 0.8s ease forwards;
  animation-delay: 0.3s;
}

.slide-caption h2 {
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 800;
  margin-bottom: 1rem;
  text-shadow: 0 4px 20px rgba(0,0,0,0.5);
  line-height: 1.1;
}

.slide-caption p {
  font-size: clamp(1rem, 2vw, 1.3rem);
  margin-bottom: 2rem;
  opacity: 0.95;
  max-width: 600px;
  text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.slide-caption .btn {
  display: inline-block;
  padding: 1rem 2.5rem;
  background: rgba(255, 255, 255, 0.2);
  color: white !important;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1.1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  text-shadow: none;
}

.slide-caption .btn:hover {
  background: rgba(255, 255, 255, 0.9);
  color: #4361ee !important;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Controles del carrusel */
.swiper-pagination {
  bottom: 20px !important;
}

.swiper-pagination-bullet {
  width: 12px;
  height: 12px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 1;
  transition: all 0.3s ease;
}

.swiper-pagination-bullet-active {
  background: white;
  transform: scale(1.3);
}

/* Flechas de navegación */
.swiper-button-next,
.swiper-button-prev {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.swiper-button-next:after,
.swiper-button-prev:after {
  font-size: 18px;
  color: white;
  font-weight: bold;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
  background: rgba(255, 255, 255, 0.9);
  transform: scale(1.1);
}

.swiper-button-next:hover:after,
.swiper-button-prev:hover:after {
  color: #4361ee;
}

/* Indicador de categoría */
.category-badge {
  position: absolute;
  top: 2rem;
  left: 2rem;
  background: rgba(255, 107, 107, 0.9);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 25px;
  font-size: 0.9rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
  z-index: 2;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Efectos de animación */
@keyframes slideUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive para carrusel */
@media (max-width: 768px) {
  .hero-slider {
    height: 400px;
  }

  .slide-caption {
    padding: 2rem 1.5rem;
  }

  .slide-caption h2 {
    font-size: 2rem;
  }

  .slide-caption p {
    font-size: 1rem;
    margin-bottom: 1.5rem;
  }

  .slide-caption .btn {
    padding: 0.8rem 2rem;
    font-size: 1rem;
  }

  .category-badge {
    top: 1rem;
    left: 1rem;
    font-size: 0.8rem;
    padding: 0.4rem 1rem;
  }

  .swiper-button-next,
  .swiper-button-prev {
    width: 40px;
    height: 40px;
  }

  .swiper-button-next:after,
  .swiper-button-prev:after {
    font-size: 16px;
  }
}
</style>

<!-- SWIPER SLIDER CON IMÁGENES REALES -->
<section class="hero-slider">
  <div class="swiper-container">
    <div class="swiper-wrapper">
      <?php 
      foreach ($cats as $cat): 
        // Buscar imagen para el slider en este orden de prioridad:
        $img = '';
        $nombreLower = strtolower($cat['nombre']);
        
        // 1. Buscar por nombre exacto de categoría
        $imagenesPosibles = [
          $nombreLower . '-slider.jpg',
          $nombreLower . '-slider.png',
          $nombreLower . '.jpg',
          $nombreLower . '.png'
        ];
        
        foreach ($imagenesPosibles as $nombreImg) {
          $rutaImg = $_SERVER['DOCUMENT_ROOT'] . '/img/sliders/' . $nombreImg;
          if (file_exists($rutaImg)) {
            $img = URL_SITIO . 'img/sliders/' . $nombreImg;
            break;
          }
        }
        
        // 2. Buscar por palabras clave si no encontró imagen exacta
        if (empty($img)) {
          $keywords = [
            'gaming' => ['gaming', 'games', 'juegos'],
            'streaming' => ['streaming', 'netflix', 'series', 'peliculas'],
            'software' => ['software', 'programas', 'windows'],
            'cursos' => ['cursos', 'educacion', 'education'],
            'libros' => ['libros', 'books', 'ebooks'],
            'musica' => ['musica', 'music', 'spotify']
          ];
          
          foreach ($keywords as $categoria => $palabras) {
            foreach ($palabras as $palabra) {
              if (strpos($nombreLower, $palabra) !== false) {
                $rutaImg = $_SERVER['DOCUMENT_ROOT'] . '/img/sliders/' . $categoria . '-slider.jpg';
                if (file_exists($rutaImg)) {
                  $img = URL_SITIO . 'img/sliders/' . $categoria . '-slider.jpg';
                  break 2;
                }
              }
            }
          }
        }
        
        // 3. Si existe imagen_slider en BD, usarla
        if (empty($img) && !empty($cat['imagen_slider'])) {
          $rutaImg = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($cat['imagen_slider'], '/');
          if (file_exists($rutaImg)) {
            $img = URL_SITIO . ltrim($cat['imagen_slider'], '/');
          }
        }
        
        // 4. Usar imagen normal de la categoría si existe
        if (empty($img) && !empty($cat['imagen'])) {
          $rutaImg = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($cat['imagen'], '/');
          if (file_exists($rutaImg)) {
            $img = URL_SITIO . ltrim($cat['imagen'], '/');
          }
        }
        
        // 5. Usar imagen por defecto si existe
        if (empty($img)) {
          $rutaDefault = $_SERVER['DOCUMENT_ROOT'] . '/img/sliders/default-slider.jpg';
          if (file_exists($rutaDefault)) {
            $img = URL_SITIO . 'img/sliders/default-slider.jpg';
          } else {
            // Fallback a placeholder online
            $img = 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80';
          }
        }
        
        // Iconos para diferentes categorías
        $iconos = [
          'games' => '🎮', 'gaming' => '🎮', 'juegos' => '🎮',
          'streaming' => '📺', 'netflix' => '📺', 'series' => '📺', 'peliculas' => '🎬',
          'software' => '💻', 'programas' => '💻', 'windows' => '💻',
          'cursos' => '🎓', 'educacion' => '🎓', 'education' => '🎓',
          'libros' => '📚', 'books' => '📚', 'ebooks' => '📚',
          'musica' => '🎵', 'music' => '🎵', 'spotify' => '🎵'
        ];
        
        // Buscar icono
        $icono = '🚀';
        foreach ($iconos as $key => $value) {
          if (strpos($nombreLower, $key) !== false) {
            $icono = $value;
            break;
          }
        }
        
        // Descripción
        $descripcion = !empty($cat['descripcion']) 
          ? htmlspecialchars($cat['descripcion'])
          : "Descubre nuestra colección de " . htmlspecialchars($cat['nombre']) . ". Productos digitales de calidad con entrega inmediata.";
      ?>
      <div class="swiper-slide">
        <div class="category-badge"><?= $icono ?> <?= htmlspecialchars($cat['nombre']) ?></div>
        <img src="<?= htmlspecialchars($img) ?>" 
             alt="<?= htmlspecialchars($cat['nombre']) ?>" 
             loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80'">
        <div class="slide-caption">
          <h2><?= htmlspecialchars($cat['nombre']) ?></h2>
          <p><?= $descripcion ?></p>
          <a href="<?= URL_SITIO ?>productos.php?categoria=<?= $cat['id'] ?>" class="btn">
            <i class="fas fa-eye"></i> Ver <?= htmlspecialchars($cat['nombre']) ?>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Controles -->
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Tu plataforma de productos digitales</h1>
      <p>Acceso inmediato y seguro a nuestro catálogo de productos con entrega automática y wallet integrado.</p>
      <div class="hero-buttons">
        <a href="<?= URL_SITIO ?>productos" class="btn">Ver productos</a>
        <?php if (!estaLogueado()): ?>
          <a href="<?= URL_SITIO ?>usuarios/registro" class="btn btn-outline">Crear cuenta</a>
        <?php else: ?>
          <a href="<?= URL_SITIO ?>wallet/recargar" class="btn btn-outline">Recargar wallet</a>
        <?php endif; ?>
      </div>
      <div class="hero-cards">
        <div class="hero-card">
          <i class="fas fa-rocket"></i>
          <h3>Entrega inmediata</h3>
          <p>Recibe tu producto al instante</p>
        </div>
        <div class="hero-card">
          <i class="fas fa-lock"></i>
          <h3>100% Seguro</h3>
          <p>Transacciones protegidas</p>
        </div>
        <div class="hero-card">
          <i class="fas fa-headset"></i>
          <h3>Soporte 24/7</h3>
          <p>Estamos para ayudarte</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DESTACADOS -->
<section class="featured">
  <div class="container">
    <div class="section-title">
      <h2>Productos Destacados</h2>
      <p>Los más vendidos de nuestro catálogo</p>
    </div>
    <div class="products">
      <?php if (empty($destacados)): ?>
        <p class="no-products">No hay productos destacados disponibles.</p>
      <?php else: ?>
        <?php foreach ($destacados as $prod):
          $rutaProdImg = !empty($prod['imagen'])
            ? URL_SITIO . ltrim($prod['imagen'], '/')
            : URL_SITIO . 'img/producto-default.jpg';
        ?>
        <div class="product">
          <?php if ($prod['destacado']): ?>
            <span class="badge">Destacado</span>
          <?php endif; ?>
          <img src="<?= htmlspecialchars($rutaProdImg) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="product-img" loading="lazy">
          <div class="info">
            <span class="category"><?= htmlspecialchars($prod['categoria']) ?></span>
            <h3 class="title"><?= htmlspecialchars($prod['nombre']) ?></h3>
            <p class="description">
                <?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 60)) ?>
                <?= strlen($prod['descripcion'] ?? '') > 60 ? '…' : '' ?>
            </p>
            <div class="price"><?= MONEDA . number_format($prod['precio'], 2) ?></div>
            <div class="actions">
              <a href="<?= URL_SITIO ?>productos/<?= $prod['id'] ?>" class="btn">Ver detalles</a>
              <a href="<?= URL_SITIO ?>carrito/agregar/<?= $prod['id'] ?>?redirigir=/" class="btn btn-secondary">Comprar</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="view-all">
      <a href="<?= URL_SITIO ?>productos" class="btn btn-outline">Ver todos los productos</a>
    </div>
  </div>
</section>

<!-- CATEGORÍAS -->
<section class="categories">
  <div class="container">
    <div class="section-title">
      <h2>Categorías</h2>
      <p>Explora por categoría</p>
    </div>
    
    <?php if (empty($cats)): ?>
      <p>No hay categorías disponibles.</p>
    <?php else: ?>
      <div class="categories-container">
        <?php foreach ($cats as $cat):
          $rutaCatImg = !empty($cat['imagen'])
            ? URL_SITIO . ltrim($cat['imagen'], '/')
            : URL_SITIO . 'img/categoria-default.jpg';
        ?>
          <div class="category-card">
            <div class="category-img-container">
              <img src="<?= htmlspecialchars($rutaCatImg) ?>" 
                   alt="<?= htmlspecialchars($cat['nombre']) ?>"
                   class="category-img"
                   loading="lazy">
            </div>
            <div class="category-info">
              <h3 class="category-title"><?= htmlspecialchars($cat['nombre']) ?></h3>
              <p class="category-description">
                <?= htmlspecialchars(substr($cat['descripcion'] ?? '', 0, 50)) ?>
                <?= strlen($cat['descripcion'] ?? '') > 50 ? '…' : '' ?>
              </p>
              <a href="<?= URL_SITIO ?>productos.php?categoria=<?= $cat['id'] ?>"
                 class="category-btn">Ver productos</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- CÓMO FUNCIONA -->
<section class="how-it-works">
  <div class="container">
    <div class="section-title">
      <h2>Cómo funciona</h2>
      <p>Compra fácil y rápida en 4 sencillos pasos</p>
    </div>
    <div class="steps">
      <?php
      $steps = [
        ['icon' => '1', 'title' => 'Crea tu cuenta', 'desc' => 'Regístrate y accede a tu panel.'],
        ['icon' => '2', 'title' => 'Recarga tu wallet', 'desc' => 'Añade saldo con nuestros métodos.'],
        ['icon' => '3', 'title' => 'Elige tu producto', 'desc' => 'Selecciona lo que necesitas.'],
        ['icon' => '4', 'title' => 'Acceso inmediato', 'desc' => 'Disfruta tu compra al instante.'],
      ];
      foreach ($steps as $s): ?>
        <div class="step">
          <div class="step-icon"><?= $s['icon'] ?></div>
          <h3><?= $s['title'] ?></h3>
          <p><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIOS -->
<section class="testimonials">
  <div class="container">
    <div class="section-title">
      <h2>Testimonios</h2>
      <p>Lo que dicen nuestros clientes</p>
    </div>
    <div class="testimonial-slider">
      <?php
      $testigos = [
        ['text' => 'Excelente servicio... plataforma muy fácil de usar.', 'name' => 'José Rodríguez', 'when' => '2023', 'img' => 'img/testimonial-1.jpg'],
        ['text' => 'La mejor plataforma... soporte técnico responde rápido.', 'name' => 'María Gómez', 'when' => '2024', 'img' => 'img/testimonial-2.jpg'],
        ['text' => 'Sin duda la mejor. Precios competitivos y entrega inmediata.', 'name' => 'Carlos Mendoza', 'when' => '2022', 'img' => 'img/testimonial-3.jpg'],
      ];
      foreach ($testigos as $t):
        $imgTest = URL_SITIO . ltrim($t['img'], '/');
      ?>
        <div class="testimonial">
          <p>"<?= htmlspecialchars($t['text']) ?>"</p>
          <div class="author">
            <img src="<?= htmlspecialchars($imgTest) ?>" alt="<?= htmlspecialchars($t['name']) ?>" loading="lazy">
            <div>
              <h4><?= htmlspecialchars($t['name']) ?></h4>
              <span>Cliente desde <?= $t['when'] ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="slider-indicators">
      <?php foreach (array_keys($testigos) as $i): ?>
        <span data-index="<?= $i ?>"<?= $i === 0 ? ' class="active"' : '' ?>></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta">
  <div class="container">
    <h2>¿Listo para empezar?</h2>
    <p>Únete a miles de clientes satisfechos hoy mismo.</p>
    <div class="cta-buttons">
      <?php if (!estaLogueado()): ?>
        <a href="<?= URL_SITIO ?>usuarios/registro" class="btn">Crear cuenta ahora</a>
      <?php else: ?>
        <a href="<?= URL_SITIO ?>productos" class="btn">Explorar productos</a>
      <?php endif; ?>
      <a href="<?= URL_SITIO ?>como-funciona" class="btn btn-outline">Saber más</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Swiper JS -->
<script src="https://unpkg.com/swiper@9/swiper-bundle.min.js"></script>

<!-- JavaScript mejorado para todos los sliders -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ========== CARRUSEL HERO ==========
  if (typeof Swiper !== 'undefined' && document.querySelector('.swiper-container')) {
    const heroSwiper = new Swiper('.swiper-container', {
      // Configuración básica
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      },
      speed: 800,
      effect: 'slide',

      // Navegación
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
        dynamicBullets: true
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },

      // Eventos
      on: {
        slideChange: function() {
          // Reiniciar animaciones de los captions
          const activeSlide = this.slides[this.activeIndex];
          const caption = activeSlide.querySelector('.slide-caption');
          if (caption) {
            caption.style.animation = 'none';
            caption.offsetHeight; // trigger reflow
            caption.style.animation = 'slideUp 0.8s ease forwards';
          }
        },
        init: function() {
          console.log('🎉 Carrusel hero inicializado correctamente');
          
          // Aplicar animación inicial
          const firstCaption = this.slides[this.activeIndex].querySelector('.slide-caption');
          if (firstCaption) {
            firstCaption.style.animation = 'slideUp 0.8s ease forwards';
          }
        }
      },

      // Responsive breakpoints
      breakpoints: {
        320: {
          spaceBetween: 10,
          autoplay: { delay: 4000 }
        },
        768: {
          spaceBetween: 20,
          autoplay: { delay: 5000 }
        },
        1024: {
          spaceBetween: 30,
          autoplay: { delay: 6000 }
        }
      }
    });

    // Control del autoplay con hover
    const swiperContainer = document.querySelector('.swiper-container');
    if (swiperContainer) {
      swiperContainer.addEventListener('mouseenter', () => {
        heroSwiper.autoplay.stop();
      });
      
      swiperContainer.addEventListener('mouseleave', () => {
        heroSwiper.autoplay.start();
      });
    }
  } else {
    console.warn('⚠️ Swiper no está disponible o no hay contenedor');
  }

  // ========== SLIDER DE TESTIMONIOS ==========
  const testimonialSlider = document.querySelector('.testimonial-slider');
  const testimonialItems = testimonialSlider ? testimonialSlider.children : [];
  const dots = document.querySelectorAll('.slider-indicators span');
  let currentTestimonial = 0;

  function showTestimonial(index) {
    if (testimonialSlider && testimonialItems.length > 0) {
      testimonialSlider.style.transform = `translateX(-${index * 100}%)`;
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
      });
      currentTestimonial = index;
    }
  }

  // Event listeners para los indicadores
  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => showTestimonial(index));
  });

  // Auto-play para testimonios
  if (testimonialItems.length > 1) {
    let testimonialAutoPlay = setInterval(() => {
      showTestimonial((currentTestimonial + 1) % testimonialItems.length);
    }, 5000);

    // Pausar auto-play al hacer hover
    if (testimonialSlider) {
      testimonialSlider.addEventListener('mouseenter', () => {
        clearInterval(testimonialAutoPlay);
      });
      
      testimonialSlider.addEventListener('mouseleave', () => {
        testimonialAutoPlay = setInterval(() => {
          showTestimonial((currentTestimonial + 1) % testimonialItems.length);
        }, 5000);
      });
    }
  }

  // ========== MANEJO DE ERRORES DE IMÁGENES ==========
  function handleImageError(img) {
    const fallbacks = [
      '<?= URL_SITIO ?>img/default-slide.jpg',
      '<?= URL_SITIO ?>img/producto-default.jpg',
      '<?= URL_SITIO ?>img/categoria-default.jpg',
      'https://via.placeholder.com/400x300/4361ee/ffffff?text=Imagen+no+disponible'
    ];
    
    const currentSrc = img.src;
    let nextFallback = fallbacks.find(fallback => fallback !== currentSrc);
    
    if (nextFallback) {
      img.src = nextFallback;
    }
    
    console.warn('⚠️ Error cargando imagen:', currentSrc);
  }

  // Aplicar handler a todas las imágenes
  const allImages = document.querySelectorAll('img');
  allImages.forEach(img => {
    img.addEventListener('error', () => handleImageError(img));
  });

  // ========== LAZY LOADING MANUAL (fallback) ==========
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            observer.unobserve(img);
          }
        }
      });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img);
    });
  }

  console.log('✅ Todos los scripts de index.php cargados correctamente');
});

// Función global para manejar errores de imágenes
function handleGlobalImageError(img) {
  img.src = 'https://via.placeholder.com/400x300/4361ee/ffffff?text=Imagen+no+disponible';
  img.alt = 'Imagen no disponible';
}
</script>