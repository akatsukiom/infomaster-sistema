/**
 * InfoMaster - Funciones para el sistema de wallet
 */

document.addEventListener('DOMContentLoaded', function() {
    initWalletFunctions();
});

/**
 * Inicializa las funciones del sistema de wallet
 */
function initWalletFunctions() {
    initMetodosPago();
    initRecargaForm();
    initHistorialFiltros();
}

/**
 * Inicializa los métodos de pago en la recarga
 */
function initMetodosPago() {
    const metodosPago = document.querySelectorAll('.payment-method');
    const metodoPagoInput = document.querySelector('input[name="metodo"]');
    
    if (metodosPago.length > 0 && metodoPagoInput) {
        // Establecer el primero como seleccionado por defecto
        metodosPago[0].classList.add('selected');
        metodoPagoInput.value = metodosPago[0].dataset.metodo;
        
        // Mostrar instrucciones del primer método
        const primeraInstruccion = document.querySelector(`.instruccion-${metodosPago[0].dataset.metodo}`);
        if (primeraInstruccion) {
            primeraInstruccion.style.display = 'block';
        }
        
        metodosPago.forEach(metodo => {
            metodo.addEventListener('click', function() {
                // Quitar selección anterior
                document.querySelector('.payment-method.selected')?.classList.remove('selected');
                
                // Seleccionar el actual
                this.classList.add('selected');
                metodoPagoInput.value = this.dataset.metodo;
                
                // Actualizar instrucciones visibles
                document.querySelectorAll('.instruccion').forEach(ins => {
                    ins.style.display = 'none';
                });
                
                const instruccion = document.querySelector(`.instruccion-${this.dataset.metodo}`);
                if (instruccion) {
                    instruccion.style.display = 'block';
                }
            });
        });
    }
}

/**
 * Inicializa el formulario de recarga
 */
function initRecargaForm() {
    const recargaForm = document.querySelector('.recharge-form');
    
    if (recargaForm) {
        const montoInput = recargaForm.querySelector('#monto');
        const montoPresets = document.querySelectorAll('.monto-preset');
        
        // Manejar presets de montos
        if (montoPresets.length > 0 && montoInput) {
            montoPresets.forEach(preset => {
                preset.addEventListener('click', function() {
                    montoInput.value = this.dataset.valor;
                    
                    // Quitar selección anterior
                    document.querySelector('.monto-preset.selected')?.classList.remove('selected');
                    
                    // Seleccionar el actual
                    this.classList.add('selected');
                });
            });
        }
        
        // Validar formulario antes de enviar
        recargaForm.addEventListener('submit', function(e) {
            const monto = parseFloat(montoInput.value);
            const metodo = recargaForm.querySelector('input[name="metodo"]').value;
            
            if (isNaN(monto) || monto <= 0) {
                e.preventDefault();
                mostrarNotificacion('Por favor ingresa un monto válido', 'error');
                return false;
            }
            
            if (!metodo) {
                e.preventDefault();
                mostrarNotificacion('Por favor selecciona un método de pago', 'error');
                return false;
            }
            
            // Confirmación de recarga
            if (!confirm(`¿Estás seguro de que deseas recargar ${formatMoney(monto)}?`)) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
}

/**
 * Inicializa los filtros de historial de transacciones
 */
function initHistorialFiltros() {
    const historialFiltros = document.querySelector('.historial-filtros');
    
    if (historialFiltros) {
        const tipoSelect = historialFiltros.querySelector('#filtro-tipo');
        const fechaDesde = historialFiltros.querySelector('#filtro-desde');
        const fechaHasta = historialFiltros.querySelector('#filtro-hasta');
        
        // Aplicar filtros en cambio
        [tipoSelect, fechaDesde, fechaHasta].forEach(input => {
            if (input) {
                input.addEventListener('change', function() {
                    historialFiltros.submit();
                });
            }
        });
        
        // Botón para limpiar filtros
        const limpiarBtn = historialFiltros.querySelector('.btn-limpiar');
        
        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (tipoSelect) tipoSelect.value = '';
                if (fechaDesde) fechaDesde.value = '';
                if (fechaHasta) fechaHasta.value = '';
                
                historialFiltros.submit();
            });
        }
    }
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
 * Formatea un número como moneda
 */
function formatMoney(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}