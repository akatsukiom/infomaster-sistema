<?php
// index.php
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

// 3) Incluir header (carga head, body abierto y <header>)
$titulo = "Inicio - Productos Digitales Premium | InfoMaster";
include 'includes/header.php';
?>

<!-- CSS específicos de esta página con preload -->
<link rel="preload" href="<?= URL_SITIO ?>assets/css/hero-slider.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="<?= URL_SITIO ?>assets/css/hero.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/featured.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/categories.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/features.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/stats.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/how-it-works.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/testimonials.css">
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/cta.css">

<!-- Schema.org JSON-LD para SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "InfoMaster",
  "description": "Tu plataforma de productos digitales con entrega inmediata",
  "url": "<?= URL_SITIO ?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?= URL_SITIO ?>productos.php?buscar={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

<?php
// ========== PREPARAR DATOS DEL SLIDER ==========
$slides = [];

if (!empty($cats)) {
    // Imágenes de alta calidad por categoría (optimizadas para Web Core Vitals)
    $imagenesCategoria = [
        'gaming' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'games' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'juegos' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'streaming' => 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'netflix' => 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'peliculas' => 'https://images.unsplash.com/photo-1489599735734-79b4ba5297fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'software' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'programas' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'cursos' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'educacion' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'libros' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'musica' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'spotify' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80'
    ];
    
    // Iconos con emojis
    $iconos = [
        'games' => '🎮', 'gaming' => '🎮', 'juegos' => '🎮',
        'streaming' => '📺', 'netflix' => '📺', 'series' => '📺', 'peliculas' => '🎬',
        'software' => '💻', 'programas' => '💻', 'windows' => '💻',
        'cursos' => '🎓', 'educacion' => '🎓', 'education' => '🎓',
        'libros' => '📚', 'books' => '📚', 'ebooks' => '📚',
        'musica' => '🎵', 'music' => '🎵', 'spotify' => '🎵'
    ];
    
    foreach ($cats as $index => $cat) {
        $nombreLower = strtolower($cat['nombre']);
        
        // 1) Escoger imagen
        $img = 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80';
        foreach ($imagenesCategoria as $key => $imagen) {
            if (strpos($nombreLower, $key) !== false) {
                $img = $imagen;
                break;
            }
        }
        
        // 2) Escoger icono
        $icono = '🚀';
        foreach ($iconos as $key => $value) {
            if (strpos($nombreLower, $key) !== false) {
                $icono = $value;
                break;
            }
        }
        
        // 3) Descripción
        $descripcion = !empty($cat['descripcion']) 
            ? htmlspecialchars($cat['descripcion'])
            : "Descubre nuestra colección premium de " . htmlspecialchars($cat['nombre']) . ". Productos digitales de calidad con entrega inmediata.";
        
        // 4) Link - CORREGIDO para usar el slug o id correcto
        $link = URL_SITIO . "productos.php?categoria=" . urlencode($cat['slug'] ?? $cat['nombre']);
        
        $slides[] = [
            'icono' => $icono,
            'img' => $img,
            'link' => $link,
            'descripcion' => $descripcion,
            'nombre' => htmlspecialchars($cat['nombre'])
        ];
    }
} else {
    // Slide fallback si no hay categorías
    $slides[] = [
        'icono' => '🚀',
        'img' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=500&q=80',
        'link' => URL_SITIO . 'productos.php',
        'descripcion' => 'Tu plataforma de productos digitales con entrega inmediata y calidad garantizada.',
        'nombre' => 'InfoMaster'
    ];
}
?>

<!-- ========== HERO SLIDER ULTRA MEJORADO ========== -->
<section class="hero-slider" role="banner" aria-label="Categorías destacadas">
  <div class="swiper mySwiper">
    <div class="swiper-wrapper">
      <?php foreach ($slides as $i => $slide): ?>
      <div class="swiper-slide" role="group" aria-label="Slide <?= $i + 1 ?> de <?= count($slides) ?>">
        <div class="category-badge" aria-hidden="true"><?= $slide['icono'] ?> <?= $slide['nombre'] ?></div>
        <img src="<?= $slide['img'] ?>" 
             alt="Categoría <?= $slide['nombre'] ?>" 
             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
             width="1200"
             height="500">
        <div class="slide-caption">
          <h2><?= $slide['nombre'] ?></h2>
          <p><?= $slide['descripcion'] ?></p>
          <a href="<?= $slide['link'] ?>" class="btn btn-primary" aria-label="Explorar productos de <?= $slide['nombre'] ?>">
            <i class="fas fa-eye" aria-hidden="true"></i> 
            <span>Explorar <?= $slide['nombre'] ?></span>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Controles del carrusel con mejor accesibilidad -->
    <div class="swiper-pagination" role="tablist" aria-label="Navegación del carrusel"></div>
    <button class="swiper-button-prev" aria-label="Slide anterior" type="button">
      <i class="fas fa-chevron-left" aria-hidden="true"></i>
    </button>
    <button class="swiper-button-next" aria-label="Siguiente slide" type="button">
      <i class="fas fa-chevron-right" aria-hidden="true"></i>
    </button>
  </div>
</section>

<!-- ========== HERO SECTION MEJORADO ========== -->
<section class="hero" role="main">
  <div class="container">
    <div class="hero-content">
      <h1>🚀 Tu plataforma premium de productos digitales</h1>
      <p>Acceso inmediato y seguro a nuestro catálogo exclusivo con entrega automática, wallet integrado y soporte 24/7.</p>
      <div class="hero-buttons">
        <a href="<?= URL_SITIO ?>productos.php" class="btn btn-primary" aria-label="Ver catálogo completo de productos">
          <i class="fas fa-shopping-bag" aria-hidden="true"></i> 
          <span>Ver productos</span>
        </a>
        <?php if (!estaLogueado()): ?>
          <a href="<?= URL_SITIO ?>registro.php" class="btn btn-outline" aria-label="Crear cuenta gratuita">
            <i class="fas fa-user-plus" aria-hidden="true"></i> 
            <span>Crear cuenta gratis</span>
          </a>
        <?php else: ?>
          <a href="<?= URL_SITIO ?>wallet.php" class="btn btn-outline" aria-label="Recargar mi wallet">
            <i class="fas fa-wallet" aria-hidden="true"></i> 
            <span>Recargar wallet</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ========== PRODUCTOS DESTACADOS ULTRA MEJORADOS ========== -->
<section class="featured-products" aria-labelledby="featured-title">
  <div class="container">
    <h2 class="section-title" id="featured-title">🌟 Productos Destacados</h2>
    <div class="products-grid" role="list">
      <?php if (!empty($destacados)): ?>
        <?php foreach ($destacados as $index => $prod): ?>
          <article class="product-card" 
                   role="listitem"
                   data-aos="fade-up" 
                   data-aos-delay="<?= $index * 100 ?>"
                   data-product-id="<?= $prod['id'] ?>">
            <div class="product-image">
              <img src="<?= htmlspecialchars(URL_SITIO . ltrim($prod['imagen'] ?? 'img/producto-default.jpg', '/')) ?>" 
                   alt="<?= htmlspecialchars($prod['nombre']) ?>"
                   loading="lazy"
                   width="300"
                   height="200"
                   onerror="this.src='<?= URL_SITIO ?>img/producto-default.jpg'">
              <?php if (isset($prod['descuento']) && $prod['descuento'] > 0): ?>
                <div class="product-discount" aria-label="Descuento del <?= $prod['descuento'] ?>%">
                  -<?= $prod['descuento'] ?>%
                </div>
              <?php endif; ?>
              <div class="product-overlay">
                <button class="quick-view-btn" 
                        onclick="showProductQuickView(<?= $prod['id'] ?>)"
                        aria-label="Vista rápida de <?= htmlspecialchars($prod['nombre']) ?>">
                  <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="product-info">
              <h3 class="product-title"><?= htmlspecialchars($prod['nombre']) ?></h3>
              <p class="product-description"><?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 80)) ?>...</p>
              <div class="product-price">
                <?php if (isset($prod['precio_original']) && $prod['precio_original'] > $prod['precio']): ?>
                  <span class="price-original" aria-label="Precio original">
                    <?= MONEDA . number_format($prod['precio_original'], 2) ?>
                  </span>
                <?php endif; ?>
                <span class="price-current" aria-label="Precio actual">
                  <?= MONEDA . number_format($prod['precio'], 2) ?>
                </span>
              </div>
              <button class="product-btn" 
                      onclick="InfoMaster.cart.add('<?= $prod['id'] ?>','<?= addslashes($prod['nombre']) ?>',<?= $prod['precio'] ?>)"
                      aria-label="Agregar <?= htmlspecialchars($prod['nombre']) ?> al carrito">
                <i class="fas fa-cart-plus" aria-hidden="true"></i> 
                <span>Agregar al carrito</span>
              </button>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" role="status">
          <i class="fas fa-box-open" aria-hidden="true"></i>
          <h3>Pronto tendremos productos increíbles</h3>
          <p>Estamos trabajando para traerte los mejores productos digitales del mercado.</p>
          <a href="<?= URL_SITIO ?>productos.php" class="btn">Ver catálogo completo</a>
        </div>
      <?php endif; ?>
    </div>
    
    <?php if (count($destacados) >= 8): ?>
    <div class="featured-cta">
      <a href="<?= URL_SITIO ?>productos.php" class="btn btn-outline">
        <i class="fas fa-arrow-right" aria-hidden="true"></i>
        <span>Ver todos los productos</span>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ========== CATEGORÍAS ULTRA MEJORADAS ========== -->
<section class="categories" aria-labelledby="categories-title">
  <div class="container">
    <h2 class="section-title" id="categories-title">🎯 Nuestras Categorías Premium</h2>
    <div class="categories-grid" role="list">
      
      <article class="category-card" 
               role="listitem"
               onclick="window.location.href='<?= URL_SITIO ?>modulos/productos/productos.php?categoria=games'" 
               data-category="gaming"
               tabindex="0"
               aria-label="Explorar categoría Gaming">
        <div class="category-icon" aria-hidden="true">🎮</div>
        <h3 class="category-title">GAMING</h3>
        <p class="category-description">
          Cuentas premium, skins exclusivas, pases de batalla y fondos para Steam, Epic Games y más plataformas
        </p>
        <div class="category-stats">
          <span class="stat">500+ productos</span>
        </div>
        <div class="category-arrow" aria-hidden="true">
          <i class="fas fa-arrow-right"></i>
        </div>
      </article>
      
      <article class="category-card" 
               role="listitem"
               onclick="window.location.href='<?= URL_SITIO ?>modulos/productos/productos.php?categoria=streaming'" 
               data-category="streaming"
               tabindex="0"
               aria-label="Explorar categoría Streaming">
        <div class="category-icon" aria-hidden="true">📺</div>
        <h3 class="category-title">STREAMING</h3>
        <p class="category-description">
          Netflix, Spotify Premium, Disney+, Amazon Prime y acceso a todas las plataformas de entretenimiento
        </p>
        <div class="category-stats">
          <span class="stat">200+ servicios</span>
        </div>
        <div class="category-arrow" aria-hidden="true">
          <i class="fas fa-arrow-right"></i>
        </div>
      </article>
      
      <article class="category-card" 
               role="listitem"
               onclick="window.location.href='<?= URL_SITIO ?>modulos/productos/productos.php?categoria=software'" 
               data-category="software"
               tabindex="0"
               aria-label="Explorar categoría Software">
        <div class="category-icon" aria-hidden="true">💻</div>
        <h3 class="category-title">SOFTWARE</h3>
        <p class="category-description">
          Windows original, Office 365, Adobe Creative Suite, antivirus premium y todas las licencias profesionales
        </p>
        <div class="category-stats">
          <span class="stat">300+ programas</span>
        </div>
        <div class="category-arrow" aria-hidden="true">
          <i class="fas fa-arrow-right"></i>
        </div>
      </article>
      
      <article class="category-card" 
               role="listitem"
               onclick="window.location.href='<?= URL_SITIO ?>modulos/productos/productos.php?categoria=cursos'" 
               data-category="cursos"
               tabindex="0"
               aria-label="Explorar categoría Cursos">
        <div class="category-icon" aria-hidden="true">🎓</div>
        <h3 class="category-title">CURSOS</h3>
        <p class="category-description">
          Udemy Premium, Coursera Plus, MasterClass y acceso completo a las mejores plataformas educativas
        </p>
        <div class="category-stats">
          <span class="stat">1000+ cursos</span>
        </div>
        <div class="category-arrow" aria-hidden="true">
          <i class="fas fa-arrow-right"></i>
        </div>
      </article>
      
      <article class="category-card" 
               role="listitem"
               onclick="window.location.href='<?= URL_SITIO ?>modulos/productos/productos.php?categoria=libros'" 
               data-category="libros"
               tabindex="0"
               aria-label="Explorar categoría Libros">
        <div class="category-icon" aria-hidden="true">📚</div>
        <h3 class="category-title">LIBROS</h3>
        <p class="category-description">
          Accede a bibliotecas digitales, eBooks, guías, y colecciones exclusivas para aprender más cada día
        </p>
        <div class="category-stats">
          <span class="stat">150+ libros</span>
        </div>
        <div class="category-arrow" aria-hidden="true">
          <i class="fas fa-arrow-right"></i>
        </div>
      </article>

    </div>
  </div>
</section>


<!-- ========== CARACTERÍSTICAS PREMIUM ========== -->
<section class="features" aria-labelledby="features-title">
  <div class="container">
    <h2 class="section-title" id="features-title">✨ Por Qué Somos la Mejor Opción</h2>
    <div class="features-grid" role="list">
      <article class="feature-card" role="listitem" data-aos="fade-up" data-aos-delay="0">
        <div class="feature-icon" aria-hidden="true">⚡</div>
        <h3 class="feature-title">Entrega Inmediata</h3>
        <p class="feature-description">
          Recibe tus productos automáticamente en segundos después del pago confirmado
        </p>
      </article>
      
      <article class="feature-card" role="listitem" data-aos="fade-up" data-aos-delay="100">
        <div class="feature-icon" aria-hidden="true">🛡️</div>
        <h3 class="feature-title">100% Garantizado</h3>
        <p class="feature-description">
          Garantía total en todos nuestros productos o reembolso completo sin preguntas
        </p>
      </article>
      
      <article class="feature-card" role="listitem" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-icon" aria-hidden="true">💎</div>
        <h3 class="feature-title">Calidad Premium</h3>
        <p class="feature-description">
          Solo trabajamos con productos originales verificados y de máxima calidad
        </p>
      </article>
      
      <article class="feature-card" role="listitem" data-aos="fade-up" data-aos-delay="300">
        <div class="feature-icon" aria-hidden="true">🎧</div>
        <h3 class="feature-title">Soporte 24/7</h3>
        <p class="feature-description">
          Nuestro equipo experto está disponible las 24 horas para resolver cualquier duda
        </p>
      </article>
    </div>
  </div>
</section>

<!-- ========== ESTADÍSTICAS IMPRESIONANTES ========== -->
<section class="stats" aria-labelledby="stats-title">
  <div class="container">
    <h2 class="section-title" id="stats-title">📊 Nuestros Números Hablan</h2>
    <div class="stats-grid" role="list">
      <div class="stat-item" role="listitem" data-aos="fade-up" data-aos-delay="0">
        <span class="stat-number" data-count="50000" aria-label="50,000">0</span>
        <div class="stat-label">Clientes Satisfechos</div>
      </div>
      
      <div class="stat-item" role="listitem" data-aos="fade-up" data-aos-delay="100">
        <span class="stat-number" data-count="2500" aria-label="2,500">0</span>
        <div class="stat-label">Productos Premium</div>
      </div>
      
      <div class="stat-item" role="listitem" data-aos="fade-up" data-aos-delay="200">
        <span class="stat-number" data-count="99" aria-label="99 por ciento">0</span>
        <div class="stat-label">% Satisfacción</div>
      </div>
      
      <div class="stat-item" role="listitem" data-aos="fade-up" data-aos-delay="300">
        <span class="stat-number" data-count="24" aria-label="24">0</span>
        <div class="stat-label">Horas de Soporte</div>
      </div>
    </div>
  </div>
</section>

<!-- ========== CÓMO FUNCIONA - PROCESO SIMPLE ========== -->
<section class="how-it-works-section" aria-labelledby="how-it-works-title">
  <div class="container">
    <div class="section-title-custom">
      <h2 id="how-it-works-title">🎯 Proceso Súper Simple</h2>
      <p>Compra fácil y segura en solo 4 pasos</p>
    </div>
    <div class="steps-grid" role="list">
      <?php foreach ([
        ['icon'=>'1','title'=>'Crea tu cuenta','desc'=>'Regístrate gratis y accede a tu panel personal en menos de 2 minutos.'],
        ['icon'=>'2','title'=>'Recarga tu wallet','desc'=>'Añade saldo de forma segura con nuestros múltiples métodos de pago.'],
        ['icon'=>'3','title'=>'Elige tu producto','desc'=>'Explora nuestro catálogo premium y selecciona lo que necesitas.'],
        ['icon'=>'4','title'=>'Disfruta al instante','desc'=>'Recibe tu producto automáticamente y empieza a disfrutarlo.'],
      ] as $index => $s): ?>
      <article class="step-card" 
               role="listitem"
               data-aos="fade-up" 
               data-aos-delay="<?= $index * 100 ?>">
        <div class="step-icon-number" aria-hidden="true"><?= $s['icon'] ?></div>
        <h3><?= $s['title'] ?></h3>
        <p><?= $s['desc'] ?></p>
        <?php if ($index < 3): ?>
          <div class="step-arrow" aria-hidden="true">
            <i class="fas fa-arrow-right"></i>
          </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========== TESTIMONIOS VERIFICADOS ========== -->
<section class="testimonials-section" aria-labelledby="testimonials-title">
  <div class="container">
    <div class="section-title-custom">
      <h2 id="testimonials-title">💬 Testimonios Verificados</h2>
      <p>Experiencias reales de nuestros clientes satisfechos</p>
    </div>
    <div class="testimonial-slider-custom" role="region" aria-label="Testimonios de clientes">
      <?php foreach ([
        ['text'=>'Excelente servicio, la plataforma es muy fácil de usar y la entrega es realmente inmediata. El soporte técnico es increíble. Lo recomiendo 100%.','name'=>'José Rodríguez','when'=>'2023','rating'=>5,'img'=>'img/testimonial-1.jpg','verified'=>true],
        ['text'=>'La mejor plataforma de productos digitales que he usado. El soporte técnico responde muy rápido y siempre resuelven mis dudas. Calidad garantizada.','name'=>'María Gómez','when'=>'2024','rating'=>5,'img'=>'img/testimonial-2.jpg','verified'=>true],
        ['text'=>'Sin duda la mejor opción del mercado. Precios competitivos, entrega inmediata y productos de calidad garantizada. Ya llevo más de 20 compras.','name'=>'Carlos Mendoza','when'=>'2022','rating'=>5,'img'=>'img/testimonial-3.jpg','verified'=>true],
      ] as $t): ?>
      <article class="testimonial-card">
        <div class="testimonial-header">
          <div class="testimonial-rating" aria-label="Calificación: <?= $t['rating'] ?> de 5 estrellas">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fas fa-star <?= $i <= $t['rating'] ? 'active' : '' ?>" aria-hidden="true"></i>
            <?php endfor; ?>
          </div>
          <?php if ($t['verified']): ?>
            <div class="verified-badge" aria-label="Cliente verificado">
              <i class="fas fa-check-circle" aria-hidden="true"></i>
              <span>Verificado</span>
            </div>
          <?php endif; ?>
        </div>
        <blockquote>
          <p>"<?= htmlspecialchars($t['text']) ?>"</p>
        </blockquote>
        <div class="author-info">
          <img src="<?= URL_SITIO . ltrim($t['img'],'/') ?>" 
               alt="Foto de <?= htmlspecialchars($t['name']) ?>" 
               loading="lazy"
               width="60"
               height="60"
               onerror="this.src='<?= URL_SITIO ?>img/avatar-default.jpg'">
          <div class="author-details">
            <h4><?= htmlspecialchars($t['name']) ?></h4>
            <span>Cliente desde <?= $t['when'] ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    
    <!-- Controles de testimonios mejorados -->
    <div class="slider-indicators-custom" role="tablist" aria-label="Navegador de testimonios">
      <button class="active" role="tab" aria-selected="true" aria-label="Testimonio 1"></button>
      <button role="tab" aria-selected="false" aria-label="Testimonio 2"></button>
      <button role="tab" aria-selected="false" aria-label="Testimonio 3"></button>
    </div>
  </div>
</section>

<!-- ========== LLAMADA A LA ACCIÓN FINAL ========== -->
<section class="cta-section" role="region" aria-labelledby="cta-title">
  <div class="container">
    <h2 id="cta-title">🚀 ¿Listo para la Mejor Experiencia Digital?</h2>
    <p>Únete a más de 50,000 clientes satisfechos y descubre por qué somos la plataforma #1 en productos digitales.</p>
    <div class="cta-buttons-custom">
      <?php if (!estaLogueado()): ?>
        <a href="<?= URL_SITIO ?>modulos/usuarios/registro.php" class="btn btn-primary" aria-label="Crear cuenta gratuita ahora">

          <i class="fas fa-rocket" aria-hidden="true"></i> 
          <span>Crear cuenta gratis</span>
        </a>
      <?php else: ?>
        <a href="<?= URL_SITIO ?>productos.php" class="btn btn-primary" aria-label="Explorar catálogo de productos">
          <i class="fas fa-shopping-bag" aria-hidden="true"></i> 
          <span>Explorar catálogo</span>
        </a>
      <?php endif; ?>
      <a href="<?= URL_SITIO ?>modulos/productos/como-funciona.php" class="btn btn-outline" aria-label="Conocer más sobre nuestro proceso">
        <i class="fas fa-info-circle" aria-hidden="true"></i> 
        <span>Conocer más</span>
      </a>
    </div>
    
    <!-- Trust badges -->
    <div class="trust-badges">
      <div class="badge">
        <i class="fas fa-shield-check" aria-hidden="true"></i>
        <span>Pago Seguro</span>
      </div>
      <div class="badge">
        <i class="fas fa-clock" aria-hidden="true"></i>
        <span>Entrega Inmediata</span>
      </div>
      <div class="badge">
        <i class="fas fa-undo" aria-hidden="true"></i>
        <span>Garantía Total</span>
      </div>
    </div>
  </div>
</section>

<!-- ========== MODAL DE VISTA RÁPIDA ========== -->
<div id="quickViewModal" class="modal" role="dialog" aria-labelledby="quickViewTitle" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="quickViewTitle">Vista Rápida del Producto</h3>
      <button class="modal-close" aria-label="Cerrar vista rápida">
        <i class="fas fa-times" aria-hidden="true"></i>
      </button>
    </div>
    <div class="modal-body" id="quickViewContent">
      <!-- Contenido dinámico del producto -->
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- JavaScript optimizado con carga diferida -->
<script>
// Precargar recursos críticos
const criticalResources = [
  '<?= URL_SITIO ?>assets/js/site.js',
  'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js'
];

// Función para cargar scripts de forma optimizada
function loadScript(src, callback) {
  const script = document.createElement('script');
  script.src = src;
  script.defer = true;
  if (callback) script.onload = callback;
  document.head.appendChild(script);
}

// Cargar scripts críticos primero
loadScript(criticalResources[1], function() {
  loadScript(criticalResources[0], function() {
    // Cargar scripts secundarios
    loadScript('<?= URL_SITIO ?>assets/js/hero-slider.js');
    loadScript('<?= URL_SITIO ?>assets/js/testimonials.js');
  });
});
</script>

<!-- Script de inicialización ultra optimizado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 InfoMaster - Página de inicio cargada completamente');
    
    // ===== INICIALIZACIÓN DE EFECTOS DE SCROLL =====
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });
    }
    
    // ===== LAZY LOADING DE IMÁGENES =====
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // ===== NAVEGACIÓN POR TECLADO EN CATEGORÍAS =====
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // ===== ACTUALIZACIÓN DEL CARRITO =====
    function updateCartDisplay() {
        if (window.InfoMaster && window.InfoMaster.cart) {
            const cartItems = window.InfoMaster.cart.getItems();
            const cartCount = document.getElementById('cartCount');
            
            if (cartCount) {
                const totalItems = cartItems.reduce((total, item) => total + item.quantity, 0);
                cartCount.textContent = totalItems;
                cartCount.classList.toggle('hidden', totalItems === 0);
                
                if (totalItems > 0) {
                    console.log(`🛒 Carrito actualizado: ${totalItems} productos`);
                }
            }
        }
    }
    
    // ===== VISTA RÁPIDA DE PRODUCTOS =====
    window.showProductQuickView = function(productId) {
        const modal = document.getElementById('quickViewModal');
        const content = document.getElementById('quickViewContent');
        
        // Mostrar modal con loading
        content.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando información del producto...</p>
            </div>
        `;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // Simular carga de datos del producto
        setTimeout(() => {
            content.innerHTML = `
                <div class="quick-view-product">
                    <div class="product-image">
                        <img src="<?= URL_SITIO ?>img/producto-default.jpg" alt="Producto">
                    </div>
                    <div class="product-details">
                        <h4>Producto ID: ${productId}</h4>
                        <p>Información detallada del producto...</p>
                        <div class="product-price">$99.99</div>
                        <button class="btn btn-primary" onclick="InfoMaster.cart.add('${productId}','Producto','99.99')">
                            <i class="fas fa-cart-plus"></i> Agregar al carrito
                        </button>
                    </div>
                </div>
            `;
        }, 1000);
    };
    
    // ===== CERRAR MODAL =====
    const modal = document.getElementById('quickViewModal');
    const closeBtn = modal.querySelector('.modal-close');
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    });
    
    // ===== ANIMACIONES DE NÚMEROS =====
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        const options = {
            threshold: 0.7,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, options);
        
        counters.forEach(counter => observer.observe(counter));
    }
    
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            element.textContent = Math.floor(current).toLocaleString();
            
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            }
        }, 16);
    }
    
    // ===== INICIALIZAR FUNCIONES =====
    setTimeout(() => {
        updateCartDisplay();
        animateCounters();
    }, 1000);
    
    // ===== PERFORMANCE MONITORING =====
    if ('PerformanceObserver' in window) {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.entryType === 'largest-contentful-paint') {
                    console.log('📊 LCP:', entry.startTime + 'ms');
                }
            }
        });
        observer.observe({entryTypes: ['largest-contentful-paint']});
    }
    
    console.log('✨ InfoMaster - Todas las funcionalidades inicializadas correctamente');
});

// ===== ESTILOS ADICIONALES PARA MEJORAR LA UX =====
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    .loading-spinner {
        text-align: center;
        padding: 2rem;
        color: #666;
    }
    
    .loading-spinner i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #6366f1;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
        transition: color 0.3s ease;
    }
    
    .modal-close:hover {
        color: #333;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .quick-view-product {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .trust-badges {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }
    
    .badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
    }
    
    .verified-badge {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #10b981;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .category-stats {
        margin-top: 0.5rem;
        opacity: 0.8;
        font-size: 0.85rem;
        color: #10b981;
        font-weight: 600;
    }
    
    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .product-card:hover .product-overlay {
        opacity: 1;
    }
    
    .quick-view-btn {
        background: #6366f1;
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quick-view-btn:hover {
        background: #5b5fef;
        transform: scale(1.1);
    }
    
    .featured-cta {
        text-align: center;
        margin-top: 3rem;
    }
    
    @media (max-width: 768px) {
        .quick-view-product {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .trust-badges {
            gap: 1rem;
        }
        
        .badge {
            font-size: 0.8rem;
        }
    }
`;
document.head.appendChild(additionalStyles);
</script>

<!-- Service Worker para PWA (opcional) -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= URL_SITIO ?>sw.js')
            .then(function(registration) {
                console.log('🔧 Service Worker registrado correctamente');
            })
            .catch(function(error) {
                console.log('❌ Error al registrar Service Worker:', error);
            });
    });
}
</script>