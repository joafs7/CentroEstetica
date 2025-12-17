<?php
session_start();
include_once 'conexEstetica.php';

// Mostrar toda la información de debug
$conexion = conectarDB();
$usuario_id = $_SESSION['usuario_id'] ?? null;
$id_negocio = 1; // Kore

echo "<h1>DEBUG HISTORIAL</h1>";
echo "<h2>Sesión:</h2>";
echo "<pre>";
echo "usuario_id: " . ($usuario_id ?? "NO DEFINIDO") . "\n";
echo "usuario: " . ($_SESSION['usuario'] ?? "NO DEFINIDO") . "\n";
echo "tipo: " . ($_SESSION['tipo'] ?? "NO DEFINIDO") . "\n";
echo "</pre>";

// Verificar si el usuario es admin
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

echo "<h2>Es Admin: " . ($esAdmin ? "SÍ" : "NO") . "</h2>";

if (!$usuario_id) {
    echo "ERROR: No hay usuario logueado";
    exit;
}

// 1. Ver qué hay en la tabla historial para este usuario
echo "<h2>Registros en HISTORIAL para usuario_id = " . $usuario_id . " e id_negocio = " . $id_negocio . ":</h2>";
$query1 = "SELECT id, id_usuario, nombre, apellido, id_servicio, id_combo, id_negocio, precio, fecha_realizacion, cancelada FROM historial WHERE id_usuario = ? AND id_negocio = ? ORDER BY fecha_realizacion DESC LIMIT 20";
$stmt1 = $conexion->prepare($query1);
$stmt1->bind_param('ii', $usuario_id, $id_negocio);
$stmt1->execute();
$result1 = $stmt1->get_result();

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Apellido</th><th>ID Servicio</th><th>ID Combo</th><th>ID Negocio</th><th>Precio</th><th>Fecha</th><th>Cancelada</th></tr>";
while ($row = $result1->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['id_usuario'] . "</td>";
    echo "<td>" . $row['nombre'] . "</td>";
    echo "<td>" . $row['apellido'] . "</td>";
    echo "<td>" . $row['id_servicio'] . "</td>";
    echo "<td>" . $row['id_combo'] . "</td>";
    echo "<td>" . $row['id_negocio'] . "</td>";
    echo "<td>$" . $row['precio'] . "</td>";
    echo "<td>" . $row['fecha_realizacion'] . "</td>";
    echo "<td>" . ($row['cancelada'] ?? 0) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Total de registros sin filtro de precio:</h2>";
$query_total = "SELECT COUNT(*) as total FROM historial WHERE id_usuario = ? AND id_negocio = ?";
$stmt_total = $conexion->prepare($query_total);
$stmt_total->bind_param('ii', $usuario_id, $id_negocio);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
echo "Total: " . $row_total['total'] . " registros<br>";

echo "<h2>Con filtro precio > 0:</h2>";
$query_precio = "SELECT COUNT(*) as total FROM historial WHERE id_usuario = ? AND id_negocio = ? AND precio > 0";
$stmt_precio = $conexion->prepare($query_precio);
$stmt_precio->bind_param('ii', $usuario_id, $id_negocio);
$stmt_precio->execute();
$result_precio = $stmt_precio->get_result();
$row_precio = $result_precio->fetch_assoc();
echo "Total: " . $row_precio['total'] . " registros<br>";

// 2. Ahora simular exactamente lo que hace obtener_historial.php
echo "<h2>Simulando obtener_historial.php:</h2>";
$periodo = 'semana';
$clausula_fecha = "AND h.fecha_realizacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

$query = "SELECT h.id, h.fecha_realizacion, h.precio, 
          COALESCE(s.nombre, c.nombre) as servicio_nombre,
          CONCAT(u.nombre, ' ', u.apellido) AS cliente_nombre,
          COALESCE(h.id_categoria, s.categoria_id, NULL) AS categoria_id
          FROM historial h
          LEFT JOIN servicios s ON h.id_servicio = s.id
          LEFT JOIN combos c ON h.id_combo = c.id
          LEFT JOIN usuarios u ON h.id_usuario = u.id
          WHERE h.id_negocio = ? 
          AND (h.cancelada = 0 OR h.cancelada IS NULL)
          AND h.precio > 0
          AND h.id_usuario = ?
          {$clausula_fecha}
          ORDER BY h.fecha_realizacion DESC";

echo "<p>Query: " . $query . "</p>";

$stmt = $conexion->prepare($query);
$stmt->bind_param('ii', $id_negocio, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

echo "Registros encontrados: " . $resultado->num_rows . "<br>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Servicio</th><th>Cliente</th><th>Fecha</th><th>Precio</th></tr>";
while ($row = $resultado->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . ($row['servicio_nombre'] ?? "SIN NOMBRE") . "</td>";
    echo "<td>" . ($row['cliente_nombre'] ?? "") . "</td>";
    echo "<td>" . $row['fecha_realizacion'] . "</td>";
    echo "<td>$" . $row['precio'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conexion->close();
?>
