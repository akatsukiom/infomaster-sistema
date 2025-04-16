<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once 'modelo.php';

// Si ya está logueado, redirigir
if(estaLogueado()) {
    // Redirigir a donde se indicó después del login, o al perfil por defecto
    $redirigir = isset($_SESSION['redirigir_despues_login']) ? $_SESSION['redirigir_despues_login'] : 'perfil.php';
    unset($_SESSION['redirigir_despues_login']);
    redireccionar($redirigir);
}

$usuario = new Usuario($conexion);
$errores = [];

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = limpiarDato($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $recordar = isset($_POST['recordar']) ? true : false;
    
    // Validaciones
    if(empty($email) || !validarEmail($email)) {
        $errores[] = "Por favor ingresa un email válido";
    }
    
    if(empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    }
    
    // Si no hay errores, intentar login
    if(empty($errores)) {
        $resultado = $usuario->login($email, $password);
        
        if(isset($resultado['success'])) {
            // Si seleccionó "recordar", guardar cookie
            if($recordar) {
                setcookie('email', $email, time() + (86400 * 30), "/"); // 30 días
            }
            
            mostrarMensaje($resultado['success'], 'success');
            
            // Redirigir a donde se indicó después del login, o al perfil por defecto
            $redirigir = isset($_SESSION['redirigir_despues_login']) ? $_SESSION['redirigir_despues_login'] : 'perfil.php';
            unset($_SESSION['redirigir_despues_login']);
            redireccionar($redirigir);
        } else {
            $errores[] = $resultado['error'];
        }
    }
}

// Obtener email de cookie si existe
$email_cookie = isset($_COOKIE['email']) ? $_COOKIE['email'] : '';

// Incluir header
$titulo = "Iniciar Sesión";
include '../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Iniciar Sesión</h1>
            <p>Accede a tu cuenta para comprar o administrar tus productos</p>
        </div>
        
        <?php if(!empty($errores)): ?>
            <div class="errores">
                <?php foreach($errores as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email_cookie; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="recordar" name="recordar" <?php echo $email_cookie ? 'checked' : ''; ?>>
                    <label for="recordar">Recordarme</label>
                </div>
                <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
        
        <div class="form-footer">
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate ahora</a></p>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>