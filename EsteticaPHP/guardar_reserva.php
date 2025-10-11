<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_reserva.php
session_start();
include 'conexEstetica.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    $conexion = conectarDB();

    // Verificar disponibilidad
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];
    
    $query_verificar = "SELECT COUNT(*) as total FROM historial 
                       WHERE fecha_realizacion = ? AND id_negocio = 1";
    
    $stmt_verificar = $conexion->prepare($query_verificar);
    $stmt_verificar->bind_param('s', $fecha_realizacion);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    $row = $result_verificar->fetch_assoc();
    
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

    $id_negocio =2; // ID fijo para Juliette

    $stmt->bind_param(
        'issiiiis', 
        $_SESSION['usuario_id'],
        $_SESSION['nombre'],
        $_SESSION['apellido'],
        $servicio['categoria_id'],
        $datos['servicio_id'],
        $id_negocio,
        $servicio['precio'],
        $fecha_realizacion
    );

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