<?php
session_start();
define('ACCESO_PERMITIDO', true);
require_once __DIR__.'/../../../includes/config.php';
require_once __DIR__.'/../../../includes/funciones.php';
if (!estaLogueado()||!esAdmin()) redireccionar('login.php');

$action = $_REQUEST['action'] ?? '';
switch($action) {

  // BORRAR PRODUCTO
  case 'delete_prod':
    if(is_numeric($_GET['id'])) {
      $conexion->query("DELETE FROM productos WHERE id=".(int)$_GET['id']);
      mostrarMensaje("Producto eliminado","success");
    }
    header('Location: '.URL_SITIO.'admin');
    break;

  // BORRAR CATEGORÍA
  case 'delete_cat':
    if(is_numeric($_GET['id'])) {
      $conexion->query("DELETE FROM categorias WHERE id=".(int)$_GET['id']);
      mostrarMensaje("Categoría eliminada","success");
    }
    header('Location: '.URL_SITIO.'admin');
    break;

  // NUEVA CATEGORÍA (prompt JS)
  case 'new_cat':
    $n = trim($_GET['nombre'] ?? '');
    if(!$n) {
      // pedimos nombre por prompt
      echo '<script>
        let nombre = prompt("Nombre de la nueva categoría:");
        if(nombre) window.location="?action=new_cat&nombre="+encodeURIComponent(nombre);
        else window.location="'.URL_SITIO.'admin";
      </script>';
      exit;
    }
    $stmt = $conexion->prepare("INSERT INTO categorias(nombre,descripcion) VALUES(?,?)");
    $stmt->bind_param('ss',$n,'');
    $stmt->execute();
    mostrarMensaje("Categoría creada","success");
    header('Location: '.URL_SITIO.'admin');
    break;

  // EXPORTAR PRODUCTOS
  case 'export_prod':
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="productos.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','categoria_id','nombre','descripcion','precio','stock','imagen']);
    $rs = $conexion->query("SELECT * FROM productos");
    while($r=$rs->fetch_assoc()) fputcsv($out,$r);
    fclose($out);
    exit;

  // EXPORTAR CATEGORÍAS
  case 'export_cat':
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="categorias.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','nombre','descripcion']);
    $rs = $conexion->query("SELECT * FROM categorias");
    while($r=$rs->fetch_assoc()) fputcsv($out,$r);
    fclose($out);
    exit;

  // IMPORTAR PRODUCTOS
  case 'import_prod':
    $f = $_FILES['csv']['tmp_name'] ?? null;
    if ($f && ($h=fopen($f,'r'))) {
      fgetcsv($h); // cabecera
      while($row=fgetcsv($h)){
        $stmt = $conexion->prepare("
          INSERT INTO productos(categoria_id,nombre,descripcion,precio,stock,imagen)
          VALUES(?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            nombre=VALUES(nombre),
            descripcion=VALUES(descripcion),
            precio=VALUES(precio),
            stock=VALUES(stock),
            imagen=VALUES(imagen)
        ");
        $stmt->bind_param('issdis',
          $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]
        );
        $stmt->execute();
      }
      fclose($h);
      mostrarMensaje("Productos importados","success");
    }
    header('Location: '.URL_SITIO.'admin');
    break;

  // IMPORTAR CATEGORÍAS
  case 'import_cat':
    $f = $_FILES['csv']['tmp_name'] ?? null;
    if ($f && ($h=fopen($f,'r'))) {
      fgetcsv($h);
      while($row=fgetcsv($h)){
        $stmt = $conexion->prepare("
          INSERT INTO categorias(id,nombre,descripcion)
          VALUES(?,?,?)
          ON DUPLICATE KEY UPDATE
            nombre=VALUES(nombre),
            descripcion=VALUES(descripcion)
        ");
        $stmt->bind_param('iss',$row[0],$row[1],$row[2]);
        $stmt->execute();
      }
      fclose($h);
      mostrarMensaje("Categorías importadas","success");
    }
    header('Location: '.URL_SITIO.'admin');
    break;
}

