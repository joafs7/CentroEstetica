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

    // --- Lógica de Duración y Verificación de Disponibilidad ---
    $duracion_horas = isset($datos['duracion_horas']) ? intval($datos['duracion_horas']) : 1;
    if ($duracion_horas < 1) $duracion_horas = 1;

    $fecha_inicio = new DateTime($datos['fecha'] . ' ' . $datos['hora']);
    $horarios_a_verificar = [];
    for ($i = 0; $i < $duracion_horas; $i++) {
        $horarios_a_verificar[] = $fecha_inicio->format('Y-m-d H:i:s');
        $fecha_inicio->add(new DateInterval('PT1H'));
    }

    $placeholders = implode(',', array_fill(0, count($horarios_a_verificar), '?'));
    $query_verificar = "SELECT fecha_realizacion FROM historial WHERE fecha_realizacion IN ($placeholders) AND id_negocio = ?";
    $stmt_verificar = $conexion->prepare($query_verificar);

    $types = str_repeat('s', count($horarios_a_verificar)) . 'i';
    $params = array_merge($horarios_a_verificar, [$id_negocio]);
    $stmt_verificar->bind_param($types, ...$params);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows > 0) {
        $horarios_ocupados = [];
        while($row = $result_verificar->fetch_assoc()) {
            $horarios_ocupados[] = (new DateTime($row['fecha_realizacion']))->format('H:i');
        }
        throw new Exception('El horario de las ' . implode(', ', $horarios_ocupados) . ' ya está ocupado. Por favor, seleccione otro.');
    }
    $stmt_verificar->close();
    // --- Fin de la lógica de verificación ---

    // Obtener datos del servicio (solo para la reserva principal)
    $fecha_realizacion_principal = $horarios_a_verificar[0];

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

    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // Insertar la reserva principal
        $query_principal = "INSERT INTO historial (id_usuario, nombre, apellido, id_categoria, id_servicio, id_negocio, precio, fecha_realizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_principal = $conexion->prepare($query_principal);
        if (!$stmt_principal) throw new Exception('Error preparando consulta principal: ' . $conexion->error);

        $stmt_principal->bind_param('issiiiis', $_SESSION['usuario_id'], $_SESSION['nombre'], $_SESSION['apellido'], $servicio['categoria_id'], $datos['servicio_id'], $id_negocio, $precio_total, $fecha_realizacion_principal);

        if (!$stmt_principal->execute()) throw new Exception('Error al ejecutar la consulta principal: ' . $stmt_principal->error);

        $id_reserva_padre = $conexion->insert_id; // Obtener el ID de la reserva principal
        $stmt_principal->close();

        // Insertar bloques adicionales si la duración es > 1 hora
        if ($duracion_horas > 1) {
            $query_bloqueo = "INSERT INTO historial (id_usuario, nombre, apellido, id_categoria, id_servicio, id_negocio, precio, fecha_realizacion, id_reserva_padre) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)";
            $stmt_bloqueo = $conexion->prepare($query_bloqueo);
            if (!$stmt_bloqueo) throw new Exception('Error preparando consulta de bloqueo: ' . $conexion->error);

            for ($i = 1; $i < $duracion_horas; $i++) {
                $fecha_bloqueo = $horarios_a_verificar[$i];
                $stmt_bloqueo->bind_param('issiiiis', $_SESSION['usuario_id'], $_SESSION['nombre'], $_SESSION['apellido'], $servicio['categoria_id'], $datos['servicio_id'], $id_negocio, $fecha_bloqueo, $id_reserva_padre);
                if (!$stmt_bloqueo->execute()) throw new Exception('Error al insertar bloque de horario: ' . $stmt_bloqueo->error);
            }
            $stmt_bloqueo->close();
        }

        // Si todo fue bien, confirmar la transacción
        $conexion->commit();

        // --- INICIO: Lógica para crear notificación al admin ---
        try {
            // Obtener el nombre del servicio para el mensaje
            $stmt_servicio_nombre = $conexion->prepare("SELECT nombre FROM servicios WHERE id = ?");
            $stmt_servicio_nombre->bind_param('i', $datos['servicio_id']);
            $stmt_servicio_nombre->execute();
            $result_nombre = $stmt_servicio_nombre->get_result();
            $servicio_info = $result_nombre->fetch_assoc();
            $nombre_servicio_notif = $servicio_info['nombre'] ?? 'Servicio no encontrado';
            $stmt_servicio_nombre->close();

            // 1. Encontrar a los admins del negocio (id_negocio = 2 para Juliette)
            $query_admins = "SELECT id FROM usuarios WHERE tipo = 'admin' AND id_negocio_admin = ?";
            $stmt_admins = $conexion->prepare($query_admins);
            $stmt_admins->bind_param('i', $id_negocio);
            $stmt_admins->execute();
            $result_admins = $stmt_admins->get_result();

            // 2. Crear el mensaje de la notificación
            $nombre_cliente = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
            $fecha_reserva_obj = new DateTime($fecha_realizacion_principal);
            $mensaje = "Nueva reserva de {$nombre_cliente} para '{$nombre_servicio_notif}' el " . $fecha_reserva_obj->format('d/m/Y \a \l\a\s H:i');

            // 3. Insertar una notificación para cada admin
            $query_insert_notif = "INSERT INTO notificaciones (id_usuario_destino, id_negocio, mensaje) VALUES (?, ?, ?)";
            while ($admin = $result_admins->fetch_assoc()) {
                $stmt_notif = $conexion->prepare($query_insert_notif);
                $stmt_notif->bind_param('iis', $admin['id'], $id_negocio, $mensaje);
                $stmt_notif->execute();
            }
        } catch (Exception $e) {
            // Si falla la notificación, no detenemos el proceso principal. Solo lo registramos.
            error_log("Error al crear notificación en guardar_reserva.php: " . $e->getMessage());
        }
        // --- FIN: Lógica para crear notificación ---

        echo json_encode([
            'success' => true,
            'message' => 'Reserva guardada exitosamente'
        ]);

    } catch (Exception $e) {
        // Si algo falla, revertir todos los cambios
        $conexion->rollback();
        throw $e; // Re-lanzar la excepción para que sea capturada por el bloque catch exterior
    }
    $conexion->close();

} catch (Exception $e) {
    error_log('Error en guardar_reserva.php: ' . $e->getMessage());
    // Asegurarse de que la conexión se cierre si aún está abierta
    if (isset($conexion) && $conexion->ping()) {
        $conexion->close();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>