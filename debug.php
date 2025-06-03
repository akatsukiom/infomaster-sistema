<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ACCESO_PERMITIDO', true);

try {
    echo "Cargando config... ";
    require_once 'includes/config.php';
    echo "✓<br>";
    
    echo "Cargando funciones... ";
    require_once 'includes/funciones.php';
    echo "✓<br>";
    
    echo "Cargando modelo de usuarios... ";
    require_once 'modulos/usuarios/modelo.php';
    echo "✓<br>";
    
    echo "Verificando tabla usuarios... ";
    $result = $conexion->query("SHOW TABLES LIKE 'usuarios'");
    if ($result->num_rows > 0) {
        echo "La tabla existe ✓<br>";
        
        $result = $conexion->query("DESCRIBE usuarios");
        echo "Estructura de la tabla:<br>";
        while ($row = $result->fetch_assoc()) {
            echo " - " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    } else {
        echo "⚠️ La tabla no existe<br>";
    }
    
    // Intentar crear un objeto Usuario
    echo "Creando objeto Usuario... ";
    $usuario = new Usuario($conexion);
    echo "✓<br>";
    
    // Todo funcionó correctamente
    echo "<br>🟢 Todos los componentes necesarios para el registro funcionan correctamente.";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>