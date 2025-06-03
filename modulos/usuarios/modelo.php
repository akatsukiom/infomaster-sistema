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
    
    // Actualizar perfil
    public function actualizarPerfil($usuario_id, $nombre) {
        $usuario_id = (int)$usuario_id;
        $nombre = limpiarDato($nombre);
        
        $sql = "UPDATE usuarios SET nombre = '$nombre' WHERE id = $usuario_id";
        
        if($this->conexion->query($sql)) {
            return ['success' => 'Perfil actualizado correctamente'];
        } else {
            return ['error' => 'Error al actualizar perfil: ' . $this->conexion->error];
        }
    }
    
    // Cambiar contraseña
    public function cambiarPassword($usuario_id, $current_password, $new_password) {
        $usuario_id = (int)$usuario_id;
        
        // Verificar contraseña actual
        $sql = "SELECT password FROM usuarios WHERE id = $usuario_id";
        $resultado = $this->conexion->query($sql);
        
        if($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            if(password_verify($current_password, $usuario['password'])) {
                // Encriptar nueva contraseña
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Actualizar contraseña
                $update = "UPDATE usuarios SET password = '$password_hash' WHERE id = $usuario_id";
                
                if($this->conexion->query($update)) {
                    return ['success' => 'Contraseña actualizada correctamente'];
                } else {
                    return ['error' => 'Error al actualizar contraseña: ' . $this->conexion->error];
                }
            } else {
                return ['error' => 'La contraseña actual es incorrecta'];
            }
        } else {
            return ['error' => 'Usuario no encontrado'];
        }
    }
    
    /**
     * Procesa la imagen de avatar subida por el usuario
     * @param int $usuario_id ID del usuario
     * @param array $file Array $_FILES con los datos de la imagen
     * @return array Resultado de la operación
     */
    public function procesarAvatar($usuario_id, $file) {
        // Validar archivo
        if (!isset($file['avatar']) || $file['avatar']['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Error al subir la imagen. Intenta nuevamente.'];
        }
        
        // Verificar tamaño (máximo 2MB)
        if ($file['avatar']['size'] > 2 * 1024 * 1024) {
            return ['error' => 'La imagen es demasiado grande. Máximo 2MB.'];
        }
        
        // Verificar tipo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['avatar']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return ['error' => 'Tipo de archivo no permitido. Solo JPG, PNG o GIF.'];
        }
        
        // Crear directorio si no existe
        $upload_dir = __DIR__ . '/../../img/avatars/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generar nombre único
        $filename = 'avatar_' . $usuario_id . '_' . time() . '.' . pathinfo($file['avatar']['name'], PATHINFO_EXTENSION);
        $filepath = $upload_dir . $filename;
        
        // Mover archivo
        if (!move_uploaded_file($file['avatar']['tmp_name'], $filepath)) {
            return ['error' => 'Error al guardar la imagen. Intenta nuevamente.'];
        }
        
        // Procesar imagen (redimensionar si es necesario)
        $this->redimensionarImagen($filepath, 300);
        
        // Actualizar en base de datos
        $ruta_bd = 'img/avatars/' . $filename;
        
        // Eliminar avatar anterior si existe
        $this->eliminarAvatarAnterior($usuario_id);
        
        // Guardar en base de datos
        $stmt = $this->conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
        $stmt->bind_param('si', $ruta_bd, $usuario_id);
        
        if ($stmt->execute()) {
            return ['success' => 'Imagen de perfil actualizada correctamente.', 'avatar_path' => $ruta_bd];
        } else {
            return ['error' => 'Error al actualizar la imagen en la base de datos.'];
        }
    }
    
    /**
     * Redimensiona una imagen si excede las dimensiones especificadas
     * @param string $filepath Ruta completa del archivo
     * @param int $max_dimension Dimensión máxima (ancho o alto)
     */
    private function redimensionarImagen($filepath, $max_dimension = 300) {
        list($width, $height, $type) = getimagesize($filepath);
        
        // Solo redimensionar si excede la dimensión máxima
        if ($width <= $max_dimension && $height <= $max_dimension) {
            return;
        }
        
        // Calcular nuevas dimensiones manteniendo proporción
        if ($width > $height) {
            $new_width = $max_dimension;
            $new_height = intval($height * $max_dimension / $width);
        } else {
            $new_height = $max_dimension;
            $new_width = intval($width * $max_dimension / $height);
        }
        
        // Crear imagen desde archivo original
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return; // Tipo no soportado
        }
        
        // Crear imagen destino
        $destination = imagecreatetruecolor($new_width, $new_height);
        
        // Preservar transparencia para PNG y GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Redimensionar
        imagecopyresampled(
            $destination, $source,
            0, 0, 0, 0,
            $new_width, $new_height, $width, $height
        );
        
        // Guardar imagen redimensionada
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filepath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filepath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filepath);
                break;
        }
        
        // Liberar memoria
        imagedestroy($source);
        imagedestroy($destination);
    }
    
    /**
     * Elimina el avatar anterior del usuario
     * @param int $usuario_id ID del usuario
     */
    private function eliminarAvatarAnterior($usuario_id) {
        // Obtener ruta actual
        $stmt = $this->conexion->prepare("SELECT avatar FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['avatar'])) {
                $filepath = __DIR__ . '/../../' . $row['avatar'];
                if (file_exists($filepath) && is_file($filepath)) {
                    unlink($filepath);
                }
            }
        }
    }
    
    /**
     * Obtiene la ruta del avatar del usuario
     * @param int $usuario_id ID del usuario
     * @return string Ruta del avatar o null si no tiene
     */
    public function obtenerAvatar($usuario_id) {
        $stmt = $this->conexion->prepare("SELECT avatar FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['avatar'];
        }
        
        return null;
    }
}
?>