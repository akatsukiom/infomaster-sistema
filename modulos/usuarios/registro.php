<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - InfoMaster</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            color: #2c3e50;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-header p {
            color: #666;
            font-size: 1rem;
            font-weight: 400;
        }

        .errores {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #ff4757;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .errores p {
            margin: 5px 0;
            font-weight: 500;
        }

        .registro-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:focus + label,
        .form-group:focus-within label {
            color: #667eea;
        }

        .form-group small {
            color: #718096;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
            font-style: italic;
        }

        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-footer p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Efectos adicionales */
        .form-group input[type="password"] {
            position: relative;
        }

        .form-group input:valid {
            border-color: #48bb78;
        }

        .form-group input:invalid:not(:placeholder-shown) {
            border-color: #f56565;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .auth-container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            .auth-header h1 {
                font-size: 1.8rem;
            }
            
            .form-group input {
                padding: 12px 16px;
            }
            
            .btn {
                padding: 14px 28px;
                font-size: 1rem;
            }
        }

        /* Animaciones de carga */
        .form-group {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .btn { animation-delay: 0.5s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efectos de hover mejorados */
        .form-group {
            transition: transform 0.2s ease;
        }

        .form-group:hover {
            transform: translateY(-2px);
        }

        /* Indicador de fortaleza de contraseña */
        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { 
            background: linear-gradient(90deg, #ff6b6b, #feca57);
            width: 33%; 
        }
        .strength-medium { 
            background: linear-gradient(90deg, #feca57, #48bb78);
            width: 66%; 
        }
        .strength-strong { 
            background: linear-gradient(90deg, #48bb78, #38a169);
            width: 100%; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Crear una cuenta</h1>
                <p>Regístrate para acceder a nuestros productos digitales</p>
            </div>
            
            <!-- Ejemplo de errores (comentado para demo) -->
            <!--
            <div class="errores">
                <p>El email es obligatorio</p>
                <p>Las contraseñas no coinciden</p>
            </div>
            -->
            
            <form method="POST" action="" class="registro-form">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Crea una contraseña segura" required>
                    <small>Mínimo 6 caracteres</small>
                    <div class="password-strength">
                        <div class="password-strength-bar strength-weak"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Registrarse</button>
            </form>
            
            <div class="form-footer">
                <p>¿Ya tienes cuenta? <a href="#">Iniciar sesión</a></p>
            </div>
        </div>
    </div>

    <script>
        // Efecto de validación en tiempo real
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#48bb78';
                } else if (this.value !== '') {
                    this.style.borderColor = '#f56565';
                } else {
                    this.style.borderColor = '#e1e8ed';
                }
            });
        });

        // Indicador de fortaleza de contraseña
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.password-strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
    </script>
</body>
</html>