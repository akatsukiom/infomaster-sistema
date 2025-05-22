<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
    exit;
}

// Instanciar modelo de menús
$menuModel = new Menu($conexion);
// Instanciar modelo de configuración
$settingModel = new Setting($conexion);

// Determinar la acción a realizar
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$menu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$idMenu = $menu_id; // Alias para compatibilidad con nuevo código

// Inicializar variables
$errores = [];
$exito = '';
$categorias = [];
$menu_items = [];
$menu_actual = [
    'id' => 0,
    'nombre' => '',
    'descripcion' => ''
];

// 1) Cargar siempre la configuración existente
$raw         = $settingModel->obtener('menu_config', '[]');
$flatConfig  = json_decode($raw, true) ?: [];

// 1b) Reagrupar por posición para que haya keys 'header', 'footer' y 'sidebar'
$menu_config = [
    'header'  => [],
    'footer'  => [],
    'sidebar' => [],
];
foreach ($flatConfig as $conf) {
    if (isset($conf['posicion']) && isset($menu_config[$conf['posicion']])) {
        $menu_config[$conf['posicion']][] = $conf;
    }
}


  // 2) Si vienen datos del formulario “configuración”
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'configuracion') {
      // A) Leer la tabla principal
      $menuIds         = $_POST['menu_ids']    ?? [];
      $posiciones      = $_POST['posiciones']  ?? [];
      $padres          = $_POST['padres']      ?? [];
      $ordenes         = $_POST['ordenes']     ?? [];
      $habilitadosPost = $_POST['habilitados'] ?? [];

      $newConfig = [];
      foreach ($menuIds as $i => $mid) {
          $newConfig[] = [
              'menu_id'   => (int)$mid,
              'posicion'  => $posiciones[$i],
              'parent_id' => ($padres[$i] !== '') ? (int)$padres[$i] : null,
              'orden'     => (int)$ordenes[$i],
              'habilitado'=> in_array($mid, $habilitadosPost) ? 1 : 0,
          ];
      }

      // B) Añadir los “select rápidos”
      foreach (['header','footer','sidebar'] as $pos) {
          if (!empty($_POST["rapido_{$pos}"])) {
              $newConfig[] = [
                  'menu_id'   => (int)$_POST["rapido_{$pos}"],
                  'posicion'  => $pos,
                  'parent_id' => null,
                  'orden'     => 1,
                  'habilitado'=> 1,
              ];
          }
      }

      // C) Guardar todo como JSON
      $settingModel->guardar('menu_config', json_encode($newConfig));

     // D) Guardar flags "mostrar en …" de manera explícita
$mostrar_header = isset($_POST['mostrar_header']) ? '1' : '0';
$mostrar_footer = isset($_POST['mostrar_footer']) ? '1' : '0';
$mostrar_sidebar = isset($_POST['mostrar_sidebar']) ? '1' : '0';

// Guardar en la base de datos
$settingModel->guardar('mostrar_menus_header', $mostrar_header);
$settingModel->guardar('mostrar_menus_footer', $mostrar_footer);
$settingModel->guardar('mostrar_menus_sidebar', $mostrar_sidebar);

// Para depuración
echo "<!-- Guardando mostrar_menus_header: $mostrar_header -->";
      // E) Redirigir para recargar con la nueva configuración
      header("Location: panel.php?accion=configuracion");
      exit;
  }



    // 4) ¡Sólo ordenar! (fuera del POST)
foreach (['header','footer','sidebar'] as $pos) {
    if (!empty($menu_config[$pos]) && is_array($menu_config[$pos])) {
        usort($menu_config[$pos], function($a,$b){
            return $a['orden'] - $b['orden'];
        });
    }
}
  
  
    

// Obtener categorías para selección rápida
try {
    $stmt = $conexion->prepare("SELECT id, nombre FROM categorias ORDER BY nombre");
    $stmt->execute();
    $categorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    // Silenciar error, no es crítico
}

// Inicializar variable para ítem a editar
$editItem = null;
if ($accion === 'editar_item' && isset($_GET['idMenu']) && isset($_GET['idItem'])) {
    $idMenu = (int)$_GET['idMenu'];
    $idItem = (int)$_GET['idItem'];
    
    // Verificar que el menú existe
    $menu_actual = $menuModel->obtenerPorId($idMenu) ?: $menu_actual;
    if ($menu_actual['id'] > 0) {
        // Obtener datos del ítem
        $menu_items = $menuModel->obtenerItems($idMenu);
        
        // Buscar el ítem específico
        foreach ($menu_items as $item) {
            if ($item['id'] == $idItem) {
                $editItem = $item;
                break;
            }
        }
        
        // Si se encontró el ítem, cambiar acción a editar para mostrar el formulario adecuado
        if ($editItem) {
            $accion = 'editar';
            $menu_id = $idMenu;
        } else {
            $errores[] = 'El elemento del menú no existe o no pertenece a este menú.';
        }
    } else {
        $errores[] = 'El menú especificado no existe.';
        $accion = 'listar'; // Redirigir a la lista de menús
    }
}

// Procesar acciones del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_menu'])) {
        // Guardar menú principal
        $nombre = limpiarDato($_POST['nombre'] ?? '');
        $descripcion = limpiarDato($_POST['descripcion'] ?? '');
        
        if (empty($nombre)) {
            $errores[] = 'El nombre del menú es obligatorio';
        } else {
            if ($menu_id > 0) {
                // Actualizar menú existente
                if ($menuModel->actualizar($menu_id, $nombre, $descripcion)) {
                    $exito = 'Menú actualizado correctamente';
                    $menu_actual['nombre'] = $nombre;
                    $menu_actual['descripcion'] = $descripcion;
                } else {
                    $errores[] = 'Error al actualizar el menú';
                }
            } else {
                // Crear nuevo menú
                $nuevo_id = $menuModel->crear($nombre, $descripcion);
                if ($nuevo_id) {
                    $exito = 'Menú creado correctamente';
                    $menu_id = $nuevo_id;
                    $menu_actual['id'] = $nuevo_id;
                    $menu_actual['nombre'] = $nombre;
                    $menu_actual['descripcion'] = $descripcion;
                    
                    // Redirigir a editar el nuevo menú
                    header("Location: " . URL_SITIO . "admin/menus/panel.php?accion=editar&id=" . $nuevo_id);
                    exit;
                } else {
                    $errores[] = 'Error al crear el menú';
                }
            }
        }
    } elseif (isset($_POST['guardar_item'])) {
        // Guardar item de menú
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
        $titulo = limpiarDato($_POST['titulo'] ?? '');
        $url = limpiarDato($_POST['url'] ?? '');
        $orden = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;
        $clase = limpiarDato($_POST['clase'] ?? '');
        $target = limpiarDato($_POST['target'] ?? '_self');
        
        if (empty($titulo) || empty($url)) {
            $errores[] = 'Título y URL son obligatorios';
        } else {
            if ($item_id > 0) {
                // Actualizar item existente
                if ($menuModel->actualizarItem($item_id, $titulo, $url, $parent_id, $orden, $clase, $target)) {
                    $exito = 'Elemento actualizado correctamente';
                } else {
                    $errores[] = 'Error al actualizar el elemento';
                }
            } else {
                // Crear nuevo item
                if ($menuModel->agregarItem($menu_id, $titulo, $url, $parent_id, $orden, $clase, $target)) {
                    $exito = 'Elemento agregado correctamente';
                } else {
                    $errores[] = 'Error al agregar el elemento';
                }
            }
        }
    } elseif (isset($_POST['guardar_orden'])) {
        // Guardar nuevo orden de items mediante AJAX
        header('Content-Type: application/json');
        
        $orden_items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Formato JSON inválido']);
            exit;
        }
        
        // Procesar recursivamente ítems del menú
        function procesarItems($items, $menu_id, $parent_id = 0, $orden_base = 0) {
            global $menuModel;
            $orden = $orden_base;
            
            foreach ($items as $item) {
                $id = (int)$item['id'];
                $titulo = isset($item['title']) ? trim($item['title']) : '';
                $url = isset($item['url']) ? trim($item['url']) : '#';
                
                if ($id > 0) {
                    // Actualizar ítem existente
                    $menuModel->actualizarItem($id, $titulo, $url, $parent_id, $orden);
                } else {
                    // Crear nuevo ítem
                    $id = $menuModel->agregarItem($menu_id, $titulo, $url, $parent_id, $orden);
                }
                
                // Procesar subítems si existen
                if (!empty($item['children']) && is_array($item['children'])) {
                    procesarItems($item['children'], $menu_id, $id, 0);
                }
                
                $orden++;
            }
        }
        
        try {
            procesarItems($orden_items, $menu_id);
            echo json_encode(['status' => 'ok', 'mensaje' => 'Orden guardado correctamente']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al guardar el orden: ' . $e->getMessage()]);
        }
        exit;
    } elseif (isset($_POST['eliminar_item'])) {
        // Eliminar ítem de menú
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        
        if ($item_id > 0) {
            if ($menuModel->eliminarItem($item_id)) {
                $exito = 'Elemento eliminado correctamente';
            } else {
                $errores[] = 'Error al eliminar el elemento';
            }
        }
    }
    
    // Manejar la actualización de ítem mediante el modal
    if (isset($_GET['accion']) && ($_GET['accion'] === 'editar_item' || $_GET['accion'] === 'guardar_item')) {
        $idItem = isset($_POST['idItem']) ? (int)$_POST['idItem'] : 0;
        $idMenu = isset($_GET['idMenu']) ? (int)$_GET['idMenu'] : $menu_id;
        $title = $_POST['title'] ?? '';
        $url = $_POST['url'] ?? '';
        $parent = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
        $orden = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;
        
        if ($idItem > 0) {
            // Actualizar ítem existente
            if ($menuModel->actualizarItem($idItem, $title, $url, $parent, $orden)) {
                $exito = 'Elemento actualizado correctamente';
            } else {
                $errores[] = 'Error al actualizar el elemento';
            }
        } else {
            // Crear nuevo ítem
            if ($menuModel->agregarItem($idMenu, $title, $url, $parent, $orden)) {
                $exito = 'Elemento agregado correctamente';
            } else {
                $errores[] = 'Error al agregar el elemento';
            }
        }
        
        // Redirigir a la página de edición del menú
        header("Location: panel.php?accion=editar&id=$idMenu");
        exit;
    }
}

// Procesar acciones específicas
if ($accion === 'eliminar' && $menu_id > 0) {
    if ($menuModel->eliminar($menu_id)) {
        $_SESSION['mensaje'] = 'Menú eliminado correctamente';
        $_SESSION['tipo_mensaje'] = 'success';
        header("Location: " . URL_SITIO . "admin/menus/panel.php");
        exit;
    } else {
        $errores[] = 'Error al eliminar el menú';
    }
} elseif ($accion === 'editar' && $menu_id > 0) {
    // Cargar datos del menú y sus ítems
    $menu_actual = $menuModel->obtenerPorId($menu_id) ?: $menu_actual;
    $menu_items = $menuModel->obtenerItems($menu_id);
} elseif ($accion === 'configuracion') {
    // Cargar datos para la configuración
    $allMenus = $menuModel->obtenerTodos();
    
    // Obtener configuración actual de menús
    $menu_config_json = $settingModel->obtener('menu_config', '{}');
    $menu_config = json_decode($menu_config_json, true);
    
    // Si no hay configuración o está corrupta, inicializar como array vacío
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($menu_config)) {
        $menu_config = [];
    }
    
    // Para compatibilidad con código antiguo
    $currentMenu = $settingModel->obtener('menu_principal_id', '1');
    
    // Si no hay configuración de header pero hay menú principal, crearlo
    if (empty($menu_config['header']) && $currentMenu > 0) {
        $menu_config['header'] = [
            [
                'menu_id' => (int)$currentMenu,
                'orden' => 0,
                'clase' => '',
                'parent_id' => 0
            ]
        ];
    }

   // Reformatear los datos para configuracion.php
$menuConfigFormatted = [];

// Procesar menús para cada posición
foreach (['header', 'footer', 'sidebar'] as $posicion) {
    if (isset($menu_config[$posicion]) && is_array($menu_config[$posicion])) {
        foreach ($menu_config[$posicion] as $item) {
            $menuConfigFormatted[] = [
                'menu_id' => $item['menu_id'],
                'posicion' => $posicion,
                'padre_id' => $item['parent_id'] ?? 0, // Nota: cambiado de parent_id a padre_id para coincidir con lo que espera configuracion.php
                'orden' => $item['orden'] ?? 0,
                'habilitado' => 1
            ];
        }
    }
}

// Definir variables necesarias para configuracion.php
$menuConfig = $menuConfigFormatted;
$posicionesMenu = ['header', 'footer', 'sidebar'];

// Capturar el contenido de configuracion.php en lugar de incluirlo directamente
ob_start();
include __DIR__ . '/views/configuracion.php';
$contenido_config = ob_get_clean();

}
// Cargar todos los menús para el listado
$menus = $menuModel->obtenerTodos();

// Título de la página
$titulo = "Gestión de Menús - " . ucfirst($accion);
include __DIR__ . '/../../../includes/header.php';
?>

<!-- Incluir jQuery y Nestable para drag and drop -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Nestable/2012-10-15/jquery.nestable.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Incluir Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Estilos CSS para el panel -->
<style>
/* Estilos generales */
:root {
    --primary-color: #3a86ff;
    --secondary-color: #ff006e;
    --dark-color: #333;
    --success-color: #2ecc71;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --light-gray: #f1f1f1;
    --border-color: #ddd;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.panel-container {
    display: flex;
    min-height: calc(100vh - 100px);
    max-width: 1400px;
    margin: 0 auto;
    background-color: #f9f9f9;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

/* Sidebar */
.panel-sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: white;
    padding: 20px 0;
    position: sticky;
    top: 0;
    height: 100%;
}

.sidebar-header {
    padding: 0 20px 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h3 {
    font-size: 20px;
    margin: 0;
}

.panel-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.panel-menu li {
    margin-bottom: 5px;
}

.panel-menu a {
    display: block;
    padding: 12px 20px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s;
}

.panel-menu a:hover, 
.panel-menu a.active {
    background-color: rgba(255,255,255,0.1);
    color: white;
}

.panel-menu a.active {
    border-left: 4px solid var(--primary-color);
}

.panel-menu a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

/* Contenido principal */
.panel-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.panel-title {
    font-size: 24px;
    margin: 0;
    color: var(--dark-color);
}

.panel-actions {
    display: flex;
    gap: 10px;
}

/* Tarjetas */
.card {
    background-color: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
    background-color: #f5f5f5;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    color: var(--dark-color);
}

.card-body {
    padding: 20px;
}

/* Formularios */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border-radius: 4px;
    border: 1px solid var(--border-color);
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(58, 134, 255, 0.2);
}

.form-row {
    display: flex;
    margin: 0 -10px;
}

.form-col {
    flex: 1;
    padding: 0 10px;
}

/* Tablas */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, 
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table th {
    background-color: var(--light-gray);
    font-weight: 600;
}

.data-table tr:hover {
    background-color: rgba(0,0,0,0.02);
}

.data-table .actions {
    display: flex;
    gap: 10px;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: #2a75eb;
}

.btn-success {
    background-color: var(--success-color);
    color: white;
}

.btn-success:hover {
    background-color: #27ae60;
}

.btn-danger {
    background-color: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background-color: #c0392b;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid var(--border-color);
    color: var(--dark-color);
}

.btn-outline:hover {
    background-color: var(--light-gray);
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Alertas */
.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Nestable (drag & drop) */
.dd {
    position: relative;
    display: block;
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 13px;
    line-height: 20px;
}

.dd-list {
    display: block;
    position: relative;
    margin: 0;
    padding: 0;
    list-style: none;
}

.dd-item, 
.dd-empty, 
.dd-placeholder {
    display: block;
    position: relative;
    margin: 0;
    padding: 0;
    min-height: 20px;
    font-size: 13px;
    line-height: 20px;
}

.dd-handle {
    display: block;
    margin: 5px 0;
    padding: 10px 10px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    border: 1px solid #ccc;
    background: #fafafa;
    border-radius: 3px;
    box-sizing: border-box;
    cursor: move;
}

.dd-handle:hover {
    background: #fff;
}

.dd-item > button {
    position: relative;
    cursor: pointer;
    float: left;
    width: 25px;
    height: 20px;
    margin: 5px 0;
    padding: 0;
    text-indent: 100%;
    white-space: nowrap;
    overflow: hidden;
    border: 0;
    background: transparent;
    font-size: 12px;
    line-height: 1;
    text-align: center;
    font-weight: bold;
}

.dd-item > button:before {
    display: block;
    position: absolute;
    width: 100%;
    text-align: center;
    text-indent: 0;
}

.dd-item > button.dd-expand:before {
    content: '+';
}

.dd-item > button.dd-collapse:before {
    content: '-';
}

.dd-placeholder,
.dd-empty {
    margin: 5px 0;
    padding: 0;
    min-height: 30px;
    background: #f2fbff;
    border: 1px dashed #b6bcbf;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
}

.dd-empty {
    border: 1px dashed #bbb;
    min-height: 100px;
    background-color: #e5e5e5;
}

.dd-dragel {
    position: absolute;
    pointer-events: none;
    z-index: 9999;
}

.dd-dragel > .dd-item .dd-handle {
    margin-top: 0;
}

.dd3-content {
    display: block;
    margin: 5px 0;
    padding: 10px 10px 10px 40px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    border: 1px solid #ccc;
    background: #fafafa;
    border-radius: 3px;
    box-sizing: border-box;
    cursor: default;
}

.dd-item-actions {
    position: absolute;
    right: 10px;
    top: 7px;
}

.dd-item-actions a {
    margin-left: 5px;
    color: #666;
}

.dd-item-actions a:hover {
    color: #000;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    overflow: auto;
}

.modal-content {
    position: relative;
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    width: 50%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal-header {
    padding-bottom: 15px;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    position: relative;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-close {
    position: absolute;
    right: 0;
    top: 0;
    font-size: 24px;
    cursor: pointer;
    color: #aaa;
    transition: color 0.3s;
}

.modal-close:hover {
    color: black;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

/* Estilos para la configuración de múltiples menús */
.menu-assignment {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.menu-assignment h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #343a40;
    font-size: 18px;
}

.menu-item-row {
    background-color: white;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    position: relative;
}

.menu-item-row .btn-remove {
    position: absolute;
    right: 10px;
    top: 10px;
}

.menu-config-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .panel-container {
        flex-direction: column;
    }
    
    .panel-sidebar {
        width: 100%;
        position: static;
    }
    
    .form-row {
        flex-direction: column;
    }
    
    .form-col {
        margin-bottom: 15px;
    }
    
    .modal-content {
        width: 90%;
    }
}
</style>

<div class="panel-container">
    <!-- Sidebar -->
    <div class="panel-sidebar">
        <div class="sidebar-header">
            <h3>Panel de menús</h3>
        </div>
        
        <ul class="panel-menu">
            <li><a href="<?= URL_SITIO ?>admin/menus/panel.php" class="<?= $accion === 'listar' ? 'active' : '' ?>"><i class="fas fa-list"></i> Todos los menús</a></li>
            <li><a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=nuevo" class="<?= $accion === 'nuevo' ? 'active' : '' ?>"><i class="fas fa-plus"></i> Nuevo menú</a></li>
            <li><a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=configuracion" class="<?= $accion === 'configuracion' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="<?= URL_SITIO ?>admin/panel" class="<?= $accion === 'panel' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Panel principal</a></li>
        </ul>
    </div>

    <!-- Contenido principal -->
    <div class="panel-content">
        <!-- Encabezado -->
        <div class="panel-header">
            <h1 class="panel-title">
                <?php 
                switch($accion) {
                    case 'nuevo':
                        echo 'Crear nuevo menú';
                        break;
                    case 'editar':
                        echo 'Editar menú: ' . htmlspecialchars($menu_actual['nombre']);
                        break;
                    case 'configuracion':
                        echo 'Configuración de menús';
                        break;
                    default:
                        echo 'Gestión de menús';
                }
                ?>
            </h1>
            <div class="panel-actions">
                <?php if ($accion === 'listar'): ?>
                    <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=nuevo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo menú
                    </a>
                <?php elseif ($accion === 'editar' || $accion === 'nuevo' || $accion === 'configuracion'): ?>
                    <a href="<?= URL_SITIO ?>admin/menus/panel.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mensajes de error/éxito -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errores as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($exito) ?></p>
            </div>
        <?php endif; ?>

        <!-- Contenido según la acción -->
        <?php if ($accion === 'configuracion'): ?>
            <!-- Mostrar el contenido de configuración -->
            <div class="card">
                <div class="card-body">
                    <?php echo $contenido_config; ?>
                </div>
            </div>
        <?php elseif ($accion === 'listar'): ?>
            <!-- Listado de menús -->
            <div class="card">
                <div class="card-header">
                    <h2>Menús disponibles</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($menus)): ?>
                        <p>No hay menús registrados en el sistema. <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=nuevo">Crea tu primer menú</a>.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Items</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menus as $menu): ?>
                                    <tr>
                                        <td><?= $menu['id'] ?></td>
                                        <td><?= htmlspecialchars($menu['nombre']) ?></td>
                                        <td><?= htmlspecialchars($menu['descripcion']) ?></td>
                                        <td>
                                            <?php 
                                            $count = count($menuModel->obtenerItems($menu['id']));
                                            echo $count . ' ' . ($count === 1 ? 'elemento' : 'elementos');
                                            ?>
                                        </td>
                                        <td class="actions">
                                            <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=editar&id=<?= $menu['id'] ?>" class="btn btn-primary btn-icon" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= URL_SITIO ?>admin/menus/panel.php?accion=eliminar&id=<?= $menu['id'] ?>" class="btn btn-danger btn-icon" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este menú? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($accion === 'nuevo' || $accion === 'editar'): ?>
            <!-- Formulario para crear/editar menú -->
            <div class="card">
                <div class="card-header">
                    <h2><?= $accion === 'editar' ? 'Editar menú' : 'Crear nuevo menú' ?></h2>
                </div>
                <div class="card-body">
                    <form action="<?= URL_SITIO ?>admin/menus/panel.php?accion=<?= $accion ?><?= $menu_id ? '&id='.$menu_id : '' ?>" method="post">
                        <div class="form-group">
                            <label for="nombre">Nombre del menú</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($menu_actual['nombre']) ?>" required>
                            <small>Este nombre será utilizado para identificar el menú en el sistema.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($menu_actual['descripcion']) ?></textarea>
                            <small>Añade una descripción que te ayude a identificar el propósito de este menú.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="guardar_menu" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $accion === 'editar' ? 'Actualizar menú' : 'Crear menú' ?>
                            </button>
                            
                            <a href="<?= URL_SITIO ?>admin/menus/panel.php" class="btn btn-outline">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($accion === 'editar' && $menu_id > 0): ?>
                <!-- Gestión de elementos del menú -->
                <div class="card">
                    <div class="card-header">
                        <h2>Elementos del menú</h2>
                        <button id="btn-add-item" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                            <i class="fas fa-plus"></i> Añadir elemento
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Sistema de arrastrar y soltar para ordenar elementos -->
                        <div class="dd" id="nestable">
                            <ol class="dd-list">
                                <?php if (empty($menu_items)): ?>
                                    <p>Este menú no tiene elementos. Usa el botón "Añadir elemento" para comenzar a crear tu menú.</p>
                                <?php else: ?>
                                    <?php
                                    // Organizar items en una estructura jerárquica
                                    $items_tree = [];
                                    foreach ($menu_items as $item) {
                                        $item['children'] = [];
                                        $items_tree[$item['id']] = $item;
                                    }
                                    
                                    $root_items = [];
                                    foreach ($items_tree as $id => $item) {
                                        if ($item['parent_id'] == 0) {
                                            $root_items[$id] = &$items_tree[$id];
                                        } else {
                                            if (isset($items_tree[$item['parent_id']])) {
                                                $items_tree[$item['parent_id']]['children'][$id] = &$items_tree[$id];
                                            }
                                        }
                                    }
                                    
                                    // Función recursiva para mostrar items
                                    function mostrarItems($items) {
                                        $html = '';
                                        foreach ($items as $item) {
                                            $html .= '<li class="dd-item" data-id="' . $item['id'] . '" data-title="' . htmlspecialchars($item['titulo']) . '" data-url="' . htmlspecialchars($item['url']) . '">';
                                            $html .= '<div class="dd-handle">' . htmlspecialchars($item['titulo']) . '</div>';
                                            $html .= '<div class="dd-item-actions">';
                                            $html .= '<a href="#" class="btn-edit-item" data-bs-toggle="modal" data-bs-target="#itemModal" data-id="' . $item['id'] . '" data-title="' . htmlspecialchars($item['titulo']) . '" data-url="' . htmlspecialchars($item['url']) . '" data-parent="' . $item['parent_id'] . '" data-order="' . $item['orden'] . '" title="Editar"><i class="fas fa-edit"></i></a>';
                                            $html .= '<a href="#" class="btn-delete-item" data-id="' . $item['id'] . '" title="Eliminar"><i class="fas fa-trash"></i></a>';
                                            $html .= '</div>';
                                            
                                            if (!empty($item['children'])) {
                                                $html .= '<ol class="dd-list">';
                                                $html .= mostrarItems($item['children']);
                                                $html .= '</ol>';
                                            }
                                            
                                            $html .= '</li>';
                                        }
                                        return $html;
                                    }
                                    
                                    echo mostrarItems($root_items);
                                    ?>
                                <?php endif; ?>
                            </ol>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="form-group" style="margin-top: 20px;">
                            <button id="btn-save-order" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar orden
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal para añadir/editar elementos (Bootstrap 5) -->
                <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="itemModalLabel">Añadir elemento al menú</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Aquí va el código del formulario modal -->
                                <form id="itemForm" action="panel.php?accion=guardar_item&idMenu=<?= $idMenu ?>" method="post">
                                    <input type="hidden" id="item-id" name="idItem" value="">
                                    
                                    <div class="form-group mb-3">
                                        <label for="item-title">Título</label>
                                        <input type="text" id="item-title" name="title" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="item-url">URL</label>
                                        <input type="text" id="item-url" name="url" class="form-control" required>
                                        
                                        <!-- Enlaces rápidos -->
                                        <div class="mt-2">
                                            <small>Enlaces rápidos:</small>
                                            <div class="btn-group mt-1">
                                                <a href="#" class="btn btn-sm btn-outline-secondary quick-link" data-url="<?= URL_SITIO ?>">Inicio</a>
                                                <a href="#" class="btn btn-sm btn-outline-secondary quick-link" data-url="<?= URL_SITIO ?>productos">Productos</a>
                                                <a href="#" class="btn btn-sm btn-outline-secondary quick-link" data-url="<?= URL_SITIO ?>contacto">Contacto</a>
                                                <a href="#" class="btn btn-sm btn-outline-secondary quick-link" data-url="#">Enlace vacío</a>
                                            </div>
                                        </div>
                                        
                                        <!-- Selector de categorías -->
                                        <?php if (!empty($categorias)): ?>
                                        <div class="mt-3">
                                            <label for="categoria_link">Seleccionar categoría:</label>
                                            <select id="categoria_link" class="form-select">
                                                <option value="">-- Seleccionar categoría --</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?= URL_SITIO ?>productos?categoria=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="item-parent">Elemento padre</label>
                                        <select id="item-parent" name="parent_id" class="form-select">
                                            <option value="0">Ninguno (elemento raíz)</option>
                                            <?php foreach ($menu_items as $item): ?>
                                                <?php if ($editItem && $item['id'] == $editItem['id']) continue; // No mostrar el elemento actual como padre ?>
                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['titulo']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="item-order">Orden</label>
                                        <input type="number" id="item-order" name="orden" class="form-control" value="0" min="0">
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btn-submit-item">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para confirmar eliminación (Bootstrap 5) -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>¿Estás seguro de que deseas eliminar este elemento del menú? Esta acción no se puede deshacer.</p>
                                <p><strong>Nota:</strong> Si este elemento tiene subelementos, estos también serán eliminados.</p>
                                <form id="delete-form" method="post">
                                    <input type="hidden" name="eliminar_item" value="1">
                                    <input type="hidden" id="delete-item-id" name="item_id" value="0">
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="btn-confirm-delete">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scripts para gestión de menús -->
                <script>
                $(document).ready(function() {
                    // Inicializar Nestable para drag and drop
                    var updateOutput = function(e) {
                        var list = e.length ? e : $(e.target);
                        if (window.JSON) {
                            console.log(window.JSON.stringify(list.nestable('serialize')));
                        }
                    };

                    $('#nestable').nestable({
                        group: 1,
                        maxDepth: 3
                    }).on('change', updateOutput);

                    // Bootstrap Modal para editar ítem
                    var itemModal = document.getElementById('itemModal');
                    if (itemModal) {
                        itemModal.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            var form = document.getElementById('itemForm');
                            
                            // Si el botón tiene data-id, es edición
                            if (button.dataset.id) {
                                form.action = 'panel.php?accion=editar_item&idMenu=<?= $idMenu ?>';
                                document.getElementById('item-id').value = button.dataset.id;
                                document.getElementById('item-title').value = button.dataset.title;
                                document.getElementById('item-url').value = button.dataset.url;
                                document.getElementById('item-parent').value = button.dataset.parent;
                                document.getElementById('item-order').value = button.dataset.order;
                            } else {
                                // Si abrimos con el botón "Añadir elemento"
                                form.action = 'panel.php?accion=guardar_item&idMenu=<?= $idMenu ?>';
                                form.reset();
                                document.getElementById('item-id').value = '';
                            }
                        });
                    }

                    // Submit del formulario
                    $('#btn-submit-item').click(function() {
                        $('#itemForm').submit();
                    });

                    // Enlaces rápidos
                    $('.quick-link').click(function() {
                        $('#item-url').val($(this).data('url'));
                    });

                    // Selección de categoría
                    $('#categoria_link').change(function() {
                        const url = $(this).val();
                        if (url) {
                            $('#item-url').val(url);
                        }
                    });

                    // Eliminar ítem
                    $(document).on('click', '.btn-delete-item', function(e) {
                        e.preventDefault();
                        const itemId = $(this).data('id');
                        $('#delete-item-id').val(itemId);
                        
                        // Abrir modal de Bootstrap
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        deleteModal.show();
                    });

                    // Confirmar eliminación
                    $('#btn-confirm-delete').click(function() {
                        $('#delete-form').submit();
                    });

                    // Guardar orden
                    $('#btn-save-order').click(function() {
                        const serialized = $('#nestable').nestable('serialize');
                        
                        $.ajax({
                            url: '<?= URL_SITIO ?>admin/menus/panel.php?accion=editar&id=<?= $menu_id ?>',
                            type: 'POST',
                            data: {
                                guardar_orden: 1,
                                items: JSON.stringify(serialized)
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'ok') {
                                    // Mostrar mensaje de éxito
                                    alert('Orden guardado correctamente');
                                } else {
                                    // Mostrar error
                                    alert('Error al guardar el orden: ' + response.mensaje);
                                }
                            },
                            error: function() {
                                alert('Error de conexión al guardar el orden');
                            }
                        });
                    });
                });
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

