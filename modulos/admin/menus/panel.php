```php
<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
    exit;
}

// Instanciar modelo
global $conexion;
$menuModel = new Menu($conexion);

// Determinar acción y ID desde URI o GET
$uri      = $_SERVER['REQUEST_URI'];
$base_url = str_replace('/index.php', '', URL_SITIO);
$path     = str_replace($base_url, '', $uri);
$segments = explode('/', trim($path, '/'));
$accion   = $segments[2] ?? 'listar';
$menu_id  = isset($segments[3]) ? (int)$segments[3] : 0;
if (isset($_GET['accion'])) $accion = $_GET['accion'];
if (isset($_GET['id']))      $menu_id = (int)$_GET['id'];
if ($accion === 'crear') $accion = 'nuevo';
if ($accion === 'editar' && $menu_id === 0) $accion = 'nuevo';

// Arrays para errores y éxito
$errores = [];
$exito   = '';

// Procesar acciones
switch ($accion) {
    case 'guardar_menu':
        $nombre      = limpiarDato($_POST['nombre'] ?? '');
        $descripcion = limpiarDato($_POST['descripcion'] ?? '');
        if (empty($nombre)) {
            $errores[] = 'El nombre del menú es obligatorio';
        } else {
            if ($menu_id > 0) {
                if ($menuModel->actualizar($menu_id, $nombre, $descripcion)) {
                    $exito = 'Menú actualizado correctamente';
                    header("Location: " . URL_SITIO . "admin/menus/editar/" . $menu_id);
                    exit;
                }
                $errores[] = 'Error al actualizar el menú';
            } else {
                $nuevo_id = $menuModel->crear($nombre, $descripcion);
                if ($nuevo_id) {
                    $exito   = 'Menú creado correctamente';
                    $menu_id = $nuevo_id;
                    header("Location: " . URL_SITIO . "admin/menus/editar/" . $nuevo_id);
                    exit;
                }
                $errores[] = 'Error al crear el menú';
            }
        }
        break;

    case 'guardar_item':
        $item_id   = (int)($_POST['item_id']   ?? 0);
        $menu_id   = (int)($_POST['menu_id']   ?? 0);
        $titulo    = limpiarDato($_POST['titulo']    ?? '');
        $url       = limpiarDato($_POST['url']       ?? '');
        $parent_id = (int)($_POST['parent_id'] ?? 0);
        $orden     = (int)($_POST['orden']     ?? 0);
        $clase     = limpiarDato($_POST['clase']     ?? '');
        $target    = limpiarDato($_POST['target']    ?? '_self');
        if (empty($titulo) || empty($url)) {
            $errores[] = 'Título y URL son obligatorios';
        } else {
            if ($item_id > 0) {
                if ($menuModel->actualizarItem($item_id, $titulo, $url, $parent_id, $orden, $clase, $target)) {
                    $exito = 'Elemento actualizado correctamente';
                } else {
                    $errores[] = 'Error al actualizar el elemento';
                }
            } else {
                if ($menuModel->agregarItem($menu_id, $titulo, $url, $parent_id, $orden, $clase, $target)) {
                    $exito = 'Elemento agregado correctamente';
                } else {
                    $errores[] = 'Error al agregar el elemento';
                }
            }
            header("Location: " . URL_SITIO . "admin/menus/editar/" . $menu_id);
            exit;
        }
        break;

    case 'eliminar_menu':
        if ($menu_id > 0 && $menuModel->eliminar($menu_id)) {
            header("Location: " . URL_SITIO . "admin/menus/listar");
            exit;
        }
        $errores[] = 'Error al eliminar el menú';
        break;

    case 'eliminar_item':
        $item_id = (int)($_GET['item_id'] ?? 0);
        if ($item_id > 0) {
            $menuModel->eliminarItem($item_id);
        }
        header("Location: " . URL_SITIO . "admin/menus/editar/" . $menu_id);
        exit;
        break;
}

// Obtener datos según acción
$menus       = [];
$menu_actual = ['id'=>0,'nombre'=>'','descripcion'=>''];
$menu_items  = [];
if ($accion === 'listar') {
    $menus = $menuModel->obtenerTodos();
} else {
    if ($menu_id > 0) {
        $menu_actual = $menuModel->obtenerPorId($menu_id) ?: $menu_actual;
        $menu_items  = $menuModel->obtenerItems($menu_id);
    }
}

// Datos para selectores
$categorias = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$productos  = $conexion->query("SELECT id, nombre FROM productos ORDER BY nombre LIMIT 100")->fetch_all(MYSQLI_ASSOC);

// Título y header
$titulo = "Gestión de Menús - " . ucfirst($accion);
include __DIR__ . '/../../../includes/header.php';
?>

<div class="shopify-panel">
  <!-- Sidebar -->
  <div class="panel-sidebar">
    <div class="panel-sidebar-header"><h3>Menús</h3></div>
    <div class="panel-nav">
      <ul class="panel-menu">
        <li class="<?= $accion==='listar'?'active':'' ?>"><a href="<?= URL_SITIO ?>admin/menus/listar"><i class="fas fa-list"></i> Todos los Menús</a></li>
        <li class="<?= $accion==='nuevo'?'active':'' ?>"><a href="<?= URL_SITIO ?>admin/menus/crear"><i class="fas fa-plus-circle"></i> Nuevo Menú</a></li>
        <li><a href="<?= URL_SITIO ?>admin"><i class="fas fa-cog"></i> Panel Admin</a></li>
      </ul>
    </div>
  </div>

  <!-- Contenido -->
  <div class="panel-content">
    <div class="panel-header">
      <h1><?php echo $accion==='listar'?'Todos los Menús':($accion==='nuevo'?'Crear Nuevo Menú':'Editar Menú: '.htmlspecialchars($menu_actual['nombre'])); ?></h1>
      <div class="panel-actions">
        <?php if($accion==='listar'): ?>
          <a href="<?= URL_SITIO ?>admin/menus/crear" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Menú</a>
        <?php else: ?>
          <a href="<?= URL_SITIO ?>admin/menus/listar" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver a Menús</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if($errores): ?><div class="alert alert-error"><?php foreach($errores as $e) echo "<p>$e</p>"; ?></div><?php endif;?>
    <?php if($exito): ?><div class="alert alert-success"><p><?= htmlspecialchars($exito) ?></p></div><?php endif; ?>

    <?php if($accion==='listar'): ?>
      <div class="panel-card">
        <div class="panel-card-header"><h2>Menús Disponibles</h2></div>
        <div class="panel-card-body">
          <?php if(!$menus): ?>
            <p class="empty-state">No hay menús. <a href="<?= URL_SITIO ?>admin/menus/crear">Crear uno</a>.</p>
          <?php else: ?>
            <table class="data-table">
              <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Items</th><th>Acciones</th></tr></thead>
              <tbody>
                <?php foreach($menus as $m): ?>
                  <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['nombre']) ?></td>
                    <td><?= htmlspecialchars($m['descripcion']) ?></td>
                    <td><?= count($menuModel->obtenerItems($m['id'])) ?></td>
                    <td class="actions">
                      <a href="<?= URL_SITIO ?>admin/menus/editar/<?= $m['id'] ?>" class="btn-icon" title="Editar"><i class="fas fa-edit"></i></a>
                      <a href="<?= URL_SITIO ?>admin/menus/eliminar_menu/<?= $m['id'] ?>" class="btn-icon delete" onclick="return confirm('¿Eliminar este menú?')"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="panel-columns">
        <div class="panel-column">
          <div class="panel-card">
            <div class="panel-card-header"><h2><?= $accion==='editar'?'Editar Menú':'Crear Menú' ?></h2></div>
            <div class="panel-card-body">
              <form method="POST" action="<?= URL_SITIO ?>admin/menus/guardar_menu/<?= $menu_id ?>" class="shopify-form">
                <input type="hidden" name="id" value="<?= $menu_id ?>">
                <div class="form-group">
                  <label for="nombre">Nombre del Menú</label>
                  <input id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($menu_actual['nombre']) ?>" required>
                </div>
                <div class="form-group">
                  <label for="descripcion">Descripción</label>
                  <textarea id="descripcion" name="descripcion" class="form-control"><?= htmlspecialchars($menu_actual['descripcion']) ?></textarea>
                </div>
                <div class="form-actions">
                  <button class="btn btn-primary"><?= $accion==='editar'?'Actualizar Menú':'Crear Menú' ?></button>
                </div>
              </form>
            </div>
          </div>

          <?php if($accion==='editar'): ?>
            <div class="panel-card mt-4">
              <div class="panel-card-header flex-between"><h2>Elementos del Menú</h2><button id="btn-add-item" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Agregar Elemento</button></div>
              <div class="panel-card-body">
                <div id="item-form-container" style="display:none;">
                  <form id="item-form" method="POST" action="<?= URL_SITIO ?>admin/menus/guardar_item" class="shopify-form">
                    <input type="hidden" name="menu_id" value="<?= $menu_id ?>">
                    <input type="hidden" name="item_id" id="edit_item_id" value="0">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="titulo">Título</label>
                        <input id="titulo" name="titulo" class="form-control" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="url">URL</label>
                        <input id="url" name="url" class="form-control" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="parent_id">Elemento Padre</label>
                        <select id="parent_id" name="parent_id" class="form-control">
                          <option value="0">Ninguno</option>
                          <?php foreach($menu_items as $it) if($it['parent_id']==0): ?><option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['titulo']) ?></option><?php endif; ?>
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="orden">Orden</label>
                        <input id="orden" name="orden" type="number" class="form-control" value="0">
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="clase">Clase CSS</label>
                        <input id="clase" name="clase" class="form-control">
                      </div>
                      <div class="form-group col-md-6">
                        <label for="target">Abrir en</label>
                        <select id="target" name="target" class="form-control">
                          <option value="_self">Misma ventana</option>
                          <option value="_blank">Nueva ventana</option>
                        </select>
                      </div>
                    </div>
                    <!-- Selector Shopify de Producto -->
                    <div class="form-group">
                      <label for="entity_id_product">Producto</label>
                      <select id="entity_id_product" name="entity_id" class="form-control">
                        <option value="">— Selecciona un producto —</option>
                        <?php foreach($productos as $p): ?>
                          <option value="<?= $p['id'] ?>" data-url="<?= URL_SITIO ?>productos/detalle.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Al elegir, se autocompleta la URL al detalle</small>
                    </div>
                    <div class="form-actions">
                      <button id="save-item-btn" class="btn btn-primary">Guardar Elemento</button>
                      <button type="button" id="cancel-item-btn" class="btn btn-outline">Cancelar</button>
                    </div>
                  </form>
                </div>
                <ul id="sortable" class="sortable-menu">
                  <?php
                  $padres=[]; $hijos=[];
                  foreach($menu_items as $i) {
                      if($i['parent_id']==0) $padres[$i['id']] = $i;
                      else $hijos[$i['parent_id']][] = $i;
                  }
                  usort($padres, function($a,$b){ return $a['orden'] <=> $b['orden']; });
                  foreach($hijos as &$list) usort($list, function($a,$b){ return $a['orden'] <=> $b['orden']; });
                  foreach($padres as $i): ?>
                    <li class="menu-item" data-id="<?= $i['id'] ?>">
                      <div class="menu-item-bar">
                        <span class="item-title"><?= htmlspecialchars($i['titulo']) ?></span>
                        <span class="item-controls">
                          <a href="#" class="edit-item" data-id="<?= $i['id'] ?>" data-titulo="<?= htmlspecialchars($i['titulo']) ?>" data-url="<?= htmlspecialchars($i['url']) ?>" data-parent="<?= $i['parent_id'] ?>" data-orden="<?= $i['orden'] ?>" data-clase="<?= htmlspecialchars($i['clase']) ?>" data-target="<?= htmlspecialchars($i['target']) ?>"><i class="fas fa-edit"></i></a>
                          <a href="<?= URL_SITIO ?>admin/menus/eliminar_item/<?= $i['id'] ?>/<?= $menu_id ?>" onclick="return confirm('¿Eliminar elemento?')" class="delete-item"><i class="fas fa-trash"></i></a>
                        </span>
                      </div>
                      <?php if(isset($hijos[$i['id']])): ?><ul class="submenu">
                        <?php foreach($hijos[$i['id']] as $c): ?>
                          <li class="menu-item" data-id="<?= $c['id'] ?>">
                            <div class="menu-item-bar">
                              <span class="item-title"><?= htmlspecialchars($c['titulo']) ?></span>
                              <span class="item-controls">
                                <a href="#" class="edit-item" data-id="<?= $c['id'] ?>" data-titulo="<?= htmlspecialchars($c['titulo']) ?>" data-url="<?= htmlspecialchars($c['url']) ?>" data-parent="<?= $c['parent_id'] ?>" data-orden="<?= $c['orden'] ?>" data-clase="<?= htmlspecialchars($c['clase']) ?>" data-target="<?= htmlspecialchars($c['target']) ?>"><i class="fas fa-edit"></i></a>
                                <a href="<?= URL_SITIO ?>admin/menus/eliminar_item/<?= $c['id'] ?>/<?= $menu_id ?>" onclick="return confirm('¿Eliminar elemento?')" class="delete-item"><i class="fas fa-trash"></i></a>
                              </span>
                            </div>
                          </li>
                        <?php endforeach; ?></ul><?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
/* Estilos para el panel de administración estilo Shopify */
.shopify-panel {
   display: flex;
   min-height: calc(100vh - 120px);
   background-color: #f9fafb;
   margin: -2rem -15px 0;
}

.panel-sidebar {
   width: 250px;
   background-color: #212b36;
   color: white;
   flex-shrink: 0;
}

.panel-sidebar-header {
   padding: 1.5rem;
   border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.panel-sidebar-header h3 {
   margin: 0;
   font-size: 1.2rem;
   font-weight: 600;
}

.panel-nav {
   padding: 1rem 0;
}

.panel-menu {
   list-style: none;
   padding: 0;
   margin: 0;
}

.panel-menu li {
   margin-bottom: 0.25rem;
}

.panel-menu li a {
   display: flex;
   align-items: center;
   padding: 0.75rem 1.5rem;
   color: rgba(255, 255, 255, 0.8);
   text-decoration: none;
}

.panel-menu li.active a {
   background-color: #1a73e8;
   color: white;
}

.panel-content {
   flex: 1;
   padding: 2rem;
   overflow-y: auto;
}

.panel-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 2rem;
}

.panel-columns {
   display: grid;
   grid-template-columns: 3fr 2fr;
   gap: 1.5rem;
}

.panel-column {
   display: flex;
   flex-direction: column;
   gap: 1.5rem;
}

.panel-card {
   background-color: white;
   border-radius: 8px;
   box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.panel-card-header {
   padding: 1.25rem 1.5rem;
   border-bottom: 1px solid #f0f0f0;
   background-color: #f9fafb;
}

.panel-card-body {
   padding: 1.5rem;
}

.alert-error {
   background-color: #fff0f0;
   border-left: 4px solid #ff4d4f;
}

.alert-success {
   background-color: #f6ffed;
   border-left: 4px solid #52c41a;
}

.shopify-form label {
   font-weight: 500;
}

.sortable-menu,
.submenu {
   list-style: none;
   padding: 0;
   margin: 0;
}

.menu-item-bar {
   background-color: #f9fafb;
   border: 1px solid #f0f0f0;
   cursor: move;
   padding: 0.75rem 1rem;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Mostrar/ocultar form elemento
    document.getElementById('btn-add-item')?.addEventListener('click', ()=>{
        document.getElementById('item-form-container').style.display = 'block';
    });
    document.getElementById('cancel-item-btn')?.addEventListener('click', ()=>{
        document.getElementById('item-form-container').style.display = 'none';
    });

    // Editar elemento
    document.querySelectorAll('.edit-item').forEach(btn => btn.addEventListener('click', e=>{
        e.preventDefault();
        const d = btn.dataset;
        document.getElementById('item-form-container').style.display = 'block';
        document.getElementById('edit_item_id').value = d.id;
        document.getElementById('titulo').value = d.titulo;
        document.getElementById('url').value = d.url;
        document.getElementById('parent_id').value = d.parent;
        document.getElementById('orden').value = d.orden;
        document.getElementById('clase').value = d.clase;
        document.getElementById('target').value = d.target;
    }));

    // Autocompletar producto
    document.getElementById('entity_id_product')?.addEventListener('change', e=>{
        const op = e.target.selectedOptions[0];
        document.getElementById('url').value = op.dataset.url;
    });

    // Sortable.js
    if (window.Sortable) {
        Sortable.create(document.getElementById('sortable'), {
            handle: '.menu-item-bar',
            animation: 150,
            onEnd: function(evt) {
                // TODO: AJAX para guardar orden
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
```
