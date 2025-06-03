<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado
if(estaLogueado()) {
    try {
        // Destruir la sesión
        session_unset();
        session_destroy();
        
        // Mostrar mensaje
        session_start(); // Reiniciar sesión para el mensaje
        $_SESSION['mensaje'] = "Has cerrado sesión correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        // En caso de error, al menos intentar destruir la sesión
        session_unset();
        session_destroy();
    }
}

// Redirigir a la página principal - usando la URL absoluta para evitar problemas
header("Location: " . URL_SITIO);
exit;