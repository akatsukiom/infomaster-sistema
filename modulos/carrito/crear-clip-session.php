<?php
// modulos/carrito/crear-clip-session.php

// 0) Permitir inclusión segura
define('ACCESO_PERMITIDO', true);

// 1) Carga configuración y helpers
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';

session_start();

// 2) Validar total
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
if ($total <= 0) {
    http_response_code(400);
    exit(json_encode(['error' => 'Total inválido']));
}

// Función auxiliar para logging y respuesta de error
function respuestaError($mensaje, $detalle = '') {
    error_log("[CLIP][ERROR] {$mensaje}" . ($detalle ? " — {$detalle}" : ''));
    http_response_code(500);
    exit(json_encode(['error' => $mensaje]));
}

// 3) Obtener access_token de Clip
$ch = curl_init(CLIP_BASE_URL . '/v1/oauth/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_FAILONERROR     => false,
    CURLOPT_POST            => true,
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_HTTPHEADER      => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS      => json_encode([
        'grant_type'    => 'client_credentials',
        'client_id'     => CLIP_CLIENT_ID,
        'client_secret' => CLIP_CLIENT_SECRET,
    ]),
]);
$resp     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

error_log("[CLIP][TOKEN] HTTP_CODE={$httpCode} — cURL_ERR={$curlErr} — RESPUESTA={$resp}");

if ($httpCode !== 200) {
    respuestaError('No se obtuvo token Clip', "HTTP={$httpCode}, cURL={$curlErr}");
}

$tokenData   = json_decode($resp, true);
$accessToken = $tokenData['access_token'] ?? null;
if (! $accessToken) {
    respuestaError('Token inválido recibido de Clip', $resp);
}

// 4) Crear cargo en Clip
$ch = curl_init(CLIP_BASE_URL . '/v1/charges');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_FAILONERROR     => false,
    CURLOPT_POST            => true,
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_HTTPHEADER      => [
        'Content-Type: application/json',
        "Authorization: Bearer {$accessToken}"
    ],
    CURLOPT_POSTFIELDS      => json_encode([
        'amount'      => $total,
        'currency'    => 'MXN',
        'orderId'     => session_id(),
        'options'     => ['lang' => 'es'],
        'redirectUrl' => CLIP_REDIRECT_URL,
        'publicKey'   => CLIP_PUBLIC_KEY
    ]),
]);
$resp     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

error_log("[CLIP][CHARGE] HTTP_CODE={$httpCode} — cURL_ERR={$curlErr} — RESPUESTA={$resp}");

if ($httpCode !== 201) {
    respuestaError('Error creando cargo en Clip', "HTTP={$httpCode}, cURL={$curlErr}");
}

$data       = json_decode($resp, true);
$paymentUrl = $data['paymentUrl'] ?? null;
if (! $paymentUrl) {
    respuestaError('Sin URL de pago', $resp);
}

// 5) Devolver URL al frontend
header('Content-Type: application/json');
echo json_encode(['paymentUrl' => $paymentUrl]);
