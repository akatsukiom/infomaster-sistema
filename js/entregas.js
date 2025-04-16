/**
 * InfoMaster - Funciones para sistema de entregas
 */

document.addEventListener('DOMContentLoaded', function() {
    initEntregasSystem();
    initCodigoCopy();
    initFiltrosEntregas();
});

/**
 * Inicializa el sistema de entregas
 */
function initEntregasSystem() {
    // Expander/colapsar detalles de entregas
    const toggleButtons = document.querySelectorAll('.entrega-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const entregaCard = this.closest('.entrega-card');
            const detalles = entregaCard.querySelector('.entrega-detalles');
            
            if (detalles) {
                // Alternar visibilidad
                if (detalles.style.maxHeight) {
                    detalles.style.maxHeight = null;
                    this.textContent = 'Ver detalles';
                    entregaCard.classList.remove('expanded');
                } else {
                    detalles.style.maxHeight = detalles.scrollHeight + 'px';
                    this.textContent = 'Ocultar detalles';
                    entregaCard.classList.add('expanded');
                }
            }
        });
    });
    
    // Manejar acciones en cada entrega
    document.querySelectorAll('.entrega-accion').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const action = this.dataset.action;
            const entregaId = this.dataset.id;
            
            switch(action) {
                case 'descargar':
                    descargarEntrega(entregaId);
                    break;
                case 'renovar':
                    renovarEntrega(entregaId);
                    break;
                case 'reportar':
                    reportarProblema(entregaId);
                    break;
            }
        });
    });
}

/**
 * Inicializa funciones para copiar códigos de acceso
 */
function initCodigoCopy() {
    const copyButtons = document.querySelectorAll('.btn-copiar');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const codigo = this.dataset.codigo;
            
            // Crear elemento temporal
            const temp = document.createElement('textarea');
            temp.value = codigo;
            document.body.appendChild(temp);
            
            // Seleccionar y copiar
            temp.select();
            document.execCommand('copy');
            
            // Eliminar elemento temporal
            document.body.removeChild(temp);
            
            // Cambiar texto del botón temporalmente
            const originalText = this.textContent;
            this.textContent = '¡Copiado!';
            this.classList.add('copied');
            
            // Restaurar texto original después de 2 segundos
            setTimeout(() => {
                this.textContent = originalText;
                this.classList.remove('copied');
            }, 2000);
            
            // Mostrar notificación
            mostrarNotificacion('Código copiado al portapapeles', 'success');
        });
    });
}

/**
 * Inicializa filtros para lista de entregas
 */
function initFiltrosEntregas() {
    const filtrosForm = document.querySelector('.entregas-filtros');
    
    if (filtrosForm) {
        // Auto-submit al cambiar filtros
        const inputs = filtrosForm.querySelectorAll('select, input[type="date"]');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                filtrosForm.submit();
            });
        });
        
        // Botón para limpiar filtros
        const resetButton = filtrosForm.querySelector('.filtros-reset');
        
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Limpiar todos los campos
                inputs.forEach(input => {
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });
                
                // Enviar formulario
                filtrosForm.submit();
            });
        }
    }
}

/**
 * Descarga una entrega
 */
function descargarEntrega(entregaId) {
    // Simulación de descarga (en un caso real esto redireccionaría a un archivo)
    mostrarNotificacion('Iniciando descarga...', 'info');
    
    // Redirigir a la URL de descarga
    window.location.href = `descargar.php?id=${entregaId}`;
}

/**
 * Renueva una entrega
 */
function renovarEntrega(entregaId) {
    if (confirm('¿Estás seguro de que deseas renovar esta entrega? Se aplicará el cargo correspondiente a tu wallet.')) {
        // Enviar solicitud de renovación
        const data = `id=${entregaId}&action=renovar`;
        
        ajaxRequest('modulos/entregas/procesar_accion.php', 'POST', data, function(error, response) {
            if (error) {
                mostrarNotificacion('Error al procesar la renovación', 'error');
                return;
            }
            
            if (response.success) {
                mostrarNotificacion(response.message, 'success');
                
                // Recargar la página después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                mostrarNotificacion(response.message, 'error');
            }
        });
    }
}

/**
 * Reporta un problema con una entrega
 */
function reportarProblema(entregaId) {
    // Mostrar modal de reporte
    const modal = document.querySelector('.reporte-modal');
    const entregaIdInput = document.querySelector('#reporte_entrega_id');
    
    if (modal && entregaIdInput) {
        entregaIdInput.value = entregaId;
        modal.style.display = 'flex';
        
        // Enfocar el textarea
        setTimeout(() => {
            document.querySelector('#reporte_descripcion').focus();
        }, 100);
    } else {
        // Fallback si no hay modal
        const problema = prompt('Por favor describe el problema que estás experimentando:');
        
        if (problema) {
            enviarReporte(entregaId, problema);
        }
    }
}

/**
 * Envía un reporte de problema
 */
function enviarReporte(entregaId, descripcion) {
    const data = `id=${entregaId}&descripcion=${encodeURIComponent(descripcion)}&action=reportar`;
    
    ajaxRequest('modulos/entregas/procesar_accion.php', 'POST', data, function(error, response) {
        if (error) {
            mostrarNotificacion('Error al enviar el reporte', 'error');
            return;
        }
        
        if (response.success) {
            mostrarNotificacion(response.message, 'success');
            
            // Cerrar modal si existe
            const modal = document.querySelector('.reporte-modal');
            if (modal) {
                modal.style.display = 'none';
                
                // Limpiar campos
                document.querySelector('#reporte_descripcion').value = '';
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
 * Función para hacer peticiones AJAX
 */
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