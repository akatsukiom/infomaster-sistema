<?php
// Mostrar errores en desarrollo (quita en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// Aseguramos que config.php y funciones.php estén incluidos
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funciones.php';

// Inicializar carrito
if (file_exists(__DIR__ . '/../modulos/carrito/modelo.php')) {
    require_once __DIR__ . '/../modulos/carrito/modelo.php';
    Carrito::inicializar();
}
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
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Estilos adicionales para el header -->
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

        /* Estilos generales */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header mejorado */
        header {
            background-color: #fff;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            flex-wrap: wrap;
        }

        /* Logo */
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
            color: var(--primary-color);
        }

        .logo span {
            color: var(--primary-color);
        }

        .logo-img {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .logo-img span {
            color: white;
            font-weight: bold;
        }

        /* Navegación principal */
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
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
            border-radius: var(--border-radius);
        }

        .menu-link:hover, .menu-link.active {
            color: var(--primary-color);
            background-color: rgba(58, 134, 255, 0.1);
        }

        /* Submenu estilizado */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 200px;
            background-color: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            padding: 10px 0;
            margin-top: 5px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 1001;
        }

        .menu-item.dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu .menu-item {
            margin: 0;
            width: 100%;
        }

        .dropdown-menu .menu-link {
            padding: 8px 20px;
            border-radius: 0;
        }

        .dropdown-menu .menu-link:hover {
            background-color: rgba(58, 134, 255, 0.05);
        }

        /* Estilos para indicador de dropdown */
        .menu-link.dropdown-toggle::after {
            content: '▼';
            font-size: 0.6em;
            margin-left: 5px;
            vertical-align: middle;
        }

        /* Menú promociones */
        .promo-nav {
            background-color: var(--secondary-color);
            padding: 8px 0;
            text-align: center;
        }

        .promo-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            justify-content: center;
        }

        .promo-item {
            margin: 0 10px;
        }

        .promo-link {
            color: white;
            font-weight: 500;
            text-decoration: none;
            padding: 5px 10px;
            transition: var(--transition);
            border-radius: var(--border-radius);
        }

        .promo-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Menú de usuario */
        .user-menu {
            display: flex;
            align-items: center;
        }

        .wallet, .cart {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-color);
            margin-right: 20px;
            font-weight: 500;
            transition: var(--transition);
        }

        .wallet:hover, .cart:hover {
            color: var(--primary-color);
        }

        .wallet i, .cart i {
            margin-right: 5px;
            font-size: 1.2rem;
        }

        .cart-count {
            background-color: var(--accent-color);
            color: white;
            font-size: 0.8rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        /* Botones */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            border: 2px solid var(--primary-color);
            cursor: pointer;
        }

        .btn:hover {
            background-color: #2a75e8;
            border-color: #2a75e8;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #7128d4;
            border-color: #7128d4;
        }

        /* Mensajes del sistema */
        .mensaje-container {
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .mensaje-info {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        .mensaje-success {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .mensaje-warning {
            background-color: #fff3e0;
            color: #e65100;
        }

        .mensaje-danger {
            background-color: #ffebee;
            color: #b71c1c;
        }

        .mensaje-container p {
            margin: 0;
        }

        .mensaje-container .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cerrar-mensaje {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }

        .cerrar-mensaje:hover {
            opacity: 1;
        }

        /* Responsive para móviles */
        @media (max-width: 992px) {
            .header-container {
                flex-direction: column;
                align-items: stretch;
            }

            .menu {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .menu-item {
                margin: 5px;
            }

            .user-menu {
                margin-top: 15px;
                justify-content: space-between;
                flex-wrap: wrap;
            }
            
            /* Menú móvil */
            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                min-width: auto;
                padding: 0 0 0 20px;
                margin-top: 0;
                display: none;
            }
            
            .menu-item.dropdown.open .dropdown-menu {
                display: block;
            }
            
            .menu-link.dropdown-toggle::after {
                float: right;
                margin-top: 8px;
            }
        }

        @media (max-width: 576px) {
            .user-actions {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }

            .wallet, .cart {
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="<?= URL_SITIO ?>" class="logo">
                <div class="logo-img"><span>IM</span></div>
                Info<span>Master</span>
            </a>
            
            <?php
            // Cargar función para mostrar menús
            if (file_exists(__DIR__ . '/mostrar_menu.php')) {
                require_once __DIR__ . '/mostrar_menu.php';
                
                // Configuración personalizada para el menú principal
                $config_menu_principal = [
                    'class_nav' => 'main-nav',
                    'class_ul_principal' => 'menu',
                    'class_ul_submenu' => 'dropdown-menu',
                    'class_li' => 'menu-item',
                    'class_link' => 'menu-link',
                    'class_dropdown' => 'dropdown',
                    'class_dropdown_toggle' => 'dropdown-toggle',
                    'dropdown_toggle_attr' => 'aria-expanded="false"'
                ];
                
                // Verificar si la función existe antes de usarla
                if (function_exists('mostrarMenuDinamico')) {
                    // Usar la nueva función con menú jerárquico
                    echo mostrarMenuDinamico(1, $config_menu_principal);
                } elseif (function_exists('mostrarMenu')) {
                    // Fallback a la función anterior si la nueva no existe
                    echo '<nav class="main-nav">';
                    echo mostrarMenu(
                        1,              // ID del menú principal
                        '',             // Clase para nav (ya estamos dentro de un nav)
                        'menu',         // Clase para ul
                        'menu-item',    // Clase para li
                        'menu-link'     // Clase para a
                    );
                    echo '</nav>';
                } else {
                    // Menú estático de respaldo si ninguna función existe
                    ?>
                    <nav class="main-nav">
                        <ul class="menu">
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>"
                                   class="menu-link <?= ($_SERVER['REQUEST_URI'] === '/') ? 'active' : '' ?>">
                                   Inicio
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>productos"
                                   class="menu-link <?= (strpos($_SERVER['REQUEST_URI'], '/productos') === 0) ? 'active' : '' ?>">
                                   Productos
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>categorias"
                                   class="menu-link <?= (strpos($_SERVER['REQUEST_URI'], '/categorias') === 0) ? 'active' : '' ?>">
                                   Categorías
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>como-funciona"
                                   class="menu-link <?= (strpos($_SERVER['REQUEST_URI'], '/como-funciona') === 0) ? 'active' : '' ?>">
                                   Cómo Funciona
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= URL_SITIO ?>contacto"
                                   class="menu-link <?= (strpos($_SERVER['REQUEST_URI'], '/contacto') === 0) ? 'active' : '' ?>">
                                   Contacto
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php
                }
            } else {
                // Menú estático si el archivo mostrar_menu.php no existe
                ?>
                <nav class="main-nav">
                    <ul class="menu">
                        <li class="menu-item">
                            <a href="<?= URL_SITIO ?>" class="menu-link">Inicio</a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= URL_SITIO ?>productos" class="menu-link">Productos</a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= URL_SITIO ?>categorias" class="menu-link">Categorías</a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= URL_SITIO ?>como-funciona" class="menu-link">Cómo Funciona</a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= URL_SITIO ?>contacto" class="menu-link">Contacto</a>
                        </li>
                    </ul>
                </nav>
                <?php
            }
            ?>
            
            <div class="user-menu">
                <?php if (function_exists('estaLogueado') && estaLogueado()): ?>
                    <a href="<?= URL_SITIO ?>wallet" class="wallet">
                        <i class="fas fa-wallet"></i>
                        <?= MONEDA . number_format($_SESSION['usuario_saldo'] ?? 0, 2) ?>
                    </a>
                    <a href="<?= URL_SITIO ?>carrito/ver" class="cart">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if (class_exists('Carrito') && method_exists('Carrito', 'contar') && Carrito::contar() > 0): ?>
                            <span class="cart-count"><?= Carrito::contar() ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="user-actions">
                        <?php if (function_exists('esAdmin') && esAdmin()): ?>
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
    </header>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje-container mensaje-<?= $_SESSION['tipo_mensaje'] ?? 'info' ?>">
            <div class="container">
                <p><?= htmlspecialchars($_SESSION['mensaje']) ?></p>
                <button class="cerrar-mensaje" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <script>
    // Script para mejorar interactividad del menú
    document.addEventListener('DOMContentLoaded', function() {
        // Funcionalidad para cerrar mensajes
        const closeButtons = document.querySelectorAll('.cerrar-mensaje');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.mensaje-container').style.display = 'none';
            });
        });
        
        // Mejorar soporte para menús en dispositivos móviles
        const mediaQuery = window.matchMedia('(max-width: 992px)');
        
        function handleMobileMenu(e) {
            if (e.matches) {
                // En móvil: añadir evento clic para desplegar submenús
                document.querySelectorAll('.menu-link.dropdown-toggle').forEach(toggle => {
                    toggle.addEventListener('click', function(event) {
                        event.preventDefault();
                        const menuItem = this.closest('.menu-item');
                        
                        // Cerrar otros menús abiertos
                        document.querySelectorAll('.menu-item.dropdown.open').forEach(item => {
                            if (item !== menuItem) {
                                item.classList.remove('open');
                            }
                        });
                        
                        // Alternar estado del menú actual
                        menuItem.classList.toggle('open');
                    });
                });
            } else {
                // En escritorio: usar hover para submenús
                document.querySelectorAll('.menu-item.dropdown').forEach(item => {
                    item.classList.remove('open');
                });
            }
        }
        
        // Ejecutar inicialmente y agregar listener para cambios
        handleMobileMenu(mediaQuery);
        mediaQuery.addEventListener('change', handleMobileMenu);
    });
    </script>