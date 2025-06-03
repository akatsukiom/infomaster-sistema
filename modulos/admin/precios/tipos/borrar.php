<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/chequear_admin.php';

if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $conexion->query("DELETE FROM plan_types WHERE id=$id");
}
header('Location: listar.php');
exit;
