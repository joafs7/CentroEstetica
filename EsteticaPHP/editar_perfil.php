<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Kore_Estetica-Inicio.php');
    exit();
}

$usuarioId = isset($_POST['usuario_id']) ? trim($_POST['usuario_id']) : '';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$celular = isset($_POST['celular']) ? trim($_POST['celular']) : '';

// Validar que el usuario que edita es el que está en la sesión
if ($usuarioId === '' || $nombre === '' || !isset($_SESSION['usuario_id']) || $usuarioId != $_SESSION['usuario_id']) {
    header('Location: Kore_Estetica-Inicio.php');
    exit();
}

// Actualizar la sesión con los nuevos datos
$_SESSION['usuario'] = $nombre;
$_SESSION['apellido'] = $apellido;
$_SESSION['email'] = $email;
$_SESSION['celular'] = $celular;

// Intentar actualizar en la base de datos si existe la conexión y la tabla usuarios
if (file_exists('conexEstetica.php')) {
    include 'conexEstetica.php';
    $conexion = conectarDB();
    if ($conexion) {
        // Actualizar campos: nombre, apellido, email, celular
        $stmt = mysqli_prepare($conexion, "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, celular = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $apellido, $email, $celular, $usuarioId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conexion);
    }
}

// Redirigir de vuelta a la página principal con indicador
header('Location: Kore_Estetica-Inicio.php?updated=1');
exit();