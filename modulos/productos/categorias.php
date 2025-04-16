<?php
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';

// Obtener todas las categorías
$sql = "SELECT c.*, COUNT(p.id) as total_productos 
        FROM categorias c 
        LEFT JOIN productos p ON c.id = p.categoria_id 
        GROUP BY c.id 
        ORDER BY c.nombre";
$resultado = $conexion->query($sql);
$categorias = [];

while($fila = $resultado->fetch_assoc()) {
    $categorias[] = $fila;
}

// Incluir header
$titulo = "Categorías";
include 'includes/header.php';
?>

<div class="container">
    <div class="categorias-container">
        <h1>Explora nuestras categorías</h1>
        
        <?php if(empty($categorias)): ?>
            <div class="no-categorias">
                <p>No hay categorías disponibles en este momento.</p>
                <a href="productos.php" class="btn">Ver todos los productos</a>
            </div>
        <?php else: ?>
            <div class="categorias-grid">
                <?php foreach($categorias as $categoria): ?>
                    <div class="categoria-card">
                        <div class="categoria-imagen">
                            <img src="<?php echo $categoria['imagen'] ?: 'img/categoria-default.jpg'; ?>" alt="<?php echo $categoria['nombre']; ?>">
                        </div>
                        <div class="categoria-info">
                            <h2><?php echo $categoria['nombre']; ?></h2>
                            <p><?php echo $categoria['descripcion']; ?></p>
                            <span class="categoria-cantidad"><?php echo $categoria['total_productos']; ?> productos</span>
                            <a href="productos.php?categoria=<?php echo $categoria['id']; ?>" class="btn">Ver productos</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>