<?php
define('ACCESO_PERMITIDO', true);
require_once '../../includes/config.php';
require_once '../../includes/funciones.php';
require_once '../usuarios/modelo.php';
require_once '../entregas/modelo.php';

// Verificar si es una petición AJAX
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // No es AJAX, redirigir
    header('Location: ../../index.php');
    exit;
}

// Verificar si el usuario está logueado
if(!estaLogueado()) {
    $response = [
        'success' => false,
        'message' => 'Debes iniciar sesión para realizar esta acción'
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Respuesta por defecto
$response = [
    'success' => false,
    'message' => 'Ocurrió un error al procesar la solicitud'
];

// Verificar acción y entrega
if(isset($_POST['id']) && isset($_POST['action'])) {
    $entrega_id = (int)$_POST['id'];
    $action = $_POST['action'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Instanciar modelo de entregas
    $entrega = new Entrega($conexion);
    
    // Verificar que la entrega existe y pertenece al usuario
    $info_entrega = $entrega->obtenerPorId($entrega_id);
    
    if($info_entrega && $info_entrega['usuario_id'] == $usuario_id) {
        switch($action) {
            case 'renovar':
                // Renovar entrega
                $resultado = $entrega->renovar($entrega_id);
                
                if(isset($resultado['success'])) {
                    $response = [
                        'success' => true,
                        'message' => 'Entrega renovada correctamente'
                    ];
                } else {
                    $response['message'] = $resultado['error'];
                }
                break;
                
            case 'reportar':
                // Reportar problema
                if(isset($_POST['descripcion']) && !empty($_POST['descripcion'])) {
                    $descripcion = $_POST['descripcion'];
                    
                    $resultado = $entrega->reportarProblema($entrega_id, $descripcion);
                    
                    if(isset($resultado['success'])) {
                        $response = [
                            'success' => true,
                            'message' => 'Reporte enviado correctamente. Nos pondremos en contacto contigo pronto.'
                        ];
                    } else {
                        $response['message'] = $resultado['error'];
                    }
                } else {
                    $response['message'] = 'Por favor proporciona una descripción del problema';
                }
                break;
                
            default:
                $response['message'] = 'Acción no válida';
        }
    } else {
        $response['message'] = 'Entrega no encontrada o no tienes permiso para esta acción';
    }
} else {
    $response['message'] = 'Datos incompletos';
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>