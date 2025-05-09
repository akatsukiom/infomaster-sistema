<?php
// modelo.php - Asegúrate que este código esté en el archivo del modelo

class Producto {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }
    
    public function obtenerPorId($id) {
        $id = (int) $id;
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = $id LIMIT 1";
                
        $rs = $this->db->query($sql);
        if ($rs && $rs->num_rows > 0) {
            return $rs->fetch_assoc();
        }
        return false;
    }
    
    public function obtenerTodos($categoria_id = 0) {
        $where = "";
        if ($categoria_id > 0) {
            $where = "WHERE p.categoria_id = " . (int)$categoria_id;
        }
        
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                $where ORDER BY p.nombre";
                
        $rs = $this->db->query($sql);
        if ($rs) {
            return $rs->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    public function crear($datos) {
        // Validar datos mínimos
        if (empty($datos['nombre']) || empty($datos['categoria_id'])) {
            return false;
        }
        
        // Preparar campos y valores
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            $campos[] = $campo;
            
            if (is_string($valor)) {
                $valores[] = "'" . $this->db->real_escape_string($valor) . "'";
            } else {
                $valores[] = $valor;
            }
        }
        
        // Asegurarnos que precio_completo esté incluido
        if (!in_array('precio_completo', $campos)) {
            $campos[] = 'precio_completo';
            $valores[] = isset($datos['precio_completo']) ? $datos['precio_completo'] : 0;
        }
        
        $sql = "INSERT INTO productos (" . implode(',', $campos) . ") 
                VALUES (" . implode(',', $valores) . ")";
        
        // Para depuración, opcional
        //error_log("SQL crear: " . $sql);
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        
        // Para depuración, opcional
        //error_log("Error SQL: " . $this->db->error);
        
        return false;
    }
    
    public function actualizar($id, $datos) {
        $id = (int) $id;
        
        // Validar datos mínimos
        if (empty($datos['nombre']) || empty($datos['categoria_id'])) {
            return false;
        }
        
        // Preparar sets
        $sets = [];
        foreach ($datos as $campo => $valor) {
            if (is_string($valor)) {
                $sets[] = "$campo = '" . $this->db->real_escape_string($valor) . "'";
            } else {
                $sets[] = "$campo = $valor";
            }
        }
        
        // Asegurarnos que precio_completo esté incluido
        if (!isset($datos['precio_completo'])) {
            $sets[] = "precio_completo = " . (isset($datos['precio_completo']) ? $datos['precio_completo'] : 0);
        }
        
        $sql = "UPDATE productos SET " . implode(',', $sets) . " WHERE id = $id";
        
        // Para depuración, opcional
        //error_log("SQL actualizar: " . $sql);
        
        if ($this->db->query($sql)) {
            return true;
        }
        
        // Para depuración, opcional
        //error_log("Error SQL: " . $this->db->error);
        
        return false;
    }
    
    public function eliminar($id) {
        $id = (int) $id;
        $sql = "DELETE FROM productos WHERE id = $id";
        return $this->db->query($sql);
    }
}