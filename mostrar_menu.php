<?php
/**
 * Helper para mostrar un menú dinámico
 * 
 * @param int $menuId ID del menú a mostrar
 * @param array $estilo Configuración de estilos CSS para el menú
 * @param array|null $override Datos opcionales para sobreescribir propiedades del menú
 * @return string HTML generado para el menú
 */
function mostrarMenuDinamico($menuId, $estilo, $override = null) {
    global $conexion;
    
    // Obtener datos del menú desde la base de datos si no hay override o si faltan datos esenciales
    if (!$override || empty($override['items'])) {
        // Aquí iría tu código original para obtener elementos del menú desde la BD
        // Por ejemplo:
        require_once __DIR__ . '/../modulos/admin/menus/modelo.php';
        $menuModel = new Menu($conexion);
        $menuData = $menuModel->obtener($menuId);
        $items = $menuModel->obtenerItems($menuId);
    } else {
        // Usar los datos proporcionados en el override
        $items = $override['items'];
        // Otros datos del menú que podrían venir en el override
        $menuData = [
            'id' => $override['id'] ?? $menuId,
            'url' => $override['url'] ?? '',
            'estilo' => $override['estilo'] ?? 'default',
            // ... otros campos que puedas necesitar
        ];
    }
    
    // Si no hay elementos o hubo un error, retornar cadena vacía
    if (empty($items)) {
        return '<!-- Menú vacío o error al cargar #' . $menuId . ' -->';
    }
    
    // Extraer clases CSS del estilo proporcionado
    $classNav = $estilo['class_nav'] ?? 'main-nav';
    $classUlPrincipal = $estilo['class_ul_principal'] ?? 'menu';
    $classUlSubmenu = $estilo['class_ul_submenu'] ?? 'dropdown-menu';
    $classLi = $estilo['class_li'] ?? 'menu-item';
    $classLink = $estilo['class_link'] ?? 'menu-link';
    $classDropdown = $estilo['class_dropdown'] ?? 'dropdown';
    $classDropdownToggle = $estilo['class_dropdown_toggle'] ?? 'dropdown-toggle';
    $dropdownToggleAttr = $estilo['dropdown_toggle_attr'] ?? '';
    
    // Iniciar generación del HTML
    $html = '<nav class="' . $classNav . '">' . PHP_EOL;
    $html .= '    <ul class="' . $classUlPrincipal . '">' . PHP_EOL;
    
    foreach ($items as $item) {
        // Determinar si es un ítem con submenú
        $tieneSubmenu = isset($item['children']) && !empty($item['children']);
        
        // Clases para el <li>
        $liClass = $classLi;
        if ($tieneSubmenu) {
            $liClass .= ' ' . $classDropdown;
        }
        
        // Agregar clase active si corresponde a la URL actual
        if (isset($item['url']) && strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) {
            $liClass .= ' active';
        }
        
        $html .= '        <li class="' . $liClass . '">' . PHP_EOL;
        
        // Determinar URL del ítem
        $itemUrl = $item['url'] ?? '#';
        
        // Atributos adicionales para el link
        $linkAttr = '';
        $linkClass = $classLink;
        
        if ($tieneSubmenu) {
            $linkClass .= ' ' . $classDropdownToggle;
            $linkAttr .= ' ' . $dropdownToggleAttr;
        }
        
        // Generar el enlace
        $html .= '            <a href="' . htmlspecialchars($itemUrl) . '" class="' . $linkClass . '"' . $linkAttr . '>' . PHP_EOL;
        $html .= '                ' . htmlspecialchars($item['label'] ?? $item['nombre'] ?? '') . PHP_EOL;
        $html .= '            </a>' . PHP_EOL;
        
        // Si tiene submenú, generar el <ul> correspondiente
        if ($tieneSubmenu) {
            $html .= '            <ul class="' . $classUlSubmenu . '">' . PHP_EOL;
            
            foreach ($item['children'] as $child) {
                $childUrl = $child['url'] ?? '#';
                
                $html .= '                <li class="' . $classLi . '">' . PHP_EOL;
                $html .= '                    <a href="' . htmlspecialchars($childUrl) . '" class="' . $classLink . '">' . PHP_EOL;
                $html .= '                        ' . htmlspecialchars($child['label'] ?? $child['nombre'] ?? '') . PHP_EOL;
                $html .= '                    </a>' . PHP_EOL;
                $html .= '                </li>' . PHP_EOL;
            }
            
            $html .= '            </ul>' . PHP_EOL;
        }
        
        $html .= '        </li>' . PHP_EOL;
    }
    
    $html .= '    </ul>' . PHP_EOL;
    $html .= '</nav>' . PHP_EOL;
    
    return $html;
}