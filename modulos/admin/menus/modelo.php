<?php
// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Menu {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Obtener todos los menús
    public function obtenerTodos() {
        $sql = "SELECT * FROM menus ORDER BY nombre";
        $resultado = $this->conexion->query($sql);
        $menus = [];
        
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                $menus[] = $row;
            }
        }
        
        return $menus;
    }
    
    // Obtener un menú por su ID
    public function obtenerPorId($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM menus WHERE id = $id LIMIT 1";
        $resultado = $this->conexion->query($sql);
        
        if ($resultado && $resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }
    
    // Obtener items de un menú
    public function obtenerItems($menu_id) {
        $menu_id = (int)$menu_id;
        $sql = "SELECT * FROM menu_items WHERE menu_id = $menu_id ORDER BY parent_id, orden";
        $resultado = $this->conexion->query($sql);
        $items = [];
        
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                $items[] = $row;
            }
        }
        
        return $items;
    }
    
    /**
     * Obtener items como estructura de árbol jerárquico
     * 
     * @param int $menu_id ID del menú
     * @return array Árbol jerárquico de items del menú
     */
    public function obtenerArbolItems($menu_id) {
        // 1. Obtener todos los items ordenados
        $items = $this->obtenerItems($menu_id);
        
        // 2. Construir lookup y árbol anidado
        $lookup = [];
        $tree = [];
        
        // Primer paso: crear la tabla de lookup con referencias
        foreach ($items as $item) {
            $item['children'] = [];
            $lookup[$item['id']] = $item;
        }
        
        // Segundo paso: construir la estructura jerárquica
        foreach ($lookup as $id => $item) {
            if ($item['parent_id'] == 0) {
                // Es un item de nivel raíz
                $tree[] = &$lookup[$id];
            } else {
                // Es un item hijo, verificar que exista el padre
                if (isset($lookup[$item['parent_id']])) {
                    $lookup[$item['parent_id']]['children'][] = &$lookup[$id];
                } else {
                    // Si el padre no existe, tratar como item raíz
                    $lookup[$id]['parent_id'] = 0;
                    $tree[] = &$lookup[$id];
                }
            }
        }
        
        return $tree;
    }
    
    // Crear nuevo menú
    public function crear($nombre, $descripcion = '') {
        $nombre = $this->conexion->real_escape_string($nombre);
        $descripcion = $this->conexion->real_escape_string($descripcion);
        
        $sql = "INSERT INTO menus (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
        
        if ($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        
        return 0;
    }
    
    // Actualizar menú
    public function actualizar($id, $nombre, $descripcion = '') {
        $id = (int)$id;
        $nombre = $this->conexion->real_escape_string($nombre);
        $descripcion = $this->conexion->real_escape_string($descripcion);
        
        $sql = "UPDATE menus SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = $id";
        
        return $this->conexion->query($sql);
    }
    
    // Eliminar menú
    public function eliminar($id) {
        $id = (int)$id;
        
        // Primero eliminar los items del menú
        $sql_items = "DELETE FROM menu_items WHERE menu_id = $id";
        $this->conexion->query($sql_items);
        
        // Luego eliminar el menú
        $sql = "DELETE FROM menus WHERE id = $id";
        
        return $this->conexion->query($sql);
    }
    
    // Agregar item a menú
    public function agregarItem($menu_id, $titulo, $url, $parent_id = 0, $orden = 0, $clase = '', $target = '_self') {
        $menu_id = (int)$menu_id;
        $parent_id = (int)$parent_id;
        $orden = (int)$orden;
        $titulo = $this->conexion->real_escape_string($titulo);
        $url = $this->conexion->real_escape_string($url);
        $clase = $this->conexion->real_escape_string($clase);
        $target = $this->conexion->real_escape_string($target);
        
        $sql = "INSERT INTO menu_items (menu_id, parent_id, titulo, url, orden, clase, target) 
                VALUES ($menu_id, $parent_id, '$titulo', '$url', $orden, '$clase', '$target')";
        
        if ($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        
        return 0;
    }
    
    // Actualizar item de menú
    public function actualizarItem($id, $titulo, $url, $parent_id = 0, $orden = 0, $clase = '', $target = '_self') {
        $id = (int)$id;
        $parent_id = (int)$parent_id;
        $orden = (int)$orden;
        $titulo = $this->conexion->real_escape_string($titulo);
        $url = $this->conexion->real_escape_string($url);
        $clase = $this->conexion->real_escape_string($clase);
        $target = $this->conexion->real_escape_string($target);
        
        $sql = "UPDATE menu_items SET 
                titulo = '$titulo', 
                url = '$url', 
                parent_id = $parent_id, 
                orden = $orden, 
                clase = '$clase', 
                target = '$target' 
                WHERE id = $id";
        
        return $this->conexion->query($sql);
    }
    
    // Eliminar item de menú
    public function eliminarItem($id) {
        $id = (int)$id;
        
        // Obtener el ítem para saber su parent_id
        $sql_item = "SELECT parent_id FROM menu_items WHERE id = $id LIMIT 1";
        $resultado = $this->conexion->query($sql_item);
        $parent_id = 0;
        
        if ($resultado && $resultado->num_rows > 0) {
            $item = $resultado->fetch_assoc();
            $parent_id = $item['parent_id'];
            
            // Actualizar los hijos para que apunten al padre del item eliminado
            $sql_hijos = "UPDATE menu_items SET parent_id = $parent_id WHERE parent_id = $id";
            $this->conexion->query($sql_hijos);
        }
        
        // Eliminar el item
        $sql = "DELETE FROM menu_items WHERE id = $id";
        
        return $this->conexion->query($sql);
    }
}