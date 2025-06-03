<?php
define('ACCESO_PERMITIDO', true);

// modulos/carrito/clip-callback.php

require_once '../../includes/config.php';
require_once '../../includes/funciones.php';

// 1) Lee query params de Clip
$chargeId = $_GET['chargeId'] ?? '';
$status   = $_GET['status']   ?? '';

// 2) Opcional: validar con la API de Clip el estado real
//    GET /v1/charges/{chargeId} con Bearer token

if ($status === 'PAID') {
    // 3) Marcar la orden como pagada en tu BD
    // Carrito::marcarPagado(session_id(), $chargeId);

    mostrarMensaje('Pago recibido correctamente', 'success');
    redireccionar(URL_SITIO . 'modulos/carrito/ver.php');
} else {
    mostrarMensaje('El pago no se completó', 'error');
    redireccionar(URL_SITIO . 'modulos/carrito/ver.php');
}
