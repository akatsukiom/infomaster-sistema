<?php
// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

/**
 * Función para mostrar un menú dinámico utilizando estructura jerárquica
 *
 * @param int $menu_id ID del menú a mostrar
 * @param string $class_nav Clase CSS para el contenedor nav
 * @param string $class_ul_principal Clase CSS para la lista ul principal
 * @param string $class_ul_submenu Clase CSS para las sublistas ul
 * @param string $class_li Clase CSS para los elementos li
 * @param string $class_link Clase CSS para los enlaces a
 * @param string $class_dropdown Clase CSS para los elementos con submenú
 * @param string $class_dropdown_toggle Clase CSS para los enlaces que abren submenús
 * @param string $dropdown_toggle_attr Atributos adicionales para los enlaces dropdown-toggle
 * @return string HTML del menú
 */
function mostrarMenuDinamico($menu_id, $config = []) {
    global $conexion;
    
    // Valores por defecto para configuración
    $defaults = [
        'class_nav' => 'navbar-nav',
        'class_ul_principal' => 'nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr' => 'data-toggle="dropdown" aria-expanded="false"',
        'active_path' => $_SERVER['REQUEST_URI'] ?? '',
        'max_depth' => 5, // Profundidad máxima para evitar bucles infinitos
    ];
    
    // Mezclar configuración proporcionada con valores predeterminados
    $config = array_merge($defaults, $config);
    
    // Verificar parámetros
    $menu_id = (int)$menu_id;
    if ($menu_id <= 0) {
        return '<!-- Error: ID de menú no válido -->';
    }
    
    // Cargar el modelo y obtener el menú
    require_once __DIR__ . '/../modulos/admin/menus/modelo.php';
    $menuModel = new Menu($conexion);
    
    // Obtener el menú
    $menu = $menuModel->obtenerPorId($menu_id);
    if (!$menu) {
        return '<!-- Error: Menú no encontrado (ID: ' . $menu_id . ') -->';
    }
    
    // Obtener los items del menú en formato de árbol
    $arbolItems = $menuModel->obtenerArbolItems($menu_id);
    if (empty($arbolItems)) {
        return '<!-- Menú sin elementos (ID: ' . $menu_id . ') -->';
    }
    
    // Generar HTML del menú
    $html = '<ul class="' . htmlspecialchars($config['class_ul_principal']) . '">';
    
    // Función recursiva para generar el menú
    $generarMenu = function($items, $nivel = 0) use (&$generarMenu, $config) {
        $resultado = '';
        
        // Evitar profundidad excesiva
        if ($nivel >= $config['max_depth']) {
            return $resultado;
        }
        
        foreach ($items as $item) {
            $tieneHijos = !empty($item['children']);
            
            // Clases CSS para el elemento li
            $clasesLi = [$config['class_li']];
            if ($tieneHijos) {
                $clasesLi[] = $config['class_dropdown'];
            }
            if (!empty($item['clase'])) {
                $clasesLi[] = $item['clase'];
            }
            
            // Determinar si es el item activo
            $urlActual = $config['active_path'];
            $urlItem = $item['url'];
            
            // Normalizar URLs para comparación
            $urlActualNormalizada = rtrim(parse_url($urlActual, PHP_URL_PATH), '/');
            $urlItemNormalizada = rtrim(parse_url($urlItem, PHP_URL_PATH), '/');
            
            $esActivo = ($urlActualNormalizada == $urlItemNormalizada) || 
                        (($urlItemNormalizada != '/' && $urlItemNormalizada != '#') && 
                         strpos($urlActualNormalizada, $urlItemNormalizada) === 0);
            
            if ($esActivo) {
                $clasesLi[] = 'active';
            }
            
            // Clases CSS para el enlace
            $clasesEnlace = [$config['class_link']];
            if ($tieneHijos) {
                $clasesEnlace[] = $config['class_dropdown_toggle'];
            }
            
            // Atributos adicionales para enlaces dropdown
            $atributosAdicionales = $tieneHijos ? ' ' . $config['dropdown_toggle_attr'] : '';
            
            // Target para enlaces externos
            $targetAttr = isset($item['target']) && $item['target'] !== '_self' ? 
                          ' target="' . htmlspecialchars($item['target']) . '"' : '';
            
            // Generar HTML para el elemento li
            $resultado .= '<li class="' . implode(' ', array_map('htmlspecialchars', $clasesLi)) . '">';
            
            // Generar HTML para el enlace
            $resultado .= '<a href="' . htmlspecialchars($item['url']) . '" ' .
                          'class="' . implode(' ', array_map('htmlspecialchars', $clasesEnlace)) . '"' .
                          $atributosAdicionales . $targetAttr . '>' . 
                          htmlspecialchars($item['titulo']) . '</a>';
            
            // Si tiene hijos, generar submenú recursivamente
            if ($tieneHijos) {
                $resultado .= '<ul class="' . htmlspecialchars($config['class_ul_submenu']) . '">';
                $resultado .= $generarMenu($item['children'], $nivel + 1);
                $resultado .= '</ul>';
            }
            
            $resultado .= '</li>';
        }
        
        return $resultado;
    };
    
    // Generar el menú recursivamente
    $html .= $generarMenu($arbolItems);
    $html .= '</ul>';
    
    // Envolver en un elemento nav si se especifica una clase
    if (!empty($config['class_nav'])) {
        $html = '<nav class="' . htmlspecialchars($config['class_nav']) . '">' . $html . '</nav>';
    }
    
    return $html;
}

/**
 * Función para mostrar un menú dinámico con estructura Bootstrap 5
 *
 * @param int $menu_id ID del menú a mostrar
 * @return string HTML del menú con clases de Bootstrap 5
 */
function mostrarMenuBootstrap5($menu_id) {
    $configuracion = [
        'class_nav' => 'navbar-nav me-auto mb-2 mb-lg-0',
        'class_ul_principal' => 'navbar-nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr' => 'data-bs-toggle="dropdown" aria-expanded="false" role="button"'
    ];
    
    return mostrarMenuDinamico($menu_id, $configuracion);
}

/**
 * Función para mostrar un menú dinámico con estructura Bootstrap 4
 *
 * @param int $menu_id ID del menú a mostrar
 * @return string HTML del menú con clases de Bootstrap 4
 */
function mostrarMenuBootstrap4($menu_id) {
    $configuracion = [
        'class_nav' => 'navbar-nav mr-auto',
        'class_ul_principal' => 'navbar-nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr' => 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"'
    ];
    
    return mostrarMenuDinamico($menu_id, $configuracion);
}

/**
 * Función para mostrar un menú lateral con estructura personalizada
 *
 * @param int $menu_id ID del menú a mostrar
 * @return string HTML del menú lateral
 */
function mostrarMenuLateral($menu_id) {
    $configuracion = [
        'class_nav' => 'menu-lateral',
        'class_ul_principal' => 'menu-items',
        'class_ul_submenu' => 'submenu',
        'class_li' => 'menu-item',
        'class_link' => 'menu-link',
        'class_dropdown' => 'has-children',
        'class_dropdown_toggle' => 'toggle-submenu',
        'dropdown_toggle_attr' => 'data-toggle="collapse" aria-expanded="false"'
    ];
    
    return mostrarMenuDinamico($menu_id, $configuracion);
}