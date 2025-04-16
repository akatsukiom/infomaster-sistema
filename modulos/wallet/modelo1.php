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
}
?>