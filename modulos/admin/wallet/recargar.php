<?php
// modulos/admin/wallet/recargar.php

// Inclusión segura
define('ACCESO_PERMITIDO', true);

// Cargar config y funciones - ajustamos las rutas para que sean relativas correctamente
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/../../usuarios/modelo.php';
require_once __DIR__ . '/../../wallet/modelo.php';

// Verificar si es administrador
if (!estaLogueado() || !esAdmin()) {
    mostrarMensaje('Acceso restringido', 'error');
    redireccionar('login');
    exit;
}

// Inicializar variables
$errores = [];
$exito = null;
$usuarios = [];
$usuario_seleccionado = null;

// Obtener lista de usuarios para el select
$sql = "SELECT id, nombre, email, saldo FROM usuarios ORDER BY nombre";
$resultado = $conexion->query($sql);
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}

// Procesar formulario de recarga
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
    $referencia = isset($_POST['referencia']) ? limpiarDato($_POST['referencia']) : 'Admin-'.time();
    
    // Validar datos
    if ($usuario_id <= 0) {
        $errores[] = "Selecciona un usuario válido";
    }
    
    if ($monto <= 0) {
        $errores[] = "El monto debe ser mayor que cero";
    }
    
    // Si no hay errores, procesar la recarga
    if (empty($errores)) {
        // Obtener usuario seleccionado para mostrar luego
        foreach ($usuarios as $u) {
            if ($u['id'] == $usuario_id) {
                $usuario_seleccionado = $u;
                break;
            }
        }
        
        // Realizar la recarga
        $wallet = new Wallet($conexion);
        $resultado = $wallet->recargar($usuario_id, $monto, $referencia);
        
        if (isset($resultado['success'])) {
            $exito = "Recarga de " . MONEDA . number_format($monto, 2) . " realizada con éxito para " . 
                     ($usuario_seleccionado ? $usuario_seleccionado['nombre'] : "el usuario #".$usuario_id);
        } else {
            $errores[] = $resultado['error'] ?? 'Error al procesar la recarga';
        }
    }
}

// Título e incluir header
$titulo = "Administrar Recargas";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1><?= $titulo ?></h1>
        <a href="<?= URL_SITIO ?>admin" class="btn btn-outline">← Volver al Panel</a>
    </div>
    
    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($exito): ?>
        <div class="alert alert-success">
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="admin-section">
        <div class="admin-form-container">
            <h2>Recargar Wallet de Usuario</h2>
            
            <form method="POST" action="" class="admin-form">
                <div class="form-group">
                    <label for="usuario_id">Usuario:</label>
                    <select name="usuario_id" id="usuario_id" class="form-control" required>
                        <option value="">-- Selecciona un usuario --</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>" <?= ($usuario_seleccionado && $usuario['id'] == $usuario_seleccionado['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>) - 
                                Saldo actual: <?= MONEDA . number_format($usuario['saldo'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="monto">Monto a recargar:</label>
                    <input type="number" id="monto" name="monto" class="form-control" min="1" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="referencia">Referencia (opcional):</label>
                    <input type="text" id="referencia" name="referencia" class="form-control" 
                          placeholder="Ej: Pago por WhatsApp, Pago por Clip, etc.">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Realizar recarga</button>
                </div>
            </form>
        </div>
        
        <!-- Últimas recargas realizadas -->
        <div class="admin-list-container">
            <h2>Últimas recargas realizadas</h2>
            
            <?php
            // Obtener últimas recargas
            $sql = "SELECT t.*, u.nombre, u.email 
                   FROM transacciones_wallet t
                   JOIN usuarios u ON t.usuario_id = u.id
                   WHERE t.tipo = 'recarga'
                   ORDER BY t.fecha DESC
                   LIMIT 10";
            $resultado = $conexion->query($sql);
            ?>
            
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($recarga = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($recarga['fecha'])) ?></td>
                                <td><?= htmlspecialchars($recarga['nombre']) ?> (<?= htmlspecialchars($recarga['email']) ?>)</td>
                                <td><?= MONEDA . number_format($recarga['monto'], 2) ?></td>
                                <td><?= htmlspecialchars($recarga['referencia']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay recargas registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .admin-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .admin-form-container,
    .admin-list-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-control {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    
    .alert-error {
        background-color: #ffebee;
        border-left: 4px solid #f44336;
        color: #b71c1c;
    }
    
    .alert-success {
        background-color: #e8f5e9;
        border-left: 4px solid #4caf50;
        color: #1b5e20;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .admin-table th {
        background-color: #f5f5f5;
        font-weight: 600;
    }
    
    @media (max-width: 992px) {
        .admin-section {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>