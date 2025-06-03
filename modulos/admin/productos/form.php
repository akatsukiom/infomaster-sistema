<?php
// módulos/admin/productos/form.php

// Si venimos con un ID, cargamos los datos del producto para editar
$id   = $_GET['id'] ?? null;
$prod = [
    'id'          => '',
    'nombre'      => '',
    'descripcion' => '',
    'precio'      => '',
    'categoria_id'=> '',
    'imagen'      => '',
    'destacado'   => 0,
];
if ($id) {
    require_once __DIR__ . '/../productos/modelo.php';
    $modelo = new Producto($conexion);
    $prod   = $modelo->obtenerPorId($id);
}

// Cargamos categorías para el select
require_once __DIR__ . '/../admin/categorias/modelo.php';
$catModel   = new Categoria($conexion);
$categorias = $catModel->obtenerTodas();
?>

<form action="<?= URL_SITIO ?>modulos/admin/productos/guardar.php" 
      method="post" enctype="multipart/form-data">
  <!-- Si es edición, mantenemos el ID -->
  <?php if ($prod['id']): ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars($prod['id']) ?>">
  <?php endif; ?>

  <div class="form-group">
    <label for="nombre">Nombre del producto</label>
    <input type="text" 
           id="nombre" 
           name="nombre" 
           class="form-control" 
           value="<?= htmlspecialchars($prod['nombre']) ?>" 
           required>
  </div>

  <div class="form-group">
    <label for="descripcion">Descripción</label>
    <textarea id="descripcion" 
              name="descripcion" 
              class="form-control" 
              rows="4"
              required><?= htmlspecialchars($prod['descripcion']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="precio">Precio (<?= MONEDA ?>)</label>
    <input type="number" 
           step="0.01" 
           id="precio" 
           name="precio" 
           class="form-control" 
           value="<?= htmlspecialchars($prod['precio']) ?>" 
           required>
  </div>

  <div class="form-group">
    <label for="categoria">Categoría</label>
    <select id="categoria" 
            name="categoria_id" 
            class="form-control" 
            required>
      <option value="">-- Selecciona una categoría --</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= $c['id'] ?>"
          <?= $c['id']==$prod['categoria_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="imagen">Imagen del producto</label>
    <?php if (!empty($prod['imagen'])): ?>
      <div>
        <img src="<?= URL_SITIO . ltrim($prod['imagen'], '/') ?>" 
             alt="Actual" 
             style="max-width:150px;margin-bottom:.5rem;">
      </div>
    <?php endif; ?>
    <input type="file" 
           id="imagen" 
           name="imagen" 
           class="form-control-file"
           accept="image/*">
    <small class="form-text text-muted">
      Si subes una nueva imagen, reemplazará la anterior.
    </small>
  </div>

  <div class="form-group form-check">
    <input type="checkbox"
           id="destacado"
           name="destacado"
           value="1"
           class="form-check-input"
           <?= !empty($prod['destacado']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="destacado">
      Producto destacado
    </label>
  </div>

  <button type="submit" class="btn btn-primary">
    <?= $prod['id'] ? 'Actualizar' : 'Crear' ?> producto
  </button>
</form>
