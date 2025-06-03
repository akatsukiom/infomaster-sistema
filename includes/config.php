<?php
// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}


// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'u321533524_cristian');   // tal cual aparece en tu panel
define('DB_PASS', 'Bz9z7meA1B34$');              // tu contraseña
define('DB_NAME', 'u321533524_infomaster');   // tal cual aparece en tu panel

// Conexión a la base de datos
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8");

// Configuración global del sitio
define('URL_SITIO', 'https://salmon-armadillo-487950.hostingersite.com/');
define('MONEDA', '$');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ———————————————————————————————
// CLIP API Credentials
// ———————————————————————————————
// Claves encontradas en tu Dashboard de Clip (Producción)
define('CLIP_CLIENT_ID',     'f8603aea-8e64-479e-988e-c8d0fc14f02e');
define('CLIP_CLIENT_SECRET', '23bd1c1b-d375-4947-8d21-70cdea079b38');
// Opcional (sólo si usas checkout transparente en tu frontend)
define('CLIP_PUBLIC_KEY',    'f8603aea-8e64-479e-988e-c8d0fc14f02e');
// Base URL de la API de Clip
define('CLIP_BASE_URL',      'https://api.clip.mx');
// URL donde Clip redirigirá tras el pago
define('CLIP_REDIRECT_URL',  URL_SITIO . 'modulos/carrito/clip-callback.php');