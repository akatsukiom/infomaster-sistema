<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Carrito {
    // Inicializar carrito en sesión
    public static function inicializar() {
        if(!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }
    
    // Agregar producto al carrito
    public static function agregar($producto_id, $cantidad = 1, $precio) {
        self::inicializar();
        $producto_id = (int)$producto_id;
        $cantidad = (int)$cantidad;
        $precio = (float)$precio;
        
        // Si ya existe, aumentar cantidad
        if(isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = [
                'id' => $producto_id,
                'cantidad' => $cantidad,
                'precio' => $precio
            ];
        }
        
        return true;
    }
    
    // Actualizar cantidad de un producto
    public static function actualizar($producto_id, $cantidad) {
        self::inicializar();
        $producto_id = (int)$producto_id;
        $cantidad = (int)$cantidad;
        
        if(isset($_SESSION['carrito'][$producto_id])) {
            if($cantidad <= 0) {
                return self::eliminar($producto_id);
            } else {
                $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
                return true;
            }
        }
        
        return false;
    }
    
    // Eliminar producto del carrito
    public static function eliminar($producto_id) {
        self::inicializar();
        $producto_id = (int)$producto_id;
        
        if(isset($_SESSION['carrito'][$producto_id])) {
            unset($_SESSION['carrito'][$producto_id]);
            return true;
        }
        
        return false;
    }
    
    // Vaciar carrito
    public static function vaciar() {
        $_SESSION['carrito'] = [];
        return true;
    }
    
    // Obtener contenido del carrito
    public static function obtener() {
        self::inicializar();
        return $_SESSION['carrito'];
    }
    
    // Contar items en el carrito
    public static function contar() {
        self::inicializar();
        $total = 0;
        
        foreach($_SESSION['carrito'] as $item) {
            $total += $item['cantidad'];
        }
        
        return $total;
    }
    
    // Calcular total del carrito
    public static function calcularTotal() {
        self::inicializar();
        $total = 0;
        
        foreach($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        
        return $total;
    }
}

class Pedido {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Crear nuevo pedido
    public function crear($usuario_id, $total) {
        $usuario_id = (int)$usuario_id;
        $total = (float)$total;
        
        $sql = "INSERT INTO pedidos (usuario_id, total) VALUES ($usuario_id, $total)";
        
        if($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        
        return false;
    }
    
    // Agregar detalle de pedido
    public function agregarDetalle($pedido_id, $producto_id, $cantidad, $precio) {
        $pedido_id = (int)$pedido_id;
        $producto_id = (int)$producto_id;
        $cantidad = (int)$cantidad;
        $precio = (float)$precio;
        
        $sql = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio) 
                VALUES ($pedido_id, $producto_id, $cantidad, $precio)";
        
        return $this->conexion->query($sql);
    }
    
    // Actualizar estado del pedido
    public function actualizarEstado($pedido_id, $estado) {
        $pedido_id = (int)$pedido_id;
        $estado = limpiarDato($estado);
        
        $sql = "UPDATE pedidos SET estado = '$estado' WHERE id = $pedido_id";
        
        return $this->conexion->query($sql);
    }
    
    // Obtener un pedido por ID
    public function obtenerPorId($pedido_id) {
        $pedido_id = (int)$pedido_id;
        
        $sql = "SELECT * FROM pedidos WHERE id = $pedido_id";
        $resultado = $this->conexion->query($sql);
        
        if($resultado->num_rows == 1) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }
    
    // Obtener detalles de un pedido
    public function obtenerDetalles($pedido_id) {
        $pedido_id = (int)$pedido_id;
        
        $sql = "SELECT d.*, p.nombre as producto_nombre
                FROM detalle_pedido d
                JOIN productos p ON d.producto_id = p.id
                WHERE d.pedido_id = $pedido_id";
        
        $resultado = $this->conexion->query($sql);
        $detalles = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $detalles[] = $fila;
        }
        
        return $detalles;
    }
    
    // Obtener pedidos de un usuario
    public function obtenerPorUsuario($usuario_id) {
        $usuario_id = (int)$usuario_id;
        
        $sql = "SELECT * FROM pedidos WHERE usuario_id = $usuario_id ORDER BY fecha_pedido DESC";
        
        $resultado = $this->conexion->query($sql);
        $pedidos = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $pedidos[] = $fila;
        }
        
        return $pedidos;
    }
    
    // Procesar pedido con carrito actual
    public function procesarPedido($usuario_id) {
        $carrito = Carrito::obtener();
        
        if(empty($carrito)) {
            return ['error' => 'El carrito está vacío'];
        }
        
        $total = Carrito::calcularTotal();
        
        // Verificar saldo suficiente
        $usuario = new Usuario($this->conexion);
        $saldo = $usuario->obtenerSaldo($usuario_id);
        
        if($saldo < $total) {
            return ['error' => 'Saldo insuficiente para realizar la compra'];
        }
        
        // Comenzar transacción
        $this->conexion->begin_transaction();
        
        try {
            // 1. Crear pedido
            $pedido_id = $this->crear($usuario_id, $total);
            if(!$pedido_id) {
                throw new Exception('Error al crear el pedido');
            }
            
            // 2. Agregar detalles de pedido
            foreach($carrito as $item) {
                $resultado = $this->agregarDetalle($pedido_id, $item['id'], $item['cantidad'], $item['precio']);
                if(!$resultado) {
                    throw new Exception('Error al agregar detalle del pedido');
                }
            }
            
            // 3. Realizar pago con wallet
            $wallet = new Wallet($this->conexion);
            $referencia = 'PEDIDO-' . $pedido_id;
            $resultado_pago = $wallet->procesarPago($usuario_id, $total, $referencia);
            
            if(isset($resultado_pago['error'])) {
                throw new Exception($resultado_pago['error']);
            }
            
            // 4. Crear entregas automáticas
            $entrega = new Entrega($this->conexion);
            
            foreach($carrito as $producto_id => $item) {
                $resultado_entrega = $entrega->crear($pedido_id, $producto_id, $usuario_id);
                
                if(isset($resultado_entrega['error'])) {
                    throw new Exception($resultado_entrega['error']);
                }
            }
            
            // 5. Actualizar estado del pedido
            $this->actualizarEstado($pedido_id, 'completado');
            
            // 6. Vaciar carrito
            Carrito::vaciar();
            
            // Confirmar transacción
            $this->conexion->commit();
            return ['success' => 'Pedido procesado correctamente', 'id' => $pedido_id];
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conexion->rollback();
            return ['error' => $e->getMessage()];
        }
    }
}
?>