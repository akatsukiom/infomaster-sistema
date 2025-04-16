<?php
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';
require_once 'modulos/productos/modelo.php';
require_once 'modulos/carrito/modelo.php';

// Obtener productos destacados
$producto = new Producto($conexion);
$destacados = $producto->obtenerDestacados(8);

// Obtener categorías
$sql = "SELECT * FROM categorias ORDER BY nombre";
$resultado_categorias = $conexion->query($sql);
$categorias = [];

while($fila = $resultado_categorias->fetch_assoc()) {
    $categorias[] = $fila;
}

// Incluir header
$titulo = "Inicio - Productos Digitales";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Tu plataforma de productos digitales</h1>
        <p>Acceso inmediato a nuestro catálogo de productos con entrega automática y sistema de wallet.</p>
        <div class="hero-buttons">
            <a href="productos.php" class="btn">Ver productos</a>
            <?php if(!estaLogueado()): ?>
                <a href="modulos/usuarios/registro.php" class="btn btn-secondary">Crear cuenta</a>
            <?php else: ?>
                <a href="modulos/wallet/recargar.php" class="btn btn-secondary">Recargar wallet</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured">
    <div class="container">
        <div class="section-title">
            <h2>Productos Destacados</h2>
            <p>Los más vendidos de nuestro catálogo</p>
        </div>
        
        <div class="products">
            <?php if(empty($destacados)): ?>
                <p class="no-products">No hay productos destacados disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach($destacados as $producto): ?>
                    <div class="product">
                        <img src="<?php echo $producto['imagen'] ?: 'img/producto-default.jpg'; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img">
                        <div class="product-info">
                            <span class="product-category"><?php echo $producto['categoria']; ?></span>
                            <h3 class="product-title"><?php echo $producto['nombre']; ?></h3>
                            <p><?php echo substr($producto['descripcion'], 0, 60) . (strlen($producto['descripcion']) > 60 ? '...' : ''); ?></p>
                            <div class="product-price"><?php echo MONEDA . number_format($producto['precio'], 2); ?></div>
                            <div class="product-actions">
                                <a href="modulos/productos/detalle.php?id=<?php echo $producto['id']; ?>" class="btn">Ver detalles</a>
                                <a href="modulos/carrito/agregar.php?id=<?php echo $producto['id']; ?>&redirigir=../../index.php" class="btn btn-secondary">Comprar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="view-all">
            <a href="productos.php" class="btn btn-outline">Ver todos los productos</a>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories">
    <div class="container">
        <div class="section-title">
            <h2>Categorías</h2>
            <p>Explora nuestros productos por categoría</p>
        </div>
        
        <div class="category-cards">
            <?php if(empty($categorias)): ?>
                <p class="no-categories">No hay categorías disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach($categorias as $categoria): ?>
                    <div class="category-card">
                        <img src="<?php echo $categoria['imagen'] ?: 'img/categoria-default.jpg'; ?>" alt="<?php echo $categoria['nombre']; ?>">
                        <h3><?php echo $categoria['nombre']; ?></h3>
                        <p><?php echo substr($categoria['descripcion'], 0, 50) . (strlen($categoria['descripcion']) > 50 ? '...' : ''); ?></p>
                        <a href="productos.php?categoria=<?php echo $categoria['id']; ?>" class="btn btn-outline">Ver productos</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="how-it-works">
    <div class="container">
        <div class="section-title">
            <h2>Cómo funciona</h2>
            <p>Compra fácil y rápida en 4 simples pasos</p>
        </div>
        
        <div class="steps">
            <div class="step">
                <div class="step-icon">1</div>
                <h3>Crea tu cuenta</h3>
                <p>Regístrate en nuestra plataforma y accede a tu panel de usuario.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">2</div>
                <h3>Recarga tu wallet</h3>
                <p>Añade saldo a tu wallet mediante nuestros métodos de pago seguros.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">3</div>
                <h3>Elige tu producto</h3>
                <p>Navega por nuestro catálogo y selecciona lo que necesitas.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">4</div>
                <h3>Acceso inmediato</h3>
                <p>Recibe acceso automático a tu compra sin esperas.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="container">
        <div class="section-title">
            <h2>Testimonios</h2>
            <p>Lo que dicen nuestros clientes</p>
        </div>
        
        <div class="testimonial-slider">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Excelente servicio, los productos se entregan al instante y la plataforma es muy fácil de usar. 100% recomendado."</p>
                </div>
                <div class="testimonial-author">
                    <img src="img/testimonial-1.jpg" alt="José Rodríguez">
                    <div>
                        <h4>José Rodríguez</h4>
                        <span>Cliente desde 2023</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"La mejor plataforma para comprar productos digitales. El sistema de wallet es muy práctico y el soporte técnico siempre responde rápido."</p>
                </div>
                <div class="testimonial-author">
                    <img src="img/testimonial-2.jpg" alt="María Gómez">
                    <div>
                        <h4>María Gómez</h4>
                        <span>Cliente desde 2024</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"He probado varias plataformas similares y esta es sin duda la mejor. Precios competitivos y entrega inmediata."</p>
                </div>
                <div class="testimonial-author">
                    <img src="img/testimonial-3.jpg" alt="Carlos Mendoza">
                    <div>
                        <h4>Carlos Mendoza</h4>
                        <span>Cliente desde 2022</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq">
    <div class="container">
        <div class="section-title">
            <h2>Preguntas Frecuentes</h2>
            <p>Respuestas a las dudas más comunes</p>
        </div>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>¿Cómo funciona la entrega automática?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Una vez realizada la compra, el sistema te proporciona automáticamente un código de acceso único. Este código te permite acceder inmediatamente al producto digital que has adquirido, sin tiempos de espera ni intervención manual.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>¿Cómo puedo recargar mi wallet?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Puedes recargar tu wallet a través de varios métodos de pago, incluyendo transferencia bancaria, depósito y PayPal. Una vez procesado tu pago, el saldo se reflejará inmediatamente en tu cuenta.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>¿Los productos tienen garantía?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Sí, todos nuestros productos tienen garantía. Si experimentas algún problema con tu compra, contáctanos dentro de los primeros 7 días y te ayudaremos a resolverlo o te ofreceremos un reembolso.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>¿Puedo usar mi compra en múltiples dispositivos?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Depende del tipo de producto adquirido. En la descripción de cada producto encontrarás información detallada sobre las limitaciones de uso. En general, la mayoría de nuestros productos pueden usarse en múltiples dispositivos con el mismo código de acceso.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta">
    <div class="container">
        <h2>¿Listo para empezar?</h2>
        <p>Únete a miles de clientes satisfechos y comienza a disfrutar de nuestros productos digitales.</p>
        <div class="cta-buttons">
            <?php if(!estaLogueado()): ?>
                <a href="modulos/usuarios/registro.php" class="btn btn-large">Crear cuenta ahora</a>
            <?php else: ?>
                <a href="productos.php" class="btn btn-large">Explorar productos</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // Script para FAQ accordion
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const toggle = question.querySelector('.faq-toggle');
            
            // Alternar visibilidad de la respuesta
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
                toggle.textContent = '+';
            } else {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                toggle.textContent = '-';
            }
        });
    });

    // Aquí podrías añadir un script para el slider de testimonios si es necesario
</script>

<?php include 'includes/footer.php'; ?>