<?php
// 0) Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

/**
 * Comprueba si el usuario tiene rol de administrador.
 * Asume que al hacer login guardas $_SESSION['usuario_rol'] = 'admin'
 *
 * @return bool
 */
function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Escapa y limpia un dato de entrada para evitar XSS y SQL Injection.
 * Úsalo sólo en texto de formularios, no sobre rutas o nombres de archivos.
 *
 * @param string $dato
 * @return string
 */
function limpiarDato($dato) {
    global $conexion;  // debe existir tras incluir config.php
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    return $conexion->real_escape_string($dato);
}

/**
 * Guarda un mensaje flash en la sesión para mostrar al usuario.
 *
 * @param string $mensaje
 * @param string $tipo 'info', 'success', 'error', etc.
 */
function mostrarMensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje']      = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
}

/**
 * Valida que un email tenga formato correcto.
 *
 * @param string $email
 * @return bool
 */
function validarEmail($email) {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Comprueba si hay un usuario logueado.
 *
 * @return bool
 */
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Redirige a una ruta dentro de tu sitio, usando la constante URL_SITIO.
 * La $ruta no debe llevar slash inicial (por ejemplo 'login.php' o 'admin/dashboard.php').
 *
 * @param string $ruta
 */
function redireccionar($ruta) {
    header("Location: " . URL_SITIO . $ruta);
    exit;
}