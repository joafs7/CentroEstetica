<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_reserva.php
session_start();
include 'conexEstetica.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    // Verificar si tenemos nombre y apellido en la sesión
    if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
        // Intentar obtenerlos de la base de datos
        $conexion_temp = conectarDB();
        $query_usuario = "SELECT nombre, apellido FROM usuarios WHERE id = ?";
        $stmt_usuario = $conexion_temp->prepare($query_usuario);
        $stmt_usuario->bind_param('i', $_SESSION['usuario_id']);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        
        if ($usuario_data = $result_usuario->fetch_assoc()) {
            $_SESSION['nombre'] = $usuario_data['nombre'];
            $_SESSION['apellido'] = $usuario_data['apellido'];
        } else {
            throw new Exception('No se encontró información del usuario');
        }
        
        $stmt_usuario->close();
        $conexion_temp->close();
    }

    // Obtener datos del formulario
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    $conexion = conectarDB();
    $id_negocio = isset($datos['id_negocio']) ? $datos['id_negocio'] : 2; // Por defecto Juliette=2

    // Verificar disponibilidad
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];
    
    $query_verificar = "SELECT COUNT(*) as total FROM historial 
                       WHERE fecha_realizacion = ? AND id_negocio = ?";
    
    $stmt_verificar = $conexion->prepare($query_verificar);
    $stmt_verificar->bind_param('si', $fecha_realizacion, $id_negocio);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    $row = $result_verificar->fetch_assoc();
    $stmt_verificar->close();
    
    if ($row['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una reserva para esta fecha y hora. Por favor, seleccione otro horario.'
        ]);
        exit;
    }

    // Obtener datos del servicio
    $stmt_servicio = $conexion->prepare("SELECT precio, categoria_id FROM servicios WHERE id = ?");
    $stmt_servicio->bind_param('i', $datos['servicio_id']);
    $stmt_servicio->execute();
    $result = $stmt_servicio->get_result();
    $servicio = $result->fetch_assoc();
    $stmt_servicio->close();

    if (!$servicio) {
        throw new Exception('Servicio no encontrado');
    }

    // Calcular precio total (incluir retirado si existe)
    $precio_total = $servicio['precio'];
    if (isset($datos['retirado']) && $datos['retirado'] == 1) {
        $precio_total += 3000; // Precio fijo de retirado
    }

    // Insertar la reserva
    $query = "INSERT INTO historial (
    id_usuario,
    nombre,
    apellido,
    id_categoria,
    id_servicio,
    id_negocio,
    precio,
    fecha_realizacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($query);

if (!$stmt) {
    throw new Exception('Error preparando consulta: ' . $conexion->error);
}

// Calcular precio total (incluir retirado si existe)
$precio_total = $servicio['precio'];
if (isset($datos['retirado']) && $datos['retirado'] == 1) {
    $precio_total += 3000; // Precio fijo de retirado
}

$stmt->bind_param(
    'issiiiis', 
    $_SESSION['usuario_id'],
    $_SESSION['nombre'],
    $_SESSION['apellido'],
    $servicio['categoria_id'],
    $datos['servicio_id'],
    $id_negocio,
    $precio_total,
    $fecha_realizacion
); // Quité la coma extra y ajusté el tipo de parámetros a 8

if (!$stmt->execute()) {
    throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
}
    echo json_encode([
        'success' => true,
        'message' => 'Reserva guardada exitosamente'
    ]);

    $stmt->close();
    $conexion->close();

} catch (Exception $e) {
    error_log('Error en guardar_reserva.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>