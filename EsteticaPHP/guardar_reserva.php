<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_reserva.php
session_start();
include 'conexEstetica.php';

header('Content-Type: application/json');

// Verificar autenticación y datos de sesión necesarios
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
    // Obtener los datos enviados
    $jsonData = file_get_contents('php://input');
    $datos = json_decode($jsonData, true);
    
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    $conexion = conectarDB();

    // Obtener precio y categoría del servicio
    $stmt_servicio = $conexion->prepare("SELECT precio, categoria_id FROM servicios WHERE id = ?");
    if (!$stmt_servicio) {
        throw new Exception('Error preparando consulta de servicio: ' . $conexion->error);
    }
    
    $stmt_servicio->bind_param('i', $datos['servicio_id']);
    $stmt_servicio->execute();
    $result = $stmt_servicio->get_result();
    $servicio = $result->fetch_assoc();
    $stmt_servicio->close();

    if (!$servicio) {
        throw new Exception('Servicio no encontrado');
    }

    // Calcular precio total incluyendo retirado si está seleccionado
    $precio_total = $servicio['precio'] + ($datos['retirado'] ? 3000 : 0);

    // Combinar fecha y hora
    $fecha_realizacion = $datos['fecha'] . ' ' . $datos['hora'];

    // Preparar la consulta de inserción
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
        throw new Exception('Error preparando consulta de inserción: ' . $conexion->error);
    }

    $id_negocio = 2; // ID fijo para Juliette Nails

    // Debug: Imprimir datos antes de la inserción
    error_log("Datos a insertar: " . print_r([
        'usuario_id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'],
        'apellido' => $_SESSION['apellido'],
        'categoria' => $servicio['categoria_id'],
        'servicio_id' => $datos['servicio_id'],
        'negocio' => $id_negocio,
        'precio' => $precio_total,
        'fecha' => $fecha_realizacion
    ], true));

    $nombre = $_SESSION['nombre'];
    $apellido = $_SESSION['apellido'];

    $stmt->bind_param(
        'issiiiis', 
        $_SESSION['usuario_id'],
        $nombre,
        $apellido,
        $servicio['categoria_id'],
        $datos['servicio_id'],
        $id_negocio,
        $precio_total,
        $fecha_realizacion
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la inserción: ' . $stmt->error);
    }

    // Registrar el historial exitosamente
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