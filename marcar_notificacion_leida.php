<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'ID de notificación no proporcionado']);
    exit;
}

include_once 'conexEstetica.php';
$conexion = conectarDB();

if ($conexion) {
    // Actualizar la notificación a 'leida = 1'
    // Se incluye el id_usuario_destino para seguridad, asegurando que un admin solo marque sus propias notificaciones
    $query = "UPDATE notificaciones SET leida = 1 WHERE id = ? AND id_usuario_destino = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ii', $notificationId, $_SESSION['usuario_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
    }
    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
}