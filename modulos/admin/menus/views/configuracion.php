<?php
// Archivo: modulos/admin/menus/views/configuracion.php

// Obtener explícitamente los valores de configuración
$mostrar_header = $settingModel->obtener('mostrar_menus_header', '0');
$mostrar_footer = $settingModel->obtener('mostrar_menus_footer', '0');
$mostrar_sidebar = $settingModel->obtener('mostrar_menus_sidebar', '0');

// Asignar valores a la variable $mostrarMenus que espera la parte inferior del código
$mostrarMenus = [
    'header' => $mostrar_header === '1',
    'footer' => $mostrar_footer === '1', 
    'sidebar' => $mostrar_sidebar === '1'
];

// Imprimir para depuración
echo "<!-- mostrar_menus_header en configuracion.php: $mostrar_header -->";
echo "<!-- mostrar_menus_footer en configuracion.php: $mostrar_footer -->";
echo "<!-- mostrar_menus_sidebar en configuracion.php: $mostrar_sidebar -->";
?>

<div class="container mt-4">
  <h2>Configuración de Menús</h2>
  
  <form method="POST" action="panel.php?accion=configuracion">
    
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Gestión de Menús y Posiciones</h4>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Configure la ubicación, jerarquía y orden de los menús disponibles en el sitio.</p>
        
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>Nombre del Menú</th>
                <th>Posición</th>
                <th>Menú Padre</th>
                <th>Orden</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($allMenus as $menu): ?>
                <?php 
                // Buscar la configuración actual de este menú (si existe)
                $menuActual = null;
                foreach($menuConfig as $config) {
                  if(isset($config['menu_id']) && $config['menu_id'] == $menu['id']) {
                    $menuActual = $config;
                    break;
                  }
                }
                
                // Valores por defecto si no hay configuración previa
                $posicionActual   = $menuActual['posicion']   ?? 'header';
                $padreActual      = $menuActual['parent_id']  ?? null;  // Nota: es parent_id, no padre_id
                $ordenActual      = $menuActual['orden']      ?? 1;
                $habilitadoActual = $menuActual['habilitado'] ?? 1;
                ?>
                
                <tr>
                  <td>
                    <?= htmlspecialchars($menu['nombre']) ?>
                    <input type="hidden" name="menu_ids[]" value="<?= $menu['id'] ?>">
                  </td>
                  <td>
                    <select name="posiciones[]" class="form-select form-select-sm">
                      <?php foreach($posicionesMenu as $posicion): ?>
                        <option value="<?= $posicion ?>" <?= $posicion == $posicionActual ? 'selected' : '' ?>>
                          <?= ucfirst($posicion) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td>
                    <select name="padres[]" class="form-select form-select-sm">
                      <option value="">Ninguno (Raíz)</option>
                      <?php foreach($allMenus as $menuPadre): ?>
                        <?php if($menuPadre['id'] != $menu['id']): ?>
                          <option value="<?= $menuPadre['id'] ?>" <?= isset($padreActual) && $menuPadre['id'] == $padreActual ? 'selected' : '' ?>>
                            <?= htmlspecialchars($menuPadre['nombre']) ?>
                          </option>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td>
                    <input type="number" name="ordenes[]" min="1" max="999" value="<?= $ordenActual ?>" class="form-control form-control-sm">
                  </td>
                  <td>
                    <div class="form-check form-switch">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        name="habilitados[]"
                        value="<?= $menu['id'] ?>"
                        id="habilitado_<?= $menu['id'] ?>"
                        <?= $habilitadoActual ? 'checked' : '' ?>
                      >
                      <label class="form-check-label" for="habilitado_<?= $menu['id'] ?>">
                        <?= $habilitadoActual ? 'Habilitado' : 'Deshabilitado' ?>
                      </label>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <h4 class="mb-0">Configuración Rápida por Posición</h4>
      </div>
      <div class="card-body">
        <?php foreach($posicionesMenu as $posicion): ?>
          <div class="mb-3">
            <label class="form-label"><strong><?= ucfirst($posicion) ?>:</strong></label>
            <div class="row">
              <div class="col-md-6">
                <select name="rapido_<?= $posicion ?>" class="form-select">
                  <option value="">-- Seleccione un menú principal --</option>
                  <?php foreach($allMenus as $menu): ?>
                    <?php 
                      $seleccionado = false;
                      foreach($menuConfig as $config) {
                        if(isset($config['menu_id']) && isset($config['posicion']) && 
                           $config['menu_id'] == $menu['id'] && 
                           $config['posicion'] == $posicion && 
                           empty($config['parent_id'])) {
                          $seleccionado = true;
                          break;
                        }
                      }
                    ?>
                    <option value="<?= $menu['id'] ?>" <?= $seleccionado ? 'selected' : '' ?>>
                      <?= htmlspecialchars($menu['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="mostrar_<?= $posicion ?>"
                    id="mostrar_<?= $posicion ?>"
                    value="1"
                    <?= $mostrarMenus[$posicion] ? 'checked' : '' ?>
                  >
                  <label class="form-check-label" for="mostrar_<?= $posicion ?>">
                    Mostrar menús en <?= ucfirst($posicion) ?>
                  </label>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Guardar configuración
      </button>
      <a href="panel.php" class="btn btn-secondary">
        <i class="fas fa-times me-1"></i> Cancelar
      </a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Actualizar etiquetas de habilitado/deshabilitado al cambiar
  const switchesHabilitados = document.querySelectorAll('input[name="habilitados[]"]');
  switchesHabilitados.forEach(function(switchEl) {
    switchEl.addEventListener('change', function() {
      const label = this.nextElementSibling;
      label.textContent = this.checked ? 'Habilitado' : 'Deshabilitado';
    });
  });
});
</script>