<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';

// Verificar si el usuario está logueado
if(estaLogueado()) {
    $usuario = new Usuario($conexion);
    $resultado = $usuario->logout();
    
    if(isset($resultado['success'])) {
        mostrarMensaje($resultado['success'], 'success');
    }
}

// Redirigir a la página principal
redireccionar('../../index.php');
?>