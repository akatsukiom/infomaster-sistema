// site.js - JavaScript Ultra Optimizado para InfoMaster
// Compatible con header CSS ultra mejorado

console.log('🚀 InfoMaster - Inicializando JavaScript principal...');

// ========== VARIABLES GLOBALES ==========
let siteCart = []; // Carrito principal
let notificationTimeout = null;
let isHeaderLoaded = false;
let searchPlaceholderInterval = null;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 DOM cargado - Inicializando funcionalidades...');
    
    // Inicializar en orden específico para mejor rendimiento
    initializeHeader();
    initializeMobileMenu();
    initializeDropdowns();
    initializeSearch();
    initializeCart();
    initializeCounters();
    initializeSmoothScrolling();
    initializeKeyboardShortcuts();
    initializeFormValidation();
    initializeAnimations();
    
    console.log('✅ InfoMaster - Todas las funcionalidades inicializadas');
    
    // Mostrar mensaje de bienvenida después de que todo esté listo
    setTimeout(() => {
        showNotification('¡Bienvenido a InfoMaster! 🚀', 'success');
    }, 2000);
});

// ========== HEADER FUNCTIONS ULTRA MEJORADAS ==========
function initializeHeader() {
    const header = document.getElementById('header');
    
    if (!header) {
        console.warn('❌ Header no encontrado');
        return;
    }
    
    // Efecto scroll optimizado con throttling
    let isScrolling = false;
    
    function updateHeaderOnScroll() {
        if (!isScrolling) {
            window.requestAnimationFrame(() => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 20) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                isScrolling = false;
            });
            isScrolling = true;
        }
    }
    
    // Event listener optimizado
    window.addEventListener('scroll', updateHeaderOnScroll, { passive: true });
    
    // Marcar header como cargado para efectos CSS
    header.classList.add('loaded');
    isHeaderLoaded = true;
    
    console.log('✅ Header ultra mejorado inicializado');
}

// ========== BÚSQUEDA MEJORADA ==========
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchContainer = document.querySelector('.search-container');
    
    if (!searchInput) return;
    
    // Placeholders rotativos mejorados
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
    
    function rotatePlaceholder() {
        if (document.activeElement !== searchInput && !searchInput.value) {
            searchInput.placeholder = placeholders[currentPlaceholder];
            currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
        }
    }
    
    // Inicializar rotación de placeholders
    searchPlaceholderInterval = setInterval(rotatePlaceholder, 3000);
    
    // Efectos de focus mejorados
    searchInput.addEventListener('focus', function() {
        this.placeholder = 'Escribe tu búsqueda...';
        if (searchContainer) {
            searchContainer.classList.add('focused');
        }
    });
    
    searchInput.addEventListener('blur', function() {
        if (searchContainer) {
            searchContainer.classList.remove('focused');
        }
        if (!this.value) {
            rotatePlaceholder();
        }
    });
    
    // Búsqueda en tiempo real (opcional)
    searchInput.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            console.log('🔍 Búsqueda:', query);
            // Aquí podrías implementar búsqueda en tiempo real
        }
    }, 300));
    
    console.log('✅ Sistema de búsqueda mejorado inicializado');
}

// ========== DROPDOWNS MEJORADOS ==========
function initializeDropdowns() {
    const dropdownItems = document.querySelectorAll('.nav-item');
    
    dropdownItems.forEach(item => {
        const link = item.querySelector('.nav-link[aria-haspopup="true"]');
        const dropdown = item.querySelector('.dropdown-menu');
        
        if (!link || !dropdown) return;
        
        let hoverTimeout;
        
        // Mouse enter
        item.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            link.setAttribute('aria-expanded', 'true');
            item.classList.add('dropdown-open');
        });
        
        // Mouse leave
        item.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(() => {
                link.setAttribute('aria-expanded', 'false');
                item.classList.remove('dropdown-open');
            }, 100);
        });
        
        // Click en móvil
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                item.classList.toggle('dropdown-open');
            }
        });
        
        // Keyboard navigation
        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (window.innerWidth <= 768) {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);
                    item.classList.toggle('dropdown-open');
                }
            }
        });
    });
    
    console.log('✅ Dropdowns mejorados inicializados');
}

// ========== MOBILE MENU MEJORADO ==========
function initializeMobileMenu() {
    const mobileToggle = document.getElementById('mobileToggle');
    const nav = document.getElementById('nav');
    
    if (!mobileToggle || !nav) {
        console.log('ℹ️ Elementos de menú móvil no encontrados');
        return;
    }
    
    mobileToggle.addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        
        this.classList.toggle('active');
        nav.classList.toggle('active');
        this.setAttribute('aria-expanded', !isExpanded);
        
        // Prevenir scroll del body cuando el menú está abierto
        if (nav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = getScrollbarWidth() + 'px';
        } else {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
        
        // Animar icono hamburger
        this.querySelectorAll('span').forEach((span, index) => {
            span.style.transitionDelay = `${index * 0.1}s`;
        });
    });
    
    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!nav.contains(e.target) && !mobileToggle.contains(e.target)) {
            if (nav.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                nav.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }
    });
    
    console.log('✅ Menú móvil mejorado inicializado');
}

// ========== CART FUNCTIONALITY MEJORADO ==========
function initializeCart() {
    try {
        const savedCart = sessionStorage.getItem('infomaster-cart');
        if (savedCart) {
            siteCart = JSON.parse(savedCart);
        }
    } catch (e) {
        console.warn('Error al cargar carrito del sessionStorage:', e);
        siteCart = [];
    }
    updateCartCount();
    console.log('✅ Carrito inicializado');
}

function addToCart(productId, productName, price) {
    try {
        const existingItem = siteCart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
            showNotification(`${productName} cantidad actualizada en el carrito`, 'success');
        } else {
            siteCart.push({
                id: productId,
                name: productName,
                price: parseFloat(price),
                quantity: 1,
                addedAt: new Date().toISOString()
            });
            showNotification(`${productName} agregado al carrito`, 'success');
        }
        
        sessionStorage.setItem('infomaster-cart', JSON.stringify(siteCart));
        updateCartCount();
        animateCartIcon();
        
        console.log('🛒 Producto agregado al carrito:', { productId, productName, price });
    } catch (e) {
        console.error('Error al agregar producto al carrito:', e);
        showNotification('Error al agregar producto al carrito', 'error');
    }
}

function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    const totalItems = siteCart.reduce((total, item) => total + item.quantity, 0);
    
    if (cartCount) {
        cartCount.textContent = totalItems;
        if (totalItems > 0) {
            cartCount.classList.remove('hidden');
            cartCount.style.display = 'flex';
        } else {
            cartCount.classList.add('hidden');
            cartCount.style.display = 'none';
        }
    }
}

function animateCartIcon() {
    const cartLink = document.querySelector('.cart');
    if (cartLink) {
        cartLink.style.animation = 'none';
        cartLink.offsetHeight; // Trigger reflow
        cartLink.style.animation = 'cartBounce 0.6s ease';
        
        setTimeout(() => {
            cartLink.style.animation = '';
        }, 600);
    }
}

function removeFromCart(productId) {
    const item = siteCart.find(item => item.id === productId);
    siteCart = siteCart.filter(item => item.id !== productId);
    sessionStorage.setItem('infomaster-cart', JSON.stringify(siteCart));
    updateCartCount();
    
    if (item) {
        showNotification(`${item.name} eliminado del carrito`, 'info');
    }
}

function clearCart() {
    siteCart = [];
    try {
        sessionStorage.removeItem('infomaster-cart');
    } catch (e) {
        console.warn('No se pudo limpiar el carrito de sessionStorage');
    }
    updateCartCount();
    showNotification('Carrito vaciado', 'info');
}

function getCartTotal() {
    return siteCart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// ========== CATEGORY FUNCTIONS MEJORADAS ==========
function handleCategoryClick(category, event) {
    if (event) {
        event.preventDefault();
    }
    
    const categories = {
        'gaming': 'Gaming',
        'streaming': 'Streaming',
        'software': 'Software',
        'cursos': 'Cursos',
        'musica': 'Música',
        'vpn': 'VPN & Seguridad',
        'cloud': 'Almacenamiento'
    };

    const categoryName = categories[category] || category;
    showNotification(`Explorando categoría: ${categoryName}`, 'info');
    
    // Agregar efecto de loading al enlace
    const link = event?.target.closest('.dropdown-item');
    if (link) {
        link.classList.add('loading');
    }
    
    // Redirigir con transición suave
    setTimeout(() => {
        window.location.href = `productos.php?categoria=${category}`;
    }, 800);
}

// ========== NOTIFICATION SYSTEM MEJORADO ==========
function showNotification(message, type = 'info', duration = 4000) {
    // Remover notificaciones existentes del mismo tipo
    const existingNotifications = document.querySelectorAll(`.notification.${type}`);
    existingNotifications.forEach(notification => {
        notification.remove();
    });

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
    };
    
    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">${icons[type]}</div>
            <div class="notification-message">${message}</div>
            <button class="notification-close" onclick="closeNotification(this)" aria-label="Cerrar notificación">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="notification-progress"></div>
    `;
    
    // Asegurar que los estilos existan
    ensureNotificationStyles();
    
    document.body.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.classList.add('show');
        
        // Animar barra de progreso
        const progress = notification.querySelector('.notification-progress');
        if (progress) {
            progress.style.animation = `notificationProgress ${duration}ms linear`;
        }
    }, 100);
    
    // Auto remove after duration
    notificationTimeout = setTimeout(() => {
        if (notification && notification.parentNode) {
            closeNotification(notification.querySelector('.notification-close'));
        }
    }, duration);
    
    return notification;
}

function ensureNotificationStyles() {
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 100px;
                right: 1.5rem;
                max-width: 380px;
                background: rgba(30, 27, 75, 0.95);
                backdrop-filter: blur(40px);
                -webkit-backdrop-filter: blur(40px);
                border-radius: 16px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
                transform: translateX(120%);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 2000;
                color: white;
                overflow: hidden;
                font-family: 'Inter', sans-serif;
            }
            
            .notification.show { 
                transform: translateX(0); 
            }
            
            .notification.success { 
                border-left: 4px solid #10b981; 
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(16, 185, 129, 0.2);
            }
            
            .notification.error { 
                border-left: 4px solid #ef4444; 
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(239, 68, 68, 0.2);
            }
            
            .notification.info { 
                border-left: 4px solid #6366f1; 
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(99, 102, 241, 0.2);
            }
            
            .notification.warning { 
                border-left: 4px solid #f59e0b; 
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(245, 158, 11, 0.2);
            }
            
            .notification-content {
                display: flex; 
                align-items: center; 
                gap: 1rem;
                padding: 1.25rem;
                position: relative;
                z-index: 1;
            }
            
            .notification-icon {
                font-size: 1.25rem;
                flex-shrink: 0;
            }
            
            .notification-message {
                flex: 1;
                font-weight: 500;
                font-size: 14px;
                line-height: 1.4;
            }
            
            .notification-close {
                background: rgba(255, 255, 255, 0.1);
                border: none;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: white;
                transition: all 0.3s ease;
                flex-shrink: 0;
            }
            
            .notification-close:hover {
                background: #ef4444;
                transform: rotate(90deg) scale(1.1);
            }
            
            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: linear-gradient(90deg, #10b981, #06b6d4);
                width: 100%;
                transform-origin: left;
            }
            
            @keyframes notificationProgress {
                from { transform: scaleX(1); }
                to { transform: scaleX(0); }
            }
            
            @keyframes cartBounce {
                0%, 100% { transform: scale(1); }
                25% { transform: scale(1.1) rotate(-5deg); }
                75% { transform: scale(1.05) rotate(3deg); }
            }
            
            @media (max-width: 480px) {
                .notification {
                    right: 1rem;
                    left: 1rem;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

function closeNotification(button) {
    if (!button) return;
    
    const notification = button.closest('.notification');
    if (notification) {
        notification.classList.remove('show');
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 400);
    }
    
    if (notificationTimeout) {
        clearTimeout(notificationTimeout);
    }
}

// ========== COUNTER ANIMATIONS ==========
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number, [data-count]');
    
    if (counters.length === 0) {
        console.log('ℹ️ No se encontraron contadores');
        return;
    }
    
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
    
    console.log('✅ Contadores animados inicializados');
}

function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-count') || element.textContent);
    if (isNaN(target)) return;
    
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        const value = Math.floor(current);
        element.textContent = value.toLocaleString();
        
        if (current >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        }
    }, 16);
}

// ========== SMOOTH SCROLLING MEJORADO ==========
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const headerHeight = document.getElementById('header')?.offsetHeight || 80;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Focus en el elemento de destino para accesibilidad
                target.setAttribute('tabindex', '-1');
                target.focus();
            }
        });
    });
    
    console.log('✅ Smooth scrolling mejorado inicializado');
}

// ========== KEYBOARD SHORTCUTS MEJORADOS ==========
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + K para búsqueda
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
                showNotification('Modo de búsqueda activado - Ctrl+K', 'info', 2000);
            }
        }
        
        // Esc para cerrar modales/menús
        if (e.key === 'Escape') {
            const activeNav = document.querySelector('.nav.active');
            const activeToggle = document.querySelector('.mobile-toggle.active');
            
            if (activeNav && activeToggle) {
                activeToggle.classList.remove('active');
                activeNav.classList.remove('active');
                activeToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            // Cerrar dropdowns abiertos
            const openDropdowns = document.querySelectorAll('.nav-item.dropdown-open');
            openDropdowns.forEach(item => {
                item.classList.remove('dropdown-open');
                const link = item.querySelector('[aria-expanded="true"]');
                if (link) link.setAttribute('aria-expanded', 'false');
            });

            // Cerrar notificaciones
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                closeNotification(notification.querySelector('.notification-close'));
            });
        }
        
        // Alt + C para ir al carrito
        if (e.altKey && e.key === 'c') {
            e.preventDefault();
            const cartLink = document.querySelector('.cart');
            if (cartLink) {
                cartLink.click();
            }
        }
    });
    
    console.log('✅ Atajos de teclado mejorados inicializados');
}

// ========== FORM VALIDATION MEJORADA ==========
function initializeFormValidation() {
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="buscar"]');
            if (searchInput) {
                const query = searchInput.value.trim();
                if (query.length < 2) {
                    e.preventDefault();
                    showNotification('Por favor ingresa al menos 2 caracteres para buscar', 'warning');
                    searchInput.focus();
                    return;
                }
                
                // Mostrar indicador de carga
                const searchBtn = this.querySelector('.search-btn');
                if (searchBtn) {
                    searchBtn.classList.add('loading');
                    searchBtn.disabled = true;
                }
                
                showNotification(`Buscando: "${query}"`, 'info', 2000);
            }
        });
    }
    
    console.log('✅ Validación de formularios mejorada inicializada');
}

// ========== ANIMATIONS MEJORADAS ==========
function initializeAnimations() {
    // Intersection Observer para animaciones
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationDelay = Math.random() * 0.3 + 's';
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements
        document.querySelectorAll('.product-card, .category-card, .feature-card').forEach(el => {
            observer.observe(el);
        });
    }
    
    console.log('✅ Sistema de animaciones inicializado');
}

// ========== RESPONSIVE HANDLING MEJORADO ==========
window.addEventListener('resize', debounce(function() {
    const nav = document.getElementById('nav');
    const mobileToggle = document.getElementById('mobileToggle');
    
    if (window.innerWidth > 768 && nav && mobileToggle) {
        nav.classList.remove('active');
        mobileToggle.classList.remove('active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Cerrar dropdowns en móvil
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.classList.remove('active', 'dropdown-open');
            const link = item.querySelector('[aria-expanded]');
            if (link) link.setAttribute('aria-expanded', 'false');
        });
    }
}, 250));

// ========== UTILITIES MEJORADAS ==========
function formatPrice(price) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'USD'
    }).format(parseFloat(price));
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function getScrollbarWidth() {
    const outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.width = '100px';
    outer.style.msOverflowStyle = 'scrollbar';
    document.body.appendChild(outer);
    
    const widthNoScroll = outer.offsetWidth;
    outer.style.overflow = 'scroll';
    
    const inner = document.createElement('div');
    inner.style.width = '100%';
    outer.appendChild(inner);
    
    const widthWithScroll = inner.offsetWidth;
    outer.parentNode.removeChild(outer);
    
    return widthNoScroll - widthWithScroll;
}

// ========== ERROR HANDLING MEJORADO ==========
window.addEventListener('error', function(e) {
    console.error('Error capturado:', e.error);
    showNotification('Ha ocurrido un error. Por favor recarga la página.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promise rechazada:', e.reason);
    showNotification('Error de conexión. Verifica tu internet.', 'warning');
});

// ========== CLEANUP ==========
window.addEventListener('beforeunload', function() {
    if (searchPlaceholderInterval) {
        clearInterval(searchPlaceholderInterval);
    }
    if (notificationTimeout) {
        clearTimeout(notificationTimeout);
    }
});

// ========== GLOBAL API MEJORADA ==========
window.InfoMaster = {
    cart: {
        add: addToCart,
        remove: removeFromCart,
        clear: clearCart,
        getTotal: getCartTotal,
        getItems: () => [...siteCart],
        updateCount: updateCartCount
    },
    notifications: {
        show: showNotification,
        close: closeNotification
    },
    categories: {
        navigate: handleCategoryClick
    },
    utils: {
        formatPrice: formatPrice,
        debounce: debounce,
        getScrollbarWidth: getScrollbarWidth
    },
    header: {
        isLoaded: () => isHeaderLoaded
    }
};

console.log('🎉 InfoMaster JavaScript ultra optimizado completamente inicializado');
console.log('🔧 API mejorada disponible en: window.InfoMaster');
console.log('📱 Responsive y accesibilidad mejorados');
console.log('⚡ Performance optimizado con throttling y debouncing');