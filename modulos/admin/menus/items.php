<?php
// items.php - Gestión de elementos de un menú con drag&drop, AJAX y modal avanzado

define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';
require_once __DIR__ . '/../categorias/modelo.php';
require_once __DIR__ . '/../posiciones/modelo.php'; // Nuevo require para el modelo de posiciones

// Verificar sesión y permisos
if (!estaLogueado() || !esAdmin()) {
    registrarLog('Acceso no autorizado a items del menú', 'seguridad');
    redireccionar('login');
    exit;
}

// Validar menu_id
$menu_id = filter_input(INPUT_GET, 'menu_id', FILTER_VALIDATE_INT);
if (!$menu_id) {
    mostrarMensaje('ID de menú no válido', 'error');
    redireccionar('admin/menus');
    exit;
}

// Instanciar modelos
$menuModel = new Menu($conexion);
$menu      = $menuModel->obtenerPorId($menu_id);
if (!$menu) {
    mostrarMensaje('Menú no encontrado', 'error');
    redireccionar('admin/menus');
    exit;
}
$catModel   = new Categoria($conexion);
$categories = $catModel->obtenerTodas();

// Nuevo: Obtener todos los menús disponibles para poder mover elementos entre menús
$todosMenus = $menuModel->obtenerTodos();

// Nuevo: Obtener posiciones de menú disponibles
$posModel = new Posicion($conexion);
$posiciones = $posModel->obtenerTodas();

// Generar token CSRF
$csrf_token = generarCSRFToken();

// Procesar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';

    // Verificar token
    if (!isset($_POST['csrf_token']) || !verificarCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['status'=>'error','mensaje'=>'Token inválido']);
        exit;
    }

    // Guardar orden drag-&-drop
    if ($action === 'guardar_orden') {
        $data = json_decode($_POST['items'] ?? '[]', true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status'=>'error','mensaje'=>'JSON inválido']);
            exit;
        }
        // Función recursiva
        function saveItems(array $nodes, int $parent = 0) {
            global $conexion, $menu_id;
            foreach ($nodes as $pos => $n) {
                $id     = filter_var($n['id'] ?? 0, FILTER_VALIDATE_INT);
                $title  = substr(trim($n['title'] ?? ''), 0, 100);
                $url    = substr(trim($n['url'] ?? '#'), 0, 255);
                if ($id > 0) {
                    $stmt = $conexion->prepare("UPDATE menu_items SET parent_id=?, orden=?, titulo=?, url=? WHERE id=? AND menu_id=?");
                    $stmt->execute([$parent, $pos, $title, $url, $id, $menu_id]);
                }
                if (!empty($n['children'])) {
                    saveItems($n['children'], $id);
                }
            }
        }
        try {
            $conexion->beginTransaction();
            saveItems($data);
            $conexion->commit();
            registrarLog("Orden guardado menú=$menu_id", 'admin');
            echo json_encode(['status'=>'ok','mensaje'=>'Orden guardado']);
        } catch (Exception $e) {
            $conexion->rollBack();
            registrarLog('Error guardando orden: '.$e->getMessage(), 'error');
            echo json_encode(['status'=>'error','mensaje'=>'Error al guardar']);
        }
        exit;
    }

    // Añadir nuevo ítem
    if ($action === 'agregar') {
        $titulo        = substr(trim($_POST['titulo'] ?? ''), 0, 100);
        $url           = substr(trim($_POST['url'] ?? '#'), 0, 255);
        $parent_id     = filter_var($_POST['parent_id'] ?? 0, FILTER_VALIDATE_INT);
        $orden         = filter_var($_POST['orden'] ?? 0, FILTER_VALIDATE_INT);
        $posicion_id   = filter_var($_POST['posicion_id'] ?? 0, FILTER_VALIDATE_INT); // Nuevo: ID de posición
        $target_menu_id = filter_var($_POST['target_menu_id'] ?? $menu_id, FILTER_VALIDATE_INT); // Nuevo: Menú destino
        
        if (empty($titulo)) {
            echo json_encode(['status'=>'error','mensaje'=>'Título obligatorio']);
            exit;
        }
        try {
            $stmt = $conexion->prepare(
                "INSERT INTO menu_items (menu_id, parent_id, titulo, url, orden, posicion_id) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$target_menu_id, $parent_id, $titulo, $url, $orden, $posicion_id]);
            registrarLog("Ítem agregado menú=$target_menu_id", 'admin');
            echo json_encode([
                'status' => 'ok',
                'mensaje' => 'Ítem agregado',
                'reload' => ($target_menu_id != $menu_id) // Indicar si debe recargar si se agregó a otro menú
            ]);
        } catch (Exception $e) {
            registrarLog('Error al agregar ítem: '.$e->getMessage(), 'error');
            echo json_encode(['status'=>'error','mensaje'=>'Error al agregar']);
        }
        exit;
    }
    
    // Editar ítem existente
    if ($action === 'editar') {
        $item_id       = filter_var($_POST['item_id'] ?? 0, FILTER_VALIDATE_INT);
        $titulo        = substr(trim($_POST['titulo'] ?? ''), 0, 100);
        $url           = substr(trim($_POST['url'] ?? '#'), 0, 255);
        $parent_id     = filter_var($_POST['parent_id'] ?? 0, FILTER_VALIDATE_INT);
        $orden         = filter_var($_POST['orden'] ?? 0, FILTER_VALIDATE_INT);
        $posicion_id   = filter_var($_POST['posicion_id'] ?? 0, FILTER_VALIDATE_INT); // Nuevo: ID de posición
        $target_menu_id = filter_var($_POST['target_menu_id'] ?? $menu_id, FILTER_VALIDATE_INT); // Nuevo: Menú destino
        
        if (empty($titulo) || $item_id <= 0) {
            echo json_encode(['status'=>'error','mensaje'=>'Título obligatorio y ID válido']);
            exit;
        }
        
        try {
            // Si se está moviendo a otro menú o cambiando la posición
            if ($target_menu_id != $menu_id || isset($_POST['posicion_id'])) {
                $stmt = $conexion->prepare(
                    "UPDATE menu_items SET menu_id=?, parent_id=?, titulo=?, url=?, orden=?, posicion_id=? WHERE id=?"
                );
                $stmt->execute([$target_menu_id, $parent_id, $titulo, $url, $orden, $posicion_id, $item_id]);
            } else {
                $stmt = $conexion->prepare(
                    "UPDATE menu_items SET parent_id=?, titulo=?, url=?, orden=? WHERE id=? AND menu_id=?"
                );
                $stmt->execute([$parent_id, $titulo, $url, $orden, $item_id, $menu_id]);
            }
            
            if ($stmt->rowCount() > 0) {
                registrarLog("Ítem actualizado id=$item_id menú=$target_menu_id", 'admin');
                echo json_encode([
                    'status' => 'ok',
                    'mensaje' => 'Ítem actualizado',
                    'reload' => ($target_menu_id != $menu_id) // Indicar si debe recargar si se movió a otro menú
                ]);
            } else {
                echo json_encode(['status'=>'error','mensaje'=>'No se realizaron cambios']);
            }
        } catch (Exception $e) {
            registrarLog('Error al actualizar ítem: '.$e->getMessage(), 'error');
            echo json_encode(['status'=>'error','mensaje'=>'Error al actualizar']);
        }
        exit;
    }
    
    // Nuevo: Obtener ítems de un menú específico
    if ($action === 'obtener_items_menu') {
        $target_menu_id = filter_var($_POST['target_menu_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$target_menu_id) {
            echo json_encode(['status'=>'error','mensaje'=>'ID de menú no válido']);
            exit;
        }
        
        try {
            $items = $menuModel->obtenerArbolItems($target_menu_id);
            
            function formatItemsForSelect($items, $prefix = '') {
                $options = [];
                foreach ($items as $item) {
                    $options[] = [
                        'id' => $item['id'],
                        'text' => $prefix . $item['titulo']
                    ];
                    if (!empty($item['children'])) {
                        $options = array_merge($options, formatItemsForSelect($item['children'], $prefix . '— '));
                    }
                }
                return $options;
            }
            
            $formattedItems = formatItemsForSelect($items);
            echo json_encode([
                'status' => 'ok',
                'items' => $formattedItems
            ]);
        } catch (Exception $e) {
            echo json_encode(['status'=>'error','mensaje'=>'Error al obtener ítems']);
        }
        exit;
    }
}

// Función para renderizar los ítems con el botón de editar actualizado
function renderizarArbolItems($items, $nivel = 0) {
    $html = '';
    foreach ($items as $item) {
        // Obtener la posición si existe
        $posicionNombre = isset($item['posicion_nombre']) ? ' <span class="badge bg-info">' . htmlspecialchars($item['posicion_nombre'], ENT_QUOTES) . '</span>' : '';
        
        $html .= '<li class="dd-item" data-id="' . $item['id'] . '" data-title="' . htmlspecialchars($item['titulo'], ENT_QUOTES) . '" data-url="' . htmlspecialchars($item['url'], ENT_QUOTES) . '">';
        $html .= '<div class="dd-handle">' . htmlspecialchars($item['titulo'], ENT_QUOTES) . $posicionNombre . '</div>';
        $html .= '<div class="dd-item-actions">';
        $html .= '<button 
                    class="btn btn-sm btn-outline-primary btn-edit-item"
                    data-bs-toggle="modal"
                    data-bs-target="#addItemModal"
                    data-id="' . $item['id'] . '"
                    data-title="' . htmlspecialchars($item['titulo'], ENT_QUOTES) . '"
                    data-url="' . htmlspecialchars($item['url'], ENT_QUOTES) . '"
                    data-parent="' . $item['parent_id'] . '"
                    data-menu="' . $item['menu_id'] . '"
                    data-posicion="' . ($item['posicion_id'] ?? 0) . '"
                    data-order="' . $item['orden'] . '">
                    <i class="fas fa-edit"></i>
                  </button>';
        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete-item" data-id="' . $item['id'] . '"><i class="fas fa-trash"></i></button>';
        $html .= '</div>';
        
        if (!empty($item['children'])) {
            $html .= '<ol class="dd-list">';
            $html .= renderizarArbolItems($item['children'], $nivel + 1);
            $html .= '</ol>';
        }
        
        $html .= '</li>';
    }
    return $html;
}

// Obtener items para renderizar con información de posición
$itemsTree = $menuModel->obtenerArbolItemsConPosicion($menu_id);

// Título de la página
$pageTitle = "Gestionar elementos: " . htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8');
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form mt-4">
  <h1><?= $pageTitle ?></h1>
  <a href="<?= URL_SITIO ?>admin/menus" class="btn btn-outline-secondary mb-3">← Volver</a>
  <div id="notificacion" class="alert d-none"></div>

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Reordenar / Añadir ítems</span>
      <div>
        <button id="addItem" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">+ Añadir ítem</button>
        <button id="saveOrder" class="btn btn-sm btn-success">Guardar orden</button>
      </div>
    </div>
    <div class="card-body">
      <!-- Leyenda de posiciones -->
      <div class="mb-3 positions-legend">
        <strong>Posiciones:</strong>
        <?php foreach($posiciones as $pos): ?>
          <span class="badge bg-info me-2"><?= htmlspecialchars($pos['nombre'], ENT_QUOTES) ?></span>
        <?php endforeach; ?>
      </div>
      
      <div class="dd" id="nestable">
        <ol class="dd-list">
          <?php if (empty($itemsTree)): ?>
            <p class="text-muted">No hay elementos aún. Usa "Añadir ítem".</p>
          <?php else: ?>
            <?= renderizarArbolItems($itemsTree) ?>
          <?php endif ?>
        </ol>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Añadir ítem -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="addItemForm">
      <input type="hidden" name="action" value="agregar">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="hidden" name="menu_id" value="<?= $menu_id ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Añadir ítem al menú</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label>Título</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label>URL</label>
            <input type="text" name="url" class="form-control" placeholder="/ruta" required>
          </div>

          <div class="form-group mb-3">
            <label>Enlaces rápidos</label><br>
            <button type="button" class="btn btn-sm btn-outline-secondary js-quick" data-url="/">Inicio</button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-quick" data-url="/productos">Productos</button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-quick" data-url="/contacto">Contacto</button>
          </div>

          <div class="form-group mb-3">
            <label>Categoría</label>
            <select name="url" id="selectCategory" class="form-control">
              <option value="">-- Seleccionar --</option>
              <?php foreach($categories as $cat): ?>
                <option value="/categorias/<?= htmlspecialchars($cat['slug'], ENT_QUOTES) ?>">
                  <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label>Buscar producto</label>
            <input type="text" id="searchProduct" class="form-control" placeholder="Escribe para buscar...">
            <ul id="productResults" class="list-group mt-2" style="display:none;"></ul>
          </div>
          
          <!-- Nuevo: Selector de menú -->
          <div class="form-group mb-3">
            <label>Menú</label>
            <select name="target_menu_id" id="targetMenu" class="form-control">
              <?php foreach($todosMenus as $menuItem): ?>
                <option value="<?= $menuItem['id'] ?>" <?= ($menuItem['id'] == $menu_id) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($menuItem['nombre'], ENT_QUOTES) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          
          <!-- Nuevo: Selector de posición -->
          <div class="form-group mb-3">
            <label>Posición</label>
            <select name="posicion_id" class="form-control">
              <option value="0">Sin posición específica</option>
              <?php foreach($posiciones as $pos): ?>
                <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['nombre'], ENT_QUOTES) ?></option>
              <?php endforeach ?>
            </select>
          </div>

          <div class="row">
            <div class="form-group col mb-3">
              <label>Ítem padre</label>
              <select name="parent_id" id="parentSelect" class="form-control">
                <option value="0">Ninguno</option>
                <?php function printOpts($nodes, $prefix=''){
                  foreach($nodes as $n){
                    echo "<option value='{$n['id']}'>".htmlspecialchars($prefix.$n['titulo'],ENT_QUOTES)."</option>";
                    if(!empty($n['children'])) printOpts($n['children'], $prefix.'— ');
                  }
                }
                printOpts($itemsTree);
                ?>
              </select>
            </div>
            <div class="form-group col mb-3">
              <label>Orden</label>
              <input type="number" name="orden" class="form-control" value="0">
            </div>
          </div>
          
          <!-- Jerarquía visual -->
          <div class="form-group mb-3">
            <label>Vista de jerarquía</label>
            <div id="hierarchyView" class="border p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
              <!-- Se llenará dinámicamente con JavaScript -->
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php';
?>

<script>
jQuery(function($){
  // Inicializar Nestable
  $('#nestable').nestable({ maxDepth: 5 });

  // Función para notificar
  function notify(msg, type='success'){
    $('#notificacion').removeClass('d-none alert-success alert-danger').addClass('alert-'+type).text(msg);
    setTimeout(()=> $('#notificacion').addClass('d-none'), 4000);
  }

  // Quick links
  $('.js-quick').click(function(){
    $('input[name="titulo"]').val(this.textContent);
    $('input[name="url"]').val(this.dataset.url);
  });

  // Category select
  $('#selectCategory').change(function(){
    let o = this.selectedOptions[0]; 
    if(!o) return;
    $('input[name="titulo"]').val(o.text);
    $('input[name="url"]').val(o.value);
  });

  // Search products
  $('#searchProduct').on('input', function(){
    let q = this.value.trim();
    if(q.length < 2){ 
      return $('#productResults').hide(); 
    }
    $.getJSON('/admin/ajax/search_products.php', {q}, function(res){
      if(!res.length) return $('#productResults').hide();
      let items = res.map(p => `<li class="list-group-item list-group-item-action" data-url="/productos/${p.slug}">${p.name}</li>`).join('');
      $('#productResults').html(items).show();
    });
  });
  
  $(document).on('click', '#productResults li', function(){
    $('input[name="titulo"]').val(this.textContent);
    $('input[name="url"]').val(this.dataset.url);
    $('#productResults').hide();
  });

  // Actualizar jerarquía visual
  function updateHierarchyView() {
    let menuName = $('#targetMenu option:selected').text();
    let selectedParent = $('#parentSelect option:selected');
    let parentText = selectedParent.val() !== '0' ? selectedParent.text() : 'Ninguno';
    
    // Construir la vista de jerarquía
    let html = `<div class="hierarchy-item mb-1">Menú: <strong>${menuName}</strong></div>`;
    html += `<div class="hierarchy-item mb-1">Padre: <strong>${parentText}</strong></div>`;
    
    $('#hierarchyView').html(html);
  }
  
  // Actualizar al cambiar valores
  $('#targetMenu, #parentSelect').on('change', updateHierarchyView);

  // Cargar ítems padres cuando cambia el menú destino
  $('#targetMenu').change(function() {
    let targetMenuId = $(this).val();
    
    // Si es el menú actual, usar los datos ya cargados
    if (targetMenuId == <?= $menu_id ?>) {
      $('#parentSelect').html('<option value="0">Ninguno</option><?php 
        ob_start();
        printOpts($itemsTree);
        echo str_replace("'", "\\'", ob_get_clean());
      ?>');
      updateHierarchyView();
      return;
    }
    
    // Obtener ítems del menú seleccionado mediante AJAX
    $.post(
      location.pathname + '?menu_id=<?= $menu_id ?>',
      {
        action: 'obtener_items_menu',
        target_menu_id: targetMenuId,
        csrf_token: '<?= $csrf_token ?>'
      },
      function(response) {
        if (response.status === 'ok') {
          let options = '<option value="0">Ninguno</option>';
          
          // Construir opciones basadas en la respuesta
          response.items.forEach(function(item) {
            options += `<option value="${item.id}">${item.text}</option>`;
          });
          
          $('#parentSelect').html(options);
          updateHierarchyView();
        } else {
          notify(response.mensaje || 'Error al cargar ítems', 'danger');
        }
      },
      'json'
    ).fail(function() {
      notify('Error de conexión', 'danger');
    });
  });

  // Add item form
  $('#addItemForm').submit(function(e){
    e.preventDefault();
    let btn = $(this).find('button[type=submit]').prop('disabled', true).text('Guardando...');
    
    $.post(
      location.pathname + '?menu_id=<?= $menu_id ?>',
      $(this).serialize(),
      function(resp) {
        if (resp.status === 'ok') { 
          notify(resp.mensaje); 
          if (resp.reload) {
            // Si el ítem se añadió a otro menú, recargar después de un tiempo
            setTimeout(() => location.reload(), 800);
          } else {
            // Si se agregó al menú actual, recargar para mostrar el nuevo ítem
            setTimeout(() => location.reload(), 800);
          }
        } else { 
          notify(resp.mensaje, 'danger'); 
          btn.prop('disabled', false).text('Guardar'); 
        }
      },
      'json'
    ).fail(function() { 
      notify('Error de conexión', 'danger'); 
      btn.prop('disabled', false).text('Guardar'); 
    });
  });

  // Save order
  $('#saveOrder').click(function(){
    if (!confirm('¿Guardar cambios en el orden?')) return;
    
    let data = $('#nestable').nestable('serialize');
    let payload = { 
      action: 'guardar_orden', 
      items: JSON.stringify(data), 
      csrf_token: '<?= $csrf_token ?>' 
    };
    
    let btn = $(this).prop('disabled', true).text('Guardando...');
    
    $.post(
      location.pathname + '?menu_id=<?= $menu_id ?>', 
      payload,
      function(r) {
        r.status === 'ok' ? notify(r.mensaje) : notify(r.mensaje, 'danger');
      },
      'json'
    ).fail(function() {
      notify('Error de conexión', 'danger');
    }).always(function() {
      btn.prop('disabled', false).text('Guardar orden');
    });
  });
  
  // Script para manejar la edición de ítems
  $(document).on('click', '.btn-edit-item', function() {
    // Cambiar título del modal
    $('.modal-title').text('Editar ítem del menú');
    
    // Actualizar valor de action
    $('input[name="action"]').val('editar');
    
    // Llenar formulario con datos del ítem
    const id = $(this).data('id');
    const title = $(this).data('title');
    const url = $(this).data('url');
    const parent = $(this).data('parent');
    const menu = $(this).data('menu');
    const posicion = $(this).data('posicion');
    const order = $(this).data('order');
    
    // Crear o actualizar campo oculto para el ID del ítem
    if ($('#item_id').length === 0) {
        $('<input>').attr({
            type: 'hidden',
            id: 'item_id',
            name: 'item_id',
            value: id
        }).appendTo('#addItemForm');
    } else {
        $('#item_id').val(id);
    }
    
    // Llenar campos del formulario
    $('input[name="titulo"]').val(title);
    $('input[name="url"]').val(url);
    $('select[name="parent_id"]').val(parent);
    $('select[name="target_menu_id"]').val(menu);
    $('select[name="posicion_id"]').val(posicion);
    $('input[name="orden"]').val(order);
    
    // Actualizar vista de jerarquía
    updateHierarchyView();
    
    // Mostrar modal (usando Bootstrap 5)
    $('#addItemModal').modal('show');
  });

  // Limpiar formulario al abrir el modal para añadir nuevo ítem
  $('#addItem').on('click', function() {
    $('.modal-title').text('Añadir ítem al menú');
    $('#addItemForm')[0].reset();
    $('input[name="action"]').val('agregar');
    
    // Establecer el menú actual como predeterminado
    $('select[name="target_menu_id"]').val(<?= $menu_id ?>);
    
    // Actualizar opciones de padres según el menú seleccionado
    $('#targetMenu').trigger('change');
    
    // Eliminar campo de ID si existe
    $('#item_id').remove();
    
    // Actualizar vista de jerarquía
    updateHierarchyView();
  });
  
  // Inicialización
  updateHierarchyView();
});
</script>