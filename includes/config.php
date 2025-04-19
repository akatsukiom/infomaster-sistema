<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'crist110_crist110_admin');
define('DB_PASS', 'Cuenta2022$');
define('DB_NAME', 'crist110_infomaster_db');

// Conexión a la base de datos
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8");

// Configuración global del sitio
define('URL_SITIO', 'https://cristianalejandro1744816593450.2130068.misitiohostgator.com/');
define('MONEDA', '$');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>