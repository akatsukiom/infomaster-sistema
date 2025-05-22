<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__.'/../../../includes/config.php';
require_once __DIR__.'/../../../includes/funciones.php';
if (!estaLogueado() || !esAdmin()) redireccionar('login');

if (!empty($_GET['id'])) {
    $id = (int)$_GET['id']; // Asegurar que es un entero
    
    try {
        // Verificar si esta categoría tiene productos asociados
        $sql_check = "SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?";
        $stmt_check = $conexion->prepare($sql_check);
        
        if (!$stmt_check) {
            throw new Exception("Error preparando consulta: " . $conexion->error);
        }
        
        $stmt_check->bind_param('i', $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $row = $result->fetch_assoc();
        $stmt_check->close();
        
        if ($row['total'] > 0) {
            // La categoría tiene productos asociados, no se puede eliminar
            mostrarMensaje("No se puede eliminar la categoría porque está siendo utilizada por {$row['total']} producto(s). Debes reasignar o eliminar estos productos primero.", 'error');
            redireccionar('admin/categorias/listar');
            exit;
        }
        
        // Si llegamos aquí, podemos eliminar la categoría porque no tiene productos asociados
        $stmt_delete = $conexion->prepare("DELETE FROM categorias WHERE id = ?");
        if (!$stmt_delete) {
            throw new Exception("Error preparando eliminación: " . $conexion->error);
        }
        
        $stmt_delete->bind_param('i', $id);
        $resultado = $stmt_delete->execute();
        
        if (!$resultado) {
            throw new Exception("Error al ejecutar eliminación: " . $stmt_delete->error);
        }
        
        if ($stmt_delete->affected_rows > 0) {
            mostrarMensaje("Categoría eliminada correctamente", 'success');
        } else {
            mostrarMensaje("No se encontró la categoría a eliminar", 'warning');
        }
        
        $stmt_delete->close();
        
    } catch (Exception $e) {
        // Registrar el error en los logs del sistema
        error_log("Error en borrar.php: " . $e->getMessage());
        mostrarMensaje("Error al eliminar la categoría: " . $e->getMessage(), 'error');
    }
}

redireccionar('admin/categorias/listar');
?>