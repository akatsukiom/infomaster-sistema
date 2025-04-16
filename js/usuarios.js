/**
 * InfoMaster - Funciones para autenticación y perfil de usuario
 */

document.addEventListener('DOMContentLoaded', function() {
    initAuthForms();
    initPerfilActions();
});

/**
 * Inicializa los formularios de autenticación
 */
function initAuthForms() {
    const loginForm = document.querySelector('.login-form');
    const registroForm = document.querySelector('.registro-form');
    const recuperarForm = document.querySelector('.recuperar-form');
    
    // Validar formulario de login
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;
            
            if (!email || !isValidEmail(email)) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="email"]'), 'Por favor ingresa un email válido');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="password"]'), 'Por favor ingresa tu contraseña');
                return false;
            }
            
            return true;
        });
    }
    
    // Validar formulario de registro
    if (registroForm) {
        registroForm.addEventListener('submit', function(e) {
            const nombre = this.querySelector('input[name="nombre"]').value.trim();
            const email = this.querySelector('input[name="email"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            // Limpiar errores previos
            limpiarErroresForm(this);
            
            if (!nombre) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="nombre"]'), 'Por favor ingresa tu nombre completo');
                return false;
            }
            
            if (!email || !isValidEmail(email)) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="email"]'), 'Por favor ingresa un email válido');
                return false;
            }
            
            if (!password || password.length < 6) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="password"]'), 'La contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="confirm_password"]'), 'Las contraseñas no coinciden');
                return false;
            }
            
            return true;
        });
    }
    
    // Validar formulario de recuperación
    if (recuperarForm) {
        recuperarForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value.trim();
            
            if (!email || !isValidEmail(email)) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="email"]'), 'Por favor ingresa un email válido');
                return false;
            }
            
            return true;
        });
    }
}

/**
 * Inicializa las acciones en la página de perfil
 */
function initPerfilActions() {
    const perfilTabs = document.querySelectorAll('.perfil-tab');
    const perfilSections = document.querySelectorAll('.perfil-section');
    const cambiarPasswordForm = document.querySelector('.cambiar-password-form');
    
    // Sistema de pestañas en perfil
    if (perfilTabs.length > 0) {
        perfilTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Quitar clase activa de todas las pestañas
                perfilTabs.forEach(t => t.classList.remove('active'));
                
                // Ocultar todas las secciones
                perfilSections.forEach(s => s.style.display = 'none');
                
                // Mostrar solo la sección correspondiente
                const targetSection = document.querySelector(this.getAttribute('href'));
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
                
                // Marcar esta pestaña como activa
                this.classList.add('active');
                
                // Actualizar URL sin recargar la página
                window.history.pushState({}, '', this.getAttribute('href'));
            });
        });
        
        // Activar pestaña inicial basada en hash de URL
        const hash = window.location.hash || '#info';
        const initialTab = document.querySelector(`.perfil-tab[href="${hash}"]`);
        
        if (initialTab) {
            initialTab.click();
        } else {
            // Si no hay hash o es inválido, activar la primera pestaña
            perfilTabs[0].click();
        }
    }
    
    // Validar formulario para cambiar contraseña
    if (cambiarPasswordForm) {
        cambiarPasswordForm.addEventListener('submit', function(e) {
            const currentPassword = this.querySelector('input[name="current_password"]').value;
            const newPassword = this.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            // Limpiar errores previos
            limpiarErroresForm(this);
            
            if (!currentPassword) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="current_password"]'), 'Por favor ingresa tu contraseña actual');
                return false;
            }
            
            if (!newPassword || newPassword.length < 6) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="new_password"]'), 'La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                mostrarErrorForm(this.querySelector('input[name="confirm_password"]'), 'Las contraseñas no coinciden');
                return false;
            }
            
            return true;
        });
    }
}

/**
 * Muestra un mensaje de error en un campo de formulario
 */
function mostrarErrorForm(input, mensaje) {
    // Limpiar error previo
    const prevError = input.parentNode.querySelector('.error-message');
    if (prevError) {
        prevError.remove();
    }
    
    // Añadir clase de error al campo
    input.classList.add('error');
    
    // Crear mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = mensaje;
    
    // Insertar después del input
    input.parentNode.insertBefore(errorDiv, input.nextSibling);
    
    // Enfocar el campo
    input.focus();
}

/**
 * Limpia todos los mensajes de error en un formulario
 */
function limpiarErroresForm(form) {
    // Quitar mensajes de error
    const errorMsgs = form.querySelectorAll('.error-message');
    errorMsgs.forEach(msg => msg.remove());
    
    // Quitar clase de error de los campos
    const errorInputs = form.querySelectorAll('.error');
    errorInputs.forEach(input => input.classList.remove('error'));
}

/**
 * Valida un email
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email.toLowerCase());
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