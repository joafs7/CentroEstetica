<?php
// filepath: c:\xampp\htdocs\estetica-Nails16-9\EsteticaPHP\guardar_historial.php
session_start();
include 'conexEstetica.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    // Intentar obtener los datos del usuario de la base de datos
    try {
        if (isset($_SESSION['usuario_id'])) {
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
            $conexion->close();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener datos del usuario: ' . $e->getMessage()]);
        exit;
    }
}

// Verificar que ahora sí tenemos los datos
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}
if (empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    echo json_encode(['success' => false, 'message' => 'No se pudieron obtener el nombre y apellido del usuario']);
    exit;
}

$conexion = null; // Inicializar la variable de conexión
try {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!$datos) {
        throw new Exception('Datos no recibidos correctamente');
    }

    // Combinar fecha y hora
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
    $stmt_verificar->close();

    if ($row['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una reserva para esta fecha y hora.'
        ]);
        exit;
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    // Preparar la consulta de inserción
    $query_insert = "INSERT INTO historial (
        id_usuario,
        nombre,
        apellido,
        id_categoria,
        id_servicio,
        id_combo,
        id_negocio,
        id_cita,
        id_venta,
        precio,
        fecha_realizacion,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt = $conexion->prepare($query_insert);
    if (!$stmt) {
         throw new Exception('Error preparando consulta de inserción: ' . $conexion->error);
     }

    if (!isset($datos['servicios']) || !is_array($datos['servicios']) || empty($datos['servicios'])) {
        throw new Exception('La lista de servicios es inválida.');
    }

    // Como ahora solo se permite un servicio, tomamos el primero del array
    $servicio_item = $datos['servicios'][0];
    $servicio_id = $servicio_item['id'];
    $is_combo = $servicio_item['is_combo'];
    $duracion = $servicio_item['duracion']; // Obtenemos la duración

    $id_servicio = null;
    $id_combo = null;
    $id_categoria = null;
    $precio = null;
    $nombre_servicio_guardar = '';
    $id_negocio = 1; // ID fijo para Kore

    if ($is_combo) {
        // Es un combo
        $stmt_combo = $conexion->prepare("SELECT nombre, precio FROM combos WHERE id = ?");
        if (!$stmt_combo) throw new Exception('Error preparando consulta de combo: ' . $conexion->error);
        $stmt_combo->bind_param('i', $servicio_id);
        $stmt_combo->execute();
        $result_combo = $stmt_combo->get_result();
        $combo = $result_combo->fetch_assoc();
        $stmt_combo->close();

        if (!$combo) {
            throw new Exception('Combo no encontrado con ID: ' . htmlspecialchars($servicio_id));
        }

        $id_servicio = null;
        $id_combo = $servicio_id;
        $id_categoria = 14; // ID de la categoría "Combos"
        $precio = $combo['precio'];
        $nombre_servicio_guardar = $combo['nombre'];
    } else {
        // Es un servicio regular
        $stmt_servicio = $conexion->prepare("SELECT nombre, precio, categoria_id FROM servicios WHERE id = ?");
        if (!$stmt_servicio) throw new Exception('Error preparando consulta de servicio: ' . $conexion->error);
        $stmt_servicio->bind_param('i', $servicio_id);
        $stmt_servicio->execute();
        $result = $stmt_servicio->get_result();
        $servicio = $result->fetch_assoc();
        $stmt_servicio->close();

        if (!$servicio) {
            throw new Exception('Servicio no encontrado con ID: ' . htmlspecialchars($servicio_id));
        }

        $id_servicio = $servicio_id;
        $id_combo = null;
        $id_categoria = $servicio['categoria_id'];
        $precio = $servicio['precio'];
        $nombre_servicio_guardar = $servicio['nombre'];
    }

    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;
    $id_cita = null; // No se está usando por ahora

    $stmt->bind_param(
        'issiiiiidssss',
        $_SESSION['usuario_id'],
        $_SESSION['nombre'],
        $_SESSION['apellido'],
        $id_categoria,
        $id_servicio,
        $id_combo,
        $id_negocio,
        $id_cita,
        $id_cita, // Placeholder para id_venta, asumiendo que es null
        $precio,
        $fecha_realizacion,
        $created_at,
        $updated_at
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la inserción para el servicio/combo ID ' . htmlspecialchars($servicio_id) . ': ' . $stmt->error);
    }

    // Guardar el id de la inserción principal para relacionar bloqueos
    $parent_insert_id = $conexion->insert_id;

    // Si la duración es mayor a 60 minutos, guarda un bloqueo para el siguiente turno
    if ($duracion > 60) {
        $fecha_bloqueo = new DateTime($fecha_realizacion);
        $fecha_bloqueo->modify('+1 hour');
        $fecha_bloqueo_str = $fecha_bloqueo->format('Y-m-d H:i:s');
        $nombre_bloqueo = "Bloqueo por combo";
        $precio_bloqueo = 0;

        // Verificar si la columna id_reserva_padre existe en la tabla `historial`
        $colCheck = mysqli_query($conexion, "SHOW COLUMNS FROM historial LIKE 'id_reserva_padre'");
        $has_parent_col = ($colCheck && mysqli_num_rows($colCheck) > 0);

        if ($has_parent_col) {
            // Insertar bloqueo relacionándolo con la reserva padre (no aparecerá en historial si se filtra por id_reserva_padre IS NULL)
            $query_bloqueo = "INSERT INTO historial (
                id_usuario, nombre, apellido, id_categoria, id_servicio, id_combo, id_negocio, id_cita, id_venta, precio, fecha_realizacion, created_at, updated_at, id_reserva_padre
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_b = $conexion->prepare($query_bloqueo);
            if (!$stmt_b) {
                throw new Exception('Error preparando consulta de bloqueo: ' . $conexion->error);
            }

            $id_reserva_padre_val = $parent_insert_id;
            $stmt_b->bind_param(
                'issiiiiidssssi',
                $_SESSION['usuario_id'],
                $_SESSION['nombre'],
                $_SESSION['apellido'],
                $id_categoria,
                $id_servicio,
                $id_combo,
                $id_negocio,
                $id_cita,
                $id_cita,
                $precio_bloqueo,
                $fecha_bloqueo_str,
                $created_at,
                $updated_at,
                $id_reserva_padre_val
            );

            if (!$stmt_b->execute()) {
                $stmt_b->close();
                throw new Exception('Error al guardar el bloqueo del turno siguiente: ' . $stmt_b->error);
            }

            $stmt_b->close();
        } else {
            // Si la columna no existe en la tabla, no insertamos el bloqueo porque generaría
            // una fila adicional visible en el historial (precio $0). En su lugar, confiar en el
            // bloqueo a nivel de aplicación o considerar crear una tabla específica para bloqueos.
            error_log('guardar_historial: la columna id_reserva_padre no existe; no se insertó bloqueo.');
        }
    }

    $stmt->close();
    $conexion->commit(); // Confirmar la transacción
    $conexion->close();

    // --- INICIO: Lógica para crear notificación al admin ---
    try {
        $conexion_notif = conectarDB();
        // 1. Encontrar a los admins del negocio (id_negocio = 1 para Kore)
        $query_admins = "SELECT id FROM usuarios WHERE tipo = 'admin' AND id_negocio_admin = ?";
        $stmt_admins = $conexion_notif->prepare($query_admins);
        $stmt_admins->bind_param('i', $id_negocio);
        $stmt_admins->execute();
        $result_admins = $stmt_admins->get_result();

        // 2. Crear el mensaje de la notificación
        $nombre_cliente = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
        $fecha_reserva_obj = new DateTime($fecha_realizacion);
        $mensaje = "Nueva reserva de {$nombre_cliente} para '{$nombre_servicio_guardar}' el " . $fecha_reserva_obj->format('d/m/Y \a \l\a\s H:i');

        // 3. Insertar una notificación para cada admin
        $query_insert_notif = "INSERT INTO notificaciones (id_usuario_destino, id_negocio, mensaje) VALUES (?, ?, ?)";
        while ($admin = $result_admins->fetch_assoc()) {
            $stmt_notif = $conexion_notif->prepare($query_insert_notif);
            $stmt_notif->bind_param('iis', $admin['id'], $id_negocio, $mensaje);
            $stmt_notif->execute();
        }
        $conexion_notif->close();
    } catch (Exception $e) {
        // Si falla la notificación, no detenemos el proceso principal. Solo lo registramos.
        error_log("Error al crear notificación: " . $e->getMessage());
    }
    // --- FIN: Lógica para crear notificación ---

    echo json_encode([
        'success' => true,
        'message' => 'Reserva guardada exitosamente'
    ]);

} catch (Exception $e) {
    error_log('Error en guardar_historial.php: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Si $conexion es un objeto y la transacción está activa, haz rollback
    if ($conexion instanceof mysqli && $conexion->thread_id) {
        $conexion->rollback(); // Revertir cambios en caso de error
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>