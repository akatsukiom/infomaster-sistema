<?php
// includes/mostrar_menu.php

// 1) Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// 2) Carga tu configuración, constantes y la conexión a BD
//    (en tu includes/config.php debes tener $conexion y URL_SITIO definidos)
require_once __DIR__ . '/config.php';

// 3) Asegurarnos de que existe el modelo donde defines class Menu, MenuItem y Setting
$modelo = dirname(__DIR__) . '/modulos/admin/menus/modelo.php';
if (!file_exists($modelo)) {
    die("Error crítico: no encuentro el modelo de menús en $modelo");
}
require_once $modelo;

/**
 * Función principal para renderizar un menú dinámico (estructura jerárquica genérica).
 * Versión mejorada que soporta ser un submenú y múltiples niveles de anidación.
 * 
 * @param int $menu_id ID del menú a mostrar
 * @param array $config Configuración personalizada del menú
 * @param bool $is_submenu Indica si el menú es un submenú dentro de otro
 * @return string HTML generado para el menú
 */
function mostrarMenuDinamico($menu_id, $config = [], $is_submenu = false)
{
    global $conexion;

    // Configuración por defecto
    $defaults = [
        'class_nav'            => 'navbar-nav',
        'class_ul_principal'   => 'nav',
        'class_ul_submenu'     => 'dropdown-menu',
        'class_li'             => 'nav-item',
        'class_link'           => 'nav-link',
        'class_dropdown'       => 'dropdown',
        'class_dropdown_toggle'=> 'dropdown-toggle',
        'dropdown_toggle_attr' => 'data-toggle="dropdown" aria-expanded="false"',
        'active_path'          => $_SERVER['REQUEST_URI'] ?? '',
        'max_depth'            => 10, // Aumentamos la profundidad máxima
        'posicion'             => '', // Nueva: posición del menú
        'menu_class_prefix'    => '', // Nueva: prefijo para clases CSS según posición
        'menu_wrapper_class'   => '', // Nueva: clase para envoltorio adicional
        'menu_id_attr'         => '', // Nueva: atributo ID para el menú
    ];
    $config = array_merge($defaults, $config);

    // Validar ID
    $menu_id = (int)$menu_id;
    if ($menu_id <= 0) {
        return '<!-- Error: ID de menú no válido -->';
    }

    // Instanciar el modelo y obtener menú + árbol de items
    $menuModel = new Menu($conexion);
    $menu      = $menuModel->obtenerPorId($menu_id);
    if (!$menu) {
        return "<!-- Error: Menú no encontrado (ID: $menu_id) -->";
    }
    
    // Obtener árbol de items
    $arbolItems = $menuModel->obtenerArbolItems($menu_id);
    if (empty($arbolItems)) {
        return "<!-- Menú sin elementos (ID: $menu_id) -->";
    }

    // Aplicar prefijos de clase según posición si está disponible
    if (!empty($config['posicion']) && empty($config['menu_class_prefix'])) {
        $config['menu_class_prefix'] = 'menu-' . $config['posicion'] . '-';
    }

    // Función recursiva para pintar la lista
    $generar = function($items, $nivel = 0) use (&$generar, $config) {
        $html = '';
        if ($nivel >= $config['max_depth']) {
            return $html;
        }
        
        foreach ($items as $item) {
            $hijos = !empty($item['children']);
            $cl_li = [$config['class_li']];
            
            if ($hijos) $cl_li[] = $config['class_dropdown'];
            if (!empty($item['clase'])) $cl_li[] = $item['clase'];
            
            // Añadir clase para nivel de profundidad
            $cl_li[] = 'level-' . $nivel;
            
            // Añadir prefijo de posición a clases si existe
            if (!empty($config['menu_class_prefix'])) {
                $cl_li[] = $config['menu_class_prefix'] . 'item';
            }

            // Comparar URL activa
            $actualNorm = rtrim(parse_url($config['active_path'], PHP_URL_PATH), '/');
            $itemNorm   = rtrim(parse_url($item['url'], PHP_URL_PATH), '/');
            $activo     = ($actualNorm === $itemNorm)
                        || ($itemNorm !== '/' && strpos($actualNorm, $itemNorm) === 0);
            if ($activo) $cl_li[] = 'active';

            $cl_a = [$config['class_link']];
            if ($hijos) $cl_a[] = $config['class_dropdown_toggle'];
            
            // Añadir prefijo de posición a clases de enlaces si existe
            if (!empty($config['menu_class_prefix'])) {
                $cl_a[] = $config['menu_class_prefix'] . 'link';
            }

            $attr_toggle = $hijos ? ' ' . $config['dropdown_toggle_attr'] : '';
            $target      = isset($item['target']) && $item['target'] !== '_self'
                         ? ' target="'.htmlspecialchars($item['target']).'"'
                         : '';

            // Atributos adicionales del ítem si existen
            $item_attrs = '';
            if (!empty($item['attributes'])) {
                foreach ($item['attributes'] as $attr_name => $attr_value) {
                    $item_attrs .= ' ' . htmlspecialchars($attr_name) . '="' . htmlspecialchars($attr_value) . '"';
                }
            }

            $html .= '<li class="'.implode(' ', array_map('htmlspecialchars', $cl_li)).'">';
            $html .= '<a href="'.htmlspecialchars($item['url']).'" '
                   .  'class="'.implode(' ', array_map('htmlspecialchars', $cl_a)).'"'
                   .  $attr_toggle
                   .  $target
                   .  $item_attrs
                   .  '>'
                   .  htmlspecialchars($item['titulo'])
                   .  '</a>';

            if ($hijos) {
                // Clase adicional para nivel de submenu si existe prefijo
                $submenu_class = $config['class_ul_submenu'];
                if (!empty($config['menu_class_prefix'])) {
                    $submenu_class .= ' ' . $config['menu_class_prefix'] . 'submenu level-' . ($nivel + 1);
                }
                
                $html .= '<ul class="'.htmlspecialchars($submenu_class).'">';
                $html .= $generar($item['children'], $nivel + 1);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }
        return $html;
    };

    // Montar el UL principal
    $ul_class = $config['class_ul_principal'];
    if (!empty($config['menu_class_prefix'])) {
        $ul_class .= ' ' . $config['menu_class_prefix'] . 'principal';
    }
    
    // Añadir atributo ID al menú si se proporciona
    $menu_id_attr = '';
    if (!empty($config['menu_id_attr'])) {
        $menu_id_attr = ' id="' . htmlspecialchars($config['menu_id_attr']) . '"';
    } elseif (!empty($menu['slug'])) {
        $menu_id_attr = ' id="menu-' . htmlspecialchars($menu['slug']) . '"';
    } else {
        $menu_id_attr = ' id="menu-' . $menu_id . '"';
    }
    
    $out = '<ul class="' . htmlspecialchars($ul_class) . '"' . $menu_id_attr . '>';
    $out .= $generar($arbolItems);
    $out .= '</ul>';

    // Si es un submenú, no envolver en nav
    if ($is_submenu) {
        return $out;
    }

    // Envolver en <nav> si se ha definido clase
    if (!empty($config['class_nav'])) {
        // Añadir prefijo de posición a la clase nav si existe
        $nav_class = $config['class_nav'];
        if (!empty($config['menu_class_prefix'])) {
            $nav_class .= ' ' . $config['menu_class_prefix'] . 'nav';
        }
        
        $out = '<nav class="' . htmlspecialchars($nav_class) . '">' . $out . '</nav>';
    }
    
    // Envoltorio adicional si se especifica
    if (!empty($config['menu_wrapper_class'])) {
        $out = '<div class="' . htmlspecialchars($config['menu_wrapper_class']) . '">' . $out . '</div>';
    }

    return $out;
}

/**
 * Función extendida para soportar la obtención de menús por posición desde la configuración
 * en caso de que no exista en la clase Menu.
 * 
 * @param string $posicion Posición del menú (header, footer, sidebar)
 * @return array Lista de menús configurados para esta posición
 */
function obtenerMenusPorPosicion($posicion) {
    global $conexion;
    
    // Instanciar modelos
    $menuModel = new Menu($conexion);
    $settingModel = new Setting($conexion);
    
    // Si existe la función en el modelo de menús, usar esa
    if (method_exists($menuModel, 'obtenerPorPosicion')) {
        return $menuModel->obtenerPorPosicion($posicion);
    }
    
    // Si no existe, implementar la lógica aquí
    $menuConfigJson = $settingModel->obtener('menu_config', '[]');
    $menuConfig = json_decode($menuConfigJson, true);
    
    // Verificar si el JSON es válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<!-- Error al decodificar menu_config: ' . json_last_error_msg() . ' -->';
        return [];
    }
    
    // Añadir depuración
    echo '<!-- menuConfig: ' . print_r($menuConfig, true) . ' -->';
    
    // Filtrar menús por posición
    $menus = [];
    
    // Si es un array simple de configuraciones
    if (isset($menuConfig[0])) {
        foreach ($menuConfig as $config) {
            if (isset($config['posicion']) && $config['posicion'] === $posicion && isset($config['menu_id'])) {
                $menu = $menuModel->obtenerPorId($config['menu_id']);
                if ($menu) {
                    // Añadir información extra de la configuración
                    $menu['orden'] = $config['orden'] ?? 0;
                    $menu['menu_padre_id'] = $config['parent_id'] ?? null;
                    $menu['habilitado'] = $config['habilitado'] ?? 1;
                    
                    // Solo incluir si está habilitado
                    if ($menu['habilitado']) {
                        $menus[] = $menu;
                    }
                }
            }
        }
    }
    // Si es un array asociativo con posiciones como claves
    elseif (isset($menuConfig[$posicion]) && is_array($menuConfig[$posicion])) {
        foreach ($menuConfig[$posicion] as $config) {
            if (isset($config['menu_id'])) {
                $menu = $menuModel->obtenerPorId($config['menu_id']);
                if ($menu) {
                    // Añadir información extra de la configuración
                    $menu['orden'] = $config['orden'] ?? 0;
                    $menu['menu_padre_id'] = $config['parent_id'] ?? null;
                    $menu['habilitado'] = $config['habilitado'] ?? 1;
                    
                    // Solo incluir si está habilitado
                    if ($menu['habilitado']) {
                        $menus[] = $menu;
                    }
                }
            }
        }
    }
    
    // Ordenar menús por orden
    usort($menus, function($a, $b) {
        return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
    });
    
    return $menus;
}

/**
 * Función para mostrar todos los menús asociados a una posición específica.
 * 
 * @param string $posicion Posición donde están los menús (header, footer, sidebar, etc.)
 * @param array $config Configuración personalizada
 * @return string HTML generado con todos los menús de esa posición
 */
function mostrarMenusPorPosicion($posicion, $config = [])
{
    global $conexion;
    
    // Sanitizar la posición
    $posicion = preg_replace('/[^a-z0-9_-]/i', '', $posicion);
    if (empty($posicion)) {
        return '<!-- Error: Posición de menú no válida -->';
    }
    
    // Configuración por defecto
    $defaults = [
        'wrapper_class' => 'menus-' . $posicion,
        'menu_class_prefix' => 'menu-' . $posicion . '-',
    ];
    $config = array_merge($defaults, $config);
    
    // Obtener menús para esta posición usando la función auxiliar
    $menus = obtenerMenusPorPosicion($posicion);
    
    // Depuración
    echo '<!-- Menús obtenidos para posición ' . $posicion . ': ' . count($menus) . ' -->';
    
    if (empty($menus)) {
        return "<!-- No hay menús configurados para la posición: $posicion -->";
    }
    
    // Ordenar por el campo 'orden' si existe
    usort($menus, function($a, $b) {
        $orden_a = isset($a['orden']) ? (int)$a['orden'] : 999;
        $orden_b = isset($b['orden']) ? (int)$b['orden'] : 999;
        return $orden_a - $orden_b;
    });
    
    $output = '';
    $menusPadre = [];
    $menusHijo = [];
    
    // Separar menús padre y menús hijo
    foreach ($menus as $menu) {
        if (empty($menu['menu_padre_id'])) {
            $menusPadre[] = $menu;
        } else {
            $menusHijo[$menu['menu_padre_id']][] = $menu;
        }
    }
    
    // Procesar menús padre
    foreach ($menusPadre as $menuPadre) {
        // Configuración específica para este menú
        $menuConfig = $config;
        $menuConfig['posicion'] = $posicion;
        $menuConfig['menu_id_attr'] = 'menu-' . $posicion . '-' . $menuPadre['id'];
        
        // Añadir clases personalizadas si existen
        if (!empty($menuPadre['clase'])) {
            $menuConfig['menu_wrapper_class'] = $menuPadre['clase'];
        }
        
        // Renderizar este menú
        $output .= mostrarMenuDinamico($menuPadre['id'], $menuConfig);
        
        // Renderizar sus menús hijo, si existen
        if (isset($menusHijo[$menuPadre['id']])) {
            foreach ($menusHijo[$menuPadre['id']] as $menuHijo) {
                // Configuración específica para el menú hijo
                $hijoConfig = $config;
                $hijoConfig['posicion'] = $posicion;
                $hijoConfig['menu_class_prefix'] = $config['menu_class_prefix'] . 'child-';
                $hijoConfig['menu_id_attr'] = 'menu-' . $posicion . '-child-' . $menuHijo['id'];
                
                // Añadir al output
                $output .= mostrarMenuDinamico($menuHijo['id'], $hijoConfig);
            }
        }
    }
    
    // Envolver todos los menús si hay más de uno
    if (count($menusPadre) > 1 && !empty($config['wrapper_class'])) {
        $output = '<div class="' . htmlspecialchars($config['wrapper_class']) . '">' . $output . '</div>';
    }
    
    return $output;
}

/**
 * Helper para mostrar menús en el header
 */
function mostrarMenusHeader($config = [])
{
    // Añadir depuración
    echo '<!-- Ejecutando mostrarMenusHeader -->';
    
    // Configuración por defecto para menús del header
    $defaults = [
        'class_nav'             => 'navbar-nav header-nav',
        'class_ul_principal'    => 'navbar-nav header-menu',
        'class_ul_submenu'      => 'dropdown-menu',
        'class_li'              => 'nav-item',
        'class_link'            => 'nav-link',
        'class_dropdown'        => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr'  => 'data-bs-toggle="dropdown" aria-expanded="false" role="button"',
        'wrapper_class'         => 'header-menus-container',
    ];
    
    // Obtener configuración
    global $conexion;
    $settingModel = new Setting($conexion);
    $mostrarHeader = $settingModel->obtener('mostrar_menus_header', '0');
    
    // Verificar si los menús del header están habilitados
    if ($mostrarHeader != '1') {
        echo '<!-- Menús del header están deshabilitados en la configuración -->';
        return '';
    }
    
    return mostrarMenusPorPosicion('header', array_merge($defaults, $config));
}

/**
 * Helper para mostrar menús en el footer
 */
function mostrarMenusFooter($config = [])
{
    $defaults = [
        'class_nav'             => 'footer-nav',
        'class_ul_principal'    => 'footer-menu',
        'class_ul_submenu'      => 'footer-submenu',
        'class_li'              => 'footer-item',
        'class_link'            => 'footer-link',
        'class_dropdown'        => 'has-children',
        'class_dropdown_toggle' => 'toggle-children',
        'dropdown_toggle_attr'  => 'data-toggle="collapse" aria-expanded="false"',
        'wrapper_class'         => 'footer-menus-container',
    ];
    
    return mostrarMenusPorPosicion('footer', array_merge($defaults, $config));
}

/**
 * Helper para mostrar menús en la barra lateral
 */
function mostrarMenusSidebar($config = [])
{
    $defaults = [
        'class_nav'             => 'sidebar-nav',
        'class_ul_principal'    => 'sidebar-menu',
        'class_ul_submenu'      => 'sidebar-submenu',
        'class_li'              => 'sidebar-item',
        'class_link'            => 'sidebar-link',
        'class_dropdown'        => 'has-submenu',
        'class_dropdown_toggle' => 'submenu-toggle',
        'dropdown_toggle_attr'  => 'data-toggle="collapse" aria-expanded="false"',
        'wrapper_class'         => 'sidebar-menus-container',
    ];
    
    return mostrarMenusPorPosicion('sidebar', array_merge($defaults, $config));
}

// Helpers originales para Bootstrap 5, 4 o menú lateral
function mostrarMenuBootstrap5($menu_id) {
    return mostrarMenuDinamico($menu_id, [
        'class_nav'             => 'navbar-nav me-auto mb-2 mb-lg-0',
        'class_ul_principal'    => 'navbar-nav',
        'class_ul_submenu'      => 'dropdown-menu',
        'class_li'              => 'nav-item',
        'class_link'            => 'nav-link',
        'class_dropdown'        => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr'  => 'data-bs-toggle="dropdown" aria-expanded="false" role="button"',
    ]);
}

function mostrarMenuBootstrap4($menu_id) {
    return mostrarMenuDinamico($menu_id, [
        'class_nav'             => 'navbar-nav mr-auto',
        'class_ul_principal'    => 'navbar-nav',
        'class_ul_submenu'      => 'dropdown-menu',
        'class_li'              => 'nav-item',
        'class_link'            => 'nav-link',
        'class_dropdown'        => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr'  => 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"',
    ]);
}

function mostrarMenuLateral($menu_id) {
    return mostrarMenuDinamico($menu_id, [
        'class_nav'             => 'menu-lateral',
        'class_ul_principal'    => 'menu-items',
        'class_ul_submenu'      => 'submenu',
        'class_li'              => 'menu-item',
        'class_link'            => 'menu-link',
        'class_dropdown'        => 'has-children',
        'class_dropdown_toggle' => 'toggle-submenu',
        'dropdown_toggle_attr'  => 'data-toggle="collapse" aria-expanded="false"',
    ]);
}