<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// Inicializar carrito
require_once 'modulos/carrito/modelo.php';
Carrito::inicializar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'InfoMaster - Productos Digitales'; ?></title>
    <link rel="stylesheet" href="<?php echo URL_SITIO; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo URL_SITIO; ?>css/responsive.css">
    <link rel="shortcut icon" href="<?php echo URL_SITIO; ?>img/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="<?php echo URL_SITIO; ?>" class="logo">Info<span>Master</span></a>
            
            <nav>
                <ul>
                    <li><a href="<?php echo URL_SITIO; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Inicio</a></li>
                    <li><a href="<?php echo URL_SITIO; ?>productos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : ''; ?>">Productos</a></li>
                    <li><a href="<?php echo URL_SITIO; ?>categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?>">Categorías</a></li>
                    <li><a href="<?php echo URL_SITIO; ?>como-funciona.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'como-funciona.php' ? 'active' : ''; ?>">Cómo funciona</a></li>
                    <li><a href="<?php echo URL_SITIO; ?>contacto.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'active' : ''; ?>">Contacto</a></li>
                </ul>
            </nav>
            
            <div class="user-menu">
                <?php if(estaLogueado()): ?>
                    <a href="<?php echo URL_SITIO; ?>modulos/wallet/recargar.php" class="wallet"><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></a>
                    <a href="<?php echo URL_SITIO; ?>modulos/carrito/ver.php" class="cart">Carrito 
                        <?php if(Carrito::contar() > 0): ?>
                            <span class="cart-count"><?php echo Carrito::contar(); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo URL_SITIO; ?>modulos/usuarios/perfil.php"><?php echo $_SESSION['usuario_nombre']; ?></a>
                <?php else: ?>
                    <a href="<?php echo URL_SITIO; ?>modulos/usuarios/login.php">Iniciar sesión</a>
                    <a href="<?php echo URL_SITIO; ?>modulos/usuarios/registro.php" class="btn-outline">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php
    // Mostrar mensajes de sesión (éxito, error, etc.)
    if(isset($_SESSION['mensaje'])): ?>
        <div class="mensaje-container mensaje-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?>">
            <div class="container">
                <p><?php echo $_SESSION['mensaje']; ?></p>
                <button class="cerrar-mensaje">&times;</button>
            </div>
        </div>
        <?php
        // Limpiar mensaje después de mostrarlo
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    endif;
    ?>