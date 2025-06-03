// testimonials.js
// Control del slider de testimonios - Versión optimizada

console.log('🗣️ Testimonials - Inicializando slider...');

// ========== VARIABLES GLOBALES ==========
let activeTestimonialIndex = 0; // Cambié el nombre para evitar conflictos
let testimonialsAutoInterval = null;
let testimonialsElements = null;
let indicatorsElements = null;
let isTestimonialsInitialized = false;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    initializeTestimonialsSlider();
});

function initializeTestimonialsSlider() {
    // Buscar elementos
    indicatorsElements = document.querySelectorAll('.slider-indicators-custom span');
    testimonialsElements = document.querySelectorAll('.testimonial-card');
    
    // Verificar si existen elementos
    if (indicatorsElements.length === 0 || testimonialsElements.length === 0) {
        console.log('ℹ️ No se encontraron testimonios o indicadores en esta página');
        hideTestimonialsSection();
        return;
    }

    console.log(`✅ Encontrados ${testimonialsElements.length} testimonios`);

    try {
        // Configuración inicial
        setupTestimonials();
        
        // Event listeners para indicadores
        setupIndicatorEvents();
        
        // Auto-cambio de testimonios
        startAutoRotation();
        
        // Pausar en hover
        setupHoverEvents();
        
        // Controles de teclado
        setupKeyboardControls();

        isTestimonialsInitialized = true;
        console.log('🎉 Testimonials slider inicializado correctamente');

    } catch (error) {
        console.error('❌ Error al inicializar testimonials:', error);
        handleTestimonialsError();
    }
}

function hideTestimonialsSection() {
    const testimonialsSection = document.querySelector('.testimonials-section');
    if (testimonialsSection) {
        testimonialsSection.style.display = 'none';
        console.log('📦 Sección de testimonios ocultada (sin contenido)');
    }
}

function handleTestimonialsError() {
    console.warn('⚠️ Testimonials en modo fallback básico');
    // En caso de error, al menos mostrar el primer testimonio
    if (testimonialsElements && testimonialsElements.length > 0) {
        testimonialsElements[0].style.display = 'block';
        testimonialsElements[0].style.opacity = '1';
    }
}

// ========== CONFIGURACIÓN INICIAL ==========
function setupTestimonials() {
    if (!testimonialsElements || !indicatorsElements) return;

    // Configurar testimonios
    testimonialsElements.forEach((testimonial, index) => {
        // Aplicar estilos iniciales
        testimonial.style.display = index === 0 ? 'block' : 'none';
        testimonial.style.opacity = index === 0 ? '1' : '0';
        testimonial.style.transform = index === 0 ? 'translateY(0)' : 'translateY(20px)';
        testimonial.style.transition = 'all 0.3s ease';
        
        // Añadir clases para identificación
        testimonial.classList.toggle('active-testimonial', index === 0);
    });

    // Configurar indicadores
    indicatorsElements.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === 0);
        indicator.style.cursor = 'pointer';
        indicator.style.transition = 'all 0.3s ease';
    });

    activeTestimonialIndex = 0;
    console.log('⚙️ Testimonios configurados inicialmente');
}

// ========== EVENT LISTENERS ==========
function setupIndicatorEvents() {
    if (!indicatorsElements) return;

    indicatorsElements.forEach((indicator, index) => {
        // Click event
        indicator.addEventListener('click', function() {
            showTestimonial(index);
        });

        // Accesibilidad
        indicator.setAttribute('role', 'button');
        indicator.setAttribute('tabindex', '0');
        indicator.setAttribute('aria-label', `Ver testimonio ${index + 1} de ${indicatorsElements.length}`);
        
        // Soporte para teclado en indicadores
        indicator.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                showTestimonial(index);
            }
        });

        // Efectos hover
        indicator.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.opacity = '0.7';
            }
        });

        indicator.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.opacity = '';
            }
        });
    });
}

function setupHoverEvents() {
    const testimonialsSection = document.querySelector('.testimonials-section');
    
    if (!testimonialsSection) return;

    testimonialsSection.addEventListener('mouseenter', function() {
        stopAutoRotation();
        console.log('⏸️ Auto-rotación pausada por hover');
    });
    
    testimonialsSection.addEventListener('mouseleave', function() {
        startAutoRotation();
        console.log('▶️ Auto-rotación reanudada');
    });
}

function setupKeyboardControls() {
    document.addEventListener('keydown', function(e) {
        // Solo funcionar si los testimonios están inicializados
        if (!isTestimonialsInitialized || !testimonialsElements) return;

        // Verificar si la sección de testimonios está visible
        const testimonialsSection = document.querySelector('.testimonials-section');
        if (!testimonialsSection || !isElementInViewport(testimonialsSection)) return;

        if (e.key === 'ArrowLeft' && e.ctrlKey) {
            e.preventDefault();
            previousTestimonial();
        } else if (e.key === 'ArrowRight' && e.ctrlKey) {
            e.preventDefault();
            nextTestimonial();
        }
    });
}

// ========== FUNCIONES PRINCIPALES ==========
function showTestimonial(index) {
    if (!testimonialsElements || !indicatorsElements) return;
    if (index === activeTestimonialIndex || index < 0 || index >= testimonialsElements.length) return;

    console.log(`🔄 Cambiando a testimonio ${index + 1}`);

    // Ocultar testimonio actual
    const currentCard = testimonialsElements[activeTestimonialIndex];
    if (currentCard) {
        currentCard.style.opacity = '0';
        currentCard.style.transform = 'translateY(20px)';
        currentCard.classList.remove('active-testimonial');
        
        setTimeout(() => {
            currentCard.style.display = 'none';
        }, 300);
    }

    // Mostrar nuevo testimonio
    const newCard = testimonialsElements[index];
    if (newCard) {
        newCard.style.display = 'block';
        
        setTimeout(() => {
            newCard.style.opacity = '1';
            newCard.style.transform = 'translateY(0)';
            newCard.classList.add('active-testimonial');
        }, 50);
    }

    // Actualizar indicadores
    indicatorsElements.forEach((indicator, i) => {
        indicator.classList.toggle('active', i === index);
        indicator.style.opacity = i === index ? '1' : '';
    });

    activeTestimonialIndex = index;

    // Reiniciar auto-rotación
    restartAutoRotation();
}

// ========== AUTO-ROTACIÓN ==========
function startAutoRotation() {
    if (!testimonialsElements || testimonialsElements.length <= 1) return;
    
    stopAutoRotation(); // Limpiar intervalo existente
    
    testimonialsAutoInterval = setInterval(() => {
        if (isTestimonialsInitialized) {
            const nextIndex = (activeTestimonialIndex + 1) % testimonialsElements.length;
            showTestimonial(nextIndex);
        }
    }, 5000); // Cambiar cada 5 segundos

    console.log('⏰ Auto-rotación de testimonios iniciada');
}

function stopAutoRotation() {
    if (testimonialsAutoInterval) {
        clearInterval(testimonialsAutoInterval);
        testimonialsAutoInterval = null;
        console.log('⏸️ Auto-rotación pausada');
    }
}

function restartAutoRotation() {
    stopAutoRotation();
    startAutoRotation();
}

// ========== FUNCIONES PÚBLICAS ==========
function nextTestimonial() {
    if (!testimonialsElements || testimonialsElements.length === 0) return false;
    
    const nextIndex = (activeTestimonialIndex + 1) % testimonialsElements.length;
    showTestimonial(nextIndex);
    return true;
}

function previousTestimonial() {
    if (!testimonialsElements || testimonialsElements.length === 0) return false;
    
    const prevIndex = activeTestimonialIndex === 0 ? 
        testimonialsElements.length - 1 : activeTestimonialIndex - 1;
    showTestimonial(prevIndex);
    return true;
}

function goToTestimonial(index) {
    if (!testimonialsElements || testimonialsElements.length === 0) return false;
    if (index < 0 || index >= testimonialsElements.length) return false;
    
    showTestimonial(index);
    return true;
}

function pauseTestimonials() {
    stopAutoRotation();
    return true;
}

function resumeTestimonials() {
    startAutoRotation();
    return true;
}

function getTestimonialsInfo() {
    return {
        isInitialized: isTestimonialsInitialized,
        currentIndex: activeTestimonialIndex,
        totalTestimonials: testimonialsElements ? testimonialsElements.length : 0,
        isAutoplayRunning: testimonialsAutoInterval !== null
    };
}

// ========== UTILIDADES ==========
function isElementInViewport(element) {
    if (!element) return false;
    
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

function reinitializeTestimonials() {
    console.log('🔄 Reinicializando testimonios...');
    
    // Limpiar estado anterior
    stopAutoRotation();
    activeTestimonialIndex = 0;
    isTestimonialsInitialized = false;
    
    // Reinicializar
    setTimeout(() => {
        initializeTestimonialsSlider();
    }, 100);
}

// ========== LIMPIEZA ==========
window.addEventListener('beforeunload', function() {
    stopAutoRotation();
});

// Limpiar cuando se navega
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRotation();
    } else if (isTestimonialsInitialized) {
        startAutoRotation();
    }
});

// ========== API PÚBLICA ==========
// Hacer funciones disponibles globalmente
window.testimonialsControl = {
    next: nextTestimonial,
    prev: previousTestimonial,
    goTo: goToTestimonial,
    pause: pauseTestimonials,
    resume: resumeTestimonials,
    getCurrent: () => activeTestimonialIndex,
    getInfo: getTestimonialsInfo,
    reinitialize: reinitializeTestimonials
};

// Alias para compatibilidad
window.nextTestimonial = nextTestimonial;
window.prevTestimonial = previousTestimonial;
window.goToTestimonial = goToTestimonial;
window.pauseTestimonials = pauseTestimonials;
window.resumeTestimonials = resumeTestimonials;
window.reinitializeTestimonials = reinitializeTestimonials;

console.log('🎉 Testimonials JavaScript cargado completamente');
console.log('🔧 API disponible en: window.testimonialsControl');