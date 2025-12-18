<?php
// Script de prueba para verificar si el UPDATE de cancelación funciona directamente

include_once 'conexEstetica.php';

$conexion = conectarDB();
if (!$conexion) {
    die("Error de conexión");
}

$id_test = 189;
$id_negocio = 2;

// Ver estado ANTES
$stmt_antes = $conexion->prepare("SELECT id, cancelada, fecha_cancelacion FROM historial WHERE id = ?");
$stmt_antes->bind_param("i", $id_test);
$stmt_antes->execute();
$result_antes = $stmt_antes->get_result();
$fila_antes = $result_antes->fetch_assoc();
echo "ANTES: ID: {$fila_antes['id']}, Cancelada: {$fila_antes['cancelada']}, Fecha Cancelación: {$fila_antes['fecha_cancelacion']}\n";
$stmt_antes->close();

// Ejecutar UPDATE
$stmt_update = $conexion->prepare("UPDATE historial SET cancelada = 1, fecha_cancelacion = NOW() WHERE id = ? AND id_negocio = ?");
$stmt_update->bind_param("ii", $id_test, $id_negocio);

if ($stmt_update->execute()) {
    echo "UPDATE ejecutado exitosamente, Filas afectadas: " . $stmt_update->affected_rows . "\n";
} else {
    echo "ERROR en UPDATE: " . $stmt_update->error . "\n";
}
$stmt_update->close();

// Ver estado DESPUÉS
$stmt_despues = $conexion->prepare("SELECT id, cancelada, fecha_cancelacion FROM historial WHERE id = ?");
$stmt_despues->bind_param("i", $id_test);
$stmt_despues->execute();
$result_despues = $stmt_despues->get_result();
$fila_despues = $result_despues->fetch_assoc();
echo "DESPUÉS: ID: {$fila_despues['id']}, Cancelada: {$fila_despues['cancelada']}, Fecha Cancelación: {$fila_despues['fecha_cancelacion']}\n";
$stmt_despues->close();

$conexion->close();
?>
