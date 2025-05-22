<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Determinar si es edición o creación
$esEdicion = isset($_GET['id']) && is_numeric($_GET['id']);
$errores = [];
$menu = [
    'id' => 0,
    'nombre' => '',
    'descripcion' => ''
];

// Si es edición, obtener datos del menú
if ($esEdicion) {
    $menuModel = new Menu($conexion);
    $menu_id = (int)$_GET['id'];
    $datosMenu = $menuModel->obtenerPorId($menu_id);
    
    if (!$datosMenu) {
        mostrarMensaje('El menú no existe', 'error');
        redireccionar('admin/menus/listar');
        exit;
    }
    
    $menu = $datosMenu;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiarDato($_POST['nombre'] ?? '');
    $descripcion = limpiarDato($_POST['descripcion'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }
    
    // Si no hay errores, guardar
    if (empty($errores)) {
        $menuModel = new Menu($conexion);
        
        if ($esEdicion) {
            $ok = $menuModel->actualizar($menu['id'], $nombre, $descripcion);
        } else {
            $ok = $menuModel->crear($nombre, $descripcion);
        }
        
        if ($ok) {
            mostrarMensaje($esEdicion ? 'Menú actualizado correctamente' : 'Menú creado correctamente', 'success');
            redireccionar('admin/menus/listar');
            exit;
        } else {
            $errores[] = 'Error al guardar en la base de datos';
        }
    }
    
    // Repoblar formulario
    $menu['nombre'] = $nombre;
    $menu['descripcion'] = $descripcion;
}

// Título de la página
$titulo = $esEdicion ? "Editar Menú" : "Crear Menú";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container admin-form">
  <h1><?= $titulo ?></h1>
  
  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errores as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="form-group">
      <label for="nombre">Nombre del menú <span class="text-danger">*</span></label>
      <input type="text" id="nombre" name="nombre"
             value="<?= htmlspecialchars($menu['nombre']) ?>"
             class="form-control" required>
    </div>
    
    <div class="form-group">
      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion"
                class="form-control" rows="3"><?= htmlspecialchars($menu['descripcion']) ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">
      <?= $esEdicion ? 'Actualizar' : 'Crear' ?>
    </button>
    <a href="<?= URL_SITIO ?>modulos/admin/menus/listar.php" class="btn btn-outline">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>