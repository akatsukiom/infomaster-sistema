<?php
if (!defined('ACCESO_PERMITIDO')) {
    define('ACCESO_PERMITIDO', true);
}
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';

// Verificar permisos
if (!estaLogueado() || !esAdmin()) {
    redireccionar('login');
}

// Obtener estadísticas de ventas
$sql_total = "SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado'";
$res_total = $conexion->query($sql_total);
$total_ventas = $res_total->fetch_assoc()['total'] ?? 0;

$sql_mes = "SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado' AND MONTH(fecha_pedido) = MONTH(CURRENT_DATE()) AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())";
$res_mes = $conexion->query($sql_mes);
$ventas_mes = $res_mes->fetch_assoc()['total'] ?? 0;

$sql_count = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'completado'";
$res_count = $conexion->query($sql_count);
$total_pedidos = $res_count->fetch_assoc()['total'] ?? 0;

// Obtener últimos pedidos
$sql_pedidos = "SELECT p.*, u.nombre as usuario_nombre 
                FROM pedidos p 
                JOIN usuarios u ON p.usuario_id = u.id 
                ORDER BY p.fecha_pedido DESC 
                LIMIT 10";
$resultado = $conexion->query($sql_pedidos);
$pedidos = [];

while ($fila = $resultado->fetch_assoc()) {
    $pedidos[] = $fila;
}

$titulo = "Reporte de Ventas";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container">
    <h1>Reporte de Ventas</h1>
    <p><a href="<?= URL_SITIO ?>admin" class="btn btn-outline">← Volver al Panel</a></p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total Ventas</div>
            <div class="stat-value"><?= MONEDA . number_format($total_ventas, 2) ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">Ventas del Mes</div>
            <div class="stat-value"><?= MONEDA . number_format($ventas_mes, 2) ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">Pedidos Completados</div>
            <div class="stat-value"><?= $total_pedidos ?></div>
        </div>
    </div>
    
    <h2>Últimos Pedidos</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($pedidos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['usuario_nombre']) ?></td>
                    <td><?= MONEDA . number_format($p['total'], 2) ?></td>
                    <td>
                        <span class="estado-<?= $p['estado'] ?>">
                            <?= ucfirst($p['estado']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
                    <td>
                        <a href="<?= URL_SITIO ?>admin/pedidos/ver?id=<?= $p['id'] ?>">Ver detalles</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>