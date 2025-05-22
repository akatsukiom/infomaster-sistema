<?php
// Mostrar errores durante el desarrollo (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Evitar accesos directos
define('ACCESO_PERMITIDO', true);

// Incluir config y funciones (subimos DOS niveles: modulos/admin → modulos → public_html)
// Nota: Reemplazado **DIR** por __DIR__ (doble guion bajo)
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// Verificar sesión y permisos
if (!estaLogueado() || !esAdmin()) {
    mostrarMensaje('Acceso restringido. Debes iniciar sesión como administrador.', 'error');
    redireccionar('login');
    exit; // Asegurar que el script termina
}

// Procesamiento del formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF para seguridad
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $mensaje = 'Error de seguridad. Por favor, intenta nuevamente.';
        $tipoMensaje = 'error';
    } else {
        // Procesar los campos del formulario
        $siteName = isset($_POST['site_name']) ? limpiarDato($_POST['site_name']) : '';
        $siteEmail = isset($_POST['site_email']) ? limpiarDato($_POST['site_email']) : '';
        $siteLogo = isset($_POST['site_logo']) ? limpiarDato($_POST['site_logo']) : '';
        $siteTheme = isset($_POST['site_theme']) ? limpiarDato($_POST['site_theme']) : 'default';
        
        // Aquí implementarías la lógica para guardar estos valores
        // Por ejemplo, actualizando una tabla de configuración en la base de datos
        
        try {
            // Ejemplo con consulta preparada
            $stmt = $conexion->prepare("
                UPDATE configuracion SET 
                valor = CASE 
                    WHEN clave = 'site_name' THEN ?
                    WHEN clave = 'site_email' THEN ?
                    WHEN clave = 'site_logo' THEN ?
                    WHEN clave = 'site_theme' THEN ?
                END
                WHERE clave IN ('site_name', 'site_email', 'site_logo', 'site_theme')
            ");
            
            $stmt->bind_param('ssss', $siteName, $siteEmail, $siteLogo, $siteTheme);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $mensaje = 'Configuración actualizada correctamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'No se realizaron cambios en la configuración.';
                $tipoMensaje = 'info';
            }
            
        } catch (Exception $e) {
            $mensaje = 'Error al guardar la configuración: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }
    }
}

// Cargar configuración actual
$configActual = [
    'site_name' => 'InfoMaster',
    'site_email' => 'info@infomaster.com',
    'site_logo' => 'img/logo.png',
    'site_theme' => 'default'
];

// En un entorno real, cargarías esto desde la base de datos
// Por ejemplo:
// $sql = "SELECT clave, valor FROM configuracion";
// $result = $conexion->query($sql);
// while ($row = $result->fetch_assoc()) {
//     $configActual[$row['clave']] = $row['valor'];
// }

// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Título de la página
$titulo = 'Configuración del Sistema';

// Cabecera común
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-cogs me-2"></i><?= htmlspecialchars($titulo) ?></h1>
        <a href="<?= URL_SITIO ?>admin" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoMensaje === 'error' ? 'danger' : ($tipoMensaje === 'success' ? 'success' : 'info') ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Menú lateral -->
        <div class="col-lg-3 mb-4">
            <div class="list-group">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-sliders-h me-2"></i> General
                </a>
                <a href="#apariencia" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-palette me-2"></i> Apariencia
                </a>
                <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-envelope me-2"></i> Configuración de Email
                </a>
                <a href="#pagos" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-credit-card me-2"></i> Opciones de Pago
                </a>
                <a href="#avanzado" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-code me-2"></i> Ajustes Avanzados
                </a>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Pestaña General -->
                        <div class="tab-pane fade show active" id="general">
                            <h4 class="card-title border-bottom pb-3 mb-3">Configuración General</h4>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="site_name" class="form-label">Nombre del Sitio</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-store"></i></span>
                                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                                value="<?= htmlspecialchars($configActual['site_name']) ?>" required>
                                            <div class="invalid-feedback">
                                                El nombre del sitio es obligatorio
                                            </div>
                                        </div>
                                        <small class="text-muted">Este nombre aparecerá en títulos y cabeceras</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="site_email" class="form-label">Email de Contacto</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="email" id="site_email" name="site_email" class="form-control" 
                                                value="<?= htmlspecialchars($configActual['site_email']) ?>" required>
                                            <div class="invalid-feedback">
                                                Introduce un email válido
                                            </div>
                                        </div>
                                        <small class="text-muted">Se usará para notificaciones y soporte</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Descripción del sitio</label>
                                    <textarea id="site_description" name="site_description" class="form-control" rows="3"><?= htmlspecialchars($configActual['site_description'] ?? '') ?></textarea>
                                    <small class="text-muted">Breve descripción para SEO y redes sociales</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Estado de mantenimiento</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?= isset($configActual['maintenance_mode']) && $configActual['maintenance_mode'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="maintenance_mode">Activar modo mantenimiento</label>
                                    </div>
                                    <small class="text-muted">Al activar, solo los administradores podrán acceder al sitio</small>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="reset" class="btn btn-light">Restaurar valores</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Pestaña Apariencia -->
                        <div class="tab-pane fade" id="apariencia">
                            <h4 class="card-title border-bottom pb-3 mb-3">Apariencia y Tema</h4>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="mb-4">
                                    <label class="form-label">Logo del sitio</label>
                                    
                                    <?php if (!empty($configActual['site_logo'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= URL_SITIO . $configActual['site_logo'] ?>" alt="Logo actual" class="img-thumbnail" style="max-height: 100px">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="site_logo_file" name="site_logo_file" accept="image/*">
                                        <label class="input-group-text" for="site_logo_file">Subir</label>
                                    </div>
                                    <small class="text-muted">Formatos recomendados: PNG, SVG. Tamaño máximo: 2MB</small>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="site_theme" class="form-label">Tema visual</label>
                                        <select class="form-select" id="site_theme" name="site_theme">
                                            <option value="default" <?= $configActual['site_theme'] === 'default' ? 'selected' : '' ?>>Tema predeterminado</option>
                                            <option value="dark" <?= $configActual['site_theme'] === 'dark' ? 'selected' : '' ?>>Tema oscuro</option>
                                            <option value="light" <?= $configActual['site_theme'] === 'light' ? 'selected' : '' ?>>Tema claro</option>
                                            <option value="custom" <?= $configActual['site_theme'] === 'custom' ? 'selected' : '' ?>>Personalizado</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="site_color" class="form-label">Color principal</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="site_color" name="site_color" value="<?= htmlspecialchars($configActual['site_color'] ?? '#3a86ff') ?>">
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($configActual['site_color'] ?? '#3a86ff') ?>" id="site_color_hex">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="css_custom" class="form-label">CSS personalizado</label>
                                    <textarea class="form-control font-monospace" id="css_custom" name="css_custom" rows="5" style="font-size: 0.9rem;"><?= htmlspecialchars($configActual['css_custom'] ?? '') ?></textarea>
                                    <small class="text-muted">CSS adicional para personalizar la apariencia del sitio</small>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="reset" class="btn btn-light">Restaurar valores</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Contenido para las demás pestañas -->
                        <div class="tab-pane fade" id="email">
                            <h4 class="card-title border-bottom pb-3 mb-3">Configuración de Email</h4>
                            <p class="text-muted">Configura los parámetros para el envío de emails desde el sistema.</p>
                            <!-- Formulario de configuración de email -->
                        </div>
                        
                        <div class="tab-pane fade" id="pagos">
                            <h4 class="card-title border-bottom pb-3 mb-3">Opciones de Pago</h4>
                            <p class="text-muted">Configura los métodos de pago y las opciones de procesamiento.</p>
                            <!-- Formulario de métodos de pago -->
                        </div>
                        
                        <div class="tab-pane fade" id="avanzado">
                            <h4 class="card-title border-bottom pb-3 mb-3">Ajustes Avanzados</h4>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>¡Precaución!</strong> Modificar estos ajustes puede afectar el funcionamiento del sistema.
                            </div>
                            <!-- Formulario de ajustes avanzados -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para validación de formularios -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activar validación de Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Actualizar campo de texto al cambiar el color
    const colorPicker = document.getElementById('site_color');
    const colorHex = document.getElementById('site_color_hex');
    
    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
        
        colorHex.addEventListener('input', function() {
            colorPicker.value = this.value;
        });
    }
    
    // Gestionar las pestañas con JavaScript
    const triggerTabList = [].slice.call(document.querySelectorAll('.list-group-item'));
    triggerTabList.forEach(function(triggerEl) {
        triggerEl.addEventListener('click', function(event) {
            event.preventDefault();
            triggerTabList.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

<?php
// Pie común
require_once __DIR__ . '/../../includes/footer.php';
?>