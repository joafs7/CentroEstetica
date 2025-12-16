<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$respuesta = ['success' => false, 'error' => ''];

// Verificar que se haya enviado un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $respuesta['error'] = 'No se recibió archivo válido';
    echo json_encode($respuesta);
    exit;
}

$archivo = $_FILES['foto'];
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$tamanoMaximo = 5 * 1024 * 1024; // 5MB

// Validar tipo de archivo
if (!in_array($archivo['type'], $tiposPermitidos)) {
    $respuesta['error'] = 'El archivo debe ser una imagen (JPG, PNG, GIF o WEBP)';
    echo json_encode($respuesta);
    exit;
}

// Validar tamaño
if ($archivo['size'] > $tamanoMaximo) {
    $respuesta['error'] = 'El archivo es muy grande (máximo 5MB)';
    echo json_encode($respuesta);
    exit;
}

// Crear directorio si no existe
$directorioFotos = 'fotos_perfil';
if (!is_dir($directorioFotos)) {
    mkdir($directorioFotos, 0755, true);
}

// Generar nombre único para el archivo
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$nombreArchivo = 'usuario_' . $usuarioId . '_' . time() . '.' . $extension;
$rutaArchivo = $directorioFotos . '/' . $nombreArchivo;

// Mover archivo
if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
    $respuesta['error'] = 'Error al guardar la imagen';
    echo json_encode($respuesta);
    exit;
}

// Guardar ruta en la base de datos
include_once 'conexEstetica.php';
$conexion = conectarDB();

if ($conexion) {
    // Obtener foto anterior para eliminarla
    $stmtOld = mysqli_prepare($conexion, "SELECT foto_perfil FROM usuarios WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtOld, 'i', $usuarioId);
    mysqli_stmt_execute($stmtOld);
    mysqli_stmt_bind_result($stmtOld, $fotoAnterior);
    mysqli_stmt_fetch($stmtOld);
    mysqli_stmt_close($stmtOld);
    
    // Eliminar foto anterior si existe
    if (!empty($fotoAnterior) && file_exists($fotoAnterior)) {
        unlink($fotoAnterior);
    }
    
    // Actualizar foto en la BD
    $stmt = mysqli_prepare($conexion, "UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $rutaArchivo, $usuarioId);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['foto_perfil'] = $rutaArchivo;
        $respuesta['success'] = true;
        $respuesta['foto_url'] = $rutaArchivo;
    } else {
        $respuesta['error'] = 'Error al guardar en la base de datos';
        unlink($rutaArchivo); // Eliminar archivo si falla la BD
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
} else {
    $respuesta['error'] = 'Error de conexión a la base de datos';
    unlink($rutaArchivo);
}

echo json_encode($respuesta);
?>
