<?php
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/funciones.php';

// Si ya está logueado, ir al perfil
if (estaLogueado()) {
    redireccionar('perfil');
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = limpiarDato($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // 1) Validar campos
    if (empty($email))    $errores[] = "El email es obligatorio";
    if (empty($password)) $errores[] = "La contraseña es obligatoria";
    
    // 2) Si no hay errores, buscar usuario
    if (empty($errores)) {
        $stmt = $conexion->prepare(
            "SELECT id, nombre, password, rol, saldo 
               FROM usuarios 
              WHERE email = ? 
              LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 1) {
            $usuario = $res->fetch_assoc();
            
            // 3) Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // 4) Guardar datos en la sesión, ¡incluyendo el rol!
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol']    = $usuario['rol'];  // <- Esto es clave
                $_SESSION['usuario_saldo']   = (float) $usuario['saldo'];  // <— Esto es clave
                
                // 5) Redirigir al panel o perfil
                redireccionar('perfil');
            } else {
                $errores[] = "Contraseña incorrecta";
            }
        } else {
            $errores[] = "No existe ningún usuario con ese email";
        }
        $stmt->close();
    }
}

// Incluir tu header y mostrar el form de login
$titulo = "Iniciar sesión";
include __DIR__ . '/../../includes/header.php';
?>

<style>
/* ===== CSS ESPECÍFICO PARA LOGIN ===== */

/* Corregir espaciado del header */
body {
    padding-top: 200px !important;
}

.container {
    margin-top: 3rem !important;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Contenedor principal del login */
.login-container {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(25px);
    border-radius: 24px;
    padding: 3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
}

.login-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    animation: rotateGradient 20s linear infinite;
}

@keyframes rotateGradient {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Título */
h1 {
    color: white;
    text-align: center;
    font-size: clamp(2rem, 5vw, 2.5rem);
    font-weight: 900;
    margin-bottom: 2rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    z-index: 1;
}

/* Errores */
.errores {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 1;
}

.errores p {
    color: #fca5a5;
    margin: 0.5rem 0;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* Formulario */
form {
    position: relative;
    z-index: 1;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    font-size: 1rem;
}

.form-group input {
    width: 100%;
    padding: 1.2rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: rgba(255, 255, 255, 0.6);
    font-weight: 400;
}

/* Botón de login */
.btn,
button[type="submit"] {
    width: 100%;
    padding: 1.2rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
    text-decoration: none;
    display: block;
    text-align: center;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.btn::before,
button[type="submit"]::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.btn:hover::before,
button[type="submit"]:hover::before {
    left: 100%;
}

.btn:hover,
button[type="submit"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

/* Enlaces adicionales */
.auth-links {
    text-align: center;
    margin-top: 2rem;
    position: relative;
    z-index: 1;
}

.auth-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.auth-links a:hover {
    color: white;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
    body {
        padding-top: 220px !important;
    }
    
    .container {
        margin-top: 2rem !important;
        padding: 0 1rem;
    }
    
    .login-container {
        padding: 2rem;
    }
    
    h1 {
        font-size: 2rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group input {
        padding: 1rem;
    }
    
    .btn,
    button[type="submit"] {
        padding: 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    body {
        padding-top: 240px !important;
    }
    
    .login-container {
        padding: 1.5rem;
        margin: 1rem;
    }
    
    h1 {
        font-size: 1.8rem;
    }
}
</style>

<div class="container">
    <div class="login-container">
        <h1>🔐 Iniciar sesión</h1>
        
        <?php if ($errores): ?>
            <div class="errores">
                <?php foreach ($errores as $e): ?>
                    <p>❌ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">📧 Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($email ?? '') ?>" 
                    placeholder="tu@email.com"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Tu contraseña"
                    required
                >
            </div>
            
            <button type="submit" class="btn">
                🚀 Iniciar sesión
            </button>
        </form>
        
        <div class="auth-links">
            <p style="color: rgba(255, 255, 255, 0.8); margin-top: 1.5rem;">
                ¿No tienes cuenta? 
                <a href="<?= URL_SITIO ?>modulos/usuarios/registro.php" style="color: #4ade80; font-weight: 700;">
                    👤 Regístrate aquí
                </a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>