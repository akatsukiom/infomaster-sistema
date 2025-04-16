/**
 * InfoMaster - Funciones para el carrito de compras
 */

document.addEventListener('DOMContentLoaded', function() {
    initCarritoActions();
});

/**
 * Inicializa las acciones del carrito
 */
function initCarritoActions() {
    // Botones de agregar al carrito
    const addButtons = document.querySelectorAll('.btn-agregar-carrito');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.id;
            const redirigir = this.dataset.redirigir || window.location.pathname;
            let cantidad = 1;
            
            // Si hay un selector de cantidad
            const cantidadInput = document.querySelector('#cantidad');
            if (cantidadInput) {
                cantidad = parseInt(cantidadInput.value);
            }
            
            agregarAlCarrito(productId, cantidad, redirigir);
        });
    });
    
    // Actualizar cantidades en el carrito
    const cantidadInputs = document.querySelectorAll('.cantidad-input');
    
    cantidadInputs.forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            
            if (form && this.value >= 1) {
                form.submit();
            }
        });
    });
    
    // Confirmación para vaciar carrito
    const vaciarBtn = document.querySelector('.btn-vaciar-carrito');
    
    if (vaciarBtn) {
        vaciarBtn.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
                e.preventDefault();
            }
        });
    }
    
    // Confirmación para eliminar producto
    const eliminarBtns = document.querySelectorAll('.btn-eliminar');
    
    eliminarBtns.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este producto del carrito?')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Agrega un producto al carrito mediante AJAX
 */
function agregarAlCarrito(productId, cantidad, redirigir) {
    const data = `id=${productId}&cantidad=${cantidad}`;
    
    ajaxRequest('modulos/carrito/agregar_ajax.php', 'POST', data, function(error, response) {
        if (error) {
            mostrarNotificacion('Error al agregar producto al carrito', 'error');
            return;
        }
        
        if (response.success) {
            // Actualizar contador de carrito
            const cartCount = document.querySelector('.cart-count');
            
            if (cartCount) {
                cartCount.textContent = response.total_items;
                
                if (response.total_items > 0 && cartCount.style.display === 'none') {
                    cartCount.style.display = 'flex';
                }
            }
            
            mostrarNotificacion(response.message, 'success');
            
            // Si está configurado para redirigir, hacerlo después de un breve retraso
            if (response.redirigir === 'true') {
                setTimeout(() => {
                    window.location.href = redirigir;
                }, 1000);
            }
        } else {
            mostrarNotificacion(response.message, 'error');
        }
    });
}

/**
 * Muestra una notificación temporal
 */
function mostrarNotificacion(mensaje, tipo) {
    // Eliminar notificaciones existentes
    const existentes = document.querySelectorAll('.notificacion');
    existentes.forEach(notif => {
        notif.remove();
    });
    
    // Crear nueva notificación
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion notificacion-${tipo}`;
    notificacion.textContent = mensaje;
    
    document.body.appendChild(notificacion);
    
    // Mostrar
    setTimeout(() => {
        notificacion.classList.add('mostrar');
    }, 10);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        notificacion.classList.remove('mostrar');
        
        // Eliminar del DOM después de la animación
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }, 3000);
}

/**
 * Actualiza el carrito en tiempo real
 */
function actualizarTotalCarrito() {
    const subtotales = document.querySelectorAll('.subtotal');
    let total = 0;
    
    subtotales.forEach(subtotal => {
        total += parseFloat(subtotal.dataset.valor);
    });
    
    const totalElement = document.querySelector('.total-valor');
    
    if (totalElement) {
        totalElement.textContent = formatMoney(total);
    }
}

/**
 * Formatea un número como moneda
 */
function formatMoney(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}