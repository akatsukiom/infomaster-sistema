<?php
// debug-clip-token.php

// 0) Permitir inclusión directa
define('ACCESO_PERMITIDO', true);

// 1) Incluye tu config
require_once __DIR__ . '/includes/config.php';

// 2) Prepara la petición al endpoint /v1/oauth/token
$payload = json_encode([
    'grant_type'    => 'client_credentials',
    'client_id'     => CLIP_CLIENT_ID,
    'client_secret' => CLIP_CLIENT_SECRET,
]);

$ch = curl_init(CLIP_BASE_URL . '/v1/oauth/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_FAILONERROR     => false,
    CURLOPT_POST            => true,
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_HTTPHEADER      => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS      => $payload,
]);

// 3) Ejecuta y captura resultado
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// 4) Muestra con texto plano
header('Content-Type: text/plain; charset=utf-8');
echo "→ HTTP status code: {$httpCode}\n";
echo "→ cURL error     : " . ($curlErr ?: 'none') . "\n\n";
echo "→ Raw response:\n";
echo $response . "\n";
