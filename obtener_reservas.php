<?php
header('Content-Type: application/json');
include 'conexEstetica.php';

$conexion = conectarDB();

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 2; // Usa el parámetro o Juliette Nails por defecto

$month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);
$first_day = "$year-$month_padded-01";
$last_day = date('Y-m-t', strtotime($first_day));

$query = "SELECT fecha_realizacion FROM historial 
          WHERE id_negocio = ? AND cancelada = 0
          AND fecha_realizacion BETWEEN ? AND ?";

$stmt = $conexion->prepare($query);
$start_datetime = "$first_day 00:00:00";
$end_datetime = "$last_day 23:59:59";
$stmt->bind_param("iss", $id_negocio, $start_datetime, $end_datetime);
$stmt->execute();
$resultado = $stmt->get_result();

$reservas = [];
while ($row = $resultado->fetch_assoc()) {
    $fecha_hora = new DateTime($row['fecha_realizacion']);
    $fecha = $fecha_hora->format('Y-m-d');
    $hora = (int)$fecha_hora->format('H');
    if (!isset($reservas[$fecha])) {
        $reservas[$fecha] = [];
    }
    $reservas[$fecha][] = $hora;
}

echo json_encode($reservas);
$conexion->close();
?>