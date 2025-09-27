<?php
session_start();
include 'conexEstetica.php'; // Asegúrate de que este archivo contiene la función `conectarDB()`
$conn = conectarDB();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['usuario']); // Usamos el campo 'email' como usuario
    $contrasena = trim($_POST['contrasena']);

    if (!empty($email) && !empty($contrasena)) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            // Verificar la contraseña con password_verify()
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // Contraseña correcta, iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];

                // Redirigir al inicio
                header("Location: index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }

        $stmt->close();
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}

$conn->close();
?>

<!-- HTML del formulario -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Centro Estético</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #fdfbfb, #ebedee);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
    }
    h1 {
      text-align: center;
      color: #d63384;
      margin-bottom: 30px;
    }
    .btn-primary {
      background-color: #d63384;
      border: none;
      border-radius: 8px;
      font-weight: 500;
    }
    .btn-primary:hover {
      background-color: #b02a6f;
    }
    .error {
      background-color: #ffebee;
      color: #c62828;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h1>Iniciar Sesión</h1>

    <?php if (!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label for="usuario" class="form-label">Email</label>
        <input type="text" name="usuario" id="usuario" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="contrasena" class="form-label">Contraseña</label>
        <input type="password" name="contrasena" id="contrasena" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
  </div>
</body>
</html>

