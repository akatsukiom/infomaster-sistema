<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificación de sesión y permisos
if (!estaLogueado() || !esAdmin()) {
    registrarLog('Intento de acceso no autorizado a gestión de menús', 'seguridad');
    redireccionar('login');
    exit;
}

// Validar y sanitizar menu_id
if (!isset($_GET['menu_id']) || !filter_var($_GET['menu_id'], FILTER_VALIDATE_INT)) {
    mostrarMensaje('ID de menú no válido', 'error');
    redireccionar('admin/menus/listar');
    exit;
}
$menu_id = filter_var($_GET['menu_id'], FILTER_SANITIZE_NUMBER_INT);

// Instanciar modelo y obtener menú
try {
    $menuModel = new Menu($conexion);
    $menu = $menuModel->obtenerPorId($menu_id);
    
    if (!$menu) {
        mostrarMensaje('El menú solicitado no existe', 'error');
        redireccionar('admin/menus/listar');
        exit;
    }
    
    // CSRF token para proteger formularios
    $csrf_token = generarCSRFToken();
    
    // AJAX: guardar orden de items drag-and-drop
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar que la petición sea AJAX
        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                 
        if ($esAjax && isset($_POST['action']) && $_POST['action'] === 'guardar_orden') {
            // Validar CSRF token
            if (!isset($_POST['csrf_token']) || !verificarCSRFToken($_POST['csrf_token'])) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'mensaje' => 'Token de seguridad inválido']);
                exit;
            }
            
            $itemsJson = json_decode($_POST['items'] ?? '[]', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['status' => 'error', 'mensaje' => 'Formato JSON inválido']);
                exit;
            }
            
            // Función recursiva para guardar
            function guardarItems($items, $parent = 0) {
                global $conexion, $menu_id;
                
                foreach ($items as $orden => $item) {
                    $id = isset($item['id']) ? filter_var($item['id'], FILTER_VALIDATE_INT) : 0;
                    $titulo = trim($item['title'] ?? '');
                    $url = trim($item['url'] ?? '#');
                    
                    // Validaciones básicas
                    if (empty($titulo)) {
                        continue; // Saltamos items sin título
                    }
                    
                    // Limitar longitud de campos
                    $titulo = substr($titulo, 0, 100);
                    $url = substr($url, 0, 255);
                    
                    if ($id === 0 || $id === false) {
                        // Nuevo item
                        $stmt = $conexion->prepare(
                            "INSERT INTO menu_items (menu_id, parent_id, titulo, url, orden) 
                             VALUES (?, ?, ?, ?, ?)"
                        );
                        $stmt->execute([$menu_id, $parent, $titulo, $url, $orden]);
                        $id = $conexion->lastInsertId();
                    } else {
                        // Actualizar orden y parent
                        $stmt = $conexion->prepare(
                            "UPDATE menu_items 
                             SET parent_id = ?, orden = ?, titulo = ?, url = ? 
                             WHERE id = ? AND menu_id = ?"
                        );
                        $stmt->execute([$parent, $orden, $titulo, $url, $id, $menu_id]);
                    }
                    
                    // Procesar hijos recursivamente
                    if (!empty($item['children']) && is_array($item['children'])) {
                        guardarItems($item['children'], $id);
                    }
                }
            }
            
            try {
                $conexion->beginTransaction();
                guardarItems($itemsJson);
                $conexion->commit();
                
                registrarLog("Menú ID $menu_id actualizado correctamente", 'admin');
                echo json_encode(['status' => 'ok', 'mensaje' => 'Menú actualizado correctamente']);
            } catch (Exception $e) {
                $conexion->rollBack();
                registrarLog("Error al actualizar menú: " . $e->getMessage(), 'error');
                echo json_encode(['status' => 'error', 'mensaje' => 'Error al guardar los cambios']);
            }
            exit;
        }
    }

    // Cargo items actuales para renderizar
    $itemsTree = $menuModel->obtenerArbolItems($menu_id);

    // Título y header
    $titulo = "Gestionar elementos del menú: " . htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8');
    include __DIR__ . '/../../../includes/header.php';
    
} catch (Exception $e) {
    registrarLog("Error en gestión de menús: " . $e->getMessage(), 'error');
    mostrarMensaje('Ha ocurrido un error al procesar la solicitud', 'error');
    redireccionar('admin/menus/listar');
    exit;
}
?>

<!-- Dependencias para Nestable -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>

<div class="container admin-form">
    <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
    <p>
        <a href="<?= htmlspecialchars(URL_SITIO, ENT_QUOTES, 'UTF-8') ?>modulos/admin/menus/listar.php" class="btn btn-outline">
            ← Volver a la lista de menús
        </a>
    </p>

    <!-- Notificaciones -->
    <div id="notificacion" class="alert d-none"></div>

    <!-- Widget drag-and-drop -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Reordenar / crear items arrastrando</h2>
            <div class="btn-group">
                <button id="addItem" class="btn btn-sm btn-primary">+ Añadir ítem</button>
                <button id="saveOrder" class="btn btn-sm btn-success">Guardar cambios</button>
            </div>
        </div>
        <div class="card-body">
            <div class="dd" id="nestable">
                <ol class="dd-list">
                    <?php if (empty($itemsTree)): ?>
                        <div class="alert alert-info">
                            No hay elementos en este menú. Utilice el botón "Añadir ítem" para crear el primero.
                        </div>
                    <?php else: ?>
                        <?php echo renderizarArbolItems($itemsTree); ?>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir/editar ítems -->
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Añadir ítem al menú</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <div class="form-group">
                        <label for="itemLabel">Etiqueta del ítem</label>
                        <input type="text" class="form-control" id="itemLabel" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="itemUrl">URL o ruta interna</label>
                        <input type="text" class="form-control" id="itemUrl" placeholder="#" maxlength="255">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveItem">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para renderizar árbol de items recursivamente
<?php
function renderizarArbolItems($items) {
    $html = '';
    foreach ($items as $item) {
        $html .= '<li class="dd-item" data-id="' . (int)$item['id'] . '" data-title="' . htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') . '" data-url="' . htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="dd-handle">' . htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div class="dd-actions"><button type="button" class="btn btn-xs btn-edit-item" title="Editar"><i class="fa fa-edit"></i></button></div>';
        
        if (!empty($item['children'])) {
            $html .= '<ol class="dd-list">' . renderizarArbolItems($item['children']) . '</ol>';
        }
        
        $html .= '</li>';
    }
    return $html;
}
?>

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Nestable
    const nestable = $('#nestable').nestable({
        maxDepth: 5,
        group: 1,
        expandBtnHTML: '<button class="dd-expand" data-action="expand"><i class="fa fa-plus"></i></button>',
        collapseBtnHTML: '<button class="dd-collapse" data-action="collapse"><i class="fa fa-minus"></i></button>',
    });
    
    // Sistema de notificaciones
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const notif = document.getElementById('notificacion');
        notif.textContent = mensaje;
        notif.className = `alert alert-${tipo}`;
        notif.classList.remove('d-none');
        
        setTimeout(() => {
            notif.classList.add('d-none');
        }, 5000);
    }
    
    // Añadir nuevo ítem - con modal
    $('#addItem').on('click', function() {
        // Resetear formulario
        document.getElementById('itemForm').reset();
        document.getElementById('modalTitle').textContent = 'Añadir ítem al menú';
        
        // Mostrar modal
        $('#itemModal').modal('show');
        
        // Configurar acción al guardar
        $('#saveItem').off('click').on('click', function() {
            const label = document.getElementById('itemLabel').value.trim();
            const url = document.getElementById('itemUrl').value.trim() || '#';
            
            if (!label) {
                alert('La etiqueta del ítem es obligatoria');
                return;
            }
            
            // Añadir nuevo item al árbol
            $('#nestable > .dd-list').append(
                '<li class="dd-item" data-id="0" data-title="' + 
                escapeHtml(label) + '" data-url="' + escapeHtml(url) + '">' +
                '<div class="dd-handle">' + escapeHtml(label) + '</div>' +
                '<div class="dd-actions"><button type="button" class="btn btn-xs btn-edit-item" title="Editar"><i class="fa fa-edit"></i></button></div>' +
                '</li>'
            );
            
            // Cerrar modal
            $('#itemModal').modal('hide');
        });
    });
    
    // Editar ítem existente
    $(document).on('click', '.btn-edit-item', function() {
        const item = $(this).closest('.dd-item');
        const id = item.data('id');
        const title = item.data('title');
        const url = item.data('url');
        
        // Rellenar formulario
        document.getElementById('itemLabel').value = title;
        document.getElementById('itemUrl').value = url;
        document.getElementById('modalTitle').textContent = 'Editar ítem del menú';
        
        // Mostrar modal
        $('#itemModal').modal('show');
        
        // Configurar acción al guardar
        $('#saveItem').off('click').on('click', function() {
            const newLabel = document.getElementById('itemLabel').value.trim();
            const newUrl = document.getElementById('itemUrl').value.trim() || '#';
            
            if (!newLabel) {
                alert('La etiqueta del ítem es obligatoria');
                return;
            }
            
            // Actualizar item en el árbol
            item.data('title', newLabel);
            item.data('url', newUrl);
            item.find('> .dd-handle').text(newLabel);
            
            // Cerrar modal
            $('#itemModal').modal('hide');
        });
    });
    
    // Guardar cambios - con AJAX y CSRF
    $('#saveOrder').on('click', function() {
        if (confirm('¿Estás seguro de guardar los cambios en el menú?')) {
            const data = $('#nestable').nestable('serialize');
            
            // Preparar datos para enviar, incluyendo URLs
            const procesarData = function(items) {
                return items.map(item => {
                    const processedItem = {
                        id: item.id,
                        title: $(`[data-id=${item.id}]`).data('title'),
                        url: $(`[data-id=${item.id}]`).data('url')
                    };
                    
                    if (item.children) {
                        processedItem.children = procesarData(item.children);
                    }
                    
                    return processedItem;
                });
            };
            
            const processedData = procesarData(data);
            
            // Mostrar indicador de carga
            $('#saveOrder').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
            
            $.ajax({
                url: 'items.php?menu_id=<?= $menu_id ?>',
                method: 'POST',
                data: {
                    action: 'guardar_orden',
                    items: JSON.stringify(processedData),
                    csrf_token: '<?= $csrf_token ?>'
                },
                dataType: 'json'
            }).done(function(resp) {
                if (resp.status === 'ok') {
                    mostrarNotificacion(resp.mensaje || 'Menú actualizado correctamente');
                    // No recargamos la página para mantener el estado de la interfaz
                } else {
                    mostrarNotificacion(resp.mensaje || 'Error al guardar los cambios', 'danger');
                }
            }).fail(function() {
                mostrarNotificacion('Error de conexión al guardar los cambios', 'danger');
            }).always(function() {
                $('#saveOrder').prop('disabled', false).html('Guardar cambios');
            });
        }
    });
    
    // Función de utilidad para escapar HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>