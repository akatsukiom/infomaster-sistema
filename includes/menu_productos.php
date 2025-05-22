<?php
/**
 * Funciones para renderizar menús con integración de productos y categorías
 */

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// Incluir clases necesarias
require_once __DIR__ . '/../modulos/admin/menus/modelo.php';
require_once __DIR__ . '/../modulos/admin/menus/modelo_productos.php';

/**
 * Función para mostrar un menú con soporte para productos, categorías y menús inteligentes
 *
 * @param int $menu_id ID del menú a mostrar
 * @param array $config Configuración adicional para el menú
 * @return string HTML del menú
 */
function mostrarMenuProductos($menu_id, $config = []) {
    global $conexion;
    
    // Instanciar modelos
    $menuModel = new Menu($conexion);
    $menuProductos = new MenuProductos($conexion);
    
    // Valores por defecto para configuración
    $defaults = [
        'class_nav' => 'navbar-nav',
        'class_ul_principal' => 'nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_ul_submenu_nivel3' => 'dropdown-menu dropdown-submenu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_nivel3' => 'dropdown-item dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'class_dropdown_item' => 'dropdown-item',
        'dropdown_toggle_attr' => 'data-toggle="dropdown" aria-expanded="false"',
        'dropdown_toggle_nivel3_attr' => 'data-toggle="dropdown" aria-expanded="false"',
        'active_path' => $_SERVER['REQUEST_URI'] ?? '',
        'max_depth' => 3, // Profundidad máxima para evitar bucles infinitos
        'mostrar_iconos' => true, // Si debe mostrar los iconos
        'class_icon' => '', // Clase adicional para los iconos
        'mostrar_miniaturas_productos' => true, // Mostrar miniaturas de productos
        'mostrar_precios' => true, // Mostrar precios de productos
        'limite_smart_menu' => 10, // Límite de productos para menús inteligentes
        'cache_tiempo' => 3600, // Tiempo de caché en segundos (1 hora por defecto)
        'forzar_recarga' => false, // Si debe forzar la recarga de la caché
    ];
    
    // Mezclar configuración proporcionada con valores predeterminados
    $config = array_merge($defaults, $config);
    
    // Verificar parámetros
    $menu_id = (int)$menu_id;
    if ($menu_id <= 0) {
        return '<!-- Error: ID de menú no válido -->';
    }
    
    // Verificar el menú
    $menu = $menuModel->obtenerPorId($menu_id);
    if (!$menu) {
        return '<!-- Error: Menú no encontrado (ID: ' . $menu_id . ') -->';
    }
    
    // Intentar recuperar de la caché si está habilitada
    $cache_key = 'menu_productos_' . $menu_id . '_' . md5(serialize($config));
    $cache_file = __DIR__ . '/../cache/' . $cache_key . '.html';
    
    if (!$config['forzar_recarga'] && file_exists($cache_file) && (time() - filemtime($cache_file) < $config['cache_tiempo'])) {
        return file_get_contents($cache_file);
    }
    
    // Obtener los items del menú en formato de árbol
    $arbolItems = $menuModel->obtenerArbolItems($menu_id);
    if (empty($arbolItems)) {
        return '<!-- Menú sin elementos (ID: ' . $menu_id . ') -->';
    }
    
    // Generar HTML del menú
    $html = '<ul class="' . htmlspecialchars($config['class_ul_principal']) . '">';
    
    // Función recursiva para generar el menú
    $generarMenu = function($items, $nivel = 0) use (&$generarMenu, $config, $menuProductos) {
        $resultado = '';
        
        // Evitar profundidad excesiva
        if ($nivel >= $config['max_depth']) {
            return $resultado;
        }
        
        foreach ($items as $item) {
            $tieneHijos = !empty($item['children']);
            $tipo = $item['tipo'] ?? 'custom';
            
            // Para menús inteligentes, generar elementos dinámicamente
            if ($tipo === 'smart') {
                $resultado .= procesarMenuInteligente($item, $nivel, $config, $menuProductos, $generarMenu);
                continue;
            }
            
            // Para categorías, opcionalmente anexar productos
            if ($tipo === 'category' && isset($item['target_id']) && $item['target_id'] > 0) {
                $resultado .= procesarMenuCategoria($item, $nivel, $config, $menuProductos, $generarMenu);
                continue;
            }
            
            // Para productos individuales
            if ($tipo === 'product' && isset($item['target_id']) && $item['target_id'] > 0) {
                $resultado .= procesarMenuProducto($item, $nivel, $config, $menuProductos);
                continue;
            }
            
            // Procesar elementos estándar
            $resultado .= procesarMenuEstandar($item, $nivel, $config, $menuProductos, $generarMenu);
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
    
    // Guardar en caché si está habilitada
    if ($config['cache_tiempo'] > 0) {
        // Asegurarse de que el directorio de caché exista
        if (!is_dir(__DIR__ . '/../cache/')) {
            mkdir(__DIR__ . '/../cache/', 0755, true);
        }
        
        file_put_contents($cache_file, $html);
    }
    
    return $html;
}

/**
 * Procesar un elemento de menú inteligente
 */
function procesarMenuInteligente($item, $nivel, $config, $menuProductos, $generarMenu) {
    // Extraer configuración del menú inteligente
    $config_item = !empty($item['config']) ? json_decode($item['config'], true) : [];
    $tipo_smart = $config_item['tipo'] ?? 'nuevos';
    $limite = isset($config_item['limite']) ? (int)$config_item['limite'] : $config['limite_smart_menu'];
    
    // Obtener productos para este tipo de menú inteligente
    $productos = $menuProductos->obtenerProductosMenuInteligente($tipo_smart, $limite);
    
    // Si no hay productos, devolver solo el ítem principal
    if (empty($productos)) {
        return procesarMenuEstandar($item, $nivel, $config, $menuProductos, $generarMenu);
    }
    
    // Clases CSS para el elemento li
    $clasesLi = [];
    
    // Dependiendo del nivel, aplicamos diferentes clases
    if ($nivel === 0) {
        $clasesLi[] = $config['class_li'];
        // Siempre es dropdown ya que contiene productos
        $clasesLi[] = $config['class_dropdown'];
    } elseif ($nivel === 1) {
        $clasesLi[] = $config['class_dropdown_nivel3'];
    }
    
    // Agregar clases personalizadas definidas en el panel
    if (!empty($item['clase'])) {
        $clasesLi[] = $item['clase'];
    }
    
    // Determinar si es el item activo
    $urlActual = $config['active_path'];
    $urlItem = $item['url'];
    
    $esActivo = esEnlaceActivo($urlActual, $urlItem);
    if ($esActivo) {
        $clasesLi[] = 'active';
    }
    
    // Clases CSS para el enlace
    $clasesEnlace = [];
    
    if ($nivel === 0) {
        $clasesEnlace[] = $config['class_link'];
        // Como tiene productos, es un toggle
        $clasesEnlace[] = $config['class_dropdown_toggle'];
    } elseif ($nivel >= 1) {
        $clasesEnlace[] = $config['class_dropdown_item'];
        $clasesEnlace[] = $config['class_dropdown_toggle'];
    }
    
    // Atributos del toggle
    $atributosAdicionales = '';
    if ($nivel === 0) {
        $atributosAdicionales = ' ' . $config['dropdown_toggle_attr'];
    } elseif ($nivel >= 1) {
        $atributosAdicionales = ' ' . $config['dropdown_toggle_nivel3_attr'];
    }
    
    // Target para enlaces externos
    $targetAttr = isset($item['target']) && $item['target'] !== '_self' ? 
                  ' target="' . htmlspecialchars($item['target']) . '"' : '';
    
    // Generar HTML para el elemento li
    $liClass = !empty($clasesLi) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesLi)) . '"' : '';
    $resultado = '<li' . $liClass . '>';
    
    // Preparar icono si existe y está habilitada la opción
    $icono = '';
    if ($config['mostrar_iconos'] && !empty($item['icono'])) {
        $iconClass = $item['icono'] . ($config['class_icon'] ? ' ' . $config['class_icon'] : '');
        $icono = '<i class="' . htmlspecialchars($iconClass) . '"></i> ';
    }
    
    // Generar HTML para el enlace principal
    $aClass = !empty($clasesEnlace) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesEnlace)) . '"' : '';
    $resultado .= '<a href="' . htmlspecialchars($item['url']) . '"' . $aClass . $atributosAdicionales . $targetAttr . '>' . 
                  $icono . htmlspecialchars($item['titulo']);
    
    // Agregar caret para dropdown
    if ($nivel === 0) {
        $resultado .= ' <span class="caret"></span>';
    }
    
    $resultado .= '</a>';
    
    // Generar submenú con productos
    $submenuClass = $nivel === 0 ? $config['class_ul_submenu'] : $config['class_ul_submenu_nivel3'];
    $resultado .= '<ul class="' . htmlspecialchars($submenuClass) . ' smart-menu-' . htmlspecialchars($tipo_smart) . '">';
    
    // Agregar los productos como elementos del submenú
    foreach ($productos as $producto) {
        $resultado .= '<li class="' . htmlspecialchars($config['class_dropdown_item']) . ' producto-item">';
        
        // Enlace al producto
        $resultado .= '<a href="' . htmlspecialchars($producto['url']) . '" class="' . htmlspecialchars($config['class_dropdown_item']) . '">';
        
        // Miniatura del producto si está habilitada
        if ($config['mostrar_miniaturas_productos'] && !empty($producto['imagen_url'])) {
            $resultado .= '<img src="' . htmlspecialchars($producto['imagen_url']) . '" class="producto-miniatura" alt="' . htmlspecialchars($producto['nombre']) . '"> ';
        }
        
        // Nombre del producto
        $resultado .= htmlspecialchars($producto['nombre']);
        
        // Precio si está habilitado
        if ($config['mostrar_precios']) {
            $resultado .= ' <span class="producto-precio">$' . htmlspecialchars($producto['precio_formateado']) . '</span>';
        }
        
        $resultado .= '</a>';
        $resultado .= '</li>';
    }
    
    // Enlace para ver todos los productos de este tipo
    $resultado .= '<li class="divider"></li>';
    $resultado .= '<li class="' . htmlspecialchars($config['class_dropdown_item']) . ' ver-todos">';
    $resultado .= '<a href="' . htmlspecialchars($item['url']) . '" class="' . htmlspecialchars($config['class_dropdown_item']) . '">';
    $resultado .= '<i class="fas fa-list"></i> Ver todos';
    $resultado .= '</a>';
    $resultado .= '</li>';
    
    $resultado .= '</ul>';
    $resultado .= '</li>';
    
    return $resultado;
}

/**
 * Procesar un elemento de menú de categoría
 */
function procesarMenuCategoria($item, $nivel, $config, $menuProductos, $generarMenu) {
    // Solo expandir automáticamente si está en nivel 0 o 1
    if ($nivel >= 2) {
        return procesarMenuEstandar($item, $nivel, $config, $menuProductos, $generarMenu);
    }
    
    // Obtener productos de la categoría si corresponde
    $categoria_id = (int)$item['target_id'];
    $productos = $menuProductos->obtenerProductosPorCategoria($categoria_id, 10); // Limitar a 10 productos
    
    // Si no hay productos o si ya tiene hijos definidos, no expand    
    if (empty($productos) || !empty($item['children'])) {
        return procesarMenuEstandar($item, $nivel, $config, $menuProductos, $generarMenu);
    }
    
    // Clases CSS para el elemento li
    $clasesLi = [];
    
    // Dependiendo del nivel, aplicamos diferentes clases
    if ($nivel === 0) {
        $clasesLi[] = $config['class_li'];
        // Como tiene productos, es un dropdown
        $clasesLi[] = $config['class_dropdown'];
    } elseif ($nivel === 1) {
        $clasesLi[] = $config['class_dropdown_nivel3'];
    }
    
    // Agregar clases personalizadas definidas en el panel
    if (!empty($item['clase'])) {
        $clasesLi[] = $item['clase'];
    }
    
    // Determinar si es el item activo
    $urlActual = $config['active_path'];
    $urlItem = $item['url'];
    
    $esActivo = esEnlaceActivo($urlActual, $urlItem);
    if ($esActivo) {
        $clasesLi[] = 'active';
    }
    
    // Clases CSS para el enlace
    $clasesEnlace = [];
    
    if ($nivel === 0) {
        $clasesEnlace[] = $config['class_link'];
        // Como tiene productos, es un toggle
        $clasesEnlace[] = $config['class_dropdown_toggle'];
    } elseif ($nivel >= 1) {
        $clasesEnlace[] = $config['class_dropdown_item'];
        $clasesEnlace[] = $config['class_dropdown_toggle'];
    }
    
    // Atributos del toggle
    $atributosAdicionales = '';
    if ($nivel === 0) {
        $atributosAdicionales = ' ' . $config['dropdown_toggle_attr'];
    } elseif ($nivel >= 1) {
        $atributosAdicionales = ' ' . $config['dropdown_toggle_nivel3_attr'];
    }
    
    // Target para enlaces externos
    $targetAttr = isset($item['target']) && $item['target'] !== '_self' ? 
                  ' target="' . htmlspecialchars($item['target']) . '"' : '';
    
    // Generar HTML para el elemento li
    $liClass = !empty($clasesLi) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesLi)) . '"' : '';
    $resultado = '<li' . $liClass . '>';
    
    // Preparar icono si existe y está habilitada la opción
    $icono = '';
    if ($config['mostrar_iconos'] && !empty($item['icono'])) {
        $iconClass = $item['icono'] . ($config['class_icon'] ? ' ' . $config['class_icon'] : '');
        $icono = '<i class="' . htmlspecialchars($iconClass) . '"></i> ';
    }
    
    // Generar HTML para el enlace principal
    $aClass = !empty($clasesEnlace) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesEnlace)) . '"' : '';
    $resultado .= '<a href="' . htmlspecialchars($item['url']) . '"' . $aClass . $atributosAdicionales . $targetAttr . '>' . 
                  $icono . htmlspecialchars($item['titulo']);
    
    // Agregar caret para dropdown
    if ($nivel === 0) {
        $resultado .= ' <span class="caret"></span>';
    }
    
    $resultado .= '</a>';
    
    // Generar submenú con productos
    $submenuClass = $nivel === 0 ? $config['class_ul_submenu'] : $config['class_ul_submenu_nivel3'];
    $resultado .= '<ul class="' . htmlspecialchars($submenuClass) . ' categoria-productos">';
    
    // Agregar los productos como elementos del submenú
    foreach ($productos as $producto) {
        $resultado .= '<li class="' . htmlspecialchars($config['class_dropdown_item']) . ' producto-item">';
        
        // Enlace al producto
        $resultado .= '<a href="' . htmlspecialchars($producto['url']) . '" class="' . htmlspecialchars($config['class_dropdown_item']) . '">';
        
        // Miniatura del producto si está habilitada
        if ($config['mostrar_miniaturas_productos'] && !empty($producto['imagen_url'])) {
            $resultado .= '<img src="' . htmlspecialchars($producto['imagen_url']) . '" class="producto-miniatura" alt="' . htmlspecialchars($producto['nombre']) . '"> ';
        }
        
        // Nombre del producto
        $resultado .= htmlspecialchars($producto['nombre']);
        
        // Precio si está habilitado
        if ($config['mostrar_precios']) {
            $resultado .= ' <span class="producto-precio">$' . htmlspecialchars($producto['precio_formateado']) . '</span>';
        }
        
        $resultado .= '</a>';
        $resultado .= '</li>';
    }
    
    // Enlace para ver todos los productos de esta categoría
    $resultado .= '<li class="divider"></li>';
    $resultado .= '<li class="' . htmlspecialchars($config['class_dropdown_item']) . ' ver-todos">';
    $resultado .= '<a href="' . htmlspecialchars($item['url']) . '" class="' . htmlspecialchars($config['class_dropdown_item']) . '">';
    $resultado .= '<i class="fas fa-list"></i> Ver todos los productos';
    $resultado .= '</a>';
    $resultado .= '</li>';
    
    $resultado .= '</ul>';
    $resultado .= '</li>';
    
    return $resultado;
}

/**
 * Procesar un elemento de menú de producto
 */
function procesarMenuProducto($item, $nivel, $config, $menuProductos) {
    // Obtener datos completos del producto
    $producto_id = (int)$item['target_id'];
    $producto = $menuProductos->obtenerProducto($producto_id);
    
    // Si no se encuentra el producto, mostrar como enlace estándar
    if (!$producto) {
        return procesarMenuEstandar($item, $nivel, $config, $menuProductos, null);
    }
    
    // Clases CSS para el elemento li
    $clasesLi = [];
    
    // Dependiendo del nivel, aplicamos diferentes clases
    if ($nivel === 0) {
        $clasesLi[] = $config['class_li'];
    }
    
    // Agregar clases personalizadas definidas en el panel
    if (!empty($item['clase'])) {
        $clasesLi[] = $item['clase'];
    }
    
    // Clases adicionales para productos
    $clasesLi[] = 'menu-product-item';
    
    // Determinar si es el item activo
    $urlActual = $config['active_path'];
    $urlItem = $item['url'];
    
    $esActivo = esEnlaceActivo($urlActual, $urlItem);
    if ($esActivo) {
        $clasesLi[] = 'active';
    }
    
    // Clases CSS para el enlace
    $clasesEnlace = [];
    
    if ($nivel === 0) {
        $clasesEnlace[] = $config['class_link'];
    } elseif ($nivel >= 1) {
        $clasesEnlace[] = $config['class_dropdown_item'];
    }
    
    // Target para enlaces externos
    $targetAttr = isset($item['target']) && $item['target'] !== '_self' ? 
                  ' target="' . htmlspecialchars($item['target']) . '"' : '';
    
    // Generar HTML para el elemento li
    $liClass = !empty($clasesLi) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesLi)) . '"' : '';
    $resultado = '<li' . $liClass . '>';
    
    // Generar HTML para el enlace
    $aClass = !empty($clasesEnlace) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesEnlace)) . '"' : '';
    $resultado .= '<a href="' . htmlspecialchars($producto['url']) . '"' . $aClass . $targetAttr . '>';
    
    // Miniatura del producto si está habilitada
    if ($config['mostrar_miniaturas_productos'] && !empty($producto['imagen_url'])) {
        $resultado .= '<img src="' . htmlspecialchars($producto['imagen_url']) . '" class="producto-miniatura" alt="' . htmlspecialchars($producto['nombre']) . '"> ';
    }
    
    // Icono si existe y está habilitado
    if ($config['mostrar_iconos'] && !empty($item['icono'])) {
        $iconClass = $item['icono'] . ($config['class_icon'] ? ' ' . $config['class_icon'] : '');
        $resultado .= '<i class="' . htmlspecialchars($iconClass) . '"></i> ';
    }
    
    // Título del producto (usar título personalizado o nombre del producto)
    $titulo = !empty($item['titulo']) ? $item['titulo'] : $producto['nombre'];
    $resultado .= htmlspecialchars($titulo);
    
    // Precio si está habilitado
    if ($config['mostrar_precios']) {
        $resultado .= ' <span class="producto-precio">$' . htmlspecialchars($producto['precio_formateado']) . '</span>';
    }
    
    $resultado .= '</a>';
    $resultado .= '</li>';
    
    return $resultado;
}

/**
 * Procesar un elemento de menú estándar
 */
function procesarMenuEstandar($item, $nivel, $config, $menuProductos, $generarMenu) {
    $tieneHijos = !empty($item['children']);
    
    // Clases CSS para el elemento li
    $clasesLi = [];
    
    // Dependiendo del nivel, aplicamos diferentes clases
    if ($nivel === 0) {
        $clasesLi[] = $config['class_li'];
        if ($tieneHijos) {
            $clasesLi[] = $config['class_dropdown'];
        }
    } elseif ($nivel === 1) {
        if ($tieneHijos) {
            $clasesLi[] = $config['class_dropdown_nivel3'];
        }
    }
    
    // Agregar clases personalizadas definidas en el panel
    if (!empty($item['clase'])) {
        $clasesLi[] = $item['clase'];
    }
    
    // Determinar si es el item activo
    $urlActual = $config['active_path'];
    $urlItem = $item['url'];
    
    $esActivo = esEnlaceActivo($urlActual, $urlItem);
    if ($esActivo) {
        $clasesLi[] = 'active';
    }
    
    // Clases CSS para el enlace
    $clasesEnlace = [];
    
    if ($nivel === 0) {
        $clasesEnlace[] = $config['class_link'];
        if ($tieneHijos) {
            $clasesEnlace[] = $config['class_dropdown_toggle'];
        }
    } elseif ($nivel >= 1) {
        $clasesEnlace[] = $config['class_dropdown_item'];
        if ($tieneHijos) {
            $clasesEnlace[] = $config['class_dropdown_toggle'];
        }
    }
    
    // Atributos adicionales para enlaces dropdown
    $atributosAdicionales = '';
    if ($tieneHijos) {
        if ($nivel === 0) {
            $atributosAdicionales = ' ' . $config['dropdown_toggle_attr'];
        } elseif ($nivel >= 1) {
            $atributosAdicionales = ' ' . $config['dropdown_toggle_nivel3_attr'];
        }
    }
    
    // Target para enlaces externos
    $targetAttr = isset($item['target']) && $item['target'] !== '_self' ? 
                  ' target="' . htmlspecialchars($item['target']) . '"' : '';
    
    // Generar HTML para el elemento li
    $liClass = !empty($clasesLi) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesLi)) . '"' : '';
    $resultado = '<li' . $liClass . '>';
    
    // Preparar icono si existe y está habilitada la opción
    $icono = '';
    if ($config['mostrar_iconos'] && !empty($item['icono'])) {
        $iconClass = $item['icono'] . ($config['class_icon'] ? ' ' . $config['class_icon'] : '');
        $icono = '<i class="' . htmlspecialchars($iconClass) . '"></i> ';
    }
    
    // Generar HTML para el enlace
    $aClass = !empty($clasesEnlace) ? ' class="' . implode(' ', array_map('htmlspecialchars', $clasesEnlace)) . '"' : '';
    $resultado .= '<a href="' . htmlspecialchars($item['url']) . '"' . $aClass . $atributosAdicionales . $targetAttr . '>' . 
                  $icono . htmlspecialchars($item['titulo']);
    
    // Agregar caret para dropdown si tiene hijos
    if ($tieneHijos && $nivel === 0) {
        $resultado .= ' <span class="caret"></span>';
    }
    
    $resultado .= '</a>';
    
    // Si tiene hijos, generar submenú recursivamente
    if ($tieneHijos && $generarMenu) {
        $submenuClass = $nivel === 0 ? $config['class_ul_submenu'] : $config['class_ul_submenu_nivel3'];
        $resultado .= '<ul class="' . htmlspecialchars($submenuClass) . '">';
        $resultado .= $generarMenu($item['children'], $nivel + 1);
        $resultado .= '</ul>';
    }
    
    $resultado .= '</li>';
    
    return $resultado;
}

/**
 * Verificar si un enlace corresponde a la página actual
 */
function esEnlaceActivo($urlActual, $urlItem) {
    // Normalizar URLs para comparación
    $urlActualNormalizada = rtrim(parse_url($urlActual, PHP_URL_PATH), '/');
    $urlItemNormalizada = rtrim(parse_url($urlItem, PHP_URL_PATH), '/');
    
    return ($urlActualNormalizada == $urlItemNormalizada) || 
           (($urlItemNormalizada != '/' && $urlItemNormalizada != '#') && 
            strpos($urlActualNormalizada, $urlItemNormalizada) === 0);
}

/**
 * Mostrar un menú con integración de productos para Bootstrap 5
 */
function mostrarMenuProductosBootstrap5($menu_id, $config = []) {
    $configuracion = [
        'class_nav' => 'navbar-nav me-auto mb-2 mb-lg-0',
        'class_ul_principal' => 'navbar-nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_ul_submenu_nivel3' => 'dropdown-menu dropdown-submenu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_nivel3' => 'dropend',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'class_dropdown_item' => 'dropdown-item',
        'dropdown_toggle_attr' => 'data-bs-toggle="dropdown" aria-expanded="false" role="button"',
        'dropdown_toggle_nivel3_attr' => 'data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"',
        'mostrar_miniaturas_productos' => true,
        'mostrar_precios' => true
    ];
    
    // Mezclar con la configuración adicional
    $configuracion = array_merge($configuracion, $config);
    
    return mostrarMenuProductos($menu_id, $configuracion);
}

/**
 * Mostrar un menú con integración de productos para Bootstrap 4
 */
function mostrarMenuProductosBootstrap4($menu_id, $config = []) {
    $configuracion = [
        'class_nav' => 'navbar-nav mr-auto',
        'class_ul_principal' => 'navbar-nav',
        'class_ul_submenu' => 'dropdown-menu',
        'class_ul_submenu_nivel3' => 'dropdown-menu dropdown-submenu',
        'class_li' => 'nav-item',
        'class_link' => 'nav-link',
        'class_dropdown' => 'dropdown',
        'class_dropdown_nivel3' => 'dropdown-submenu',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'class_dropdown_item' => 'dropdown-item',
        'dropdown_toggle_attr' => 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"',
        'dropdown_toggle_nivel3_attr' => 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"',
        'mostrar_miniaturas_productos' => true,
        'mostrar_precios' => true
    ];
    
    // Mezclar con la configuración adicional
    $configuracion = array_merge($configuracion, $config);
    
    return mostrarMenuProductos($menu_id, $configuracion);
}

/**
 * Generar HTML para un carrusel de productos basado en un menú inteligente
 */
function mostrarCarruselMenuInteligente($menu_id, $config = []) {
    global $conexion;
    
    // Valores por defecto
    $defaults = [
        'tipo' => 'destacados', // Tipo de productos a mostrar
        'limite' => 8, // Número de productos
        'titulo' => 'Productos Destacados', // Título del carrusel
        'columnas_desktop' => 4, // Productos por fila en desktop
        'columnas_tablet' => 3, // Productos por fila en tablet
        'columnas_movil' => 2, // Productos por fila en móvil
        'class_container' => 'products-carousel my-4', // Clase del contenedor
        'mostrar_precios' => true, // Mostrar precios
        'mostrar_boton_comprar' => true, // Mostrar botón de compra
        'cache_tiempo' => 3600, // 1 hora de caché
    ];
    
    // Mezclar configuración
    $config = array_merge($defaults, $config);
    
    // Instanciar modelos
    $menuProductos = new MenuProductos($conexion);
    
    // Obtener productos
    $productos = $menuProductos->obtenerProductosMenuInteligente($config['tipo'], $config['limite']);
    
    if (empty($productos)) {
        return '<!-- No hay productos para mostrar en el carrusel -->';
    }
    
    // Generar HTML del carrusel
    $html = '<div class="' . htmlspecialchars($config['class_container']) . '">';
    
    // Título del carrusel
    if (!empty($config['titulo'])) {
        $html .= '<h2 class="section-title">' . htmlspecialchars($config['titulo']) . '</h2>';
    }
    
    // Contenedor de productos
    $html .= '<div class="row product-grid">';
    
    // Calcular clases responsive
    $class_desktop = 'col-lg-' . (12 / $config['columnas_desktop']);
    $class_tablet = 'col-md-' . (12 / $config['columnas_tablet']);
    $class_movil = 'col-' . (12 / $config['columnas_movil']);
    
    // Generar tarjetas de productos
    foreach ($productos as $producto) {
        $html .= '<div class="' . $class_desktop . ' ' . $class_tablet . ' ' . $class_movil . ' mb-4">';
        $html .= '<div class="card product-card h-100">';
        
        // Imagen del producto
        $html .= '<a href="' . htmlspecialchars($producto['url']) . '" class="product-image-link">';
        $html .= '<img src="' . htmlspecialchars($producto['imagen_url']) . '" class="card-img-top product-image" alt="' . htmlspecialchars($producto['nombre']) . '">';
        $html .= '</a>';
        
        // Contenido de la tarjeta
        $html .= '<div class="card-body">';
        $html .= '<h5 class="card-title product-title">';
        $html .= '<a href="' . htmlspecialchars($producto['url']) . '">' . htmlspecialchars($producto['nombre']) . '</a>';
        $html .= '</h5>';
        
        // Precio
        if ($config['mostrar_precios']) {
            $html .= '<p class="card-text product-price">$' . htmlspecialchars($producto['precio_formateado']) . '</p>';
        }
        
        // Botón de compra
        if ($config['mostrar_boton_comprar']) {
            $html .= '<a href="' . htmlspecialchars($producto['url']) . '" class="btn btn-primary btn-sm">Ver producto</a>';
            
            // Si existe función para agregar al carrito
            if (function_exists('botonAgregarAlCarrito')) {
                $html .= botonAgregarAlCarrito($producto['id']);
            }
        }
        
        $html .= '</div>'; // card-body
        $html .= '</div>'; // card
        $html .= '</div>'; // col
    }
    
    $html .= '</div>'; // row
    
    // Enlace para ver todos
    $html .= '<div class="text-center mt-3">';
    $html .= '<a href="' . URL_SITIO . 'productos/' . htmlspecialchars($config['tipo']) . '" class="btn btn-outline-primary">Ver todos</a>';
    $html .= '</div>';
    
    $html .= '</div>'; // container
    
    return $html;
}