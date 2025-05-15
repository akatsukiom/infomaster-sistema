<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Verificar si se ha subido un archivo
if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
    mostrarMensaje('No se ha subido ningún archivo o hubo un error en la subida', 'error');
    redireccionar('admin/menus/listar');
    exit;
}

// Verificar tipo de archivo
$mimeType = mime_content_type($_FILES['archivo_csv']['tmp_name']);
if ($mimeType !== 'text/csv' && $mimeType !== 'text/plain') {
    mostrarMensaje('El archivo debe ser CSV', 'error');
    redireccionar('admin/menus/listar');
    exit;
}

// Instanciar modelo
$menuModel = new Menu($conexion);

// Abrir el archivo
$handle = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
if (!$handle) {
    mostrarMensaje('Error al abrir el archivo', 'error');
    redireccionar('admin/menus/listar');
    exit;
}

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Leer la primera línea (headers)
    $headers = fgetcsv($handle);
    
    // Verificar si es la sección de menús
    if (count($headers) === 4 && $headers[0] === 'id' && $headers[1] === 'nombre') {
        // Procesamos menús
        while (($data = fgetcsv($handle)) !== false) {
            // Si encontramos una línea en blanco, significa que pasamos a la siguiente sección
            if (count($data) === 1 && empty($data[0])) {
                break;
            }
            
            // Verificar si tenemos datos de menú
            if (count($data) === 4) {
                $id = (int)$data[0];
                $nombre = $data[1];
                $descripcion = $data[2];
                
                // Verificar si el menú ya existe
                $menu_existente = $menuModel->obtenerPorId($id);
                
                if ($menu_existente) {
                    // Actualizar
                    $menuModel->actualizar($id, $nombre, $descripcion);
                } else {
                    // Insertar con ID específico
                    $sql = "INSERT INTO menus (id, nombre, descripcion) VALUES (?, ?, ?)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param('iss', $id, $nombre, $descripcion);
                    $stmt->execute();
                }
            }
        }
        
        // Leer la siguiente línea (headers de items)
        $headers = fgetcsv($handle);
        
        // Verificar si es la sección de items
        if (count($headers) === 8 && $headers[0] === 'menu_id' && $headers[1] === 'id') {
            // Procesamos items
            while (($data = fgetcsv($handle)) !== false) {
                // Verificar si tenemos datos de item
                if (count($data) === 8) {
                    $menu_id = (int)$data[0];
                    $id = (int)$data[1];
                    $parent_id = (int)$data[2];
                    $titulo = $data[3];
                    $url = $data[4];
                    $orden = (int)$data[5];
                    $clase = $data[6];
                    $target = $data[7];
                    
                    // Verificar si el menú existe
                    $menu = $menuModel->obtenerPorId($menu_id);
                    
                    if ($menu) {
                        // Insertar item con ID específico
                        $sql = "INSERT INTO menu_items (id, menu_id, parent_id, titulo, url, orden, clase, target) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                menu_id = VALUES(menu_id),
                                parent_id = VALUES(parent_id),
                                titulo = VALUES(titulo),
                                url = VALUES(url),
                                orden = VALUES(orden),
                                clase = VALUES(clase),
                                target = VALUES(target)";
                        $stmt = $conexion->prepare($sql);
                        $stmt->bind_param('iiississ', $id, $menu_id, $parent_id, $titulo, $url, $orden, $clase, $target);
                        $stmt->execute();
                    }
                }
            }
        }
    }
    
    // Confirmar transacción
    $conexion->commit();
    mostrarMensaje('Importación completada con éxito', 'success');
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->rollback();
    mostrarMensaje('Error en la importación: ' . $e->getMessage(), 'error');
}

// Cerrar el archivo
fclose($handle);

// Redireccionar
redireccionar('admin/menus/listar');