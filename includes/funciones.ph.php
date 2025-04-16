<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// Función para limpiar entradas
function limpiarDato($dato) {
    global $conexion;
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    $dato = $conexion->real_escape_string($dato);
    return $dato;
}

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
}

// Función para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para verificar si usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Función para redireccionar
function redireccionar($url) {
    header("Location: " . URL_SITIO . $url);
    exit;
}
?>