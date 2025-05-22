<?php
// modulos/admin/productos/crear_editar.php

// Mostrar errores en desarrollo (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Inclusión segura
define('ACCESO_PERMITIDO', true);

// 2) Carga configuración y funciones generales
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// 3) Verificar acceso de administrador
if (!estaLogueado() || !esAdmin()) {
    mostrarMensaje("Acceso restringido", 'error');
    redireccionar('login');
    exit;
}

// 4) Cargar modelo de productos
require_once __DIR__ . '/modelo.php';
$productoModel = new Producto($conexion);

// 5) Cargar categorías para el select
$categorias = [];
$resCat = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre");
if ($resCat) {
    $categorias = $resCat->fetch_all(MYSQLI_ASSOC);
}

// 6) Inicializar variables
$esEdicion = isset($_GET['id']) && is_numeric($_GET['id']);
$errores = [];
$imagenExistente = '';
$categoriaPrefijada = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$categoria_nueva = isset($_POST['nueva_categoria_id']) ? (int)$_POST['nueva_categoria_id'] : 0;

$producto = [
    'id'              => 0,
    'categoria_id'    => '',
    'nombre'          => '',
    'descripcion'     => '',
    'precio_base'     => '', // Precio mensual base
    'precio_1_mes'    => '', // Alias para precio_base para consistencia en el formulario
    'precio_completo' => '',
    'precio_3_meses'  => '',
    'precio_12_meses' => '',
    'stock'           => 0,
    'imagen'          => ''
];

// 7) Si es edición, cargar datos existentes
if ($esEdicion) {
    $id = (int) $_GET['id'];
    $datosProducto = $productoModel->obtenerPorId($id);
    if (!$datosProducto) {
        mostrarMensaje('El producto no existe', 'error');
       redireccionar('admin/productos/listar');
        exit;
    }
    $producto = $datosProducto;
    // Asegurarse de que todos los campos existan
    $producto['precio_1_mes'] = $producto['precio_base'] ?? '';
    $producto['precio_completo'] = $producto['precio_completo'] ?? '';
    $producto['precio_3_meses'] = $producto['precio_3_meses'] ?? '';
    $producto['precio_12_meses'] = $producto['precio_12_meses'] ?? '';
    $imagenExistente = $producto['imagen'];
}

// 7.1) Procesar nueva categoría si se envió
if (isset($_POST['crear_categoria']) && !empty($_POST['nueva_categoria'])) {
    $nombreCategoria = limpiarDato($_POST['nueva_categoria']);
    $stmt = $conexion->prepare("INSERT INTO categorias (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombreCategoria);
    
    if ($stmt->execute()) {
        $categoria_nueva = $conexion->insert_id;
        mostrarMensaje("Categoría creada correctamente", 'success');
        
        // Recargar categorías
        $resCat = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre");
        if ($resCat) {
            $categorias = $resCat->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        $errores[] = 'Error al crear la categoría: ' . $conexion->error;
    }
}

// 8) Procesar formulario (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['crear_categoria'])) {
    // 8.1) Leer y sanitizar campos
    $datos = [
        'categoria_id'    => (int) ($_POST['categoria'] ?? 0),
        'nombre'          => limpiarDato($_POST['nombre'] ?? ''),
        'descripcion'     => limpiarDato(strip_tags($_POST['descripcion'] ?? '')),
        'precio_base'     => (float) ($_POST['precio_1_mes'] ?? 0),
        'precio_completo' => (float) ($_POST['precio_completo'] ?? 0),
        'precio_3_meses'  => (float) ($_POST['precio_3_meses'] ?? 0),
        'precio_12_meses' => (float) ($_POST['precio_12_meses'] ?? 0),
        'stock'           => (int) ($_POST['stock'] ?? 0),
        'imagen'          => $imagenExistente, // Mantener imagen existente por defecto
    ];

    // 8.2) Subir imagen si se envía
    if (!empty($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp     = $_FILES['imagen']['tmp_name'];
        $dirImg  = __DIR__ . '/../../../img/productos/';
        if (!is_dir($dirImg)) mkdir($dirImg, 0755, true);
        $nombreUnico = uniqid('prod_', true) . '-' . basename($_FILES['imagen']['name']);
        $destino     = $dirImg . $nombreUnico;
        if (move_uploaded_file($tmp, $destino)) {
            $datos['imagen'] = 'img/productos/' . $nombreUnico;
            
            // Si hay una imagen existente y se subió una nueva, eliminar la anterior
            if ($imagenExistente && $imagenExistente !== $datos['imagen']) {
                $rutaAnterior = __DIR__ . '/../../../' . ltrim($imagenExistente, '/');
                if (file_exists($rutaAnterior)) {
                    @unlink($rutaAnterior);
                }
            }
        }
    }

    // 8.3) Validaciones mínimas
    if ($datos['categoria_id'] <= 0)  $errores[] = 'Selecciona una categoría.';
    if ($datos['nombre'] === '')      $errores[] = 'El nombre es obligatorio.';
    if ($datos['precio_base'] <= 0)   $errores[] = 'El precio mensual debe ser mayor que 0.';

    // 8.4) Guardar si no hay errores
    if (empty($errores)) {
        if ($esEdicion) {
            $ok = $productoModel->actualizar($id, $datos);
        } else {
            $ok = $productoModel->crear($datos);
        }
        if ($ok) {
            mostrarMensaje($esEdicion ? 'Producto actualizado' : 'Producto creado', 'success');
redireccionar('admin/productos/listar');
            exit;
        }
        $errores[] = 'Error al guardar en la base de datos.';
    }
}

// 9) Preparar título e incluir header
$titulo = $esEdicion
    ? 'Editar Producto: ' . htmlspecialchars($producto['nombre'], ENT_QUOTES)
    : 'Crear Nuevo Producto';
include __DIR__ . '/../../../includes/header.php';
?>

<style>
  :root {
    --primary-color: #3b88ff;
    --primary-hover: #2a75eb;
    --secondary-color: #6c757d;
    --secondary-hover: #5a6268;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --light-gray: #f8f9fa;
    --border-color: #ced4da;
    --text-color: #333;
    --text-muted: #6c757d;
  }

  /* Estilos del contenedor principal */
  .admin-container {
    max-width: 900px;
    margin: 20px auto;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 25px;
  }

  /* Estilos de encabezado y títulos */
  .admin-header {
    margin-bottom: 25px;
  }

  .admin-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--text-color);
    margin: 0;
  }

  /* Estilos de formulario */
  .admin-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  .admin-form-group {
    margin-bottom: 0;
  }

  .admin-form-group.full-width {
    grid-column: 1 / 3;
  }

  .admin-form label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--text-color);
    font-size: 14px;
  }

  .admin-form input[type="text"],
  .admin-form input[type="number"],
  .admin-form textarea,
  .admin-form select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 14px;
  }

  .admin-form input[type="text"]:focus,
  .admin-form input[type="number"]:focus,
  .admin-form textarea:focus,
  .admin-form select:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 136, 255, 0.15);
  }

  .admin-form textarea {
    resize: vertical;
    min-height: 100px;
  }

  .admin-form .required::after {
    content: '*';
    color: var(--danger-color);
    margin-left: 3px;
  }

  .admin-form .input-group {
    display: flex;
    align-items: center;
  }

  .admin-form .input-group .currency-symbol {
    margin-right: 8px;
    font-weight: 500;
    color: #555;
  }

  .admin-form .input-help {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
  }

  /* Estilo para agregar categoría */
  .nueva-categoria {
    margin-top: 10px;
    display: none;
  }
  
  .nueva-categoria.active {
    display: flex;
    align-items: flex-end;
    gap: 10px;
  }
  
  .nueva-categoria input {
    flex-grow: 1;
  }

  /* Estilos de botones y acciones */
  .admin-actions {
    margin-top: 25px;
    padding-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  .admin-btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    border: none;
    font-size: 14px;
  }

  .admin-btn-primary {
    background-color: var(--primary-color);
    color: white;
  }

  .admin-btn-primary:hover {
    background-color: var(--primary-hover);
  }

  .admin-btn-secondary {
    background-color: var(--secondary-color);
    color: white;
  }

  .admin-btn-secondary:hover {
    background-color: var(--secondary-hover);
  }

  /* Estilos de alertas */
  .admin-alert {
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 20px;
  }

  .admin-alert-danger {
    background-color: #ffeaec;
    border: 1px solid #ffd0d5;
    color: #d32f2f;
  }

  .admin-alert ul {
    margin: 0;
    padding-left: 20px;
  }

  /* Estilos de secciones y títulos */
  .section-title {
    font-size: 16px;
    font-weight: 600;
    margin: 15px 0 10px;
    color: var(--text-color);
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
  }

  /* Estilos de tarjetas de precios */
  .precio-card {
    border: 1px solid #eaeaea;
    border-radius: 6px;
    padding: 15px;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.2s;
    height: 100%;
  }
  
  .precio-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  
  .precio-card h4 {
    font-size: 16px;
    margin-top: 0;
    margin-bottom: 12px;
    font-weight: 600;
    color: var(--text-color);
  }

  /* Estilos de carga de archivos */
  .file-upload {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
  }

  .file-upload-label {
    display: block;
    padding: 10px 15px;
    background-color: #f8f9fa;
    border: 1px dashed var(--border-color);
    border-radius: 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
  }

  .file-upload-label:hover {
    background-color: #e9ecef;
  }

  .file-upload input[type="file"] {
    opacity: 0;
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  .file-name {
    margin-top: 5px;
    display: block;
    font-size: 0.85rem;
    color: var(--text-muted);
  }

  /* Estilos para previsualización de imagen */
  .img-preview {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .img-preview img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 4px;
    border: 1px solid var(--border-color);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .admin-form {
      grid-template-columns: 1fr;
    }
    
    .admin-form-group.full-width {
      grid-column: auto;
    }

    .admin-actions {
      flex-direction: column;
    }
    
    .admin-actions button {
      width: 100%;
    }
  }
</style>

<div class="admin-container">
  <div class="admin-header">
    <h1 class="admin-title"><?= htmlspecialchars($titulo, ENT_QUOTES) ?></h1>
  </div>
  
  <?php if (!empty($errores)): ?>
    <div class="admin-alert admin-alert-danger">
      <ul>
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Formulario para crear nueva categoría -->
  <div class="nueva-categoria <?= isset($_POST['crear_categoria']) ? 'active' : '' ?>" id="form-nueva-categoria">
    <form method="POST" class="admin-form" style="display: flex; width: 100%;">
      <div style="flex-grow: 1;">
        <input type="text" name="nueva_categoria" placeholder="Nombre de la nueva categoría" required>
      </div>
      <div>
        <button type="submit" name="crear_categoria" class="admin-btn admin-btn-primary">Crear Categoría</button>
      </div>
    </form>
  </div>

  <!-- Formulario principal -->
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <!-- Categoría -->
    <div class="admin-form-group full-width">
      <label for="categoria" class="required">Categoría</label>
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <div style="flex-grow: 1;">
          <select id="categoria" name="categoria" required>
            <option value="">-- Selecciona --</option>
            <?php foreach($categorias as $cat): 
                // si vino ?categoria=xx, o si ya está en el producto, o si la acabas de crear
                $sel = ($cat['id'] === $categoriaPrefijada
                     || $cat['id'] === $producto['categoria_id']
                     || $cat['id'] === $categoria_nueva)
                     ? ' selected' : '';
            ?>
            <option value="<?= $cat['id'] ?>"<?= $sel ?>>
              <?= htmlspecialchars($cat['nombre'], ENT_QUOTES) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <button type="button" id="btn-nueva-categoria" class="admin-btn admin-btn-secondary">
            Nueva Categoría
          </button>
        </div>
      </div>
    </div>

    <!-- Nombre y descripción -->
    <div class="admin-form-group full-width">
      <label for="nombre" class="required">Nombre del producto</label>
      <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES) ?>">
    </div>
    
    <div class="admin-form-group full-width">
      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES) ?></textarea>
    </div>

    <!-- Sección de precios -->
    <div class="admin-form-group full-width">
      <h3 class="section-title">Información de precios</h3>
    </div>

    <!-- Plan mensual (individual) -->
    <div class="admin-form-group">
      <div class="precio-card">
        <h4>Plan mensual (individual)</h4>
        <label for="precio_1_mes" class="required">1 mes (<?= MONEDA ?>)</label>
        <div class="input-group">
          <span class="currency-symbol"><?= MONEDA ?></span>
          <input type="number" step="0.01" id="precio_1_mes" name="precio_1_mes" required value="<?= htmlspecialchars($producto['precio_1_mes'] ?: $producto['precio_base'], ENT_QUOTES) ?>">
        </div>
        <div class="input-help">Precio para la suscripción mensual individual</div>
      </div>
    </div>

    <!-- Plan cuenta completa -->
    <div class="admin-form-group">
      <div class="precio-card">
        <h4>Plan cuenta completa</h4>
        <label for="precio_completo" class="required">1 mes (<?= MONEDA ?>)</label>
        <div class="input-group">
          <span class="currency-symbol"><?= MONEDA ?></span>
          <input type="number" step="0.01" id="precio_completo" name="precio_completo" required value="<?= htmlspecialchars($producto['precio_completo'], ENT_QUOTES) ?>">
        </div>
        <div class="input-help">Precio mensual para cuenta completa</div>
      </div>
    </div>

    <!-- Plan trimestral -->
    <div class="admin-form-group">
      <div class="precio-card">
        <h4>Plan trimestral</h4>
        <label for="precio_3_meses">3 meses (<?= MONEDA ?>)</label>
        <div class="input-group">
          <span class="currency-symbol"><?= MONEDA ?></span>
          <input type="number" step="0.01" id="precio_3_meses" name="precio_3_meses" value="<?= htmlspecialchars($producto['precio_3_meses'], ENT_QUOTES) ?>">
        </div>
        <div class="input-help">Precio para la suscripción de 3 meses</div>
      </div>
    </div>

    <!-- Plan anual -->
    <div class="admin-form-group">
      <div class="precio-card">
        <h4>Plan anual</h4>
        <label for="precio_12_meses">12 meses (<?= MONEDA ?>)</label>
        <div class="input-group">
          <span class="currency-symbol"><?= MONEDA ?></span>
          <input type="number" step="0.01" id="precio_12_meses" name="precio_12_meses" value="<?= htmlspecialchars($producto['precio_12_meses'], ENT_QUOTES) ?>">
        </div>
        <div class="input-help">Precio para la suscripción anual</div>
      </div>
    </div>

    <!-- Stock -->
    <div class="admin-form-group full-width">
      <label for="stock">Stock</label>
      <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($producto['stock'], ENT_QUOTES) ?>">
      <div class="input-help">0 = Sin límite de stock</div>
    </div>

    <!-- Imagen (ocupa todo el ancho) -->
    <div class="admin-form-group full-width">
      <label for="imagen">Imagen del producto</label>
      <div class="file-upload">
        <label for="imagen" class="file-upload-label">
          <i class="fas fa-cloud-upload-alt"></i> Seleccionar imagen
        </label>
        <input type="file" id="imagen" name="imagen" accept="image/*">
        <span id="file-name" class="file-name">Ningún archivo seleccionado</span>
      </div>
      
      <?php if (!empty($producto['imagen'])): ?>
        <div class="img-preview">
          <img src="<?= URL_SITIO . ltrim($producto['imagen'], '/') ?>" alt="Imagen actual">
          <div>
            <p>Imagen actual</p>
            <small>Subir una nueva imagen reemplazará la actual</small>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Botones de acción -->
    <div class="admin-actions full-width">
<a href="listar.php" class="admin-btn admin-btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
      </a>
      <button type="submit" class="admin-btn admin-btn-primary">
        <?= $esEdicion ? 'Actualizar' : 'Crear' ?> producto
      </button>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar formulario para nueva categoría
    const btnNuevaCategoria = document.getElementById('btn-nueva-categoria');
    const formNuevaCategoria = document.getElementById('form-nueva-categoria');
    
    if (btnNuevaCategoria && formNuevaCategoria) {
      btnNuevaCategoria.addEventListener('click', function() {
        formNuevaCategoria.classList.toggle('active');
        if (formNuevaCategoria.classList.contains('active')) {
          formNuevaCategoria.querySelector('input').focus();
        }
      });
    }

    // Mostrar nombre del archivo seleccionado
    const fileInput = document.getElementById('imagen');
    const fileNameDisplay = document.getElementById('file-name');

    if (fileInput && fileNameDisplay) {
      fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
          fileNameDisplay.textContent = this.files[0].name;
        } else {
          fileNameDisplay.textContent = 'Ningún archivo seleccionado';
        }
      });
    }
  });
</script>

<?php
// 10) Footer
include __DIR__ . '/../../../includes/footer.php';
?>