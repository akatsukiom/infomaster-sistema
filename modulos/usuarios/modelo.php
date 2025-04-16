<?php
// Evitar acceso directo
if(!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Usuario {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Registrar nuevo usuario
    public function registrar($nombre, $email, $password) {
        // Verificar si el email ya existe
        $email = limpiarDato($email);
        $verificar = $this->conexion->query("SELECT id FROM usuarios WHERE email = '$email'");
        
        if($verificar->num_rows > 0) {
            return ['error' => 'El email ya está registrado'];
        }
        
        // Encriptar contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $nombre = limpiarDato($nombre);
        
        // Insertar usuario
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES ('$nombre', '$email', '$password_hash')";
        
        if($this->conexion->query($sql)) {
            return ['success' => 'Usuario registrado correctamente', 'id' => $this->conexion->insert_id];
        } else {
            return ['error' => 'Error al registrar: ' . $this->conexion->error];
        }
    }
    
    // Iniciar sesión
    public function login($email, $password) {
        $email = limpiarDato($email);
        
        $sql = "SELECT id, nombre, email, password, saldo FROM usuarios WHERE email = '$email'";
        $resultado = $this->conexion->query($sql);
        
        if($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            if(password_verify($password, $usuario['password'])) {
                // Actualizar último acceso
                $this->conexion->query("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = {$usuario['id']}");
                
                // Guardar en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_saldo'] = $usuario['saldo'];
                
                return ['success' => 'Sesión iniciada correctamente'];
            } else {
                return ['error' => 'Contraseña incorrecta'];
            }
        } else {
            return ['error' => 'Usuario no encontrado'];
        }
    }
    
    // Cerrar sesión
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => 'Sesión cerrada correctamente'];
    }
    
    // Obtener saldo
    public function obtenerSaldo($usuario_id) {
        $usuario_id = (int)$usuario_id;
        $sql = "SELECT saldo FROM usuarios WHERE id = $usuario_id";
        $resultado = $this->conexion->query($sql);
        
        if($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            return $usuario['saldo'];
        }
        return 0;
    }
    
    // Actualizar saldo
    public function actualizarSaldo($usuario_id, $monto) {
        $usuario_id = (int)$usuario_id;
        $monto = (float)$monto;
        
        $sql = "UPDATE usuarios SET saldo = saldo + $monto WHERE id = $usuario_id";
        
        if($this->conexion->query($sql)) {
            // Actualizar saldo en sesión
            $_SESSION['usuario_saldo'] += $monto;
            return true;
        }
        return false;
    }
}
?>