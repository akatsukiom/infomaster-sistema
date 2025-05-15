<?php
/**
 * Búsqueda AJAX de productos para la interfaz de edición de menús
 */

// Evitar acceso directo o peticiones no AJAX
define('ACCESO_PERMITIDO', true);
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/funciones.php';
require_once __DIR__ . '/modelo.php';
require_once __DIR__ . '/modelo_productos.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso no autorizado']);
    exit;
}

// Verificar si es una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo no permitido');
}

// Configurar headers de respuesta
header('Content-Type: application/json');

// Obtener acción de la petición
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

// Instanciar modelos
$menuProductos = new MenuProductos($conexion);

// Procesar diferentes acciones
switch ($accion) {
    // Buscar productos
    case 'buscar_productos':
        $termino = isset($_GET['q']) ? trim($_GET['q']) : '';
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        
        if (strlen($termino) < 2) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'Ingresa al menos 2 caracteres para buscar'
            ]);
            exit;
        }
        
        $productos = $menuProductos->buscarProductos($termino, $limite);
        
        echo json_encode([
            'status' => 'success',
            'productos' => $productos,
            'total' => count($productos)
        ]);
        break;
        
    // Obtener producto por ID
    case 'obtener_producto':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'ID de producto no válido'
            ]);
            exit;
        }
        
        $producto = $menuProductos->obtenerProducto($id);
        
        if ($producto) {
            echo json_encode([
                'status' => 'success',
                'producto' => $producto
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Producto no encontrado'
            ]);
        }
        break;
        
    // Obtener categorías
    case 'obtener_categorias':
        $incluir_conteo = isset($_GET['conteo']) ? (bool)$_GET['conteo'] : true;
        
        $categorias = $menuProductos->obtenerCategorias($incluir_conteo);
        
        echo json_encode([
            'status' => 'success',
            'categorias' => $categorias,
            'total' => count($categorias)
        ]);
        break;
        
    // Obtener categoría por ID
    case 'obtener_categoria':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $incluir_conteo = isset($_GET['conteo']) ? (bool)$_GET['conteo'] : true;
        
        if ($id <= 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'ID de categoría no válido'
            ]);
            exit;
        }
        
        $categoria = $menuProductos->obtenerCategoria($id, $incluir_conteo);
        
        if ($categoria) {
            echo json_encode([
                'status' => 'success',
                'categoria' => $categoria
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Categoría no encontrada'
            ]);
        }
        break;
        
    // Contar productos en categoría
    case 'contar_productos_categoria':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode([
                'status' => 'error', 
                'mensaje' => 'ID de categoría no válido'
            ]);
            exit;
        }
        
        $total = $menuProductos->contarProductosPorCategoria($id);
        
        echo json_encode([
            'status' => 'success',
            'total' => $total
        ]);
        break;
        
    // Obtener tipos de menús inteligentes
    case 'tipos_menu_inteligente':
        $tipos = $menuProductos->obtenerTiposMenuInteligente();
        
        echo json_encode([
            'status' => 'success',
            'tipos' => $tipos
        ]);
        break;
        
    // Acción no reconocida
    default:
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Acción no válida'
        ]);
        break;
}