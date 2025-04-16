<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';

// Si ya está logueado, redirigir
if(estaLogueado()) {
    redireccionar('perfil.php');
}

$usuario = new Usuario($conexion);
$errores = [];

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDato($_POST['nombre'] ?? '');
    $email = limpiarDato($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if(empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    
    if(empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif(!validarEmail($email)) {
        $errores[] = "Email inválido";
    }
    
    if(empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    } elseif(strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    if($password != $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Si no hay errores, registrar
    if(empty($errores)) {
        $resultado = $usuario->registrar($nombre, $email, $password);
        
        if(isset($resultado['success'])) {
            mostrarMensaje($resultado['success'], 'success');
            // Auto login después de registro
            $usuario->login($email, $password);
            redireccionar('perfil.php');
        } else {
            $errores[] = $resultado['error'];
        }
    }
}

// Incluir header
$titulo = "Registro";
include '../../includes/header.php';
?>

<div class="container">
    <div class="registro-form">
        <h1>Crear una cuenta</h1>
        
        <?php if(!empty($errores)): ?>
            <div class="errores">
                <?php foreach($errores as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
        
        <p>¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>