<a href="<?= URL_SITIO ?>admin/categorias/panel?accion=nuevo">
                            <i class="fas fa-plus-circle"></i> Nueva Categoría
                        </a>
                    </li>
                    <li>
                        <a href="<?= URL_SITIO ?>admin">
                            <i class="fas fa-cog"></i> Panel Admin
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Contenido principal -->
        <div class="panel-content">
            <div class="panel-header">
                <h1><?= $accion === 'editar' ? 'Editar Categoría: ' . htmlspecialchars($categoria_actual['nombre']) : ($accion === 'nuevo' ? 'Nueva Categoría' : 'Categorías del Sitio') ?></h1>
                
                <div class="panel-actions">
                    <?php if ($accion === 'listar'): ?>
                        <a href="<?= URL_SITIO ?>admin/categorias/panel?accion=nuevo" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Categoría
                        </a>
                    <?php elseif ($accion === 'editar' || $accion === 'nuevo'): ?>
                        <a href="<?= URL_SITIO ?>admin/categorias/panel" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Volver a Categorías
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($errores)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errores as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert alert-success">
                    <p><?= htmlspecialchars($exito) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Contenido según la acción -->
            <?php if ($accion === 'listar'): ?>
                <!-- Lista de categorías -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h2>Categorías Disponibles</h2>
                    </div>
                    <div class="panel-card-body">
                        <?php if (empty($categorias)): ?>
                            <p class="empty-state">No hay categorías registradas. <a href="<?= URL_SITIO ?>admin/categorias/panel?accion=nuevo">Crea tu primera categoría</a>.</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">Imagen</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Categoría Padre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($categoria['imagen'])): ?>
                                                    <img src="<?= URL_SITIO . $categoria['imagen'] ?>" alt="<?= htmlspecialchars($categoria['nombre']) ?>" width="50" height="50" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="no-image">Sin imagen</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                                            <td><?= substr(htmlspecialchars($categoria['descripcion']), 0, 100) . (strlen($categoria['descripcion']) > 100 ? '...' : '') ?></td>
                                            <td>
                                                <?php 
                                                    $parent_name = "Ninguna";
                                                    if ($categoria['parent_id'] > 0) {
                                                        foreach ($categorias as $parent) {
                                                            if ($parent['id'] == $categoria['parent_id']) {
                                                                $parent_name = htmlspecialchars($parent['nombre']);
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    echo $parent_name;
                                                ?>
                                            </td>
                                            <td class="actions">
                                                <a href="<?= URL_SITIO ?>admin/categorias/panel?accion=editar&id=<?= $categoria['id'] ?>" class="btn-icon" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= URL_SITIO ?>admin/categorias/panel?accion=eliminar&id=<?= $categoria['id'] ?>" 
                                                   class="btn-icon delete" 
                                                   title="Eliminar" 
                                                   onclick="return confirm('¿Estás seguro de eliminar esta categoría? Esta acción no se puede deshacer.')">
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
                <!-- Formulario de categoría -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h2><?= $accion === 'editar' ? 'Editar Categoría' : 'Crear Nueva Categoría' ?></h2>
                    </div>
                    <div class="panel-card-body">
                        <form method="POST" action="<?= URL_SITIO ?>admin/categorias/panel?accion=guardar<?= $categoria_id ? '&id='.$categoria_id : '' ?>" class="shopify-form" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nombre">Nombre de la Categoría</label>
                                    <input type="text" id="nombre" name="nombre" class="form-control" 
                                           value="<?= htmlspecialchars($categoria_actual['nombre']) ?>" required>
                                    <small>Nombre visible de la categoría</small>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="parent_id">Categoría Padre</label>
                                    <select id="parent_id" name="parent_id" class="form-control">
                                        <option value="0">Ninguna (Categoría Principal)</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <?php if ($cat['id'] != $categoria_id): // Evitar que se seleccione a sí misma ?>
                                                <option value="<?= $cat['id'] ?>" <?= $categoria_actual['parent_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <small>Opcional: selecciona si esta es una subcategoría</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="4"><?= htmlspecialchars($categoria_actual['descripcion']) ?></textarea>
                                <small>Breve descripción de la categoría</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Imagen de la Categoría</label>
                                
                                <?php if (!empty($categoria_actual['imagen'])): ?>
                                    <div class="current-image">
                                        <img src="<?= URL_SITIO . $categoria_actual['imagen'] ?>" alt="<?= htmlspecialchars($categoria_actual['nombre']) ?>" style="max-width: 200px; max-height: 200px; margin-bottom: 1rem;">
                                        <input type="hidden" name="imagen_actual" value="<?= $categoria_actual['imagen'] ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="file-upload">
                                    <input type="file" id="imagen" name="imagen" class="form-control-file" accept="image/jpeg, image/png, image/webp">
                                    <small>Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 2MB</small>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <?= $accion === 'editar' ? 'Actualizar Categoría' : 'Crear Categoría' ?>
                                </button>
                                <a href="<?= URL_SITIO ?>admin/categorias/panel" class="btn btn-outline">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Estilos para el panel de administración estilo Shopify */
.shopify-panel {
    display: flex;
    min-height: calc(100vh - 80px);
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
    transition: background-color 0.2s, color 0.2s;
}

.panel-menu li a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}

.panel-menu li.active a {
    background-color: #1a73e8;
    color: white;
}

.panel-menu li a i {
    margin-right: 0.75rem;
    font-size: 1rem;
    width: 20px;
    text-align: center;
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

.panel-header h1 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: #212b36;
}

.panel-actions {
    display: flex;
    gap: 1rem;
}

.panel-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.panel-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background-color: #f9fafb;
}

.panel-card-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #212b36;
}

.panel-card-body {
    padding: 1.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #fff0f0;
    border-left: 4px solid #ff4d4f;
    color: #cf1322;
}

.alert-success {
    background-color: #f6ffed;
    border-left: 4px solid #52c41a;
    color: #389e0d;
}

.shopify-form .form-group {
    margin-bottom: 1.25rem;
}

.shopify-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #212b36;
}

.shopify-form .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.shopify-form .form-control:focus {
    border-color: #1a73e8;
    box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    outline: none;
}

.shopify-form .form-control-file {
    padding: 0.5rem 0;
}

.shopify-form small {
    display: block;
    margin-top: 0.25rem;
    color: #637381;
    font-size: 0.875rem;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -0.75rem;
    margin-left: -0.75rem;
}

.form-row > .form-group {
    padding-right: 0.75rem;
    padding-left: 0.75rem;
    flex: 0 0 100%;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 4px;
    font-weight: 500;
    transition: background-color 0.2s, color 0.2s, border-color 0.2s, transform 0.1s;
    border: none;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.875rem;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}

.btn-primary {
    background-color: #1a73e8;
    color: white;
}

.btn-primary:hover {
    background-color: #1669d9;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #d9d9d9;
    color: #212b36;
}

.btn-outline:hover {
    border-color: #1a73e8;
    color: #1a73e8;
}

.btn i {
    margin-right: 0.5rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #637381;
    background-color: transparent;
    transition: background-color 0.2s, color 0.2s;
    text-decoration: none;
}

.btn-icon:hover {
    background-color: #f9fafb;
    color: #212b36;
}

.btn-icon.delete:hover {
    color: #cf1322;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 0.875rem 1rem;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.data-table th {
    font-weight: 600;
    color: #212b36;
    background-color: #f9fafb;
}

.data-table tbody tr:hover {
    background-color: #f9fafb;
}

.data-table .actions {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #637381;
}

.no-image {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
    border-radius: 4px;
    font-size: 0.75rem;
    color: #637381;
}

.current-image {
    margin-bottom: 1rem;
}

.current-image img {
    border-radius: 4px;
    border: 1px solid #f0f0f0;
}

.file-upload {
    margin-top: 0.5rem;
}
</style>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>