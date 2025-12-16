<?php
header('Content-Type: application/json');
include 'conexEstetica.php';

$conexion = conectarDB();

// Obtener mes, año e id_negocio de la solicitud, con valores por defecto
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 1; // Por defecto Kore Estética (id = 1)

// Asegurarse de que el mes tenga dos dígitos (ej. 01, 09, 12)
$month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);

// El primer y último día del mes
$first_day = "$year-$month_padded-01";
$last_day = date('Y-m-t', strtotime($first_day));

// Obtener TODAS las reservas (principales y bloqueos) para mostrar todos los horarios ocupados
$query = "SELECT fecha_realizacion FROM historial 
          WHERE id_negocio = ? 
          AND fecha_realizacion BETWEEN ? AND ?
          AND (cancelada = 0 OR cancelada IS NULL)";

$stmt = mysqli_prepare($conexion, $query);
// La fecha en la BD es DATETIME, así que hay que incluir la hora en el rango
$start_datetime = "$first_day 00:00:00";
$end_datetime = "$last_day 23:59:59";
mysqli_stmt_bind_param($stmt, "iss", $id_negocio, $start_datetime, $end_datetime);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$reservas = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $fecha_hora = new DateTime($row['fecha_realizacion']);
    $fecha = $fecha_hora->format('Y-m-d');
    $hora = (int)$fecha_hora->format('H');
    
    if (!isset($reservas[$fecha])) {
        $reservas[$fecha] = [];
    }
    // Agregar solo si no existe ya (evitar duplicados)
    if (!in_array($hora, $reservas[$fecha])) {
        $reservas[$fecha][] = $hora;
    }
}

// Ordenar las horas
foreach ($reservas as &$horas) {
    sort($horas);
}

echo json_encode($reservas);
mysqli_close($conexion);
?>