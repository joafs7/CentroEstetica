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

<<<<<<< HEAD:obtener_historial.php
// Identificar el negocio. Asumimos Kore (id=1) por defecto si no se especifica.
// La página de Juliette Nails debería pasar id_negocio=2
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 1;

// Verificar si el usuario es admin de este negocio
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

=======
>>>>>>> master:EsteticaPHP/obtener_historial.php
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

<<<<<<< HEAD:obtener_historial.php
// Construir la consulta dinámicamente
$query = "SELECT h.fecha_realizacion, h.precio, 
          COALESCE(s.nombre, c.nombre) as servicio_nombre,
          CONCAT(u.nombre, ' ', u.apellido) AS cliente_nombre,
          COALESCE(h.id_categoria, s.categoria_id, NULL) AS categoria_id
          FROM historial h
          LEFT JOIN servicios s ON h.id_servicio = s.id
          LEFT JOIN combos c ON h.id_combo = c.id
          LEFT JOIN usuarios u ON h.id_usuario = u.id
          WHERE h.id_negocio = ? AND COALESCE(s.nombre, c.nombre) IS NOT NULL";

// Si no es admin, filtramos por su ID de usuario. Si es admin, ve todo.
if (!$esAdmin) {
    $query .= " AND h.id_usuario = ?";
}

// Añadir el filtro de id_reserva_padre SOLO si es para Kore Estética (id_negocio = 1)
// Añadir el filtro de id_reserva_padre SOLO si es para Kore Estética (id_negocio = 1)
if ($id_negocio === 1) {
    // Verificar si la columna existe en la tabla `historial` antes de usarla
    $colExists = false;
    $colCheck = mysqli_query($conexion, "SHOW COLUMNS FROM historial LIKE 'id_reserva_padre'");
    if ($colCheck && mysqli_num_rows($colCheck) > 0) {
        $colExists = true;
    }

    if ($colExists) {
        $query .= " AND h.id_reserva_padre IS NULL";
    } else {
        // Si la columna no existe, no agregamos la cláusula para evitar errores.
        // Opcional: se podría registrar este hecho para depuración.
    }
}

$query .= " {$clausula_fecha}
          ORDER BY h.fecha_realizacion DESC";

$stmt = mysqli_prepare($conexion, $query);
if ($esAdmin) {
    mysqli_stmt_bind_param($stmt, "i", $id_negocio);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $id_negocio, $usuario_id);
}
=======
$query = "SELECT h.fecha_realizacion, h.precio, COALESCE(s.nombre, c.nombre) as nombre
          FROM historial h
          LEFT JOIN servicios s ON h.id_servicio = s.id
          LEFT JOIN combos c ON h.id_combo = c.id
          WHERE h.id_usuario = ? AND COALESCE(s.nombre, c.nombre) IS NOT NULL {$clausula_fecha}
          ORDER BY h.fecha_realizacion DESC";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
>>>>>>> master:EsteticaPHP/obtener_historial.php
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$historial = [];
while ($row = mysqli_fetch_assoc($resultado)) {
<<<<<<< HEAD:obtener_historial.php
    // Mapear id de categoría a nombre legible (ajusta según tu esquema)
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
        'fecha_realizacion' => $row['fecha_realizacion'],
        'servicio' => $row['servicio_nombre'] ?? 'Servicio no especificado',
        'cliente' => $row['cliente_nombre'] ?? '',
        'precio' => $row['precio'],
        'categoria_id' => $categoria_id,
        'categoria_nombre' => $categoria_nombre
=======
    $historial[] = [
        'fecha_realizacion' => $row['fecha_realizacion'],
        'nombre' => $row['nombre'] ?? 'Servicio no especificado',
        'precio' => $row['precio']
>>>>>>> master:EsteticaPHP/obtener_historial.php
    ];
}

echo json_encode($historial);
mysqli_close($conexion);
?>