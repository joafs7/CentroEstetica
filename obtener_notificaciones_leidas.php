<?php
session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json');

// Seguridad: Solo para administradores autenticados
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$conexion = conectarDB();
$usuario_id = $_SESSION['usuario_id'];
$id_negocio = $_SESSION['id_negocio_admin'];

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