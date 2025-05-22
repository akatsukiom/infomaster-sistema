<?php
// Mostrar errores en desarrollo (quita en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

// 1) Incluir configuración global y funciones
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funciones.php';

// 2) Cargar modelo de categorías y leer todas
require_once __DIR__ . '/../modulos/admin/categorias/modelo.php';
$catModel   = new Categoria($conexion);
$categorias = $catModel->obtenerTodas();

// 3) Inicializar carrito si existe
if (file_exists(__DIR__ . '/../modulos/carrito/modelo.php')) {
    require_once __DIR__ . '/../modulos/carrito/modelo.php';
    Carrito::inicializar();
}

// 4) Cargar modelo de menús y configuración de Settings
require_once __DIR__ . '/../modulos/admin/menus/modelo.php';
$settingModel   = new Setting($conexion);
$menuConfigJson = $settingModel->obtener('menu_config', '[]');
$menuConfig     = json_decode($menuConfigJson, true);

// 5) Definir configuraciones de estilos para diferentes posiciones
$estilos = [
    // Estilo por defecto (menú principal)
    'default' => [
        'class_nav'             => 'main-nav',
        'class_ul_principal'    => 'menu',
        'class_ul_submenu'      => 'dropdown-menu',
        'class_li'              => 'menu-item',
        'class_link'            => 'menu-link',
        'class_dropdown'        => 'dropdown',
        'class_dropdown_toggle' => 'dropdown-toggle',
        'dropdown_toggle_attr'  => 'aria-expanded="false"',
    ],
    // Menú secundario (ejemplo)
    'secundario' => [
        'class_nav'             => 'secondary-nav',
        'class_ul_principal'    => 'menu-secondary',
        'class_ul_submenu'      => 'dropdown-menu-secondary',
        'class_li'              => 'menu-item-secondary',
        'class_link'            => 'menu-link-secondary',
        'class_dropdown'        => 'dropdown-secondary',
        'class_dropdown_toggle' => 'dropdown-toggle-secondary',
        'dropdown_toggle_attr'  => 'aria-expanded="false"',
    ],
    // Menú compacto (ejemplo)
    'compacto' => [
        'class_nav'             => 'compact-nav',
        'class_ul_principal'    => 'menu-compact',
        'class_ul_submenu'      => 'dropdown-menu-compact',
        'class_li'              => 'menu-item-compact',
        'class_link'            => 'menu-link-compact',
        'class_dropdown'        => 'dropdown-compact',
        'class_dropdown_toggle' => 'dropdown-toggle-compact',
        'dropdown_toggle_attr'  => 'aria-expanded="false"',
    ]
];

// 6) Función para organizar menús por posición (definición única)
function organizarMenusPorPosicion($menuConfig) {
    $menusPorPosicion = [];
    
    // Si el formato es un array numérico (formato plano)
    if (isset($menuConfig[0])) {
        foreach ($menuConfig as $item) {
            $posicion = $item['posicion'] ?? 'principal';
            $menuId = $item['menu_id'] ?? ($item['id'] ?? 0);
            
            if ($menuId > 0) {
                if (!isset($menusPorPosicion[$posicion])) {
                    $menusPorPosicion[$posicion] = [];
                }
                
                $menusPorPosicion[$posicion][] = [
                    'id' => $menuId,
                    'posicion' => $posicion,
                    'orden' => $item['orden'] ?? 0,
                    'estilo' => $item['estilo'] ?? 'default',
                    'parent_id' => $item['parent_id'] ?? 0,
                    'habilitado' => $item['habilitado'] ?? 1,
                    'url' => $item['url'] ?? null,
                    'items' => $item['items'] ?? [],
                ];
            }
        }
    }
    // Si el formato es asociativo con posiciones como claves
    else if (is_array($menuConfig)) {
        foreach (['header', 'footer', 'sidebar', 'principal', 'top', 'bottom'] as $posicion) {
            if (isset($menuConfig[$posicion]) && is_array($menuConfig[$posicion])) {
                if (!isset($menusPorPosicion[$posicion])) {
                    $menusPorPosicion[$posicion] = [];
                }
                
                foreach ($menuConfig[$posicion] as $item) {
                    $menuId = $item['menu_id'] ?? 0;
                    
                    if ($menuId > 0) {
                        $menusPorPosicion[$posicion][] = [
                            'id' => $menuId,
                            'posicion' => $posicion,
                            'orden' => $item['orden'] ?? 0,
                            'estilo' => $item['estilo'] ?? 'default',
                            'parent_id' => $item['parent_id'] ?? 0,
                            'habilitado' => $item['habilitado'] ?? 1,
                            'url' => $item['url'] ?? null,
                            'items' => $item['items'] ?? [],
                        ];
                    }
                }
            }
        }
    }
    
    // Si hay un menú en header, asegurarse que también esté en principal
    if (isset($menusPorPosicion['header']) && !empty($menusPorPosicion['header'])) {
        if (!isset($menusPorPosicion['principal'])) {
            $menusPorPosicion['principal'] = [];
        }
        
        // Verificar si el primer menú de header ya está en principal
        $primeraEntrada = $menusPorPosicion['header'][0];
        $encontrado = false;
        
        foreach ($menusPorPosicion['principal'] as $menu) {
            if ($menu['id'] == $primeraEntrada['id']) {
                $encontrado = true;
                break;
            }
        }
        
        // Si no está, añadirlo
        if (!$encontrado) {
            $menusPorPosicion['principal'][] = $primeraEntrada;
        }
    }
    
    // Ordenar menús por orden en cada posición
    foreach ($menusPorPosicion as $posicion => $menus) {
        usort($menus, function($a, $b) {
            return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
        });
        $menusPorPosicion[$posicion] = $menus;
    }
    
    return $menusPorPosicion;
}

// 7) Organizar menús por posición (usamos la función definida anteriormente)
$menusPorPosicion = organizarMenusPorPosicion($menuConfig);

// 8) Incluir helper para pintar el menú una sola vez
require_once __DIR__ . '/mostrar_menu.php';

// 9) Función para obtener iconos de categorías
function getIconoCategoria($nombre) {
    $iconos = [
        'games' => '<i class="fas fa-gamepad"></i>',
        'gaming' => '<i class="fas fa-gamepad"></i>',
        'juegos' => '<i class="fas fa-gamepad"></i>',
        'streaming' => '<i class="fas fa-play"></i>',
        'netflix' => '<i class="fas fa-play"></i>',
        'series' => '<i class="fas fa-tv"></i>',
        'software' => '<i class="fas fa-download"></i>',
        'programas' => '<i class="fas fa-download"></i>',
        'windows' => '<i class="fab fa-windows"></i>',
        'cursos' => '<i class="fas fa-graduation-cap"></i>',
        'educacion' => '<i class="fas fa-book"></i>',
        'libros' => '<i class="fas fa-book-open"></i>',
        'musica' => '<i class="fas fa-music"></i>',
        'spotify' => '<i class="fab fa-spotify"></i>',
        'peliculas' => '<i class="fas fa-film"></i>',
        'movies' => '<i class="fas fa-film"></i>'
    ];
    
    $nombreLower = strtolower($nombre);
    foreach ($iconos as $key => $icono) {
        if (strpos($nombreLower, $key) !== false) {
            return $icono;
        }
    }
    return '<i class="fas fa-tag"></i>'; // icono por defecto
}

// Debug - comentamos para mostrar la configuración
// echo '<!-- Menú raw config: ' . htmlspecialchars($menuConfigJson) . ' -->';
// echo '<!-- Menús organizados por posición: -->';
// echo '<!-- ' . htmlspecialchars(print_r($menusPorPosicion, true)) . ' -->';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) : 'InfoMaster - Productos Digitales' ?></title>
    
    <!-- Favicon mejorado -->
    <link rel="shortcut icon" href="<?= URL_SITIO ?>img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= URL_SITIO ?>img/favicon.ico" type="image/x-icon">
    
    <!-- Meta tags SEO -->
    <meta name="description" content="InfoMaster - Tu plataforma de productos digitales con entrega inmediata y wallet integrado">
    <meta name="keywords" content="productos digitales, gaming, streaming, software, cursos online">
    <meta name="author" content="InfoMaster">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= URL_SITIO ?>">
    <meta property="og:title" content="<?= isset($titulo) ? htmlspecialchars($titulo) : 'InfoMaster - Productos Digitales' ?>">
    <meta property="og:description" content="Tu plataforma de productos digitales con entrega inmediata">
    <meta property="og:image" content="<?= URL_SITIO ?>img/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= URL_SITIO ?>">
    <meta property="twitter:title" content="<?= isset($titulo) ? htmlspecialchars($titulo) : 'InfoMaster - Productos Digitales' ?>">
    <meta property="twitter:description" content="Tu plataforma de productos digitales con entrega inmediata">
    <meta property="twitter:image" content="<?= URL_SITIO ?>img/og-image.jpg">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?= URL_SITIO ?>css/style.css" as="style">
    <link rel="preload" href="<?= URL_SITIO ?>css/home.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    
    <!-- Swiper CSS (Carrusel) -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@9/swiper-bundle.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/nuevo-estilo.css">
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/style.css">
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/home.css?v=3.0">
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/responsive.css">
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/perfil.css">
    
    <style>
        :root {
            --primary-color: #3a86ff;
            --primary-accent: #4361ee;
            --secondary-color: #8338ec;
            --secondary-accent: #ff6b6b;
            --accent-color: #ff006e;
            --text-color: #2b2d42;
            --text-dark: #333333;
            --text-light: #ffffff;
            --light-color: #f8f9fa;
            --light-bg: #f8f9fa;
            --dark-color: #212529;
            --success-color: #38b000;
            --warning-color: #ffbe0b;
            --danger-color: #ff5400;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-soft: 0 8px 30px rgba(0,0,0,0.06);
            --transition: all 0.3s ease;
            --transition-smooth: all 0.3s ease-in-out;
        }

        /* ————— Estilos Generales ————— */
        * {
            box-sizing: border-box;
        }
        
        body { 
            margin: 0; 
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: var(--text-color); 
            line-height: 1.6; 
            background-color: var(--light-bg);
        }
        
        .container { 
            width: 100%; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 15px; 
        }

        /* ————— Header Mejorado ————— */
        header { 
            background: #fff; 
            box-shadow: var(--shadow-soft); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .header-container { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 0; 
            flex-wrap: wrap; 
        }
        
        .logo { 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            color: var(--text-color); 
            font-size: 1.5rem; 
            font-weight: 700; 
            transition: var(--transition); 
        }
        
        .logo:hover { 
            color: var(--primary-accent); 
            transform: scale(1.02);
        }
        
        .logo-img { 
            width: 40px; 
            height: 40px; 
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-right: 10px; 
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .logo-img span { 
            color: #fff; 
            font-weight: bold; 
            font-size: 1.1rem;
        }
        
        /* ————— Navegación Principal Mejorada ————— */
        .main-nav { 
            flex-grow: 1; 
            margin: 0 30px; 
        }
        
        .menu { 
            display: flex; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            justify-content: center; 
        }
        
        .menu-item { 
            position: relative; 
            margin: 0 5px; 
        }
        
        .menu-link { 
            display: block; 
            padding: 12px 18px; 
            text-decoration: none; 
            color: var(--text-color); 
            font-weight: 500; 
            transition: var(--transition-smooth); 
            border-radius: var(--border-radius); 
            position: relative;
            font-size: 0.95rem;
        }
        
        .menu-link:hover, 
        .menu-link.active { 
            color: var(--primary-accent); 
            background: rgba(67, 97, 238, 0.1); 
            transform: translateY(-1px);
        }
        
        .menu-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .menu-link:hover::before,
        .menu-link.active::before {
            width: 80%;
        }
        
        .dropdown-menu { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            min-width: 220px; 
            background: #fff; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
            border-radius: var(--border-radius); 
            padding: 10px 0; 
            margin-top: 8px; 
            opacity: 0; 
            visibility: hidden; 
            transform: translateY(10px); 
            transition: var(--transition-smooth); 
            z-index: 1001; 
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .menu-item.dropdown:hover .dropdown-menu { 
            opacity: 1; 
            visibility: visible; 
            transform: translateY(0); 
        }
        
        .dropdown-menu .menu-link { 
            padding: 10px 20px; 
            border-radius: 0; 
            font-size: 0.9rem;
        }
        
        .dropdown-menu .menu-link:hover { 
            background: rgba(67, 97, 238, 0.08); 
            padding-left: 25px;
        }
        
        .dropdown-menu .menu-link::before {
            display: none;
        }
        
        .menu-link.dropdown-toggle::after { 
            content: '▼'; 
            font-size: 0.6em; 
            margin-left: 8px; 
            vertical-align: middle; 
            transition: transform 0.3s ease;
        }
        
        .menu-item.dropdown:hover .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        /* ————— Navegación Secundaria ————— */
        .secondary-nav { 
            margin-top: 5px; 
        }
        
        .menu-secondary { 
            display: flex; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            justify-content: flex-end; 
            font-size: 0.85rem; 
        }
        
        .menu-item-secondary { 
            position: relative; 
            margin: 0 3px; 
        }
        
        .menu-link-secondary { 
            display: block; 
            padding: 6px 12px; 
            text-decoration: none; 
            color: var(--secondary-color); 
            transition: var(--transition); 
            border-radius: 15px;
        }
        
        .menu-link-secondary:hover { 
            color: var(--accent-color); 
            background: rgba(131, 56, 236, 0.1);
        }
        
        /* ————— Navegación Compacta ————— */
        .compact-nav { 
            margin: 5px 0; 
        }
        
        .menu-compact { 
            display: flex; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            justify-content: flex-start; 
            font-size: 0.8rem; 
        }
        
        .menu-item-compact { 
            position: relative; 
            margin: 0 2px; 
        }
        
        .menu-link-compact { 
            display: block; 
            padding: 4px 10px; 
            text-decoration: none; 
            color: var(--text-color); 
            opacity: 0.8; 
            transition: var(--transition); 
            border-radius: 12px;
        }
        
        .menu-link-compact:hover { 
            opacity: 1; 
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-accent);
        }
        
        /* ————— Menú de Usuario Mejorado ————— */
        .user-menu { 
            display: flex; 
            align-items: center; 
            gap: 15px;
        }
        
        .wallet, .cart { 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            color: var(--text-color); 
            font-weight: 500; 
            transition: var(--transition-smooth); 
            padding: 8px 12px;
            border-radius: var(--border-radius);
            background: rgba(67, 97, 238, 0.05);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }
        
        .wallet:hover, .cart:hover { 
            color: var(--primary-accent); 
            background: rgba(67, 97, 238, 0.1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
        }
        
        .wallet i, .cart i {
            margin-right: 6px;
            font-size: 1.1em;
        }
        
        .cart-count { 
            background: var(--secondary-accent); 
            color: #fff; 
            font-size: 0.75rem; 
            width: 18px; 
            height: 18px; 
            border-radius: 50%; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            margin-left: 6px; 
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .user-actions { 
            display: flex; 
            gap: 10px; 
        }
        
        /* ————— Botones Mejorados ————— */
        .btn, a.btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: var(--primary-accent); 
            color: var(--text-light) !important; 
            text-decoration: none; 
            border-radius: 25px; 
            font-weight: 600; 
            transition: var(--transition-smooth); 
            border: 2px solid var(--primary-accent); 
            cursor: pointer; 
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover, a.btn:hover { 
            background: #3051d3; 
            border-color: #3051d3; 
            transform: translateY(-2px); 
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3); 
        }
        
        .btn-outline, a.btn-outline { 
            background: transparent; 
            color: var(--primary-accent) !important; 
            border: 2px solid var(--primary-accent); 
        }
        
        .btn-outline:hover, a.btn-outline:hover { 
            background: var(--primary-accent); 
            color: var(--text-light) !important; 
        }
        
        .btn-secondary { 
            background: var(--secondary-accent); 
            border-color: var(--secondary-accent); 
        }
        
        .btn-secondary:hover { 
            background: #e55656; 
            border-color: #e55656; 
        }
        
        /* ————— Capas de Menús ————— */
        .header-top { 
            display: flex; 
            width: 100%; 
            justify-content: flex-end; 
            margin-bottom: 10px; 
            padding: 5px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .header-middle { 
            display: flex; 
            width: 100%; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .header-bottom { 
            display: flex; 
            width: 100%; 
            margin-top: 10px; 
            padding: 5px 0;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        /* ————— Mensajes del Sistema ————— */
        .mensaje-container { 
            padding: 15px 0; 
            margin-bottom: 20px; 
            border-radius: var(--border-radius);
            position: relative;
        }
        
        .mensaje-exito {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .mensaje-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .cerrar-mensaje { 
            background: none; 
            border: none; 
            font-size: 1.5rem; 
            cursor: pointer; 
            color: inherit; 
            opacity: 0.7; 
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            transition: var(--transition);
        }
        
        .cerrar-mensaje:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }
        
        /* ————— Menu Móvil ————— */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
            margin-left: 15px;
        }
        
        .mobile-menu-toggle span {
            display: block;
            width: 25px;
            height: 3px;
            background: var(--primary-accent);
            margin: 3px 0;
            transition: var(--transition);
            border-radius: 2px;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
        
        /* ————— Responsive Design ————— */
        @media (max-width: 1024px) {
            .header-container {
                flex-wrap: wrap;
            }
            
            .main-nav {
                margin: 0 15px;
            }
            
            .user-menu {
                gap: 10px;
            }
            
            .btn, a.btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 10px 0;
            }
            
            .logo {
                font-size: 1.3rem;
            }
            
            .logo-img {
                width: 35px;
                height: 35px;
            }
            
            .main-nav {
                display: none;
                width: 100%;
                margin: 15px 0 0 0;
                order: 3;
            }
            
            .main-nav.active {
                display: block;
            }
            
            .menu {
                flex-direction: column;
                background: #fff;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-soft);
                padding: 10px 0;
            }
            
            .menu-item {
                margin: 0;
            }
            
            .menu-link {
                padding: 12px 20px;
                border-radius: 0;
            }
            
            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: rgba(67, 97, 238, 0.05);
                margin: 0;
                border-radius: 0;
            }
            
            .user-menu {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .wallet, .cart {
                font-size: 0.85rem;
                padding: 6px 10px;
            }
            
            .user-actions {
                gap: 8px;
            }
            
            .btn, a.btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .header-top,
            .header-bottom {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            
            .header-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .logo {
                margin-bottom: 10px;
            }
            
            .user-menu {
                width: 100%;
                justify-content: space-between;
            }
            
            .user-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn, a.btn {
                width: 100%;
                text-align: center;
            }
        }
        
        /* ————— Loading States ————— */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--primary-accent);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* ————— Accessibility ————— */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* ————— Print Styles ————— */
        @media print {
            header {
                position: static;
                box-shadow: none;
            }
            
            .user-menu,
            .mobile-menu-toggle {
                display: none;
            }
        }
    </style>
</head>
<body class="<?= isset($bodyClass) ? htmlspecialchars($bodyClass) : 'home-page' ?>">

    <header>
        <div class="container header-container">
            <!-- Estructura con múltiples capas para menús -->
            <?php if (isset($menusPorPosicion['top']) && !empty($menusPorPosicion['top'])): ?>
            <div class="header-top">
                <?php foreach ($menusPorPosicion['top'] as $menu): ?>
                    <?php if (isset($menu['habilitado']) && $menu['habilitado'] == 0) continue; ?>
                    <?php 
                        $estiloMenu = $estilos[$menu['estilo'] ?? 'default'];
                        echo mostrarMenuDinamico($menu['id'], $estiloMenu);
                    ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="header-middle">
                <!-- Logo mejorado -->
                <a href="<?= URL_SITIO ?>" class="logo" title="InfoMaster - Inicio">
                    <div class="logo-img">
                        <span>IM</span>
                    </div>
                    Info<span style="color: var(--primary-accent);">Master</span>
                </a> 
                
                <!-- Navegación principal -->
                <?php if (isset($menusPorPosicion['header']) && !empty($menusPorPosicion['header'])): ?>
                    <nav class="main-nav" id="main-nav">
                        <?php 
                        foreach ($menusPorPosicion['header'] as $menu) {
                            if (empty($menu['habilitado'])) continue;

                            // Override dinámico para CATEGORIAS
                            if ($menu['id'] == 31) {
                                $menu['url'] = URL_SITIO . 'categorias.php';
                                $menu['items'] = array_map(function($c) {
                                    return [
                                        'label' => $c['nombre'],
                                        'url' => URL_SITIO . 'productos.php?categoria=' . $c['id'],
                                        'icon' => getIconoCategoria($c['nombre'])
                                    ];
                                }, $categorias);
                            }

                            echo mostrarMenuDinamico($menu['id'], $estilos[$menu['estilo'] ?? 'default'], $menu);
                        }
                        ?>
                    </nav>
                <?php else: ?>
                    <!-- Menú por defecto si no hay configuración -->
                    <nav class="main-nav" id="main-nav">
                        <ul class="menu">
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                                    <i class="fas fa-home"></i> Inicio
                                </a>
                            </li>
                            <li class="menu-item dropdown">
                                <a href="<?= URL_SITIO ?>productos.php" class="menu-link dropdown-toggle <?= basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : '' ?>">
                                    <i class="fas fa-shopping-bag"></i> Productos
                                </a>
                                <?php if (!empty($categorias)): ?>
                                <ul class="dropdown-menu">
                                    <?php foreach ($categorias as $cat): ?>
                                    <li class="menu-item">
                                        <a href="<?= URL_SITIO ?>productos.php?categoria=<?= $cat['id'] ?>" class="menu-link">
                                            <?= getIconoCategoria($cat['nombre']) ?> <?= htmlspecialchars($cat['nombre']) ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>soporte.php" class="menu-link">
                                    <i class="fas fa-headset"></i> Soporte
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>contacto.php" class="menu-link">
                                    <i class="fas fa-envelope"></i> Contacto
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- Menú de usuario mejorado -->
                <div class="user-menu">
                    <?php if (estaLogueado()): ?>
                        <a href="<?= URL_SITIO ?>wallet" class="wallet" title="Mi Wallet">
                            <i class="fas fa-wallet"></i>
                            <span><?= MONEDA . number_format($_SESSION['usuario_saldo'] ?? 0, 2) ?></span>
                        </a>
                        <a href="<?= URL_SITIO ?>carrito/ver" class="cart" title="Ver Carrito">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Carrito</span>
                            <?php if (class_exists('Carrito') && Carrito::contar() > 0): ?>
                                <span class="cart-count"><?= Carrito::contar() ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="user-actions">
                            <?php if (esAdmin()): ?>
                                <a href="<?= URL_SITIO ?>admin" class="btn btn-secondary" title="Panel de Administración">
                                    <i class="fas fa-cog"></i> Panel VIP
                                </a>
                            <?php endif; ?>
                            <a href="<?= URL_SITIO ?>perfil" class="btn btn-outline mi-cuenta" title="Mi Cuenta">
                                <i class="fas fa-user"></i> Mi Cuenta
                            </a>
                            <a href="<?= URL_SITIO ?>logout" class="btn" title="Cerrar Sesión">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="user-actions">
                            <a href="<?= URL_SITIO ?>login" class="btn btn-outline" title="Iniciar Sesión">
                                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                            </a>
                            <a href="<?= URL_SITIO ?>registro" class="btn" title="Crear Cuenta">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Toggle para menú móvil -->
                    <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
            
            <?php if (isset($menusPorPosicion['bottom']) && !empty($menusPorPosicion['bottom'])): ?>
            <div class="header-bottom">
                <?php foreach ($menusPorPosicion['bottom'] as $menu): ?>
                    <?php if (isset($menu['habilitado']) && $menu['habilitado'] == 0) continue; ?>
                    <?php 
                        $estiloMenu = $estilos[$menu['estilo'] ?? 'default'];
                        echo mostrarMenuDinamico($menu['id'], $estiloMenu);
                    ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Mensajes del sistema mejorados -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje-container mensaje-<?= $_SESSION['tipo_mensaje'] ?? 'exito' ?>">
            <div class="container">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <?php 
                        $tipoMensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
                        $iconos = [
                            'exito' => 'fas fa-check-circle',
                            'error' => 'fas fa-exclamation-triangle',
                            'warning' => 'fas fa-exclamation-circle',
                            'info' => 'fas fa-info-circle'
                        ];
                        ?>
                        <i class="<?= $iconos[$tipoMensaje] ?? $iconos['info'] ?>" style="margin-right: 10px; font-size: 1.2em;"></i>
                        <span><?= htmlspecialchars($_SESSION['mensaje']) ?></span>
                    </div>
                    <button class="cerrar-mensaje" onclick="this.closest('.mensaje-container').style.display='none'" title="Cerrar mensaje" aria-label="Cerrar mensaje">
                        &times;
                    </button>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Contenido principal con ID para accessibility -->
    <main id="main-content">

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Header.php cargado correctamente');
        
        // ========== MENÚ MÓVIL ==========
        const mobileToggle = document.getElementById('mobile-menu-toggle');
        const mainNav = document.getElementById('main-nav');
        
        if (mobileToggle && mainNav) {
            mobileToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                mainNav.classList.toggle('active');
                
                // Accesibilidad
                const isExpanded = mainNav.classList.contains('active');
                this.setAttribute('aria-expanded', isExpanded);
                mainNav.setAttribute('aria-hidden', !isExpanded);
            });
        }
        
        // ========== CERRAR MENSAJES ==========
        const cerrarMensajes = document.querySelectorAll('.cerrar-mensaje');
        cerrarMensajes.forEach(btn => {
            btn.addEventListener('click', function() {
                const mensaje = this.closest('.mensaje-container');
                if (mensaje) {
                    mensaje.style.opacity = '0';
                    mensaje.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        mensaje.style.display = 'none';
                    }, 300);
                }
            });
        });
        
        // Auto-cerrar mensajes después de 5 segundos
        setTimeout(() => {
            const mensajes = document.querySelectorAll('.mensaje-container');
            mensajes.forEach(mensaje => {
                if (mensaje.style.display !== 'none') {
                    mensaje.style.opacity = '0';
                    mensaje.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        mensaje.style.display = 'none';
                    }, 300);
                }
            });
        }, 5000);
        
        // ========== DROPDOWN MENUS ==========
        const dropdownItems = document.querySelectorAll('.menu-item.dropdown');
        dropdownItems.forEach(item => {
            const link = item.querySelector('.dropdown-toggle');
            const menu = item.querySelector('.dropdown-menu');
            
            if (link && menu) {
                // Keyboard navigation
                link.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        item.classList.toggle('active');
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!item.contains(e.target)) {
                        item.classList.remove('active');
                    }
                });
            }
        });
        
        // ========== ACTIVE MENU HIGHLIGHTING ==========
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-link');
        
        menuLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPath) {
                link.classList.add('active');
                
                // Si está en un dropdown, marcar el padre también
                const parentDropdown = link.closest('.dropdown');
                if (parentDropdown) {
                    const parentLink = parentDropdown.querySelector('.dropdown-toggle');
                    if (parentLink) {
                        parentLink.classList.add('active');
                    }
                }
            }
        });
        
        // ========== SMOOTH SCROLLING ==========
        const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
        smoothScrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // ========== LOADING STATES ==========
        const actionButtons = document.querySelectorAll('.btn[href], .menu-link[href]');
        actionButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Evitar double-clicks
                if (this.classList.contains('loading')) {
                    return false;
                }
                
                // Solo para navegación externa
                if (this.href && !this.href.startsWith(window.location.origin)) {
                    this.classList.add('loading');
                    this.style.position = 'relative';
                }
            });
        });
        
        // ========== ACCESSIBILITY IMPROVEMENTS ==========
        // Skip to main content
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.textContent = 'Saltar al contenido principal';
        skipLink.className = 'sr-only';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-accent);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
            transition: top 0.3s;
        `;
        
        skipLink.addEventListener('focus', function() {
            this.style.top = '6px';
        });
        
        skipLink.addEventListener('blur', function() {
            this.style.top = '-40px';
        });
        
        document.body.insertBefore(skipLink, document.body.firstChild);
        
        console.log('✅ Header scripts inicializados correctamente');
    });
    </script>