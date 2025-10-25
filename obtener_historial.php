<?php
session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$conexion = conectarDB();
$usuario_id = $_SESSION['usuario_id'];

// Determinar el intervalo de tiempo basado en el parámetro GET
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'semana'; // Por defecto 'semana'
$clausula_fecha = "";

switch ($periodo) {
    case 'mes':
        $clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case 'tres_meses':
        $clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        break;
    case 'todos':
        $clausula_fecha = ""; // Sin filtro de fecha
        break;
    case 'semana':
    default:
        $clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
}

$query = "SELECT h.fecha_realizacion, h.precio, COALESCE(s.nombre, c.nombre) as nombre
          FROM historial h
          LEFT JOIN servicios s ON h.id_servicio = s.id
          LEFT JOIN combos c ON h.id_combo = c.id
          WHERE h.id_usuario = ? AND COALESCE(s.nombre, c.nombre) IS NOT NULL {$clausula_fecha}
          ORDER BY h.fecha_realizacion DESC";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$historial = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $historial[] = [
        'fecha_realizacion' => $row['fecha_realizacion'],
        'nombre' => $row['nombre'] ?? 'Servicio no especificado',
        'precio' => $row['precio']
    ];
}

echo json_encode($historial);
mysqli_close($conexion);
?>