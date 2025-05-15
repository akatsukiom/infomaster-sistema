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

// Obtener todos los usuarios
$sql = "SELECT id, nombre, email, saldo, fecha_registro, ultimo_acceso FROM usuarios ORDER BY id DESC";
$resultado = $conexion->query($sql);
$usuarios = [];

while ($fila = $resultado->fetch_assoc()) {
    $usuarios[] = $fila;
}

$titulo = "Listado de Usuarios";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="container">
    <h1>Gestión de Usuarios</h1>
    <p><a href="<?= URL_SITIO ?>admin" class="btn btn-outline">← Volver al Panel</a></p>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Saldo</th>
                <th>Fecha Registro</th>
                <th>Último Acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= MONEDA . number_format($u['saldo'], 2) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($u['fecha_registro'])) ?></td>
                    <td><?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca' ?></td>
                    <td>
                        <a href="<?= URL_SITIO ?>admin/usuarios/editar?id=<?= $u['id'] ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>