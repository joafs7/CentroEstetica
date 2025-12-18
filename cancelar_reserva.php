<?php
// Limpiar cualquier output buffer anterior
ob_start();

session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json; charset=utf-8');

// Limpiar buffers para asegurar que solo devolvemos JSON
ob_clean();

// LOGGING para debug
$log_file = __DIR__ . '/debug_cancelacion.log';
function log_msg($msg) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}
log_msg("=== NUEVA SOLICITUD ===");

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    log_msg("ERROR: Usuario no logueado");
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado', 'success' => false], JSON_UNESCAPED_UNICODE);
    exit();
}

log_msg("Usuario ID: " . $_SESSION['usuario_id']);

$conexion = conectarDB();

// Verificar conexión
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos', 'success' => false], JSON_UNESCAPED_UNICODE);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
$id_negocio_admin = $_SESSION['id_negocio_admin'] ?? null;

log_msg("Es admin: " . ($es_admin ? "SÍ" : "NO") . ", tipo sesión: " . ($_SESSION['tipo'] ?? "NULL"));

// Obtener datos del POST
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

log_msg("Input recibido: " . $input_raw);

// Validar que se recibieron los datos
if (!$input) {
    log_msg("ERROR: JSON inválido");
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos JSON válidos', 'success' => false], JSON_UNESCAPED_UNICODE);
    exit();
}

// Validar que existe el campo id_historial
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id_historial'])) {
    echo json_encode(['success' => false, 'error' => 'No se envió el ID de la cita (id_historial)']);
    exit();
}

$id_historial = intval($input['id_historial']);

log_msg("ID a cancelar: " . $id_historial);

if ($id_historial <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cita inválido: ' . $id_historial, 'success' => false], JSON_UNESCAPED_UNICODE);
    exit();
}

// Obtener información de la reserva
$query_reserva = "SELECT h.id_usuario, h.id_negocio, CONCAT(u.nombre, ' ', u.apellido) as cliente_nombre,
                         COALESCE(s.nombre, c.nombre) as servicio_nombre, h.fecha_realizacion
                  FROM historial h
                  LEFT JOIN usuarios u ON h.id_usuario = u.id
                  LEFT JOIN servicios s ON h.id_servicio = s.id
                  LEFT JOIN combos c ON h.id_combo = c.id
                  WHERE h.id = ?";

$stmt_reserva = mysqli_prepare($conexion, $query_reserva);
if (!$stmt_reserva) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar consulta: ' . mysqli_error($conexion), 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_close($conexion);
    exit();
}

mysqli_stmt_bind_param($stmt_reserva, "i", $id_historial);
if (!mysqli_stmt_execute($stmt_reserva)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar consulta: ' . mysqli_stmt_error($stmt_reserva), 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($stmt_reserva);
    mysqli_close($conexion);
    exit();
}

$resultado_reserva = mysqli_stmt_get_result($stmt_reserva);
$reserva = mysqli_fetch_assoc($resultado_reserva);

if (!$reserva) {
    echo json_encode(['error' => 'Reserva no encontrada', 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($stmt_reserva);
    mysqli_close($conexion);
    exit();
}

// Verificar permisos: el usuario solo puede cancelar sus propias reservas, el admin puede cancelar cualquiera
if (!$es_admin && intval($reserva['id_usuario']) !== intval($usuario_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para cancelar esta reserva', 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($stmt_reserva);
    mysqli_close($conexion);
    exit();
}

if ($es_admin && intval($reserva['id_negocio']) !== intval($id_negocio_admin)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para cancelar esta reserva', 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($stmt_reserva);
    mysqli_close($conexion);
    exit();
}

// Cancelar la reserva (marcar como cancelada)
$query_cancelar = "UPDATE historial SET cancelada = 1, fecha_cancelacion = NOW() WHERE id = ?";
$stmt_cancelar = mysqli_prepare($conexion, $query_cancelar);
if (!$stmt_cancelar) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar actualización: ' . mysqli_error($conexion), 'success' => false], JSON_UNESCAPED_UNICODE);
    mysqli_stmt_close($stmt_reserva);
    mysqli_close($conexion);
    exit();
}

mysqli_stmt_bind_param($stmt_cancelar, "i", $id_historial);

if (mysqli_stmt_execute($stmt_cancelar)) {
    log_msg("UPDATE ejecutado para ID: $id_historial");
    
    // También cancelar todos los bloqueos relacionados (registros con nombre "Bloqueo por combo" del mismo usuario y horarios siguientes)
    // Obtener fecha_realizacion y id_usuario de la reserva cancelada
    $fecha_reserva = $reserva['fecha_realizacion'];
    $id_usuario_reserva = $reserva['id_usuario'];
    $id_negocio_reserva = $reserva['id_negocio'];
    
    // Calcular fecha límite (2 horas después de la reserva principal)
    $fecha_limite = date('Y-m-d H:i:s', strtotime($fecha_reserva . ' +2 hours'));
    
    $query_cancelar_bloqueos = "UPDATE historial SET cancelada = 1, fecha_cancelacion = NOW() 
                                WHERE id_usuario = ? 
                                AND id_negocio = ?
                                AND nombre = 'Bloqueo por combo' 
                                AND fecha_realizacion > ? 
                                AND fecha_realizacion <= ?
                                AND (cancelada = 0 OR cancelada IS NULL)";
    $stmt_cancelar_bloqueos = mysqli_prepare($conexion, $query_cancelar_bloqueos);
    if ($stmt_cancelar_bloqueos) {
        mysqli_stmt_bind_param($stmt_cancelar_bloqueos, "iiss", $id_usuario_reserva, $id_negocio_reserva, $fecha_reserva, $fecha_limite);
        if (mysqli_stmt_execute($stmt_cancelar_bloqueos)) {
            $filas_afectadas = mysqli_stmt_affected_rows($stmt_cancelar_bloqueos);
            log_msg("Bloqueos cancelados para reserva ID: $id_historial (filas afectadas: " . $filas_afectadas . ")");
        } else {
            log_msg("Error al cancelar bloqueos: " . mysqli_stmt_error($stmt_cancelar_bloqueos));
        }
        mysqli_stmt_close($stmt_cancelar_bloqueos);
    } else {
        log_msg("Error al preparar consulta de cancelación de bloqueos: " . mysqli_error($conexion));
    }
    
    // Verificar inmediatamente el estado
    $verify_query = "SELECT cancelada FROM historial WHERE id = $id_historial";
    $verify_result = $conexion->query($verify_query);
    $verify_data = $verify_result->fetch_assoc();
    log_msg("Verificación POST-UPDATE: cancelada = " . $verify_data['cancelada']);
    
    // Inicializar variables
    $id_usuario_destino = null;
    $mensaje = null;
    
    if ($es_admin) {
        // El admin cancela, notificar al usuario
        $id_usuario_destino = $reserva['id_usuario'];
        $mensaje = "Lo siento, por motivos de fuerza mayor debo cancelar tu cita de '" . 
                   $reserva['servicio_nombre'] . "' el " . 
                   date('d/m/Y', strtotime($reserva['fecha_realizacion'])) . 
                   " a las " . date('H:i', strtotime($reserva['fecha_realizacion'])) . 
                   ". Por favor agenda una nueva cita.";
        log_msg("Admin cancelando. Usuario destino: " . $id_usuario_destino);
    } else {
        // El usuario cancela, notificar a TODOS los admins de la negocio
        $negocio_id = $reserva['id_negocio'];
        log_msg("Usuario cancelando. Negocio ID: " . $negocio_id);
        
        // Buscar TODOS los admins de esa negocio
        $query_admins = "SELECT id FROM usuarios WHERE id_negocio_admin = ? AND tipo = 'admin'";
        $stmt_admins = mysqli_prepare($conexion, $query_admins);
        if ($stmt_admins) {
            mysqli_stmt_bind_param($stmt_admins, "i", $negocio_id);
            mysqli_stmt_execute($stmt_admins);
            $resultado_admins = mysqli_stmt_get_result($stmt_admins);
            $lista_admins = [];
            while ($admin = mysqli_fetch_assoc($resultado_admins)) {
                $lista_admins[] = $admin['id'];
            }
            mysqli_stmt_close($stmt_admins);
            
            log_msg("Búsqueda de admins con negocio_id=$negocio_id, encontrados: " . json_encode($lista_admins));
            
            // El mensaje será igual para todos los admins
            $mensaje = "El cliente {$reserva['cliente_nombre']} ha cancelado su reserva para '{$reserva['servicio_nombre']}' el " . 
                       date('d/m/Y \a \l\a\s H:i', strtotime($reserva['fecha_realizacion'])) . ".";
            log_msg("Mensaje de cancelación: " . $mensaje);
        } else {
            log_msg("Error preparando consulta de admins: " . mysqli_error($conexion));
            $lista_admins = [];
        }
    }
    
    // Insertar notificación(es)
    if ($es_admin) {
        // Si es admin, notificar al usuario (un solo destino)
        log_msg("Antes de insertar notificación - id_usuario_destino: " . ($id_usuario_destino ?? "NULL") . ", mensaje: " . ($mensaje ?? "NULL"));
        
        if ($id_usuario_destino) {
            $query_notif = "INSERT INTO notificaciones (id_usuario_destino, id_negocio, mensaje) VALUES (?, ?, ?)";
            $stmt_notif = mysqli_prepare($conexion, $query_notif);
            if ($stmt_notif) {
                mysqli_stmt_bind_param($stmt_notif, "iis", $id_usuario_destino, $reserva['id_negocio'], $mensaje);
                if (mysqli_stmt_execute($stmt_notif)) {
                    log_msg("Notificación insertada exitosamente para user_id=" . $id_usuario_destino);
                } else {
                    log_msg("Error al insertar notificación: " . mysqli_stmt_error($stmt_notif));
                }
                mysqli_stmt_close($stmt_notif);
            } else {
                log_msg("Error al preparar notificación: " . mysqli_error($conexion));
            }
        }
    } else {
        // Si es usuario, notificar a TODOS los admins
        if (!empty($lista_admins)) {
            log_msg("Insertando notificación para " . count($lista_admins) . " admin(es)");
            
            foreach ($lista_admins as $admin_id) {
                $query_notif = "INSERT INTO notificaciones (id_usuario_destino, id_negocio, mensaje) VALUES (?, ?, ?)";
                $stmt_notif = mysqli_prepare($conexion, $query_notif);
                if ($stmt_notif) {
                    mysqli_stmt_bind_param($stmt_notif, "iis", $admin_id, $reserva['id_negocio'], $mensaje);
                    if (mysqli_stmt_execute($stmt_notif)) {
                        log_msg("Notificación insertada exitosamente para admin_id=" . $admin_id);
                    } else {
                        log_msg("Error al insertar notificación para admin_id=$admin_id: " . mysqli_stmt_error($stmt_notif));
                    }
                    mysqli_stmt_close($stmt_notif);
                } else {
                    log_msg("Error al preparar notificación para admin_id=$admin_id: " . mysqli_error($conexion));
                }
            }
        } else {
            log_msg("No hay admins para notificar");
        }
    }
    
    echo json_encode(['success' => true, 'error' => null, 'message' => 'Reserva cancelada correctamente'], JSON_UNESCAPED_UNICODE);
    log_msg("Respuesta exitosa enviada");
} else {
    $error_msg = 'Error al cancelar la reserva: ' . mysqli_stmt_error($stmt_cancelar);
    log_msg($error_msg);
    http_response_code(500);
    echo json_encode(['error' => $error_msg, 'success' => false], JSON_UNESCAPED_UNICODE);
}

if (isset($stmt_reserva)) mysqli_stmt_close($stmt_reserva);
if (isset($stmt_cancelar)) mysqli_stmt_close($stmt_cancelar);
mysqli_close($conexion);
?>
