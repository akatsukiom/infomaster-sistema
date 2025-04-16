<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Producto {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Obtener todos los productos
    public function obtenerTodos($categoria_id = null) {
        $where = '';
        if($categoria_id) {
            $categoria_id = (int)$categoria_id;
            $where = "WHERE categoria_id = $categoria_id";
        }
        
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                $where 
                ORDER BY p.destacado DESC, p.id DESC";
        
        $resultado = $this->conexion->query($sql);
        $productos = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }
        
        return $productos;
    }
    
    // Obtener productos destacados
    public function obtenerDestacados($limite = 6) {
        $limite = (int)$limite;
        
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.destacado = 1 
                ORDER BY p.id DESC 
                LIMIT $limite";
        
        $resultado = $this->conexion->query($sql);
        $productos = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }
        
        return $productos;
    }
    
    // Obtener un producto por ID
    public function obtenerPorId($id) {
        $id = (int)$id;
        
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = $id";
        
        $resultado = $this->conexion->query($sql);
        
        if($resultado->num_rows == 1) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }
    
    // Generar código de acceso único
    public function generarCodigoAcceso() {
        return md5(uniqid() . time() . rand(1000, 9999));
    }
}
?>