// hero-slider.js
// Inicialización y control del carrusel Swiper - Versión optimizada

console.log('🚀 Hero Slider - Iniciando carrusel...');

// ========== VARIABLES GLOBALES ==========
let heroSwiper = null;
let isInitialized = false;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    initializeHeroSlider();
});

function initializeHeroSlider() {
    // Verificar que Swiper esté disponible
    if (typeof Swiper === 'undefined') {
        console.error('❌ Swiper no está disponible');
        return;
    }

    // Verificar que el contenedor existe
    const swiperContainer = document.querySelector('.mySwiper');
    if (!swiperContainer) {
        console.log('ℹ️ Contenedor de slider no encontrado en esta página');
        return;
    }

    console.log('✅ Swiper disponible, inicializando...');

    try {
        // Configuración de Swiper
        const swiperConfig = {
            // Configuración básica
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            speed: 1000,
            effect: 'slide',
            
            // Navegación
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '" aria-label="Ir a slide ' + (index + 1) + '"></span>';
                }
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            
            // Accesibilidad
            a11y: {
                enabled: true,
                prevSlideMessage: 'Slide anterior',
                nextSlideMessage: 'Siguiente slide',
                firstSlideMessage: 'Este es el primer slide',
                lastSlideMessage: 'Este es el último slide'
            },
            
            // Eventos
            on: {
                init: function() {
                    const slideCount = this.slides ? this.slides.length : 0;
                    console.log('✅ Carrusel inicializado con', slideCount, 'slides');
                    
                    // Mostrar caption del primer slide
                    showSlideCaption(this, this.activeIndex);
                    isInitialized = true;
                },
                slideChange: function() {
                    console.log('🔄 Cambio a slide:', (this.realIndex || 0) + 1);
                    
                    // Manejar captions
                    hideAllCaptions(this);
                    showSlideCaption(this, this.activeIndex);
                },
                autoplayStart: function() {
                    console.log('▶️ Autoplay iniciado');
                },
                autoplayStop: function() {
                    console.log('⏸️ Autoplay pausado');
                },
                destroy: function() {
                    console.log('🔥 Swiper destruido');
                    isInitialized = false;
                }
            },
            
            // Responsive
            breakpoints: {
                320: {
                    autoplay: { delay: 3500 },
                    speed: 800
                },
                768: {
                    autoplay: { delay: 4000 },
                    speed: 1000
                },
                1024: {
                    autoplay: { delay: 4500 },
                    speed: 1200
                }
            }
        };

        // Inicializar Swiper
        heroSwiper = new Swiper('.mySwiper', swiperConfig);

        // Configurar controles adicionales
        setupHoverControls();
        setupKeyboardControls();
        setupQualityCheck();

        // Hacer swiper disponible globalmente para debugging
        window.heroSwiper = heroSwiper;

        console.log('🎉 Hero Slider inicializado exitosamente');

    } catch (error) {
        console.error('❌ Error al inicializar carrusel:', error);
        handleSwiperError(error);
    }
}

// ========== FUNCIONES DE CAPTION ==========
function showSlideCaption(swiper, slideIndex) {
    if (!swiper.slides || !swiper.slides[slideIndex]) return;
    
    const activeSlide = swiper.slides[slideIndex];
    const caption = activeSlide.querySelector('.slide-caption');
    
    if (caption) {
        setTimeout(() => {
            caption.style.opacity = '1';
            caption.style.transform = 'translateY(0)';
            caption.style.visibility = 'visible';
        }, 300);
    }
}

function hideAllCaptions(swiper) {
    if (!swiper.slides) return;
    
    swiper.slides.forEach(slide => {
        const caption = slide.querySelector('.slide-caption');
        if (caption) {
            caption.style.opacity = '0';
            caption.style.transform = 'translateY(50px)';
            caption.style.visibility = 'hidden';
        }
    });
}

// ========== CONTROLES ADICIONALES ==========
function setupHoverControls() {
    const swiperContainer = document.querySelector('.mySwiper');
    if (!swiperContainer || !heroSwiper) return;

    swiperContainer.addEventListener('mouseenter', () => {
        if (heroSwiper && heroSwiper.autoplay) {
            heroSwiper.autoplay.stop();
            console.log('⏸️ Autoplay pausado por hover');
        }
    });
    
    swiperContainer.addEventListener('mouseleave', () => {
        if (heroSwiper && heroSwiper.autoplay) {
            heroSwiper.autoplay.start();
            console.log('▶️ Autoplay reanudado');
        }
    });
}

function setupKeyboardControls() {
    document.addEventListener('keydown', function(e) {
        // Solo funcionar si el slider está inicializado y visible
        if (!heroSwiper || !isInitialized) return;
        
        const swiperContainer = document.querySelector('.mySwiper');
        if (!swiperContainer || !isElementVisible(swiperContainer)) return;

        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                heroSwiper.slidePrev();
                break;
            case 'ArrowRight':
                e.preventDefault();
                heroSwiper.slideNext();
                break;
            case ' ':
                e.preventDefault();
                toggleAutoplay();
                break;
            case 'Home':
                e.preventDefault();
                heroSwiper.slideTo(0);
                break;
            case 'End':
                e.preventDefault();
                const lastSlide = heroSwiper.slides ? heroSwiper.slides.length - 1 : 0;
                heroSwiper.slideTo(lastSlide);
                break;
        }
    });
}

function setupQualityCheck() {
    // Verificar funcionamiento después de 2 segundos
    setTimeout(() => {
        if (!heroSwiper) {
            console.warn('⚠️ Hero Slider no está disponible');
            return;
        }

        if (heroSwiper.autoplay && heroSwiper.autoplay.running) {
            console.log('🎯 Carrusel funcionando perfectamente');
        } else {
            console.warn('⚠️ Autoplay no está corriendo, intentando reiniciar...');
            if (heroSwiper.autoplay) {
                heroSwiper.autoplay.start();
            }
        }
    }, 2000);
}

// ========== FUNCIONES DE UTILIDAD ==========
function isElementVisible(element) {
    return element && 
           element.offsetWidth > 0 && 
           element.offsetHeight > 0 && 
           window.getComputedStyle(element).display !== 'none';
}

function toggleAutoplay() {
    if (!heroSwiper || !heroSwiper.autoplay) return;
    
    if (heroSwiper.autoplay.running) {
        heroSwiper.autoplay.stop();
        console.log('⏸️ Autoplay pausado manualmente');
    } else {
        heroSwiper.autoplay.start();
        console.log('▶️ Autoplay iniciado manualmente');
    }
}

function handleSwiperError(error) {
    console.error('Error en Swiper:', error);
    
    // Intentar fallback sin Swiper
    const slides = document.querySelectorAll('.swiper-slide');
    if (slides.length > 0) {
        console.log('🔄 Intentando fallback básico...');
        setupBasicSlider(slides);
    }
}

function setupBasicSlider(slides) {
    // Fallback básico si Swiper falla
    let currentSlide = 0;
    
    slides.forEach((slide, index) => {
        slide.style.display = index === 0 ? 'block' : 'none';
    });
    
    const nextBtn = document.querySelector('.swiper-button-next');
    const prevBtn = document.querySelector('.swiper-button-prev');
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(slides, currentSlide);
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(slides, currentSlide);
        });
    }
    
    console.log('✅ Slider básico configurado como fallback');
}

function showSlide(slides, index) {
    slides.forEach((slide, i) => {
        slide.style.display = i === index ? 'block' : 'none';
    });
}

// ========== API PÚBLICA ==========
function controlHeroSlider(action, ...args) {
    if (!heroSwiper) {
        console.warn('⚠️ Hero Slider no está inicializado');
        return false;
    }

    try {
        switch(action) {
            case 'play':
                if (heroSwiper.autoplay) {
                    heroSwiper.autoplay.start();
                    return true;
                }
                break;
            case 'pause':
                if (heroSwiper.autoplay) {
                    heroSwiper.autoplay.stop();
                    return true;
                }
                break;
            case 'next':
                heroSwiper.slideNext();
                return true;
            case 'prev':
                heroSwiper.slidePrev();
                return true;
            case 'goTo':
                if (args[0] !== undefined) {
                    heroSwiper.slideTo(args[0]);
                    return true;
                }
                break;
            case 'destroy':
                heroSwiper.destroy(true, true);
                heroSwiper = null;
                isInitialized = false;
                return true;
            case 'getInfo':
                return {
                    isInitialized,
                    currentSlide: heroSwiper.activeIndex || 0,
                    totalSlides: heroSwiper.slides ? heroSwiper.slides.length : 0,
                    isAutoplayRunning: heroSwiper.autoplay ? heroSwiper.autoplay.running : false
                };
            default:
                console.log(`
Acciones disponibles:
- play: Iniciar autoplay
- pause: Pausar autoplay  
- next: Siguiente slide
- prev: Slide anterior
- goTo(index): Ir a slide específico
- destroy: Destruir slider
- getInfo: Obtener información del slider
                `);
                return false;
        }
    } catch (error) {
        console.error('Error al controlar slider:', error);
        return false;
    }
}

// ========== LIMPIEZA ==========
window.addEventListener('beforeunload', function() {
    if (heroSwiper) {
        try {
            heroSwiper.destroy(true, true);
        } catch (e) {
            console.warn('Error al destruir Swiper:', e);
        }
    }
});

// ========== EXPORTS ==========
// Hacer funciones disponibles globalmente
window.controlHeroSlider = controlHeroSlider;
window.HeroSlider = {
    control: controlHeroSlider,
    getInstance: () => heroSwiper,
    isReady: () => isInitialized
};

console.log('🎉 Hero Slider JavaScript cargado completamente');
console.log('🔧 API disponible en: window.HeroSlider y window.controlHeroSlider');