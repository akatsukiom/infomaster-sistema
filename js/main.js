/**
 * InfoMaster - Archivo JavaScript principal
 * Contiene funciones generales del sitio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicialización general
    initMessages();
    initMobileMenu();
    initScrollEffects();
    initFaqAccordion();
    initImageLazyLoad();
    
    // Cargar funciones específicas según la página
    const currentPage = getCurrentPage();
    
    if (currentPage === 'index.php') {
        initTestimonialSlider();
    } else if (currentPage === 'productos.php') {
        initProductFilters();
    }
});

/**
 * Obtiene el nombre de la página actual
 */
function getCurrentPage() {
    const path = window.location.pathname;
    const page = path.split('/').pop();
    return page || 'index.php';
}

/**
 * Inicializa los mensajes de alerta
 */
function initMessages() {
    const messageContainers = document.querySelectorAll('.mensaje-container');
    const closeButtons = document.querySelectorAll('.cerrar-mensaje');
    
    // Auto-ocultar mensajes después de 5 segundos
    if (messageContainers.length > 0) {
        setTimeout(() => {
            messageContainers.forEach(container => {
                fadeOut(container);
            });
        }, 5000);
    }
    
    // Manejar botones de cierre
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const container = this.closest('.mensaje-container');
            fadeOut(container);
        });
    });
}

/**
 * Inicializa el menú móvil
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('nav ul');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('show');
        });
    }
}

/**
 * Inicializa efectos de scroll
 */
function initScrollEffects() {
    // Revelar elementos al hacer scroll
    const revealElements = document.querySelectorAll('.reveal');
    
    if (revealElements.length > 0) {
        window.addEventListener('scroll', function() {
            const windowHeight = window.innerHeight;
            const revealPoint = 150;
            
            revealElements.forEach(element => {
                const revealTop = element.getBoundingClientRect().top;
                
                if (revealTop < windowHeight - revealPoint) {
                    element.classList.add('active');
                }
            });
        });
    }
    
    // Header fijo al scroll
    const header = document.querySelector('header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('fixed');
            } else {
                header.classList.remove('fixed');
            }
        });
    }
}

/**
 * Inicializa el accordion de preguntas frecuentes
 */
function initFaqAccordion() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const toggle = this.querySelector('.faq-toggle');
            
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
}

/**
 * Inicializa el slider de testimonios
 */
function initTestimonialSlider() {
    const slider = document.querySelector('.testimonial-slider');
    const testimonials = document.querySelectorAll('.testimonial');
    
    if (slider && testimonials.length > 1) {
        let currentSlide = 0;
        
        // Crear indicadores
        const indicatorsContainer = document.createElement('div');
        indicatorsContainer.className = 'slider-indicators';
        
        for (let i = 0; i < testimonials.length; i++) {
            const indicator = document.createElement('span');
            indicator.className = i === 0 ? 'active' : '';
            indicator.dataset.index = i;
            
            indicator.addEventListener('click', function() {
                goToSlide(parseInt(this.dataset.index));
            });
            
            indicatorsContainer.appendChild(indicator);
        }
        
        slider.parentNode.appendChild(indicatorsContainer);
        
        // Crear botones de navegación
        const prevButton = document.createElement('button');
        prevButton.className = 'slider-nav prev';
        prevButton.innerHTML = '&lt;';
        prevButton.addEventListener('click', prevSlide);
        
        const nextButton = document.createElement('button');
        nextButton.className = 'slider-nav next';
        nextButton.innerHTML = '&gt;';
        nextButton.addEventListener('click', nextSlide);
        
        slider.parentNode.appendChild(prevButton);
        slider.parentNode.appendChild(nextButton);
        
        // Configurar slider
        slider.style.width = `${testimonials.length * 100}%`;
        testimonials.forEach(testimonial => {
            testimonial.style.width = `${100 / testimonials.length}%`;
        });
        
        // Auto-rotate
        let interval = setInterval(nextSlide, 5000);
        
        slider.addEventListener('mouseenter', () => {
            clearInterval(interval);
        });
        
        slider.addEventListener('mouseleave', () => {
            interval = setInterval(nextSlide, 5000);
        });
        
        // Funciones de navegación
        function nextSlide() {
            currentSlide = (currentSlide + 1) % testimonials.length;
            goToSlide(currentSlide);
        }
        
        function prevSlide() {
            currentSlide = (currentSlide - 1 + testimonials.length) % testimonials.length;
            goToSlide(currentSlide);
        }
        
        function goToSlide(index) {
            slider.style.transform = `translateX(-${index * (100 / testimonials.length)}%)`;
            
            // Actualizar indicadores
            document.querySelectorAll('.slider-indicators span').forEach((indicator, i) => {
                indicator.className = i === index ? 'active' : '';
            });
            
            currentSlide = index;
        }
    }
}

/**
 * Inicializa la carga diferida de imágenes
 */
function initImageLazyLoad() {
    const lazyImages = document.querySelectorAll('.lazy-load');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const image = entry.target;
                    image.src = image.dataset.src;
                    image.classList.remove('lazy-load');
                    imageObserver.unobserve(image);
                }
            });
        });
        
        lazyImages.forEach(image => {
            imageObserver.observe(image);
        });
    } else {
        // Fallback para navegadores que no soportan IntersectionObserver
        lazyImages.forEach(image => {
            image.src = image.dataset.src;
            image.classList.remove('lazy-load');
        });
    }
}

/**
 * Inicializa los filtros de productos
 */
function initProductFilters() {
    const filterForm = document.querySelector('.product-filters');
    
    if (filterForm) {
        const rangeInputs = filterForm.querySelectorAll('input[type="range"]');
        
        rangeInputs.forEach(input => {
            const output = document.querySelector(`output[for="${input.id}"]`);
            
            if (output) {
                output.textContent = input.value;
                
                input.addEventListener('input', function() {
                    output.textContent = this.value;
                });
            }
        });
        
        // Actualizar filtros sin recargar la página
        filterForm.addEventListener('change', function(e) {
            if (e.target.tagName !== 'BUTTON') {
                this.submit();
            }
        });
    }
}

/**
 * Funciones de utilidad
 */

// Función para fade out
function fadeOut(element) {
    element.style.opacity = 1;
    
    (function fade() {
        if ((element.style.opacity -= 0.1) < 0) {
            element.style.display = 'none';
        } else {
            requestAnimationFrame(fade);
        }
    })();
}

// Función para fade in
function fadeIn(element, display) {
    element.style.opacity = 0;
    element.style.display = display || 'block';
    
    (function fade() {
        let val = parseFloat(element.style.opacity);
        if (!((val += 0.1) > 1)) {
            element.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
}

// Función para hacer peticiones AJAX
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status >= 200 && this.status < 300) {
            callback(null, JSON.parse(this.response));
        } else {
            callback(new Error(this.statusText), null);
        }
    };
    
    xhr.onerror = function() {
        callback(new Error('Error de red'), null);
    };
    
    xhr.send(data);
}