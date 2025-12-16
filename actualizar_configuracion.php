<?php
session_start();
include_once 'conexEstetica.php';

header('Content-Type: application/json');

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_negocio = isset($_POST['id_negocio']) ? intval($_POST['id_negocio']) : 0;
$tipo_actualizacion = $_POST['tipo'] ?? '';

try {
    $conexion = conectarDB();

    // Actualizar servicios
    if ($tipo_actualizacion === 'servicios') {
        $nombres = $_POST['servicio_nombre'] ?? [];
        $descripciones = $_POST['servicio_descripcion'] ?? [];
        $urls = $_POST['servicio_imagen_url'] ?? [];
        $precios = $_POST['servicio_precio'] ?? [];
        $duraciones = $_POST['servicio_duracion'] ?? [];

        foreach ($nombres as $id_servicio => $nombre) {
            $descripcion = $descripciones[$id_servicio] ?? '';
            $url = $urls[$id_servicio] ?? '';
            $precio = floatval($precios[$id_servicio] ?? 0);
            $duracion = intval($duraciones[$id_servicio] ?? 45);

            $stmt = $conexion->prepare("UPDATE servicios SET nombre = ?, descripcion = ?, imagen_url = ?, precio = ?, duracion_minutos = ? WHERE id = ? AND id_negocio = ?");
            $stmt->bind_param('sssdiii', $nombre, $descripcion, $url, $precio, $duracion, $id_servicio, $id_negocio);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => true, 'message' => 'Servicios actualizados correctamente', 'tipo' => 'servicios']);
    }
    // Actualizar combos
    elseif ($tipo_actualizacion === 'combos') {
        $nombres = $_POST['combo_nombre'] ?? [];
        $descripciones = $_POST['combo_descripcion'] ?? [];
        $urls = $_POST['combo_imagen_url'] ?? [];
        $duraciones = $_POST['combo_duracion'] ?? [];
        $precios = $_POST['combo_precio'] ?? [];

        foreach ($nombres as $combo_id => $nombre) {
            $descripcion = $descripciones[$combo_id] ?? '';
            $url = $urls[$combo_id] ?? '';
            $duracion = intval($duraciones[$combo_id] ?? 105);
            $precio = floatval($precios[$combo_id] ?? 0);

            $stmt = $conexion->prepare("UPDATE combos SET nombre = ?, descripcion = ?, imagen_url = ?, duracion_minutos = ?, precio = ? WHERE id = ? AND id_negocio = ?");
            $stmt->bind_param('sssidii', $nombre, $descripcion, $url, $duracion, $precio, $combo_id, $id_negocio);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => true, 'message' => 'Combos actualizados correctamente', 'tipo' => 'combos']);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Tipo de actualización no especificado']);
    }

    $conexion->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
?>
