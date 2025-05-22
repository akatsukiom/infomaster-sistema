<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('ACCESO_PERMITIDO')) define('ACCESO_PERMITIDO', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

$titulo = 'Panel Admin';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container admin-dashboard">
  <h1>Panel de Administración</h1>
  <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></p>
  
  <div class="admin-sections">

    <!-- Gestión de Usuarios y Wallet -->
    <div class="admin-section">
      <h2>Gestión de Usuarios y Wallet</h2>
      <div class="admin-actions">
        <a href="<?= URL_SITIO ?>admin/usuarios" class="btn">Listar usuarios</a>
        <a href="<?= URL_SITIO ?>admin/wallet/recargar" class="btn btn-secondary">Recargar wallet usuarios</a>
        <a href="<?= URL_SITIO ?>admin/reportes/ventas" class="btn">Reporte de ventas</a>
      </div>
    </div>

    <!-- Gestión de Productos -->
    <div class="admin-section">
      <h2>Gestión de Productos</h2>
      <div class="admin-actions">
        <a href="<?= URL_SITIO ?>admin/productos" class="btn">Listar productos</a>
        <a href="<?= URL_SITIO ?>admin/productos/crear" class="btn">Nuevo producto</a>
        <a href="<?= URL_SITIO ?>admin/productos/exportar" class="btn btn-outline">Exportar productos</a>
        <form id="importProductosForm" method="post"
              action="<?= URL_SITIO ?>modulos/admin/productos/ajax.php?action=import_prod"
              enctype="multipart/form-data" style="display:inline;">
          <input type="file" name="csv" id="importProductosFile" style="display:none;" accept=".csv">
          <button type="button" class="btn btn-outline"
                  onclick="document.getElementById('importProductosFile').click()">
            Importar productos
          </button>
        </form>
      </div>
    </div>

    <!-- Gestión de Categorías -->
    <div class="admin-section">
      <h2>Gestión de Categorías</h2>
      <div class="admin-actions">
        <a href="<?= URL_SITIO ?>admin/categorias" class="btn">Listar categorías</a>
        <a href="<?= URL_SITIO ?>admin/categorias/crear" class="btn">Nueva categoría</a>
        <a href="<?= URL_SITIO ?>admin/categorias/exportar" class="btn btn-outline">Exportar categorías</a>
        <form id="importCategoriasForm" method="post"
              action="<?= URL_SITIO ?>modulos/admin/productos/ajax.php?action=import_cat"
              enctype="multipart/form-data" style="display:inline;">
          <input type="file" name="csv" id="importCategoriasFile" style="display:none;" accept=".csv">
          <button type="button" class="btn btn-outline"
                  onclick="document.getElementById('importCategoriasFile').click()">
            Importar categorías
          </button>
        </form>
      </div>
    </div>

    <!-- Gestión de Menús -->
    <div class="admin-section">
      <h2>Gestión de Menús</h2>
      <div class="admin-actions">
        <a href="<?= URL_SITIO ?>admin/menus" class="btn">Listar menús</a>
        <a href="<?= URL_SITIO ?>admin/menus/crear" class="btn">Nuevo menú</a>
        <a href="<?= URL_SITIO ?>admin/menus/exportar" class="btn btn-outline">Exportar menús</a>
        <form id="importMenusForm" method="post"
              action="<?= URL_SITIO ?>modulos/admin/menus/ajax.php?action=import_menu"
              enctype="multipart/form-data" style="display:inline;">
          <input type="file" name="csv" id="importMenusFile" style="display:none;" accept=".csv">
          <button type="button" class="btn btn-outline"
                  onclick="document.getElementById('importMenusFile').click()">
            Importar menús
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<style>
    .admin-dashboard {
        padding: 2rem 0;
    }
    
    .admin-sections {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .admin-section {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .admin-section h2 {
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
        color: #333;
    }
    
    .admin-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .admin-actions .btn {
        padding: 0.8rem 1.2rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .admin-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #ff006e 0%, #fb5607 100%);
        color: white;
    }
    
    .btn-secondary:hover {
        background: linear-gradient(135deg, #e5005d 0%, #e44c0a 100%);
    }
    
    @media (max-width: 768px) {
        .admin-actions {
            flex-direction: column;
        }
        
        .admin-actions .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
  document.getElementById('importProductosFile').addEventListener('change', function() {
    if (this.files.length) document.getElementById('importProductosForm').submit();
  });
  document.getElementById('importCategoriasFile').addEventListener('change', function() {
    if (this.files.length) document.getElementById('importCategoriasForm').submit();
  });
  document.getElementById('importMenusFile').addEventListener('change', function() {
    if (this.files.length) document.getElementById('importMenusForm').submit();
  });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>