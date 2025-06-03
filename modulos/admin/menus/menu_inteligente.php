<?php
/**
 * Gestión de menús inteligentes que se actualizan automáticamente
 */

define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';
require_once __DIR__ . '/modelo_productos.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    registrarLog('Acceso no autorizado a menús inteligentes', 'seguridad');
    redireccionar('login');
    exit;
}

// Inicializar variables
$errores = [];
$exito = '';

// Verificar el ID del menú
$menu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validar que se ha especificado un ID de menú
if ($menu_id <= 0) {
    mostrarMensaje('Debe especificar un menú válido', 'error');
    redireccionar('admin/menus/panel.php');
    exit;
}

// Instanciar modelos
$menuModel = new Menu($conexion);
$menuProductos = new MenuProductos($conexion);

// Verificar que el menú existe
$menu_actual = $menuModel->obtenerPorId($menu_id);
if (!$menu_actual) {
    mostrarMensaje('El menú solicitado no existe', 'error');
    redireccionar('admin/menus/panel.php');
    exit;
}

// Determinar acción a realizar
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'nuevo';
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

// Inicializar datos del elemento
$item_actual = [
    'id' => 0,
    'titulo' => '',
    'url' => '',
    'tipo' => 'smart',
    'parent_id' => 0,
    'orden' => 0,
    'clase' => '',
    'target' => '_self',
    'config' => json_encode(['tipo' => 'nuevos', 'limite' => 10]),
    'icono' => '',
];

// Si es edición, cargar datos del elemento
if ($accion === 'editar' && $item_id > 0) {
    $item = $menuModel->obtenerItemPorId($item_id);
    
    if (!$item) {
        mostrarMensaje('El elemento solicitado no existe', 'error');
        redireccionar('admin/menus/panel.php?accion=editar&id=' . $menu_id);
        exit;
    }
    
    // Verificar que el elemento pertenece al menú actual
    if ($item['menu_id'] != $menu_id) {
        mostrarMensaje('El elemento no pertenece al menú actual', 'error');
        redireccionar('admin/menus/panel.php?accion=editar&id=' . $menu_id);
        exit;
    }
    
    // Verificar que es un menú inteligente
    if ($item['tipo'] !== 'smart') {
        mostrarMensaje('El elemento no es un menú inteligente', 'error');
        redireccionar('admin/menus/panel.php?accion=editar&id=' . $menu_id);
        exit;
    }
    
    // Cargar datos del elemento
    $item_actual = $item;
    
    // Decodificar configuración
    if (!empty($item['config'])) {
        $item_actual['config'] = $item['config'];
    }
}

// Obtener tipos de menús inteligentes
$tipos_menu = $menuProductos->obtenerTiposMenuInteligente();

// Obtener elementos del menú para selector de padre
$menu_items = $menuModel->obtenerItems($menu_id);

// Procesar formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $clase = isset($_POST['clase']) ? trim($_POST['clase']) : '';
    $target = isset($_POST['target']) ? trim($_POST['target']) : '_self';
    $icono = isset($_POST['icono']) ? trim($_POST['icono']) : '';
    $tipo_smart = isset($_POST['tipo_smart']) ? trim($_POST['tipo_smart']) : 'nuevos';
    $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 10;
    
    // Validar datos
    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio';
    }
    
    // Validar tipo de menú inteligente
    if (!array_key_exists($tipo_smart, $tipos_menu)) {
        $errores[] = 'El tipo de menú inteligente no es válido';
    }
    
    // Validar límite
    if ($limite <= 0) {
        $limite = 10; // Establecer un valor por defecto
    } elseif ($limite > 50) {
        $limite = 50; // Limitar a un máximo de 50 productos
    }
    
    // Si no hay errores, guardar
    if (empty($errores)) {
        // Generar URL para este tipo de menú
        $url = URL_SITIO . 'productos/' . $tipo_smart;
        
        // Configuración del menú inteligente
        $config = json_encode([
            'tipo' => $tipo_smart,
            'limite' => $limite
        ]);
        
        if ($accion === 'editar' && $item_id > 0) {
            // Actualizar elemento existente
            $resultado = $menuModel->actualizarItem(
                $item_id,
                $titulo,
                $url,
                $parent_id,
                isset($_POST['orden']) ? (int)$_POST['orden'] : 0,
                $clase,
                $target,
                'smart',
                0, // target_id no aplica para menús inteligentes
                $icono,
                $config
            );
            
            if ($resultado) {
                $exito = 'Menú inteligente actualizado correctamente';
            } else {
                $errores[] = 'Error al actualizar el menú inteligente';
            }
        } else {
            // Crear nuevo elemento
            $resultado = $menuModel->agregarItem(
                $menu_id,
                $titulo,
                $url,
                $parent_id,
                isset($_POST['orden']) ? (int)$_POST['orden'] : 0,
                $clase,
                $target,
                'smart',
                0, // target_id no aplica para menús inteligentes
                $icono,
                $config
            );
            
            if ($resultado) {
                // Redirigir a la edición del menú
                mostrarMensaje('Menú inteligente creado correctamente', 'success');
                redireccionar('admin/menus/panel.php?accion=editar&id=' . $menu_id);
                exit;
            } else {
                $errores[] = 'Error al crear el menú inteligente';
            }
        }
    }
}

// Preparar datos para la vista
$config_actual = json_decode($item_actual['config'], true) ?: ['tipo' => 'nuevos', 'limite' => 10];
$tipo_actual = $config_actual['tipo'] ?? 'nuevos';
$limite_actual = $config_actual['limite'] ?? 10;

// Título de la página
$titulo_pagina = ($accion === 'editar') ? 'Editar Menú Inteligente' : 'Nuevo Menú Inteligente';
include __DIR__ . '/../../../includes/header.php';
?>

<!-- Enlazar estilos específicos -->
<link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/admin/menu-editor.css">

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0"><?= $titulo_pagina ?></h1>
                </div>
                <div class="card-body">
                    <!-- Enlaces de navegación -->
                    <div class="mb-4">
                        <a href="<?= URL_SITIO ?>admin/menus/panel.php" class="btn btn-sm btn-secondary me-2">
                            <i class="fas fa-list"></i> Todos los menús
                        </a>
                        <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=editar&id=<?= $menu_id ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver al menú
                        </a>
                    </div>
                    
                    <!-- Mensajes de error -->
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Mensaje de éxito -->
                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($exito) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulario de menú inteligente -->
                    <form action="<?= URL_SITIO ?>admin/menus/menu_inteligente.php?accion=<?= $accion ?>&id=<?= $menu_id ?><?= $item_id ? '&item_id='.$item_id : '' ?>" method="post">
                        <!-- Información del menú al que pertenece -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <strong>Menú:</strong> <?= htmlspecialchars($menu_actual['nombre']) ?>
                                    <p class="mb-0 small"><?= htmlspecialchars($menu_actual['descripcion']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <!-- Columna izquierda: Datos básicos -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información básica</h5>
                                
                                <!-- Título del menú -->
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($item_actual['titulo']) ?>" required>
                                    <div class="form-text">Nombre visible en el menú</div>
                                </div>
                                
                                <!-- Tipo de menú inteligente -->
                                <div class="mb-3">
                                    <label for="tipo_smart" class="form-label">Tipo de menú <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo_smart" name="tipo_smart" required>
                                        <?php foreach ($tipos_menu as $key => $tipo): ?>
                                            <option value="<?= $key ?>" <?= $tipo_actual === $key ? 'selected' : '' ?> data-icon="<?= htmlspecialchars($tipo['icono']) ?>">
                                                <?= htmlspecialchars($tipo['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text" id="descripcion_tipo"></div>
                                </div>
                                
                                <!-- Límite de productos -->
                                <div class="mb-3">
                                    <label for="limite" class="form-label">Límite de productos</label>
                                    <input type="number" class="form-control" id="limite" name="limite" value="<?= (int)$limite_actual ?>" min="1" max="50">
                                    <div class="form-text">Máximo número de productos a mostrar (1-50)</div>
                                </div>
                                
                                <!-- Elemento padre -->
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Elemento padre</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="0">Ninguno (Nivel principal)</option>
                                        <?php
                                        // Mostrar solo elementos de nivel 1 y 2 como posibles padres
                                        $nivel1 = [];
                                        foreach ($menu_items as $item) {
                                            if ($item['parent_id'] == 0 && $item['id'] != $item_id) {
                                                $nivel1[$item['id']] = $item;
                                                echo '<option value="' . $item['id'] . '" ' . 
                                                     ($item['id'] == $item_actual['parent_id'] ? 'selected' : '') . '>' . 
                                                     htmlspecialchars($item['titulo']) . '</option>';
                                                
                                                // Buscar elementos de nivel 2
                                                foreach ($menu_items as $subitem) {
                                                    if ($subitem['parent_id'] == $item['id'] && $subitem['id'] != $item_id) {
                                                        echo '<option value="' . $subitem['id'] . '" ' . 
                                                             ($subitem['id'] == $item_actual['parent_id'] ? 'selected' : '') . '>' . 
                                                             '&nbsp;&nbsp;— ' . htmlspecialchars($subitem['titulo']) . '</option>';
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="form-text">Selecciona el elemento padre para crear submenús (máximo 3 niveles)</div>
                                </div>
                            </div>
                            
                            <!-- Columna derecha: Opciones avanzadas -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Opciones avanzadas</h5>
                                
                                <!-- Icono -->
                                <div class="mb-3">
                                    <label for="icono" class="form-label">Icono (FontAwesome)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i id="icono-preview" class="<?= htmlspecialchars($item_actual['icono']) ?>"></i>
                                        </span>
                                        <input type="text" class="form-control" id="icono" name="icono" value="<?= htmlspecialchars($item_actual['icono']) ?>" placeholder="fas fa-star">
                                    </div>
                                    <div class="form-text">Clase CSS de FontAwesome para el icono</div>
                                    
                                    <!-- Selector de iconos -->
                                    <div class="icon-selector mt-2 border rounded p-2" style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px;">
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-star"><i class="fas fa-star"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-fire"><i class="fas fa-fire"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-percent"><i class="fas fa-percent"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-thumbs-up"><i class="fas fa-thumbs-up"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-shopping-cart"><i class="fas fa-shopping-cart"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-tag"><i class="fas fa-tag"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-gift"><i class="fas fa-gift"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon" data-icon="fas fa-bolt"><i class="fas fa-bolt"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Clases CSS -->
                                <div class="mb-3">
                                    <label for="clase" class="form-label">Clases CSS personalizadas</label>
                                    <input type="text" class="form-control" id="clase" name="clase" value="<?= htmlspecialchars($item_actual['clase']) ?>">
                                    <div class="form-text">Clases CSS adicionales separadas por espacios</div>
                                </div>
                                
                                <!-- Target -->
                                <div class="mb-3">
                                    <label for="target" class="form-label">Comportamiento del enlace</label>
                                    <select class="form-select" id="target" name="target">
                                        <option value="_self" <?= $item_actual['target'] === '_self' ? 'selected' : '' ?>>Abrir en la misma ventana</option>
                                        <option value="_blank" <?= $item_actual['target'] === '_blank' ? 'selected' : '' ?>>Abrir en una nueva ventana</option>
                                    </select>
                                </div>
                                
                                <!-- Orden -->
                                <div class="mb-3">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?= (int)$item_actual['orden'] ?>" min="0">
                                    <div class="form-text">Posición en el menú (menor número = aparece primero)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vista previa -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="h6 mb-0">Vista previa</h5>
                            </div>
                            <div class="card-body" id="menu-preview">
                                <!-- Aquí se mostrará la vista previa -->
                            </div>
                        </div>
                        
                        <!-- Botones del formulario -->
                        <div class="text-end">
                            <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=editar&id=<?= $menu_id ?>" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $accion === 'editar' ? 'Actualizar' : 'Crear' ?> menú inteligente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para el formulario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const tipoSelect = document.getElementById('tipo_smart');
    const descripcionTipo = document.getElementById('descripcion_tipo');
    const iconoInput = document.getElementById('icono');
    const iconoPreview = document.getElementById('icono-preview');
    const limiteInput = document.getElementById('limite');
    const tituloInput = document.getElementById('titulo');
    const menuPreview = document.getElementById('menu-preview');
    
    // Datos de tipos de menú
    const tiposMenuData = <?= json_encode($tipos_menu) ?>;
    
    // Inicializar descripción del tipo seleccionado
    actualizarDescripcionTipo();
    
    // Actualizar vista previa
    actualizarVistaPrevia();
    
    // Eventos
    tipoSelect.addEventListener('change', function() {
        actualizarDescripcionTipo();
        
        // Actualizar icono automáticamente si no hay uno personalizado
        if (!iconoInput.value) {
            const iconoTipo = this.options[this.selectedIndex].getAttribute('data-icon');
            if (iconoTipo) {
                iconoInput.value = iconoTipo;
                iconoPreview.className = iconoTipo;
            }
        }
        
        // Actualizar título automáticamente si está vacío
        if (!tituloInput.value) {
            const nombreTipo = this.options[this.selectedIndex].textContent.trim();
            if (nombreTipo) {
                tituloInput.value = nombreTipo;
            }
        }
        
        actualizarVistaPrevia();
    });
    
    // Botones de selección de iconos
    document.querySelectorAll('.btn-icon').forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.getAttribute('data-icon');
            iconoInput.value = icon;
            iconoPreview.className = icon;
            actualizarVistaPrevia();
        });
    });
    
    // Actualizar vista previa al cambiar inputs
    [tituloInput, iconoInput, limiteInput].forEach(input => {
        input.addEventListener('input', actualizarVistaPrevia);
        input.addEventListener('change', actualizarVistaPrevia);
    });
    
    // Función para actualizar la descripción del tipo de menú
    function actualizarDescripcionTipo() {
        const tipoSeleccionado = tipoSelect.value;
        if (tiposMenuData[tipoSeleccionado]) {
            descripcionTipo.textContent = tiposMenuData[tipoSeleccionado].descripcion;
        } else {
            descripcionTipo.textContent = '';
        }
    }
    
    // Función para actualizar la vista previa
    function actualizarVistaPrevia() {
        const tipo = tipoSelect.value;
        const icono = iconoInput.value;
        const titulo = tituloInput.value || 'Menú Inteligente';
        const limite = limiteInput.value || '10';
        
        let html = '<div class="menu-preview">';
        
        // Simular un elemento de menú dropdown
        html += '<ul class="nav navbar-nav menu-preview-ul">';
        html += '<li class="nav-item dropdown menu-preview-li">';
        
        // Enlace del elemento
        html += '<a class="nav-link dropdown-toggle menu-preview-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
        
        // Icono si existe
        if (icono) {
            html += `<i class="${icono}"></i> `;
        }
        
        // Título
        html += titulo;
        html += '</a>';
        
        // Submenú con elementos simulados
        html += '<ul class="dropdown-menu menu-preview-submenu">';
        
        // Información del tipo de menú
        if (tiposMenuData[tipo]) {
            const tipoInfo = tiposMenuData[tipo];
            
            // Mostrar elementos simulados según el tipo
            switch (tipo) {
                case 'nuevos':
                    html += simulateItems('Nuevos productos', limite, 'Fecha: hace 2 días');
                    break;
                    
                case 'vendidos':
                    html += simulateItems('Productos más vendidos', limite, 'Ventas: 1,234 unidades');
                    break;
                    
                case 'destacados':
                    html += simulateItems('Productos destacados', limite, 'Destacado desde: 10/05/2025');
                    break;
                    
                case 'ofertas':
                    html += simulateItems('Ofertas especiales', limite, 'Ahorro: 20%');
                    break;
                    
                default:
                    html += simulateItems('Productos automáticos', limite, 'Información adicional');
            }
            
            // Enlace para ver todos
            html += '<li><hr class="dropdown-divider"></li>';
            html += '<li><a class="dropdown-item ver-todos" href="#"><i class="fas fa-list"></i> Ver todos</a></li>';
        }
        
        html += '</ul>';
        html += '</li>';
        html += '</ul>';
        
        // Añadir información adicional
        html += '<div class="mt-3 p-3 bg-light rounded">';
        html += '<div><strong>Tipo:</strong> ' + (tiposMenuData[tipo]?.nombre || 'No seleccionado') + '</div>';
        html += '<div><strong>Descripción:</strong> ' + (tiposMenuData[tipo]?.descripcion || 'Sin descripción') + '</div>';
        html += '<div><strong>Límite:</strong> ' + limite + ' productos</div>';
        html += '<div class="mt-2 small text-muted">Los productos mostrados se actualizarán automáticamente basándose en el tipo de menú seleccionado.</div>';
        html += '</div>';
        
        html += '</div>';
        
        menuPreview.innerHTML = html;
    }
    
    // Función para simular elementos de menú
    function simulateItems(type, limit, extraInfo) {
        const count = Math.min(parseInt(limit) || 5, 8);
        let items = '';
        
        for (let i = 1; i <= count; i++) {
            items += `
            <li>
                <a class="dropdown-item" href="#">
                    <img src="${URL_SITIO}assets/img/producto-default.jpg" class="producto-miniatura" alt="Producto ${i}">
                    Producto ejemplo ${i}
                    <span class="producto-precio">$${(Math.random() * 100).toFixed(2)}</span>
                </a>
            </li>`;
        }
        
        return items;
    }
    
    // Estilos para la vista previa
    const style = document.createElement('style');
    style.textContent = `
        .menu-preview-ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .menu-preview-li {
            position: relative;
        }
        .menu-preview-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #212529;
            text-decoration: none;
        }
        .menu-preview-link i {
            margin-right: 0.5rem;
        }
        .menu-preview-submenu {
            display: block;
            position: static;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
            padding: 0.5rem 0;
            width: 100%;
        }
        .producto-miniatura {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 8px;
        }
        .producto-precio {
            margin-left: auto;
            font-weight: bold;
            color: #5c6ac4;
        }
        .ver-todos {
            text-align: center;
            background-color: rgba(92, 106, 196, 0.1);
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>