<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once 'modelo.php';

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    redireccionar('modulos/usuarios/login.php');
}

$wallet = new Wallet($conexion);
$errores = [];
$exito = null;

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $monto = (float)($_POST['monto'] ?? 0);
    $metodo = limpiarDato($_POST['metodo'] ?? '');
    
    // Validaciones
    if($monto <= 0) {
        $errores[] = "El monto debe ser mayor que cero";
    }
    
    if(empty($metodo)) {
        $errores[] = "Seleccione un método de pago";
    }
    
    // Si no hay errores, procesar recarga
    if(empty($errores)) {
        // En un entorno real aquí se procesaría el pago con el método seleccionado
        // Para este ejemplo, simularemos una recarga exitosa
        
        $referencia = 'RECARGA-' . time() . '-' . rand(1000, 9999);
        $resultado = $wallet->recargar($_SESSION['usuario_id'], $monto, $referencia);
        
        if(isset($resultado['success'])) {
            $exito = $resultado['success'];
        } else {
            $errores[] = $resultado['error'];
        }
    }
}

// Incluir header
$titulo = "Recargar Wallet";
include '../../includes/header.php';
?>

<div class="container">
    <div class="wallet-recargar">
        <h1>Recargar Wallet</h1>
        
        <div class="saldo-actual">
            <p>Tu saldo actual: <strong><?php echo MONEDA . number_format($_SESSION['usuario_saldo'], 2); ?></strong></p>
        </div>
        
        <?php if(!empty($errores)): ?>
            <div class="errores">
                <?php foreach($errores as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if($exito): ?>
            <div class="exito">
                <p><?php echo $exito; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="monto">Monto a recargar</label>
                <input type="number" id="monto" name="monto" step="0.01" min="10" value="<?php echo $monto ?? 100; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Método de pago</label>
                <div class="metodos-pago">
                    <div class="metodo">
                        <input type="radio" id="metodo-deposito" name="metodo" value="deposito" checked>
                        <label for="metodo-deposito">Depósito bancario</label>
                    </div>
                    
                    <div class="metodo">
                        <input type="radio" id="metodo-transferencia" name="metodo" value="transferencia">
                        <label for="metodo-transferencia">Transferencia</label>
                    </div>
                    
                    <div class="metodo">
                        <input type="radio" id="metodo-paypal" name="metodo" value="paypal">
                        <label for="metodo-paypal">PayPal</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Recargar ahora</button>
        </form>
        
        <div class="instrucciones">
            <h3>Instrucciones de pago:</h3>
            <div class="instruccion-deposito">
                <h4>Depósito bancario</h4>
                <p>Realiza un depósito a la siguiente cuenta:</p>
                <p>Banco: BBVA</p>
                <p>Cuenta: 0123456789</p>
                <p>A nombre de: InfoMaster</p>
            </div>
            
            <div class="instruccion-transferencia">
                <h4>Transferencia</h4>
                <p>Realiza una transferencia a:</p>
                <p>CLABE: 012345678901234567</p>
                <p>A nombre de: InfoMaster</p>
            </div>
            
            <div class="instruccion-paypal">
                <h4>PayPal</h4>
                <p>Envía tu pago a la cuenta: pagos@infomaster.com.mx</p>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>