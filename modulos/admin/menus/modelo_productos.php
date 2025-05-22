<?php
/**
 * Modelo de integración de menús con productos y categorías
 */

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

/**
 * Clase para manejar la integración de menús con productos y categorías
 */
class MenuProductos {
    private $conexion;
    
    /**
     * Constructor
     * 
     * @param object $conexion Conexión a la base de datos
     */
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Buscar productos por nombre para selector de menús
     * 
     * @param string $termino Término de búsqueda
     * @param int $limite Límite de resultados (por defecto 10)
     * @return array Lista de productos encontrados
     */
    public function buscarProductos($termino, $limite = 10) {
        $termino = $this->conexion->real_escape_string("%{$termino}%");
        $limite = (int)$limite;
        
        $sql = "SELECT id, nombre, precio, imagen, sku, stock
                FROM productos 
                WHERE nombre LIKE ? OR sku LIKE ? OR descripcion LIKE ? 
                ORDER BY nombre 
                LIMIT ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssi", $termino, $termino, $termino, $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $productos = [];
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                // Añadir URL y ruta de imagen para el producto
                $row['url'] = URL_SITIO . 'producto/' . $row['id'];
                
                // Formatear precio
                $row['precio_formateado'] = number_format($row['precio'], 2, ',', '.');
                
                // Verificar imagen
                if (!empty($row['imagen'])) {
                    $row['imagen_url'] = URL_SITIO . 'uploads/productos/' . $row['imagen'];
                } else {
                    $row['imagen_url'] = URL_SITIO . 'assets/img/producto-default.jpg';
                }
                
                $productos[] = $row;
            }
        }
        
        return $productos;
    }
    
    /**
     * Obtener datos de un producto específico
     * 
     * @param int $id ID del producto
     * @return array|null Datos del producto o null si no existe
     */
    public function obtenerProducto($id) {
        $id = (int)$id;
        
        $sql = "SELECT id, nombre, precio, imagen, sku, stock 
                FROM productos 
                WHERE id = ? 
                LIMIT 1";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $resultado->num_rows > 0) {
            $producto = $resultado->fetch_assoc();
            
            // Añadir URL y ruta de imagen para el producto
            $producto['url'] = URL_SITIO . 'producto/' . $producto['id'];
            
            // Formatear precio
            $producto['precio_formateado'] = number_format($producto['precio'], 2, ',', '.');
            
            // Verificar imagen
            if (!empty($producto['imagen'])) {
                $producto['imagen_url'] = URL_SITIO . 'uploads/productos/' . $producto['imagen'];
            } else {
                $producto['imagen_url'] = URL_SITIO . 'assets/img/producto-default.jpg';
            }
            
            return $producto;
        }
        
        return null;
    }
    
    /**
     * Obtener todas las categorías para selector de menús
     * 
     * @param bool $incluir_conteo Si debe incluir conteo de productos por categoría
     * @return array Lista de categorías
     */
    public function obtenerCategorias($incluir_conteo = true) {
        if ($incluir_conteo) {
            $sql = "SELECT c.id, c.nombre, c.slug, c.descripcion, c.imagen, 
                    (SELECT COUNT(*) FROM productos_categorias pc WHERE pc.categoria_id = c.id) as total_productos
                    FROM categorias c
                    ORDER BY c.nombre";
        } else {
            $sql = "SELECT id, nombre, slug, descripcion, imagen 
                    FROM categorias 
                    ORDER BY nombre";
        }
        
        $resultado = $this->conexion->query($sql);
        $categorias = [];
        
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                // Añadir URL y ruta de imagen para la categoría
                $row['url'] = URL_SITIO . 'categoria/' . $row['slug'];
                
                // Verificar imagen
                if (!empty($row['imagen'])) {
                    $row['imagen_url'] = URL_SITIO . 'uploads/categorias/' . $row['imagen'];
                } else {
                    $row['imagen_url'] = URL_SITIO . 'assets/img/categoria-default.jpg';
                }
                
                $categorias[] = $row;
            }
        }
        
        return $categorias;
    }
    
    /**
     * Obtener datos de una categoría específica
     * 
     * @param int $id ID de la categoría
     * @param bool $incluir_conteo Si debe incluir conteo de productos
     * @return array|null Datos de la categoría o null si no existe
     */
    public function obtenerCategoria($id, $incluir_conteo = true) {
        $id = (int)$id;
        
        if ($incluir_conteo) {
            $sql = "SELECT c.id, c.nombre, c.slug, c.descripcion, c.imagen, 
                    (SELECT COUNT(*) FROM productos_categorias pc WHERE pc.categoria_id = c.id) as total_productos
                    FROM categorias c
                    WHERE c.id = ?
                    LIMIT 1";
        } else {
            $sql = "SELECT id, nombre, slug, descripcion, imagen 
                    FROM categorias 
                    WHERE id = ? 
                    LIMIT 1";
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $resultado->num_rows > 0) {
            $categoria = $resultado->fetch_assoc();
            
            // Añadir URL y ruta de imagen para la categoría
            $categoria['url'] = URL_SITIO . 'categoria/' . $categoria['slug'];
            
            // Verificar imagen
            if (!empty($categoria['imagen'])) {
                $categoria['imagen_url'] = URL_SITIO . 'uploads/categorias/' . $categoria['imagen'];
            } else {
                $categoria['imagen_url'] = URL_SITIO . 'assets/img/categoria-default.jpg';
            }
            
            return $categoria;
        }
        
        return null;
    }
    
    /**
     * Crear un elemento de menú inteligente
     * 
     * @param int $menu_id ID del menú
     * @param string $tipo Tipo de menú inteligente (nuevos, vendidos, destacados, ofertas)
     * @param string $titulo Título del elemento
     * @param int $parent_id ID del elemento padre (0 para nivel raíz)
     * @param int $limite Límite de productos a mostrar (0 para todos)
     * @param int $orden Orden del elemento
     * @param string $clase Clases CSS adicionales
     * @return int|false ID del nuevo elemento o false si hay error
     */
    public function crearMenuInteligente($menu_id, $tipo, $titulo, $parent_id = 0, $limite = 10, $orden = 0, $clase = '') {
        $menu_id = (int)$menu_id;
        $parent_id = (int)$parent_id;
        $limite = (int)$limite;
        $orden = (int)$orden;
        
        // Verificar tipo válido
        $tipos_validos = ['nuevos', 'vendidos', 'destacados', 'ofertas'];
        if (!in_array($tipo, $tipos_validos)) {
            error_log("Tipo de menú inteligente no válido: $tipo");
            return false;
        }
        
        // Limpiar datos
        $titulo = $this->conexion->real_escape_string($titulo);
        $tipo = $this->conexion->real_escape_string($tipo);
        $clase = $this->conexion->real_escape_string($clase);
        
        // Generar URL genérica para este tipo de menú inteligente
        // La URL real se generará dinámicamente al renderizar el menú
        $url = URL_SITIO . 'productos/' . $tipo;
        
        // Crear transacción para asegurar consistencia
        $this->conexion->begin_transaction();
        
        try {
            // Insertar el elemento de menú
            $sql = "INSERT INTO menu_items (menu_id, parent_id, titulo, url, orden, clase, tipo, config) 
                    VALUES (?, ?, ?, ?, ?, ?, 'smart', ?)";
            
            $config = json_encode(['tipo' => $tipo, 'limite' => $limite]);
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iississ", $menu_id, $parent_id, $titulo, $url, $orden, $clase, $config);
            $stmt->execute();
            
            $nuevo_id = $stmt->insert_id;
            
            // Confirmar transacción
            $this->conexion->commit();
            return $nuevo_id;
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conexion->rollback();
            error_log("Error al crear menú inteligente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un elemento de menú inteligente
     * 
     * @param int $id ID del elemento de menú
     * @param string $tipo Tipo de menú inteligente (nuevos, vendidos, destacados, ofertas)
     * @param string $titulo Título del elemento
     * @param int $parent_id ID del elemento padre (0 para nivel raíz)
     * @param int $limite Límite de productos a mostrar (0 para todos)
     * @param int $orden Orden del elemento
     * @param string $clase Clases CSS adicionales
     * @return bool True si se actualiza correctamente, false en caso contrario
     */
    public function actualizarMenuInteligente($id, $tipo, $titulo, $parent_id = 0, $limite = 10, $orden = 0, $clase = '') {
        $id = (int)$id;
        $parent_id = (int)$parent_id;
        $limite = (int)$limite;
        $orden = (int)$orden;
        
        // Verificar tipo válido
        $tipos_validos = ['nuevos', 'vendidos', 'destacados', 'ofertas'];
        if (!in_array($tipo, $tipos_validos)) {
            error_log("Tipo de menú inteligente no válido: $tipo");
            return false;
        }
        
        // Limpiar datos
        $titulo = $this->conexion->real_escape_string($titulo);
        $tipo = $this->conexion->real_escape_string($tipo);
        $clase = $this->conexion->real_escape_string($clase);
        
        // Generar URL genérica para este tipo de menú inteligente
        $url = URL_SITIO . 'productos/' . $tipo;
        
        // Crear transacción para asegurar consistencia
        $this->conexion->begin_transaction();
        
        try {
            // Actualizar el elemento de menú
            $sql = "UPDATE menu_items SET 
                    parent_id = ?, 
                    titulo = ?, 
                    url = ?, 
                    orden = ?, 
                    clase = ?, 
                    tipo = 'smart',
                    config = ?
                    WHERE id = ?";
            
            $config = json_encode(['tipo' => $tipo, 'limite' => $limite]);
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ississ", $parent_id, $titulo, $url, $orden, $clase, $config, $id);
            $stmt->execute();
            
            // Confirmar transacción
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conexion->rollback();
            error_log("Error al actualizar menú inteligente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener productos para un tipo de menú inteligente
     * 
     * @param string $tipo Tipo de menú inteligente (nuevos, vendidos, destacados, ofertas)
     * @param int $limite Límite de productos a mostrar (0 para todos)
     * @return array Lista de productos para el menú inteligente
     */
    public function obtenerProductosMenuInteligente($tipo, $limite = 10) {
        $limite = (int)$limite;
        $sql = "";
        
        switch ($tipo) {
            case 'nuevos':
                // Productos más recientes
                $sql = "SELECT id, nombre, precio, imagen, fecha_creacion
                       FROM productos 
                       WHERE activo = 1 
                       ORDER BY fecha_creacion DESC";
                break;
                
            case 'vendidos':
                // Productos más vendidos
                $sql = "SELECT p.id, p.nombre, p.precio, p.imagen, SUM(dp.cantidad) as total_vendidos
                       FROM productos p
                       JOIN detalle_pedido dp ON p.id = dp.producto_id
                       JOIN pedidos pe ON dp.pedido_id = pe.id
                       WHERE p.activo = 1 AND pe.estado != 'cancelado'
                       GROUP BY p.id
                       ORDER BY total_vendidos DESC";
                break;
                
            case 'destacados':
                // Productos destacados
                $sql = "SELECT id, nombre, precio, imagen 
                       FROM productos 
                       WHERE destacado = 1 AND activo = 1 
                       ORDER BY fecha_actualizacion DESC";
                break;
                
            case 'ofertas':
                // Productos en oferta
                $sql = "SELECT id, nombre, precio, precio_oferta as precio, imagen 
                       FROM productos 
                       WHERE precio_oferta > 0 AND precio_oferta < precio AND activo = 1 
                       ORDER BY (precio - precio_oferta) / precio DESC";
                break;
                
            default:
                return [];
        }
        
        // Aplicar límite si se ha especificado
        if ($limite > 0) {
            $sql .= " LIMIT " . $limite;
        }
        
        $resultado = $this->conexion->query($sql);
        $productos = [];
        
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                // Añadir URL y ruta de imagen para el producto
                $row['url'] = URL_SITIO . 'producto/' . $row['id'];
                
                // Formatear precio
                $row['precio_formateado'] = number_format($row['precio'], 2, ',', '.');
                
                // Verificar imagen
                if (!empty($row['imagen'])) {
                    $row['imagen_url'] = URL_SITIO . 'uploads/productos/' . $row['imagen'];
                } else {
                    $row['imagen_url'] = URL_SITIO . 'assets/img/producto-default.jpg';
                }
                
                $productos[] = $row;
            }
        }
        
        return $productos;
    }
    
    /**
     * Obtener productos por categoría
     * 
     * @param int $categoria_id ID de la categoría
     * @param int $limite Límite de productos a mostrar (0 para todos)
     * @return array Lista de productos de la categoría
     */
    public function obtenerProductosPorCategoria($categoria_id, $limite = 0) {
        $categoria_id = (int)$categoria_id;
        $limite = (int)$limite;
        
        $sql = "SELECT p.id, p.nombre, p.precio, p.imagen 
                FROM productos p
                JOIN productos_categorias pc ON p.id = pc.producto_id
                WHERE pc.categoria_id = ? AND p.activo = 1
                ORDER BY p.nombre";
        
        // Aplicar límite si se ha especificado
        if ($limite > 0) {
            $sql .= " LIMIT " . $limite;
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $productos = [];
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                // Añadir URL y ruta de imagen para el producto
                $row['url'] = URL_SITIO . 'producto/' . $row['id'];
                
                // Formatear precio
                $row['precio_formateado'] = number_format($row['precio'], 2, ',', '.');
                
                // Verificar imagen
                if (!empty($row['imagen'])) {
                    $row['imagen_url'] = URL_SITIO . 'uploads/productos/' . $row['imagen'];
                } else {
                    $row['imagen_url'] = URL_SITIO . 'assets/img/producto-default.jpg';
                }
                
                $productos[] = $row;
            }
        }
        
        return $productos;
    }
    
    /**
     * Contar productos por categoría
     * 
     * @param int $categoria_id ID de la categoría
     * @return int Número de productos en la categoría
     */
    public function contarProductosPorCategoria($categoria_id) {
        $categoria_id = (int)$categoria_id;
        
        $sql = "SELECT COUNT(*) as total 
                FROM productos_categorias 
                WHERE categoria_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $row = $resultado->fetch_assoc()) {
            return (int)$row['total'];
        }
        
        return 0;
    }
    
    /**
     * Obtener los tipos de menús inteligentes disponibles
     * 
     * @return array Lista de tipos de menús inteligentes con sus detalles
     */
    public function obtenerTiposMenuInteligente() {
        return [
            'nuevos' => [
                'nombre' => 'Productos Nuevos',
                'descripcion' => 'Muestra los productos más recientes',
                'icono' => 'fas fa-star'
            ],
            'vendidos' => [
                'nombre' => 'Más Vendidos',
                'descripcion' => 'Muestra los productos más populares',
                'icono' => 'fas fa-fire'
            ],
            'destacados' => [
                'nombre' => 'Productos Destacados',
                'descripcion' => 'Muestra los productos marcados como destacados',
                'icono' => 'fas fa-thumbs-up'
            ],
            'ofertas' => [
                'nombre' => 'Ofertas Especiales',
                'descripcion' => 'Muestra los productos con precios rebajados',
                'icono' => 'fas fa-percent'
            ]
        ];
    }
}