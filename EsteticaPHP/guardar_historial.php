<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_historial.php
session_start();
include 'conexEstetica.php';

// Activar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Debug de la sesión
error_log("Sesión al inicio: " . print_r($_SESSION, true));

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    // PRIMERO: Asignar valores directos a la sesión
    // Esto es un parche temporal para este error específico
    $_SESSION['nombre'] = isset($_SESSION['nombre']) && !empty($_SESSION['nombre']) ? 
                          $_SESSION['nombre'] : 'Usuario Temporal';
    $_SESSION['apellido'] = isset($_SESSION['apellido']) && !empty($_SESSION['apellido']) ? 
                           $_SESSION['apellido'] : 'Apellido Temporal';

    error_log("Sesión después de asignar valores por defecto: " . print_r($_SESSION, true));

    // SEGUNDO: Intentar obtener datos reales del usuario
    $conexion = conectarDB();
    $query_usuario = "SELECT nombre, apellido FROM usuarios WHERE id = ?";
    $stmt_usuario = $conexion->prepare($query_usuario);
    
    if (!$stmt_usuario) {
        throw new Exception('Error al preparar consulta de usuario: ' . $conexion->error);
    }
    
    $stmt_usuario->bind_param('i', $_SESSION['usuario_id']);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    
    if ($usuario = $result_usuario->fetch_assoc()) {
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        error_log("Usuario encontrado en DB: " . print_r($usuario, true));
    } else {
        error_log("Usuario NO encontrado en DB con ID: " . $_SESSION['usuario_id']);
    }
    
    $stmt_usuario->close();
    
    // TERCERO: Obtener datos de la reserva
    $datos = json_decode(file_get_contents('php://input'), true);
    error_log("Datos recibidos: " . print_r($datos, true));
    
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    // Combinar fecha y hora
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];
    error_log("Fecha de reserva: " . $fecha_realizacion);

    // Verificar disponibilidad
    $query_verificar = "SELECT COUNT(*) as total FROM historial 
                       WHERE fecha_realizacion = ? AND id_negocio = 1";
    $stmt_verificar = $conexion->prepare($query_verificar);
    $stmt_verificar->bind_param('s', $fecha_realizacion);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    $row = $result_verificar->fetch_assoc();
    $stmt_verificar->close();
    
    if ($row['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una reserva para esta fecha y hora.'
        ]);
        exit;
    }

    // Verificar si es servicio o combo
    $stmt_servicio = $conexion->prepare("SELECT precio, categoria_id FROM servicios WHERE id = ?");
    $stmt_servicio->bind_param('i', $datos['servicio_id']);
    $stmt_servicio->execute();
    $result = $stmt_servicio->get_result();
    $servicio = $result->fetch_assoc();
    $stmt_servicio->close();

    $id_servicio = null;
    $id_combo = null;
    $id_categoria = null;
    $precio_total = null;
    $id_negocio = 1; // ID fijo para

    if ($servicio) {
        $id_servicio = $datos['servicio_id'];
        $id_combo = null;
        $id_categoria = $servicio['categoria_id'];
        $precio_total = $servicio['precio'];
        error_log("Es un servicio: " . print_r($servicio, true));
    } else {
        // Es un combo
        $stmt_combo = $conexion->prepare("SELECT precio FROM combos WHERE id = ?");
        $stmt_combo->bind_param('i', $datos['servicio_id']);
        $stmt_combo->execute();
        $result_combo = $stmt_combo->get_result();
        $combo = $result_combo->fetch_assoc();
        $stmt_combo->close();

        if (!$combo) {
            throw new Exception('Servicio o combo no encontrado');
        }

        $id_servicio = null;
        $id_combo = $datos['servicio_id'];
        $id_categoria = 14; // ID para categoría "Combos"
        $precio_total = $combo['precio'];
        error_log("Es un combo: " . print_r($combo, true));
    }

    // Verificación FINAL antes de insertar
    $nombre_usuario = $_SESSION['nombre'] ?? 'Usuario Desconocido';
    $apellido_usuario = $_SESSION['apellido'] ?? 'Apellido Desconocido';
    
    error_log("Valores finales para inserción: Nombre: $nombre_usuario, Apellido: $apellido_usuario");
    
    // Preparar la consulta de inserción
    $query = "INSERT INTO historial (
        id_usuario,
        nombre,
        apellido,
        id_categoria,
        id_servicio,
        id_combo,
        id_negocio,
        precio,
        fecha_realizacion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception('Error preparando consulta de inserción: ' . $conexion->error);
    }

    $stmt->bind_param(
        'issiiiiss',
        $_SESSION['usuario_id'],
        $nombre_usuario,
        $apellido_usuario,
        $id_categoria,
        $id_servicio,
        $id_combo,
        $id_negocio,
        $precio_total,
        $fecha_realizacion
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la inserción: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Reserva guardada exitosamente'
    ]);

    $stmt->close();
    $conexion->close();

} catch (Exception $e) {
    error_log('Error en guardar_historial.php: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>