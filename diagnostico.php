<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/modulos/admin/menus/modelo.php';

// Verificar permisos
if (!estaLogueado() || !esAdmin()) {
    die("Acceso no autorizado");
}

// Obtener configuración
$settingModel = new Setting($conexion);
$menuConfig = $settingModel->obtenerJSON('menu_config', []);
$mostrar_header = $settingModel->obtener('mostrar_menus_header', 'no encontrado');
$mostrar_footer = $settingModel->obtener('mostrar_menus_footer', 'no encontrado');
$mostrar_sidebar = $settingModel->obtener('mostrar_menus_sidebar', 'no encontrado');
$menu_principal_id = $settingModel->obtener('menu_principal_id', 'no encontrado');

// Mostrar información
echo "<h1>Diagnóstico de Menus</h1>";
echo "<h2>Configuración General</h2>";
echo "<ul>";
echo "<li><strong>mostrar_menus_header:</strong> $mostrar_header</li>";
echo "<li><strong>mostrar_menus_footer:</strong> $mostrar_footer</li>";
echo "<li><strong>mostrar_menus_sidebar:</strong> $mostrar_sidebar</li>";
echo "<li><strong>menu_principal_id:</strong> $menu_principal_id</li>";
echo "</ul>";

echo "<h2>Configuración de Menús</h2>";
echo "<pre>";
print_r($menuConfig);
echo "</pre>";

// Formulario para actualizar la configuración directamente
echo "<h2>Actualizar Configuración</h2>";
echo "<form method='post'>";
echo "<div><label>mostrar_menus_header: <input type='checkbox' name='mostrar_header' value='1' " . ($mostrar_header === '1' ? "checked" : "") . "></label></div>";
echo "<div><label>mostrar_menus_footer: <input type='checkbox' name='mostrar_footer' value='1' " . ($mostrar_footer === '1' ? "checked" : "") . "></label></div>";
echo "<div><label>mostrar_menus_sidebar: <input type='checkbox' name='mostrar_sidebar' value='1' " . ($mostrar_sidebar === '1' ? "checked" : "") . "></label></div>";
echo "<div><button type='submit'>Actualizar</button></div>";
echo "</form>";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mostrar_header_nuevo = isset($_POST['mostrar_header']) ? '1' : '0';
    $mostrar_footer_nuevo = isset($_POST['mostrar_footer']) ? '1' : '0';
    $mostrar_sidebar_nuevo = isset($_POST['mostrar_sidebar']) ? '1' : '0';
    
    $settingModel->guardar('mostrar_menus_header', $mostrar_header_nuevo);
    $settingModel->guardar('mostrar_menus_footer', $mostrar_footer_nuevo);
    $settingModel->guardar('mostrar_menus_sidebar', $mostrar_sidebar_nuevo);
    
    echo "<p>Configuración actualizada. <a href='?'>Recargar</a></p>";
}
?>