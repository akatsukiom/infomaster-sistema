/**
 * JavaScript para la interfaz de edición de menús con soporte para productos y categorías
 */

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initMenuEditor();
});

/**
 * Inicializar el editor de menús
 */
function initMenuEditor() {
    // Referencias a elementos del DOM
    const searchProductInput = document.getElementById('producto_buscar');
    const searchProductBtn = document.getElementById('btn-buscar-producto');
    const productResults = document.getElementById('resultados_productos');
    const categorySelect = document.getElementById('categoria_select');
    const smartMenuTypeSelect = document.getElementById('tipo_menu_inteligente');
    
    // Inicializar tabs del modal
    initModalTabs();
    
    // Inicializar búsqueda de productos
    if (searchProductInput && searchProductBtn) {
        initProductSearch(searchProductInput, searchProductBtn, productResults);
    }
    
    // Inicializar selector de categorías
    if (categorySelect) {
        initCategorySelector(categorySelect);
    }
    
    // Inicializar selector de menús inteligentes
    if (smartMenuTypeSelect) {
        initSmartMenuSelector(smartMenuTypeSelect);
    }
    
    // Inicializar previsualización
    initPreviewTab();
}

/**
 * Inicializar los tabs del modal
 */
function initModalTabs() {
    const tabs = document.querySelectorAll('.modal-tab');
    const contents = document.querySelectorAll('.tab-content');
    const tipoInput = document.getElementById('tipo');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Desactivar todos los tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Activar el tab clickeado
            this.classList.add('active');
            
            // Ocultar todos los contenidos
            contents.forEach(c => c.classList.remove('active'));
            // Mostrar el contenido correspondiente
            const tabId = this.getAttribute('data-tab');
            document.getElementById('tab-' + tabId).classList.add('active');
            
            // Actualizar el tipo de elemento
            if (tipoInput) {
                tipoInput.value = tabId;
            }
            
            // Si es tab de vista previa, actualizar la vista previa
            if (tabId === 'preview') {
                updateMenuItemPreview();
            }
        });
    });
}

/**
 * Inicializar la búsqueda de productos
 */
function initProductSearch(searchInput, searchBtn, resultsContainer) {
    // Buscar al hacer clic en el botón
    searchBtn.addEventListener('click', function() {
        searchProducts(searchInput.value);
    });
    
    // Buscar al presionar Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchProducts(searchInput.value);
        }
    });
    
    // Buscar automáticamente después de 500ms al escribir
    let typingTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        
        if (searchInput.value.length >= 2) {
            typingTimer = setTimeout(function() {
                searchProducts(searchInput.value);
            }, 500);
        } else {
            resultsContainer.style.display = 'none';
        }
    });
    
    // Función para realizar la búsqueda
    function searchProducts(term) {
        if (term.length < 2) {
            showNotification('Ingresa al menos 2 caracteres para buscar', 'warning');
            return;
        }
        
        // Mostrar indicador de carga
        resultsContainer.innerHTML = '<div class="searching-indicator"><i class="fas fa-spinner fa-spin"></i> Buscando productos...</div>';
        resultsContainer.style.display = 'block';
        
        // Realizar petición AJAX
        fetch(`${BASE_URL}modulos/admin/menus/ajax_buscar_productos.php?accion=buscar_productos&q=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderProductResults(data.productos, resultsContainer);
                } else {
                    resultsContainer.innerHTML = `<div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ${data.mensaje || 'No se encontraron productos'}
                    </div>`;
                }
            })
            .catch(error => {
                console.error('Error al buscar productos:', error);
                resultsContainer.innerHTML = `<div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Error al buscar productos
                </div>`;
            });
    }
    
    // Función para renderizar resultados de productos
    function renderProductResults(products, container) {
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No se encontraron productos
            </div>`;
            return;
        }
        
        let html = '<div class="search-results-list">';
        
        products.forEach(product => {
            html += `
            <div class="product-result-item" data-id="${product.id}" data-nombre="${escapeHtml(product.nombre)}" 
                 data-url="${escapeHtml(product.url)}" data-precio="${product.precio}" 
                 data-imagen="${escapeHtml(product.imagen_url)}">
                <div class="product-result-image">
                    <img src="${escapeHtml(product.imagen_url)}" alt="${escapeHtml(product.nombre)}">
                </div>
                <div class="product-result-info">
                    <div class="product-result-name">${escapeHtml(product.nombre)}</div>
                    <div class="product-result-price">$${product.precio_formateado}</div>
                    <div class="product-result-sku">${product.sku ? 'SKU: ' + escapeHtml(product.sku) : ''}</div>
                </div>
                <div class="product-result-actions">
                    <button type="button" class="btn btn-sm btn-primary btn-select-product">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>`;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        // Agregar eventos para seleccionar productos
        container.querySelectorAll('.btn-select-product').forEach(btn => {
            btn.addEventListener('click', function() {
                const productItem = this.closest('.product-result-item');
                selectProduct(productItem);
            });
        });
        
        // Permitir seleccionar producto haciendo clic en toda la fila
        container.querySelectorAll('.product-result-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Evitar si se hizo clic en el botón
                if (e.target.closest('.btn-select-product')) {
                    return;
                }
                selectProduct(this);
            });
        });
    }
    
    // Función para seleccionar un producto
    function selectProduct(productItem) {
        const id = productItem.getAttribute('data-id');
        const nombre = productItem.getAttribute('data-nombre');
        const url = productItem.getAttribute('data-url');
        
        // Llenar campos del formulario
        document.getElementById('titulo').value = nombre;
        document.getElementById('url').value = url;
        document.getElementById('target_id').value = id;
        
        // Ocultar resultados
        resultsContainer.style.display = 'none';
        
        // Mostrar notificación
        showNotification(`Producto "${nombre}" seleccionado`, 'success');
        
        // Actualizar vista previa
        updateMenuItemPreview();
    }
}

/**
 * Inicializar selector de categorías
 */
function initCategorySelector(select) {
    // Cargar categorías al iniciar
    loadCategories();
    
    // Evento de cambio en el selector
    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            // Obtener datos de la categoría seleccionada
            const categoriaId = option.value;
            const categoriaNombre = option.text;
            const categoriaUrl = option.getAttribute('data-url');
            
            // Actualizar campos del formulario
            document.getElementById('titulo').value = categoriaNombre;
            document.getElementById('url').value = categoriaUrl;
            document.getElementById('target_id').value = categoriaId;
            
            // Mostrar contador de productos
            const totalProductos = option.getAttribute('data-total');
            if (totalProductos) {
                showNotification(`Categoría con ${totalProductos} productos`, 'info');
            }
            
            // Actualizar vista previa
            updateMenuItemPreview();
        }
    });
    
    // Función para cargar categorías
    function loadCategories() {
        fetch(`${BASE_URL}modulos/admin/menus/ajax_buscar_productos.php?accion=obtener_categorias&conteo=true`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderCategoriesOptions(data.categorias);
                } else {
                    console.error('Error al cargar categorías:', data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error al cargar categorías:', error);
            });
    }
    
    // Función para renderizar opciones de categorías
    function renderCategoriesOptions(categories) {
        // Mantener la opción por defecto
        select.innerHTML = '<option value="">-- Seleccionar categoría --</option>';
        
        if (!categories || categories.length === 0) {
            return;
        }
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.setAttribute('data-url', category.url);
            option.setAttribute('data-imagen', category.imagen_url);
            option.setAttribute('data-total', category.total_productos || 0);
            option.textContent = `${category.nombre} (${category.total_productos || 0})`;
            select.appendChild(option);
        });
    }
}

/**
 * Inicializar selector de menús inteligentes
 */
function initSmartMenuSelector(select) {
    // Cargar tipos de menús inteligentes
    loadSmartMenuTypes();
    
    // Eventos para cambios en los parámetros de menú inteligente
    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            // Obtener datos del tipo seleccionado
            const tipo = option.value;
            const nombre = option.getAttribute('data-nombre');
            const icono = option.getAttribute('data-icono');
            
            // Actualizar campos del formulario
            if (!document.getElementById('titulo').value) {
                document.getElementById('titulo').value = nombre;
            }
            
            // Si hay campo para icono, actualizarlo
            const iconoInput = document.getElementById('icono');
            if (iconoInput && icono) {
                iconoInput.value = icono;
                
                // Actualizar previsualización del icono
                const iconoPreview = document.getElementById('icono-preview');
                if (iconoPreview) {
                    iconoPreview.className = icono;
                }
                
                // Seleccionar el icono en el selector de iconos
                document.querySelectorAll('.icon-option').forEach(opt => {
                    if (opt.getAttribute('data-icon') === icono) {
                        opt.classList.add('selected');
                    } else {
                        opt.classList.remove('selected');
                    }
                });
            }
            
            // Generar URL genérica
            document.getElementById('url').value = `${BASE_URL}productos/${tipo}`;
            
            // Actualizar vista previa
            updateMenuItemPreview();
        }
    });
    
    // Evento para cambio en límite de productos
    const limiteInput = document.getElementById('limite_productos');
    if (limiteInput) {
        limiteInput.addEventListener('input', function() {
            updateMenuItemPreview();
        });
    }
    
    // Función para cargar tipos de menús inteligentes
    function loadSmartMenuTypes() {
        fetch(`${BASE_URL}modulos/admin/menus/ajax_buscar_productos.php?accion=tipos_menu_inteligente`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderSmartMenuOptions(data.tipos);
                } else {
                    console.error('Error al cargar tipos de menús inteligentes:', data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error al cargar tipos de menús inteligentes:', error);
            });
    }
    
    // Función para renderizar opciones de menús inteligentes
    function renderSmartMenuOptions(types) {
        // Mantener la opción por defecto
        select.innerHTML = '<option value="">-- Seleccionar tipo de menú --</option>';
        
        if (!types) {
            return;
        }
        
        for (const [key, type] of Object.entries(types)) {
            const option = document.createElement('option');
            option.value = key;
            option.setAttribute('data-nombre', type.nombre);
            option.setAttribute('data-icono', type.icono);
            option.textContent = type.nombre;
            select.appendChild(option);
        }
    }
}

/**
 * Inicializar tab de vista previa
 */
function initPreviewTab() {
    // Actualizar vista previa al cambiar
    const camposFormulario = ['titulo', 'url', 'icono', 'clase', 'target'];
    
    camposFormulario.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', updateMenuItemPreview);
            elemento.addEventListener('change', updateMenuItemPreview);
        }
    });
}

/**
 * Actualizar vista previa del elemento de menú
 */
function updateMenuItemPreview() {
    const previewContainer = document.getElementById('menu-item-preview');
    if (!previewContainer) return;
    
    const titulo = document.getElementById('titulo').value || 'Título del elemento';
    const url = document.getElementById('url').value || '#';
    const icono = document.getElementById('icono')?.value || '';
    const clase = document.getElementById('clase')?.value || '';
    const target = document.getElementById('target')?.value || '_self';
    const tipo = document.getElementById('tipo')?.value || 'custom';
    
    let html = '<div class="menu-preview-container">';
    
    // Estilos de vista previa
    html += `
    <style>
        .menu-preview-container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .menu-preview-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            transition: all .2s ease;
        }
        .menu-preview-item:hover {
            background-color: #f0f7ff;
            box-shadow: 0 4px 8px rgba(0,0,0,.08);
        }
        .menu-preview-icon {
            margin-right: 10px;
            color: #5c6ac4;
        }
        .menu-preview-title {
            flex: 1;
            font-weight: 500;
        }
        .menu-preview-badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #f0f0f0;
            border-radius: 50px;
            font-size: 12px;
            margin-left: 10px;
            color: #666;
        }
        .menu-preview-external-icon {
            margin-left: 5px;
            font-size: 12px;
            color: #999;
        }
    </style>`;
    
    // Diferentes estilos según el tipo
    let tipoLabel = '';
    let extraContent = '';
    
    switch (tipo) {
        case 'product':
            tipoLabel = 'Producto';
            
            // Obtener datos adicionales del producto si está seleccionado
            const productId = document.getElementById('target_id').value;
            if (productId) {
                // Agregar miniatura del producto si está disponible
                const productItem = document.querySelector(`.product-result-item[data-id="${productId}"]`);
                
                if (productItem) {
                    const imagenUrl = productItem.getAttribute('data-imagen');
                    const precio = productItem.getAttribute('data-precio');
                    
                    if (imagenUrl) {
                        extraContent = `
                        <div class="menu-preview-product-details">
                            <div class="menu-preview-product-image">
                                <img src="${escapeHtml(imagenUrl)}" alt="${escapeHtml(titulo)}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-top: 10px;">
                            </div>
                            <div class="menu-preview-product-price" style="font-weight: bold; color: #5c6ac4; margin-top: 5px;">
                                $${parseFloat(precio).toFixed(2)}
                            </div>
                        </div>`;
                    }
                }
            }
            break;
            
        case 'category':
            tipoLabel = 'Categoría';
            
            // Obtener conteo de productos si está disponible
            const categoriaOption = document.querySelector(`#categoria_select option[value="${document.getElementById('target_id').value}"]`);
            if (categoriaOption) {
                const totalProductos = categoriaOption.getAttribute('data-total');
                if (totalProductos) {
                    extraContent = `
                    <div class="menu-preview-category-details">
                        <div class="menu-preview-category-count" style="font-size: 12px; color: #666; margin-top: 5px;">
                            ${totalProductos} producto${totalProductos !== '1' ? 's' : ''}
                        </div>
                    </div>`;
                }
            }
            break;
            
        case 'smart':
            tipoLabel = 'Menú Inteligente';
            
            // Mostrar tipo de menú inteligente
            const tipoSelect = document.getElementById('tipo_menu_inteligente');
            const limiteInput = document.getElementById('limite_productos');
            
            let tipoInteligente = 'No seleccionado';
            let limite = 10;
            
            if (tipoSelect) {
                const option = tipoSelect.options[tipoSelect.selectedIndex];
                if (option && option.value) {
                    tipoInteligente = option.textContent;
                }
            }
            
            if (limiteInput) {
                limite = parseInt(limiteInput.value) || 10;
            }
            
            extraContent = `
            <div class="menu-preview-smart-details">
                <div class="menu-preview-smart-type" style="font-size: 12px; color: #666; margin-top: 5px;">
                    Tipo: ${tipoInteligente}
                </div>
                <div class="menu-preview-smart-limit" style="font-size: 12px; color: #666; margin-top: 2px;">
                    Límite: ${limite} productos
                </div>
            </div>`;
            break;
            
        default:
            tipoLabel = 'Enlace';
    }
    
    // Construir vista previa
    html += `<div class="menu-preview-item ${escapeHtml(clase)}">`;
    
    // Icono
    if (icono) {
        html += `<div class="menu-preview-icon"><i class="${escapeHtml(icono)}"></i></div>`;
    }
    
    // Título y tipo
    html += `
    <div class="menu-preview-title">${escapeHtml(titulo)}</div>
    <div class="menu-preview-badge">${tipoLabel}</div>`;
    
    // Indicador de enlace externo
    if (target === '_blank') {
        html += `<div class="menu-preview-external-icon"><i class="fas fa-external-link-alt"></i></div>`;
    }
    
    html += `</div>`;
    
    // Agregar contenido extra
    if (extraContent) {
        html += extraContent;
    }
    
    html += `
    <div class="menu-preview-url" style="margin-top: 10px; font-size: 13px; color: #666;">
        <strong>URL:</strong> ${escapeHtml(url)}
    </div>`;
    
    html += '</div>';
    
    // Actualizar contenedor
    previewContainer.innerHTML = html;
}

/**
 * Función para escapar HTML y prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'success') {
    // Si existe toast-notification, usarlo
    const toast = document.getElementById('toast-notification');
    if (toast) {
        const icon = toast.querySelector('i');
        const msg = toast.querySelector('.toast-message');
        
        // Actualizar icono
        if (icon) {
            icon.className = type === 'success' ? 'fas fa-check-circle' : 
                            type === 'warning' ? 'fas fa-exclamation-triangle' : 
                            type === 'error' ? 'fas fa-times-circle' : 
                            'fas fa-info-circle';
        }
        
        // Actualizar mensaje
        if (msg) {
            msg.textContent = message;
        }
        
        // Actualizar color según tipo
        toast.style.borderLeftColor = type === 'success' ? 'var(--success-color)' : 
                                    type === 'warning' ? 'var(--warning-color)' : 
                                    type === 'error' ? 'var(--danger-color)' : 
                                    'var(--info-color)';
        
        // Mostrar toast
        toast.classList.add('show');
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
        
        return;
    }
    
    // Si no existe toast-notification, crear uno temporal
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '350px';
    
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 
                          type === 'warning' ? 'exclamation-triangle' : 
                          type === 'error' ? 'times-circle' : 
                          'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}