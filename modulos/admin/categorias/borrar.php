<?php
define('ACCESO_PERMITIDO',true);
require_once __DIR__.'/../../../includes/config.php';
require_once __DIR__.'/../../../includes/funciones.php';
if (!estaLogueado()||!esAdmin()) redireccionar('login');
if (!empty($_GET['id'])) {
  $stmt = $conexion->prepare("DELETE FROM categorias WHERE id=?");
  $stmt->bind_param('i',$_GET['id']);
  $stmt->execute();
  $stmt->close();
  mostrarMensaje("Categoría eliminada",'info');
}
redireccionar('admin/categorias/listar');