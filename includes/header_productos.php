<?php
// 0) Asegurar que la sesión esté activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Definir acceso si no viene de index.php
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}

// 2) Apuntar a tu carpeta includes y cargar config + funciones
$inc = __DIR__;             // __DIR__ = .../public_html/includes
require_once $inc . '/config.php';
require_once $inc . '/funciones.php';

// Desde aquí ya podrás usar estaLogueado()

// Obtener las configuraciones del sitio
$titulo_sitio = obtenerConfiguracion('titulo_sitio') ?? 'Mi Tienda';
$logo_url = obtenerConfiguracion('logo_url') ?? URL_SITIO . 'assets/img/logo.png';

// Obtener los menús marcados para el header
$menuModel = new Menu($conexion);
$menus_header = [];

$sql = "SELECT id, nombre FROM menus WHERE es_header = 1 ORDER BY nombre";
$resultado = $conexion->query($sql);
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $menus_header[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' - ' : '' ?><?= htmlspecialchars($titulo_sitio) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos para menús con productos -->
    <link rel="stylesheet" href="<?= URL_SITIO ?>assets/css/menu-productos.css">
    
    <?php
    // Hook para CSS adicional del tema
    if (function_exists('header_css_hook')) {
        header_css_hook();
    }
    ?>
</head>
<body>
    <!-- Barra de información superior (opcional) -->
    <div class="bg-primary text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <small>
                        <i class="fas fa-phone me-2"></i> (123) 456-7890
                        <span class="mx-3">|</span>
                        <i class="fas fa-envelope me-2"></i> info@mitienda.com
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>
                        <a href="<?= URL_SITIO ?>envios" class="text-white me-3"><i class="fas fa-truck me-1"></i> Envíos</a>
                        <a href="<?= URL_SITIO ?>pagos" class="text-white me-3"><i class="fas fa-credit-card me-1"></i> Pagos</a>
                        <a href="<?= URL_SITIO ?>contacto" class="text-white"><i class="fas fa-map-marker-alt me-1"></i> Contacto</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Header principal con menús -->
    <header class="bg-white border-bottom">
        <div class="container">
            <!-- Barra superior con logo, búsqueda y carrito -->
            <div class="py-3 d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a class="navbar-brand" href="<?= URL_SITIO ?>">
                    <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= htmlspecialchars($titulo_sitio) ?>" height="40">
                </a>
                
                <!-- Búsqueda - visible en desktop, oculto en móvil -->
                <div class="d-none d-md-block flex-grow-1 mx-5">
                    <form class="d-flex" action="<?= URL_SITIO ?>buscar" method="get">
                        <input class="form-control me-2" type="search" name="q" placeholder="¿Qué estás buscando?" aria-label="Buscar">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- Enlaces de usuario y carrito -->
                <div class="d-flex align-items-center">
                    <?php if (estaLogueado()): ?>
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownUsuario" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> Mi cuenta
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUsuario">
                                <li><a class="dropdown-item" href="<?= URL_SITIO ?>mi-cuenta"><i class="fas fa-user-circle me-2"></i> Mi perfil</a></li>
                                <li><a class="dropdown-item" href="<?= URL_SITIO ?>mis-pedidos"><i class="fas fa-box me-2"></i> Mis pedidos</a></li>
                                <li><a class="dropdown-item" href="<?= URL_SITIO ?>favoritos"><i class="fas fa-heart me-2"></i> Favoritos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= URL_SITIO ?>logout"><i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= URL_SITIO ?>login" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-user"></i> Ingresar
                        </a>
                    <?php endif; ?>
                    
                    <!-- Carrito de compras con contador -->
                    <a href="<?= URL_SITIO ?>carrito" class="btn btn-primary position-relative">
                        <i class="fas fa-shopping-cart"></i> Carrito
                        <?php if (function_exists('obtenerCantidadCarrito') && obtenerCantidadCarrito() > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= obtenerCantidadCarrito() ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            
            <!-- Barra de navegación con menús -->
            <nav class="navbar navbar-expand-lg navbar-light px-0 py-0 border-top">
                <div class="container-fluid px-0">
                    <!-- Botón hamburguesa para móvil -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMainMenu" aria-controls="navbarMainMenu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <!-- Buscador para móvil -->
                    <div class="d-flex d-md-none order-lg-1 ms-auto me-2">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false" aria-controls="searchCollapse">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <!-- Menú principal colapsable -->
                    <div class="collapse navbar-collapse" id="navbarMainMenu">
                        <?php 
                        // Mostrar menús del header
                        if (!empty($menus_header)) {
                            foreach ($menus_header as $menu) {
                                // Configuración para mostrar productos con miniaturas y precios
                                $config = [
                                    'mostrar_miniaturas_productos' => true,
                                    'mostrar_precios' => true,
                                    'cache_tiempo' => 1800, // 30 minutos de caché
                                    'mostrar_iconos' => true,
                                    'class_icon' => 'me-1'
                                ];
                                
                                echo mostrarMenuProductosBootstrap5($menu['id'], $config);
                            }
                        } else {
                            // Menú por defecto si no hay menús configurados
                            echo '<ul class="navbar-nav me-auto">
                                <li class="nav-item"><a class="nav-link" href="' . URL_SITIO . '">Inicio</a></li>
                                <li class="nav-item"><a class="nav-link" href="' . URL_SITIO . 'productos">Productos</a></li>
                                <li class="nav-item"><a class="nav-link" href="' . URL_SITIO . 'categorias">Categorías</a></li>
                                <li class="nav-item"><a class="nav-link" href="' . URL_SITIO . 'contacto">Contacto</a></li>
                            </ul>';
                        }
                        ?>
                    </div>
                </div>
            </nav>
            
            <!-- Buscador colapsable para móvil -->
            <div class="collapse" id="searchCollapse">
                <div class="py-3">
                    <form action="<?= URL_SITIO ?>buscar" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="¿Qué estás buscando?" aria-label="Buscar">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Contenedor principal para el contenido de la página -->
    <main>
        <?php
        // Mostrar mensajes de sesión (éxito, error, etc.)
        if (isset($_SESSION['mensaje']) && isset($_SESSION['tipo_mensaje'])) {
            $tipo_clase = 'alert-' . ($_SESSION['tipo_mensaje'] === 'error' ? 'danger' : $_SESSION['tipo_mensaje']);
            echo '<div class="container mt-3">
                <div class="alert ' . $tipo_clase . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['mensaje']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>';
            
            // Limpiar mensajes después de mostrarlos
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }
        ?>