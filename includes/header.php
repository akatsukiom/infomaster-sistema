<?php
// Mostrar errores en desarrollo (quita en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// 1) Incluir configuración y funciones globales primero
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funciones.php';

// 2) Inicializar carrito si existe
if (file_exists(__DIR__ . '/../modulos/carrito/modelo.php')) {
    require_once __DIR__ . '/../modulos/carrito/modelo.php';
    Carrito::inicializar();
}

// 3) Incluir el modelo de menús (contiene Menu + Setting)
require_once __DIR__ . '/../modulos/admin/menus/modelo.php';

// 4) Incluir el helper que define mostrarMenuDinamico()
require_once __DIR__ . '/mostrar_menu.php';

// 5) Incluir menú de productos si existe
if (file_exists(__DIR__ . '/menu_productos.php')) {
  //  require_once __DIR__ . '/menu_productos.php';
}

// 6) Inicializar el modelo de configuración
$settingModel = new Setting($conexion);

// Función para verificar la configuración de menús
function diagnosticoMenus() {
    global $settingModel, $conexion;
    
    $output = '<!-- DIAGNÓSTICO DE MENÚS -->' . PHP_EOL;
    $output .= '<!-- mostrar_menus_header: ' . $settingModel->obtener('mostrar_menus_header', 'no encontrado') . ' -->' . PHP_EOL;
    $output .= '<!-- menu_principal_id: ' . $settingModel->obtener('menu_principal_id', 'no encontrado') . ' -->' . PHP_EOL;
    
    // Verificar la configuración del menú
    $menuConfigJson = $settingModel->obtener('menu_config', '[]');
    $menuConfig = json_decode($menuConfigJson, true);
    
    $output .= '<!-- JSON Menu Config: ' . htmlspecialchars($menuConfigJson) . ' -->' . PHP_EOL;
    
    // Verificar si hay menús configurados
    if (empty($menuConfig) || json_last_error() !== JSON_ERROR_NONE) {
        $output .= '<!-- ADVERTENCIA: No hay configuración de menús válida en la BD -->' . PHP_EOL;
    } else {
        $output .= '<!-- Menús configurados: ' . count($menuConfig) . ' -->' . PHP_EOL;
        
        // Buscar menús en posición header
        $headerMenus = [];
        foreach ($menuConfig as $config) {
            if (isset($config['posicion']) && $config['posicion'] === 'header') {
                $headerMenus[] = $config;
            }
        }
        
        $output .= '<!-- Menús en posición header: ' . count($headerMenus) . ' -->' . PHP_EOL;
        
        // Verificar menús disponibles
        $menuModel = new Menu($conexion);
        $todosMenus = $menuModel->obtenerTodos();
        $output .= '<!-- Total de menús disponibles: ' . count($todosMenus) . ' -->' . PHP_EOL;
        
        foreach ($todosMenus as $menu) {
            $itemsCount = count($menuModel->obtenerItems($menu['id']));
            $output .= '<!-- Menú ID: ' . $menu['id'] . ', Nombre: ' . $menu['nombre'] . ', Items: ' . $itemsCount . ' -->' . PHP_EOL;
        }
    }
    
    return $output;
}

// Mostrar diagnóstico
echo diagnosticoMenus();

// 7) Obtener la configuración de menús desde settings
$menuConfigJson = $settingModel->obtener('menu_config', '[]');
$menuConfig = json_decode($menuConfigJson, true);

// Si no hay configuración o hay error en el JSON, usar configuración por defecto
if (empty($menuConfig) || json_last_error() !== JSON_ERROR_NONE) {
    // Configuración por defecto: solo el menú principal
    $menuConfig = [
        [
            'menu_id' => (int) $settingModel->obtener('menu_principal_id', '1'),
            'posicion' => 'header',
            'parent_id' => 0,
            'orden' => 0,
            'habilitado' => 1
        ]
    ];
    
    echo '<!-- Usando configuración por defecto para el menú -->';
}

// 8) Definir configuraciones de estilos para diferentes posiciones
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

// 9) Función para organizar los menús por posición
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
                    'habilitado' => $item['habilitado'] ?? 1
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
                            'habilitado' => $item['habilitado'] ?? 1
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

// Organizar menús por posición
$menusPorPosicion = organizarMenusPorPosicion($menuConfig);

// Para debugging
echo '<!-- Menús organizados por posición: -->';
echo '<!-- ' . htmlspecialchars(print_r($menusPorPosicion, true)) . ' -->';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) : 'InfoMaster - Productos Digitales' ?></title>
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/nuevo-estilo.css">
    <link rel="stylesheet" href="<?= URL_SITIO ?>css/responsive.css">
    <link rel="shortcut icon" href="<?= URL_SITIO ?>img/favicon.ico" type="image/x-icon">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #8338ec;
            --accent-color: #ff006e;
            --text-color: #2b2d42;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #38b000;
            --warning-color: #ffbe0b;
            --danger-color: #ff5400;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        /* ————— Estilos Generales ————— */
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:var(--text-color); line-height:1.6; }
        .container { width:100%; max-width:1200px; margin:0 auto; padding:0 15px; }
        /* ————— Header ————— */
        header { background:#fff; box-shadow:var(--box-shadow); position:sticky; top:0; z-index:1000; }
        .header-container { display:flex; justify-content:space-between; align-items:center; padding:15px 0; flex-wrap:wrap; }
        .logo { display:flex; align-items:center; text-decoration:none; color:var(--text-color); font-size:1.5rem; font-weight:700; transition:var(--transition); }
        .logo:hover { color:var(--primary-color); }
        .logo-img { width:40px; height:40px; background:var(--primary-color); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:10px; }
        .logo-img span { color:#fff; font-weight:bold; }
        
        /* ————— Navegación Principal ————— */
        .main-nav { flex-grow:1; margin:0 30px; }
        .menu { display:flex; list-style:none; margin:0; padding:0; justify-content:center; }
        .menu-item { position:relative; margin:0 5px; }
        .menu-link { display:block; padding:10px 15px; text-decoration:none; color:var(--text-color); font-weight:500; transition:var(--transition); border-radius:var(--border-radius); }
        .menu-link:hover, .menu-link.active { color:var(--primary-color); background:rgba(58,134,255,0.1); }
        .dropdown-menu { position:absolute; top:100%; left:0; min-width:200px; background:#fff; box-shadow:var(--box-shadow); border-radius:var(--border-radius); padding:10px 0; margin-top:5px; opacity:0; visibility:hidden; transform:translateY(10px); transition:var(--transition); z-index:1001; }
        .menu-item.dropdown:hover .dropdown-menu { opacity:1; visibility:visible; transform:translateY(0); }
        .dropdown-menu .menu-link { padding:8px 20px; border-radius:0; }
        .dropdown-menu .menu-link:hover { background:rgba(58,134,255,0.05); }
        .menu-link.dropdown-toggle::after { content:'▼'; font-size:0.6em; margin-left:5px; vertical-align:middle; }
        
        /* ————— Navegación Secundaria ————— */
        .secondary-nav { margin-top:5px; }
        .menu-secondary { display:flex; list-style:none; margin:0; padding:0; justify-content:flex-end; font-size:0.9rem; }
        .menu-item-secondary { position:relative; margin:0 3px; }
        .menu-link-secondary { display:block; padding:5px 10px; text-decoration:none; color:var(--secondary-color); transition:var(--transition); }
        .menu-link-secondary:hover { color:var(--accent-color); }
        
        /* ————— Navegación Compacta ————— */
        .compact-nav { margin:5px 0; }
        .menu-compact { display:flex; list-style:none; margin:0; padding:0; justify-content:flex-start; font-size:0.85rem; }
        .menu-item-compact { position:relative; margin:0 2px; }
        .menu-link-compact { display:block; padding:3px 8px; text-decoration:none; color:var(--text-color); opacity:0.8; transition:var(--transition); }
        .menu-link-compact:hover { opacity:1; text-decoration:underline; }
        
        /* ————— Menú de Usuario ————— */
        .user-menu { display:flex; align-items:center; }
        .wallet, .cart { display:flex; align-items:center; text-decoration:none; color:var(--text-color); margin-right:20px; font-weight:500; transition:var(--transition); }
        .wallet:hover, .cart:hover { color:var(--primary-color); }
        .cart-count { background:var(--accent-color); color:#fff; font-size:0.8rem; width:20px; height:20px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-left:5px; }
        .user-actions { display:flex; gap:10px; }
        .btn { display:inline-block; padding:8px 16px; background:var(--primary-color); color:#fff; text-decoration:none; border-radius:var(--border-radius); font-weight:500; transition:var(--transition); border:2px solid var(--primary-color); cursor:pointer; }
        .btn:hover { background:#2a75e8; border-color:#2a75e8; }
        .btn-outline { background:transparent; color:var(--primary-color); }
        .btn-outline:hover { background:var(--primary-color); color:#fff; }
        .btn-secondary { background:var(--secondary-color); border-color:var(--secondary-color); }
        .btn-secondary:hover { background:#7128d4; border-color:#7128d4; }
        
        /* ————— Capas de Menús ————— */
        .header-top { display:flex; width:100%; justify-content:flex-end; margin-bottom:10px; }
        .header-middle { display:flex; width:100%; justify-content:space-between; align-items:center; }
        .header-bottom { display:flex; width:100%; margin-top:10px; }
        
        /* ————— Mensajes ————— */
        .mensaje-container { padding:15px 0; margin-bottom:20px; }
        .cerrar-mensaje { background:none; border:none; font-size:1.5rem; cursor:pointer; color:inherit; opacity:0.7; }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <!-- Estructura con múltiples capas para menús -->
            <?php if (isset($menusPorPosicion['top'])): ?>
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
                <!-- Logo -->
                <a href="<?= URL_SITIO ?>" class="logo">
                    <div class="logo-img"><span>IM</span></div>
                    Info<span>Master</span>
                </a>

                <?php
                $mostrarHeader = $settingModel->obtener('mostrar_menus_header', '0');

                // Verificar si está habilitada la visualización de menús en el header
                if ($mostrarHeader === '1' && isset($menusPorPosicion['header']) && !empty($menusPorPosicion['header'])) {
                    echo '<!-- Usando menú de posición header con mostrar_menus_header = 1 -->';
                    foreach ($menusPorPosicion['header'] as $menu) {
                        if (isset($menu['habilitado']) && $menu['habilitado'] == 0) continue;
                        $estiloMenu = $estilos[$menu['estilo'] ?? 'default'];
                        echo mostrarMenuDinamico($menu['id'], $estiloMenu);
                    }
                }
                // Si no está habilitado o no hay menús de header configurados, mostrar el menú principal
                elseif (isset($menusPorPosicion['principal']) && !empty($menusPorPosicion['principal'])) {
                    echo '<!-- Usando menú principal como alternativa -->';
                    $menuPrincipal = $menusPorPosicion['principal'][0]; 
                    $estiloMenu = $estilos[$menuPrincipal['estilo'] ?? 'default'];
                    echo mostrarMenuDinamico($menuPrincipal['id'], $estiloMenu);
                }
                // Si no hay nada configurado, mostrar mensaje de advertencia
                else {
                    echo '<!-- ADVERTENCIA: No hay menús configurados para mostrar -->';
                }
                ?>

                <!-- Menú de usuario -->
                <div class="user-menu">
                    <?php if (estaLogueado()): ?>
                        <a href="<?= URL_SITIO ?>wallet" class="wallet">
                            <i class="fas fa-wallet"></i>
                            <?= MONEDA . number_format($_SESSION['usuario_saldo'] ?? 0, 2) ?>
                        </a>
                        <a href="<?= URL_SITIO ?>carrito/ver" class="cart">
                            <i class="fas fa-shopping-cart"></i>
                            Carrito
                            <?php if (Carrito::contar() > 0): ?>
                                <span class="cart-count"><?= Carrito::contar() ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="user-actions">
                            <?php if (esAdmin()): ?>
                                <a href="<?= URL_SITIO ?>admin" class="btn btn-secondary">Panel VIP</a>
                            <?php endif; ?>
                            <a href="<?= URL_SITIO ?>perfil" class="btn btn-outline">Mi Cuenta</a>
                            <a href="<?= URL_SITIO ?>logout" class="btn">Cerrar Sesión</a>
                        </div>
                    <?php else: ?>
                        <div class="user-actions">
                            <a href="<?= URL_SITIO ?>login" class="btn btn-outline">Iniciar sesión</a>
                            <a href="<?= URL_SITIO ?>registro" class="btn">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($menusPorPosicion['bottom'])): ?>
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

    <!-- Mensajes del sistema -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje-container mensaje-<?= $_SESSION['tipo_mensaje'] ?>">
            <div class="container">
                <p><?= htmlspecialchars($_SESSION['mensaje']) ?></p>
                <button class="cerrar-mensaje" onclick="this.closest('.mensaje-container').style.display='none'">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar mensajes
        document.querySelectorAll('.cerrar-mensaje')
            .forEach(btn => btn.addEventListener('click', () => btn.closest('.mensaje-container').style.display = 'none'));

        // aquí tu lógica para menús móviles…
    });
    </script>