<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_historial.php
session_start();
include 'conexEstetica.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    // Intentar obtener los datos del usuario de la base de datos
    try {
        $conexion = conectarDB();
        $query_usuario = "SELECT nombre, apellido FROM usuarios WHERE id = ?";
        $stmt_usuario = $conexion->prepare($query_usuario);
        $stmt_usuario->bind_param('i', $_SESSION['usuario_id']);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        
        if ($usuario = $result_usuario->fetch_assoc()) {
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
        } else {
            throw new Exception('No se encontraron los datos del usuario');
        }
        
        $stmt_usuario->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener datos del usuario: ' . $e->getMessage()]);
        exit;
    }
}

// Verificar que ahora sí tenemos los datos
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    echo json_encode(['success' => false, 'message' => 'No se pudieron obtener el nombre y apellido del usuario']);
    exit;
}

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    $conexion = conectarDB();
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];

    // Verificar disponibilidad del horario
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
            'message' => 'Ya existe una reserva para esta fecha y hora.'
        ]);
        exit;
    }


   // Intentar obtener datos del servicio
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
    $id_negocio = 1; // ID fijo para Kore

    // Si no encontramos el servicio, buscamos en la tabla de combos
   if ($servicio) {
        // Es un servicio regular
        $id_servicio = $datos['servicio_id'];
        $id_combo = null;
        $id_categoria = $servicio['categoria_id'];
        $precio_total = $servicio['precio'];
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
        // Usa el id real de la categoría combos (ajusta este valor según tu base de datos)
        $id_categoria = 14; // <-- Cambia este valor por el id real de la categoría "Combos"
        $precio_total = $combo['precio'];
    }
    
    // Combinar fecha y hora
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];

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
        $_SESSION['nombre'],
        $_SESSION['apellido'],
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
    error_log('Error en guardar_historial.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>