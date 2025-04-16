<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Entrega {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Crear entrega automática
    public function crear($pedido_id, $producto_id, $usuario_id) {
        $pedido_id = (int)$pedido_id;
        $producto_id = (int)$producto_id;
        $usuario_id = (int)$usuario_id;
        
        // Generar código de acceso único
        $producto = new Producto($this->conexion);
        $codigo_acceso = $producto->generarCodigoAcceso();
        
        $sql = "INSERT INTO entregas (pedido_id, producto_id, usuario_id, codigo_acceso) 
                VALUES ($pedido_id, $producto_id, $usuario_id, '$codigo_acceso')";
        
        if($this->conexion->query($sql)) {
            return ['success' => 'Entrega creada correctamente', 'codigo' => $codigo_acceso];
        } else {
            return ['error' => 'Error al crear la entrega: ' . $this->conexion->error];
        }
    }
    
    // Obtener entregas de un usuario
    public function obtenerPorUsuario($usuario_id) {
        $usuario_id = (int)$usuario_id;
        
        $sql = "SELECT e.*, p.nombre as producto_nombre, p.imagen 
                FROM entregas e 
                JOIN productos p ON e.producto_id = p.id 
                WHERE e.usuario_id = $usuario_id 
                AND e.estado = 'activo' 
                ORDER BY e.fecha_entrega DESC";
        
        $resultado = $this->conexion->query($sql);
        $entregas = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $entregas[] = $fila;
        }
        
        return $entregas;
    }
    
    // Verificar acceso a un producto
    public function verificarAcceso($usuario_id, $producto_id) {
        $usuario_id = (int)$usuario_id;
        $producto_id = (int)$producto_id;
        
        $sql = "SELECT id FROM entregas 
                WHERE usuario_id = $usuario_id 
                AND producto_id = $producto_id 
                AND estado = 'activo'";
        
        $resultado = $this->conexion->query($sql);
        
        return $resultado->num_rows > 0;
    }
}
?>