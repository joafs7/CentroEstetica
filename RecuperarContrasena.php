<?php
session_start();

// Configuración de la base de datos
$servername = "localhost";
$username = "root"; // Usuario por defecto de XAMPP
$password = "";     // Contraseña por defecto de XAMPP
$dbname = "esteticadb"; // Asegúrate de que este sea el nombre de tu BD

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = '';
$error = '';
$paso = 1; // 1 para pedir email, 2 para pedir nueva contraseña

// --- LÓGICA PARA PROCESAR FORMULARIOS ---

// Paso 1: Verificar si el correo electrónico existe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verificar_email'])) {
    $email = $_POST['email'];

    if (empty($email)) {
        $error = "Por favor, ingresa tu correo electrónico.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // El correo existe, pasamos al siguiente paso
            $_SESSION['email_recuperacion'] = $email;
            $paso = 2;
        } else {
            $error = "El correo electrónico no se encuentra registrado.";
        }
        $stmt->close();
    }
}

// Paso 2: Restablecer la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restablecer_pass'])) {
    $paso = 2; // Mantenerse en el paso 2 si hay errores
    $email_recuperacion = $_SESSION['email_recuperacion'] ?? null;

    if (!$email_recuperacion) {
        $error = "Sesión inválida. Por favor, inicia el proceso de nuevo.";
        $paso = 1;
        unset($_SESSION['email_recuperacion']);
    } else {
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];

        if (empty($password_nueva) || empty($password_confirmar)) {
            $error = "Ambos campos de contraseña son obligatorios.";
        } elseif ($password_nueva !== $password_confirmar) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Hashear la nueva contraseña por seguridad
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE email = ?");
            $stmt->bind_param("ss", $password_hash, $email_recuperacion);

            if ($stmt->execute()) {
                $mensaje = "¡Contraseña actualizada con éxito! Ya puedes <a href='login.php'>iniciar sesión</a>.";
                $paso = 3; // Paso final para mostrar solo el mensaje de éxito
                unset($_SESSION['email_recuperacion']); // Limpiar la sesión
            } else {
                $error = "Error al actualizar la contraseña: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e91e63 0%, #f6b8b3 100%);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .recovery-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            width: 100%;
            max-width: 420px; /* Consistente con login */
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .recovery-box h1 {
            text-align: center;
            color: #d63384;
            margin-bottom: 25px;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-image: linear-gradient(45deg, #d63384, #c74a94);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(214, 51, 132, 0.4);
            background-image: linear-gradient(45deg, #c74a94, #d63384);
        }
        .btn-primary:focus {
            box-shadow: 0 8px 25px rgba(214, 51, 132, 0.4);
        }
        .input-group {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .input-group:focus-within {
            border-color: #d63384;
            box-shadow: 0 0 0 0.25rem rgba(214, 51, 132, 0.25);
        }
        .input-group .form-control {
            border: none;
            box-shadow: none;
        }
        .input-group .input-group-text {
            background: transparent;
            border: none;
            color: #7d6c79ff;
        }
        .bottom-link a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .bottom-link a:hover {
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="recovery-box">
        <h1>Recuperar Contraseña</h1>
                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-success"><?php echo $mensaje; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($paso == 1): ?>
                            <p>Ingresa tu correo electrónico para iniciar la recuperación.</p>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <button type="submit" name="verificar_email" class="btn btn-primary w-100">Verificar</button>
                            </form>
                        <?php elseif ($paso == 2): ?>
                            <p>Correo verificado. Ahora puedes establecer una nueva contraseña.</p>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-4">
                                    <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                    </div>
                                </div>
                                <button type="submit" name="restablecer_pass" class="btn btn-primary w-100">Restablecer Contraseña</button>
                            </form>
                        <?php endif; ?>

        <?php if ($paso != 3): // No mostrar si ya se completó el proceso ?>
            <div class="text-center mt-4 bottom-link">
                <a href="login.php">Volver a Iniciar Sesión</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>