<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Wallet {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Registrar transacción
    public function registrarTransaccion($usuario_id, $tipo, $monto, $referencia = '') {
        $usuario_id = (int)$usuario_id;
        $tipo = limpiarDato($tipo);
        $monto = (float)$monto;
        $referencia = limpiarDato($referencia);
        
        $sql = "INSERT INTO transacciones_wallet (usuario_id, tipo, monto, referencia) 
                VALUES ($usuario_id, '$tipo', $monto, '$referencia')";
        
        if($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        return false;
    }
    
    // Recargar wallet
    public function recargar($usuario_id, $monto, $referencia = '') {
        $usuario_id = (int)$usuario_id;
        $monto = (float)$monto;
        
        // Validar que el monto sea positivo
        if($monto <= 0) {
            return ['error' => 'El monto debe ser mayor que cero'];
        }
        
        // Comenzar transacción
        $this->conexion->begin_transaction();
        
        try {
            // Actualizar saldo del usuario
            $usuario = new Usuario($this->conexion);
            if(!$usuario->actualizarSaldo($usuario_id, $monto)) {
                throw new Exception('Error al actualizar el saldo');
            }
            
            // Registrar transacción
            $transaccion_id = $this->registrarTransaccion($usuario_id, 'recarga', $monto, $referencia);
            if(!$transaccion_id) {
                throw new Exception('Error al registrar la transacción');
            }
            
            // Confirmar transacción
            $this->conexion->commit();
            return ['success' => 'Recarga realizada correctamente', 'id' => $transaccion_id];
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conexion->rollback();
            return ['error' => $e->getMessage()];
        }
    }
    
    // Procesar pago con wallet
    public function procesarPago($usuario_id, $monto, $referencia = '') {
        $usuario_id = (int)$usuario_id;
        $monto = (float)$monto;
        
        // Validar que el monto sea positivo
        if($monto <= 0) {
            return ['error' => 'El monto debe ser mayor que cero'];
        }
        
        // Verificar saldo disponible
        $usuario = new Usuario($this->conexion);
        $saldo = $usuario->obtenerSaldo($usuario_id);
        
        if($saldo < $monto) {
            return ['error' => 'Saldo insuficiente'];
        }
        
        // Comenzar transacción
        $this->conexion->begin_transaction();
        
        try {
            // Actualizar saldo del usuario (monto negativo para restar)
            if(!$usuario->actualizarSaldo($usuario_id, -$monto)) {
                throw new Exception('Error al actualizar el saldo');
            }
            
            // Registrar transacción
            $transaccion_id = $this->registrarTransaccion($usuario_id, 'compra', $monto, $referencia);
            if(!$transaccion_id) {
                throw new Exception('Error al registrar la transacción');
            }
            
            // Confirmar transacción
            $this->conexion->commit();
            return ['success' => 'Pago realizado correctamente', 'id' => $transaccion_id];
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conexion->rollback();
            return ['error' => $e->getMessage()];
        }
    }
    
    // Obtener historial de transacciones
    public function obtenerHistorial($usuario_id, $limite = 10) {
        $usuario_id = (int)$usuario_id;
        $limite = (int)$limite;
        
        $sql = "SELECT id, tipo, monto, referencia, fecha 
                FROM transacciones_wallet 
                WHERE usuario_id = $usuario_id 
                ORDER BY fecha DESC 
                LIMIT $limite";
        
        $resultado = $this->conexion->query($sql);
        $transacciones = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $transacciones[] = $fila;
        }
        
        return $transacciones;
    }
    
    // Obtener historial filtrado
    public function obtenerHistorialFiltrado($usuario_id, $tipo = '', $desde = '', $hasta = '', $limite = 100) {
        $usuario_id = (int)$usuario_id;
        $limite = (int)$limite;
        
        $where = "WHERE usuario_id = $usuario_id";
        
        // Filtrar por tipo
        if(!empty($tipo)) {
            $tipo = limpiarDato($tipo);
            $where .= " AND tipo = '$tipo'";
        }
        
        // Filtrar por fechas
        if(!empty($desde)) {
            $desde = limpiarDato($desde);
            $where .= " AND fecha >= '$desde 00:00:00'";
        }
        
        if(!empty($hasta)) {
            $hasta = limpiarDato($hasta);
            $where .= " AND fecha <= '$hasta 23:59:59'";
        }
        
        $sql = "SELECT id, tipo, monto, referencia, fecha 
                FROM transacciones_wallet 
                $where 
                ORDER BY fecha DESC 
                LIMIT $limite";
        
        $resultado = $this->conexion->query($sql);
        $transacciones = [];
        
        while($fila = $resultado->fetch_assoc()) {
            $transacciones[] = $fila;
        }
        
        return $transacciones;
    }
    
    // Obtener total de recargas
    public function obtenerTotalRecargas($usuario_id) {
        $usuario_id = (int)$usuario_id;
        
        $sql = "SELECT SUM(monto) as total 
                FROM transacciones_wallet 
                WHERE usuario_id = $usuario_id AND tipo = 'recarga'";
        
        $resultado = $this->conexion->query($sql);
        $row = $resultado->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
    // Obtener total de compras
    public function obtenerTotalCompras($usuario_id) {
        $usuario_id = (int)$usuario_id;
        
        $sql = "SELECT SUM(monto) as total 
                FROM transacciones_wallet 
                WHERE usuario_id = $usuario_id AND tipo = 'compra'";
        
        $resultado = $this->conexion->query($sql);
        $row = $resultado->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
    // Obtener estadísticas del último mes
    public function obtenerEstadisticasMes($usuario_id) {
        $usuario_id = (int)$usuario_id;
        $primer_dia_mes = date('Y-m-01');
        $ultimo_dia_mes = date('Y-m-t');
        
        $sql = "SELECT 
                    COUNT(*) as total_transacciones,
                    SUM(CASE WHEN tipo = 'recarga' THEN monto ELSE 0 END) as total_recargas,
                    SUM(CASE WHEN tipo = 'compra' THEN monto ELSE 0 END) as total_compras
                FROM transacciones_wallet 
                WHERE usuario_id = $usuario_id 
                AND fecha BETWEEN '$primer_dia_mes 00:00:00' AND '$ultimo_dia_mes 23:59:59'";
        
        $resultado = $this->conexion->query($sql);
        $row = $resultado->fetch_assoc();
        
        return [
            'total_transacciones' => $row['total_transacciones'] ?? 0,
            'total_recargas' => $row['total_recargas'] ?? 0,
            'total_compras' => $row['total_compras'] ?? 0
        ];
    }
}
?>