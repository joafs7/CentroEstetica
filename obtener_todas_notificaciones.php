<?php
session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json');

// Verificar que el usuario está logueado (admin o usuario normal)
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$conexion = conectarDB();
$usuario_id = $_SESSION['usuario_id'];

// Obtener id_negocio del usuario
$id_negocio = null;
if (isset($_SESSION['id_negocio_admin'])) {
    // Es admin
    $id_negocio = $_SESSION['id_negocio_admin'];
} else {
    // Es usuario normal, obtener de BD
    $query_negocio = "SELECT id_negocio FROM usuarios WHERE id = ?";
    $stmt_negocio = mysqli_prepare($conexion, $query_negocio);
    mysqli_stmt_bind_param($stmt_negocio, "i", $usuario_id);
    mysqli_stmt_execute($stmt_negocio);
    $resultado_negocio = mysqli_stmt_get_result($stmt_negocio);
    if ($resultado_negocio && $resultado_negocio->num_rows > 0) {
        $row = mysqli_fetch_assoc($resultado_negocio);
        $id_negocio = $row['id_negocio'];
    }
    mysqli_stmt_close($stmt_negocio);
}

if (!$id_negocio) {
    $id_negocio = 1; // Fallback
}

// Obtener todas las notificaciones (leídas y no leídas) para este admin
// Nota: Las notificaciones solo contienen mensaje, sin información adicional
$query = "SELECT 
    id, 
    mensaje, 
    fecha_creacion, 
    leida
FROM notificaciones
WHERE id_usuario_destino = ? AND id_negocio = ?
ORDER BY fecha_creacion DESC";

$stmt = mysqli_prepare($conexion, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta: ' . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $id_negocio);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit();
}

$resultado = mysqli_stmt_get_result($stmt);

$notificaciones = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    // Extraer información del mensaje
    // Formato esperado: "Nueva reserva de [cliente] para '[servicio]' el [fecha]"
    $mensaje = $row['mensaje'];
    $cliente = '';
    $servicio = '';
    
    // Buscar cliente: "Nueva reserva de CLIENTE para"
    if (preg_match('/Nueva reserva de (.+?) para/', $mensaje, $matches)) {
        $cliente = trim($matches[1]);
    }
    
    // Buscar servicio: "para 'SERVICIO'"
    if (preg_match("/para '([^']+)'/", $mensaje, $matches)) {
        $servicio = trim($matches[1]);
    }
    
    $row['cliente'] = $cliente;
    $row['servicio'] = $servicio;
    
    $notificaciones[] = $row;
}

echo json_encode($notificaciones);
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
