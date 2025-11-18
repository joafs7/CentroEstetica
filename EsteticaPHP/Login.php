<?php
session_start();
include 'conexEstetica.php';
$conex = conectarDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    if (!empty($email) && !empty($contrasena)) {
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conex->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($usuario = $resultado->fetch_assoc()) {
            if (password_verify($contrasena, $usuario['contrasena'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario'] = $usuario['nombre'];
                $_SESSION['nombre'] = $usuario['nombre']; // AGREGAR ESTA LÍNEA
                $_SESSION['apellido'] = $usuario['apellido']; // AGREGAR ESTA LÍNEA
                $_SESSION['email'] = $usuario['email']; // AGREGAR ESTA LÍNEA
                $_SESSION['tipo'] = $usuario['tipo'];
                $_SESSION['id_negocio_admin'] = $usuario['id_negocio_admin'];
                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('Contraseña incorrecta.');</script>";
            }
        } else {
            echo "<script>alert('Usuario no encontrado.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Por favor, complete todos los campos.');</script>";
    }
}
$conex->close();
?>

<!-- HTML del formulario -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Centro Estético</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #fdfbfb, #ebedee);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
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
    @media (max-width: 480px) {
      .login-box {
        padding: 20px 10px;
        max-width: 98vw;
      }
      h1 {
        font-size: 1.3rem;
      }
      .form-label {
        font-size: 1rem;
      }
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
        <div class="input-group">
          <input type="password" name="contrasena" id="contrasena" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('contrasena', this)">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
  </div>
  <script>
    function togglePassword(inputId, button) {
      const input = document.getElementById(inputId);
      const icon = button.querySelector('i');
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>

