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

// Identificar el negocio
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 1;

// Verificar si el usuario es admin
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

// Determinar el período
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'semana';
$clausula_fecha = "";

switch ($periodo) {
    case 'mes':
        $clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case 'tres_meses':
        $clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        break;
    case 'todos':
        $clausula_fecha = "";
        break;
    case 'semana':
    default:
        // Mostrar cancelaciones de la última semana y futuras
        $clausula_fecha = "AND (h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 7 DAY) OR h.fecha_realizacion >= NOW())";
        break;
}

// Obtener citas canceladas
$query = "SELECT 
    h.id,
    h.fecha_realizacion, 
    h.precio, 
    COALESCE(h.fecha_cancelacion, NOW()) as fecha_cancelacion,
    COALESCE(s.nombre, c.nombre) as servicio_nombre,
    CONCAT(u.nombre, ' ', u.apellido) AS cliente_nombre,
    COALESCE(h.id_categoria, s.categoria_id, NULL) AS categoria_id
FROM historial h
LEFT JOIN servicios s ON h.id_servicio = s.id
LEFT JOIN combos c ON h.id_combo = c.id
LEFT JOIN usuarios u ON h.id_usuario = u.id
WHERE h.id_negocio = ? AND h.cancelada = 1 AND h.precio > 0";

// Si no es admin, filtramos por su ID de usuario
if (!$esAdmin) {
    $query .= " AND h.id_usuario = ?";
}

// Filtrar para mostrar solo reservas principales (excluir bloqueos con precio 0)
$query .= " AND h.precio > 0";

$query .= " {$clausula_fecha}
ORDER BY h.created_at DESC";

$stmt = mysqli_prepare($conexion, $query);
if ($esAdmin) {
    mysqli_stmt_bind_param($stmt, "i", $id_negocio);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $id_negocio, $usuario_id);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$historial = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    // Mapear categoría
    $categoryNames = [
        6 => 'Capping',
        7 => 'Capping Poly',
        8 => 'Esmaltado',
        9 => 'Soft Gel',
        10 => 'Corporales',
        11 => 'Faciales',
        12 => 'Masajes',
        14 => 'Combos'
    ];

    $categoria_id = isset($row['categoria_id']) ? intval($row['categoria_id']) : null;
    $categoria_nombre = $categoria_id && isset($categoryNames[$categoria_id]) ? $categoryNames[$categoria_id] : ($categoria_id ? 'Categoría ' . $categoria_id : 'General');

    $historial[] = [
        'id' => $row['id'],
        'fecha_realizacion' => $row['fecha_realizacion'],
        'fecha_cancelacion' => $row['fecha_cancelacion'],
        'servicio' => $row['servicio_nombre'] ?? 'Servicio no especificado',
        'cliente' => $row['cliente_nombre'] ?? '',
        'precio' => $row['precio'],
        'categoria_id' => $categoria_id,
        'categoria_nombre' => $categoria_nombre
    ];
}

echo json_encode($historial);
mysqli_close($conexion);
?>
