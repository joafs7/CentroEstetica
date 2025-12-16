<?php
session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json');

// Seguridad: Solo para usuarios autenticados (admin o usuario normal)
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado']);
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

// Determinar el intervalo de tiempo basado en el parámetro GET
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'semana';
$clausula_fecha = "";

switch ($periodo) {
    case 'mes':
        $clausula_fecha = "AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case 'tres_meses':
        $clausula_fecha = "AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        break;
    case 'todos':
        $clausula_fecha = ""; // Sin filtro de fecha
        break;
    case 'semana':
    default:
        $clausula_fecha = "AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
}

// Consulta para obtener notificaciones leídas (leida = 1) para el admin actual
$query = "SELECT id, mensaje, fecha_creacion FROM notificaciones 
          WHERE id_usuario_destino = ? AND id_negocio = ? AND leida = 1 {$clausula_fecha}
          ORDER BY fecha_creacion DESC";

$stmt = $conexion->prepare($query);
$stmt->bind_param('ii', $usuario_id, $id_negocio);
$stmt->execute();
$resultado = $stmt->get_result();

$notificaciones = $resultado->fetch_all(MYSQLI_ASSOC);

echo json_encode($notificaciones);
mysqli_close($conexion);