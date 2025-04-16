<?php
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';
require_once 'modulos/productos/modelo.php';
require_once 'modulos/carrito/modelo.php';

// Filtros
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busqueda = isset($_GET['busqueda']) ? limpiarDato($_GET['busqueda']) : '';
$orden = isset($_GET['orden']) ? limpiarDato($_GET['orden']) : 'nombre_asc';

// Obtener productos según filtros
$producto = new Producto($conexion);
$productos = $producto->obtenerTodos($categoria_id);

// Aplicar búsqueda si existe
if(!empty($busqueda)) {
    $productos = array_filter($productos, function($p) use ($busqueda) {
        return stripos($p['nombre'], $busqueda) !== false || 
               stripos($p['descripcion'], $busqueda) !== false;
    });
}

// Aplicar ordenamiento
usort($productos, function($a, $b) use ($orden) {
    switch($orden) {
        case 'precio_asc':
            return $a['precio'] - $b['precio'];
        case 'precio_desc':
            return $b['precio'] - $a['precio'];
        case 'nombre_desc':
            return strcmp($b['nombre'], $a['nombre']);
        case 'nombre_asc':
        default:
            return strcmp($a['nombre'], $b['nombre']);
    }
});

// Obtener categorías para el filtro
$sql = "SELECT * FROM categorias ORDER BY nombre";
$resultado_categorias = $conexion->query($sql);
$categorias = [];

while($fila = $resultado_categorias->fetch_assoc()) {
    $categorias[] = $fila;
}

// Incluir header
$titulo = "Productos";
include 'includes/header.php';
?>

<div class="container">
    <div class="productos-container">
        <h1>Nuestros Productos</h1>
        
        <div class="productos-grid">
            <div class="filtros-sidebar">
                <div class="filtros-header">
                    <h3>Filtros</h3>
                    <button class="btn-small btn-outline filtros-reset">Limpiar</button>
                </div>
                
                <form method="GET" action="" class="product-filters">
                    <div class="filtro-grupo">
                        <label for="busqueda">Buscar:</label>
                        <input type="text" id="busqueda" name="busqueda" value="<?php echo $busqueda; ?>" placeholder="Nombre o descripción">
                    </div>
                    
                    <div class="filtro-grupo">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria">
                            <option value="">Todas las categorías</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filtro-grupo">
                        <label for="orden">Ordenar por:</label>
                        <select id="orden" name="orden">
                            <option value="nombre_asc" <?php echo $orden == 'nombre_asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                            <option value="nombre_desc" <?php echo $orden == 'nombre_desc' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                            <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio (menor a mayor)</option>
                            <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio (mayor a menor)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Aplicar filtros</button>
                </form>
            </div>
            
            <div class="productos-lista">
                <?php if(empty($productos)): ?>
                    <div class="no-productos">
                        <p>No se encontraron productos que coincidan con tu búsqueda.</p>
                        <a href="productos.php" class="btn btn-outline">Ver todos los productos</a>
                    </div>
                <?php else: ?>
                    <div class="products">
                        <?php foreach($productos as $producto): ?>
                            <div class="product">
                                <img src="<?php echo $producto['imagen'] ?: 'img/producto-default.jpg'; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img">
                                <div class="product-info">
                                    <span class="product-category"><?php echo $producto['categoria']; ?></span>
                                    <h3 class="product-title"><?php echo $producto['nombre']; ?></h3>
                                    <p><?php echo substr($producto['descripcion'], 0, 60) . (strlen($producto['descripcion']) > 60 ? '...' : ''); ?></p>
                                    <div class="product-price"><?php echo MONEDA . number_format($producto['precio'], 2); ?></div>
                                    <div class="product-actions">
                                        <a href="modulos/productos/detalle.php?id=<?php echo $producto['id']; ?>" class="btn">Ver detalles</a>
                                        <a href="modulos/carrito/agregar.php?id=<?php echo $producto['id']; ?>&redirigir=../../productos.php" class="btn btn-secondary">Comprar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>