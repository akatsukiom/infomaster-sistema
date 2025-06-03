<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php';

if (isset($_GET['months'])) {
    $months = (int)$_GET['months'];
    if ($months > 0) {
        $stmt = $conexion->prepare("DELETE FROM duration_rates WHERE months = ?");
        $stmt->bind_param("i", $months);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: listar.php');
exit;
