<?php
session_start();
include 'conexEstetica.php';
$conex = conectarDB();

$error = '';
$success = false;

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
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['apellido'] = $usuario['apellido'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['tipo'] = $usuario['tipo'];
                $_SESSION['id_negocio_admin'] = $usuario['id_negocio_admin'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
        $stmt->close();
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
$conex->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Centro Estético</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      overflow: hidden;
    }

    /* Fondo gradiente animado */
    .login-container {
      background: linear-gradient(-45deg, #f8b6b0, #f6b8b3, #fadcd9, #fde4e1);
      background-size: 400% 400%;
      animation: gradient 15s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }

    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Tarjeta principal */
    .login-card {
      background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 50%, #e89c94 100%);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      box-shadow: 
        0 25px 70px rgba(216, 112, 106, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.2),
        0 0 40px rgba(248, 182, 176, 0.15);
      width: 100%;
      max-width: 450px;
      padding: 50px 40px;
      animation: slideIn 0.6s ease-out, glow 3s ease-in-out infinite;
      border: 3px solid #d8706a;
      background-origin: border-box;
      background-clip: padding-box;
      position: relative;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes glow {
      0%, 100% {
        box-shadow: 
          0 25px 70px rgba(216, 112, 106, 0.3),
          inset 0 1px 0 rgba(255, 255, 255, 0.2),
          0 0 40px rgba(248, 182, 176, 0.2);
      }
      50% {
        box-shadow: 
          0 25px 70px rgba(216, 112, 106, 0.5),
          inset 0 1px 0 rgba(255, 255, 255, 0.4),
          0 0 80px rgba(216, 112, 106, 0.5),
          0 0 120px rgba(248, 182, 176, 0.4);
      }
    }

    /* Encabezado */
    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .login-icon {
      font-size: 60px;
      color: white;
      margin-bottom: 15px;
      animation: pulse 2s ease-in-out infinite;
      text-shadow: 0 2px 8px rgba(216, 112, 106, 0.3);
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    /* Logo */
    .login-logo {
      margin-bottom: 20px;
      display: flex;
      justify-content: center;
    }

    .logo-img {
      max-width: 140px;
      height: auto;
      object-fit: contain;
      animation: logoFade 0.8s ease-out;
      filter: drop-shadow(0 4px 12px rgba(216, 112, 106, 0.2));
    }

    @keyframes logoFade {
      from {
        opacity: 0;
        transform: scale(0.8);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .login-header h1 {
      font-size: 32px;
      font-weight: 700;
      color: #5a4038;
      margin: 0;
      letter-spacing: -0.5px;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .login-header p {
      font-size: 14px;
      color: #5a4038;
      margin-top: 8px;
      font-weight: 500;
    }

    /* Formulario */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      font-weight: 600;
      color: #5a4038;
      font-size: 14px;
      margin-bottom: 10px;
      display: block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-control {
      border: 2px solid rgba(216, 112, 106, 0.3);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.85);
      color: #5a4038;
    }

    .form-control:focus {
      border-color: #d8706a;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 0 0 0.2rem rgba(216, 112, 106, 0.25);
      outline: none;
      color: #5a4038;
    }

    .form-control::placeholder {
      color: #a89089;
    }

    /* Input group */
    .input-group {
      position: relative;
    }

    .input-group .btn-toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #8b6f65;
      cursor: pointer;
      padding: 6px;
      transition: color 0.3s ease;
      z-index: 10;
    }

    .input-group .btn-toggle-password:hover {
      color: #d8706a;
    }

    .input-group .form-control {
      padding-right: 45px;
    }

    /* Mensaje de error */
    .alert-error {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 248, 248, 0.9) 100%);
      border: 2px solid #d8706a;
      color: #8b4a44;
      padding: 14px 16px;
      border-radius: 12px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.5s ease-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .alert-error i {
      font-size: 18px;
      flex-shrink: 0;
    }

    /* Botón de submit */
    .btn-login {
      background: linear-gradient(135deg, #d8706a 0%, #e89c94 100%);
      border: none;
      border-radius: 12px;
      padding: 14px 24px;
      font-weight: 600;
      font-size: 16px;
      color: white;
      width: 100%;
      margin-top: 30px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 8px 20px rgba(216, 112, 106, 0.3);
      cursor: pointer;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(216, 112, 106, 0.4);
      background: linear-gradient(135deg, #c95a54 0%, #d8706a 100%);
      color: white;
    }

    .btn-login:active {
      transform: translateY(0);
    }

    /* Enlaces adicionales */
    .login-footer {
      text-align: center;
      margin-top: 25px;
      padding-top: 25px;
      border-top: 2px solid rgba(216, 112, 106, 0.2);
    }

    .login-footer p {
      color: #7f8c8d;
      font-size: 14px;
      margin: 0;
    }

    .login-footer a {
      color: #d8706a;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .login-footer a:hover {
      color: #c95a54;
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 600px) {
      .login-card {
        padding: 40px 25px;
        max-width: 100%;
      }

      .login-header h1 {
        font-size: 26px;
      }

      .login-icon {
        font-size: 50px;
      }

      .form-label {
        font-size: 13px;
      }

      .form-control {
        padding: 11px 14px;
        font-size: 14px;
      }

      .btn-login {
        padding: 12px 20px;
        font-size: 15px;
      }
    }

    /* Animación de carga */
    .loading {
      pointer-events: none;
      opacity: 0.7;
    }

    .btn-login:disabled {
      cursor: not-allowed;
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <!-- Header -->
      <div class="login-header">
        <h1>Iniciar Sesión</h1>
        <p>Bienvenido de vuelta</p>
      </div>

      <!-- Error Message -->
      <?php if (!empty($error)): ?>
        <div class="alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <!-- Formulario -->
      <form method="post" action="" id="loginForm">
        <!-- Email -->
        <div class="form-group">
          <label for="usuario" class="form-label">
            <i class="fas fa-envelope"></i> Email
          </label>
          <input 
            type="email" 
            name="usuario" 
            id="usuario" 
            class="form-control" 
            placeholder="tu@email.com"
            required
            autofocus
          >
        </div>

        <!-- Contraseña -->
        <div class="form-group">
          <label for="contrasena" class="form-label">
            <i class="fas fa-lock"></i> Contraseña
          </label>
          <div class="input-group">
            <input 
              type="password" 
              name="contrasena" 
              id="contrasena" 
              class="form-control" 
              placeholder="••••••••"
              required
            >
            <button 
              type="button" 
              class="btn-toggle-password" 
              onclick="togglePassword('contrasena', this)"
              aria-label="Mostrar/Ocultar contraseña"
            >
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>

        <!-- Botón Submit -->
        <button type="submit" class="btn-login" id="submitBtn">
          <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </button>
      </form>

      <!-- Footer -->
      <div class="login-footer">
        <p>¿No tienes cuenta? <a href="RecuperarContrasena.php">Recuperar contraseña</a></p>
      </div>
    </div>
  </div>

  <script>
    // Toggle Password Visibility
    function togglePassword(inputId, button) {
      const input = document.getElementById(inputId);
      const icon = button.querySelector('i');
      
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Mostrar contraseña');
      }
    }

    // Validación en tiempo real
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('usuario');
    const passwordInput = document.getElementById('contrasena');
    const submitBtn = document.getElementById('submitBtn');

    // Validar inputs
    function validateForm() {
      const isValid = emailInput.value.trim() && passwordInput.value.trim();
      submitBtn.disabled = !isValid;
    }

    emailInput.addEventListener('input', validateForm);
    passwordInput.addEventListener('input', validateForm);

    // Inicializar estado del botón
    validateForm();

    // Efecto de carga
    form.addEventListener('submit', function(e) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
    });

    // Enter para submit
    passwordInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter' && !submitBtn.disabled) {
        form.submit();
      }
    });
  </script>
</body>
</html>

