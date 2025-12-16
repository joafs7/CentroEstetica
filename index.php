<?php
session_start();
$usuarioLogueado = isset($_SESSION['usuario']);
$esAdmin = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Estético</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/CSS/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Pacifico&family=Playwrite+AU+QLD:wght@200&display=swap" rel="stylesheet">  
    <script>
    // Variable JS que viene desde PHP
    const usuarioLogueado = <?php echo $usuarioLogueado ? 'true' : 'false'; ?>;

    document.addEventListener("DOMContentLoaded", function() {
        if (usuarioLogueado) {
            // Ocultar los botones de login/registro
            document.querySelector(".btn-Iniciar").style.display = "none";
        }
        
        // Si viene con #registro en la URL, abrir el modal de registro
        if (window.location.hash === '#registro') {
            document.getElementById("registroOverlay").style.display = "flex";
        }
    });
    </script>
    <script>
 function mostrarLogin() {
    document.getElementById("loginOverlay").style.display = "flex";
}
function mostrarRegistro() {
    document.getElementById("registroOverlay").style.display = "flex";
}
function cerrar(id) {
    document.getElementById(id).style.display = "none";
}
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
// Cierra si se hace clic fuera del modal
window.addEventListener("click", function(e) {
    const loginOverlay = document.getElementById("loginOverlay");
    const registroOverlay = document.getElementById("registroOverlay");
    if (e.target === loginOverlay) cerrar("loginOverlay");
    if (e.target === registroOverlay) cerrar("registroOverlay");
});
    </script>  
	
	<style>
/* Tipografía general */
body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #fdfbfb, #ebedee);
  margin: 0;
  padding: 0;
}

/* Encabezado con botones */
.encabezado {
  background: linear-gradient(90deg, #f8cdda, #f1a7c5);
  padding: 20px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.botones {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 10px;
}

.btn-Iniciar,
.btn-Registrarse {
  background-color: #fff;
  color: #d63384;
  border: 2px solid #d63384;
  padding: 10px 20px;
  border-radius: 30px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-Iniciar:hover,
.btn-Registrarse:hover {
  background-color: #d63384;
  color: #fff;
}

span{
  text-align: center;
  margin-top: 10px;
  font-family: 'Pacifico', cursive;
  font-size: 20px;
  color: #555;
}
/* Bienvenida */
.bienvenida {
  text-align: center;
  margin-top: 30px;
  font-family: 'Pacifico', cursive;
  font-size: 36px;
  color: #d63384;
}

/* Subtítulo */
h3 {
  text-align: center;
  color: #555;
  margin-bottom: 40px;
}

/* Tarjetas */
.tarjeta {
  padding: 20px;
  transition: transform 0.3s ease;
}

.tarjeta:hover {
  transform: scale(1.05);
}

.card {
  border-radius: 20px;
  background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 50%, #e89c94 100%);
  border: 3px solid #d8706a;
  box-shadow: 
    0 25px 70px rgba(216, 112, 106, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.2),
    0 0 40px rgba(248, 182, 176, 0.2);
  overflow: hidden;
  transition: all 0.3s ease;
  animation: cardGlow 3s ease-in-out infinite;
}

.card:hover {
  box-shadow: 
    0 25px 70px rgba(216, 112, 106, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.3),
    0 0 80px rgba(216, 112, 106, 0.4),
    0 0 120px rgba(248, 182, 176, 0.3);
}

@keyframes cardGlow {
  0%, 100% {
    box-shadow: 
      0 25px 70px rgba(216, 112, 106, 0.3),
      inset 0 1px 0 rgba(255, 255, 255, 0.2),
      0 0 40px rgba(248, 182, 176, 0.2);
  }
  50% {
    box-shadow: 
      0 25px 70px rgba(216, 112, 106, 0.5),
      inset 0 1px 0 rgba(255, 255, 255, 0.3),
      0 0 80px rgba(216, 112, 106, 0.4),
      0 0 120px rgba(248, 182, 176, 0.3);
  }
}

.card-img-top {
  height: 250px;
  object-fit: cover;
  border-bottom: 3px solid rgba(216, 112, 106, 0.2);
}

.tarjeta:first-child .card-img-top {
  object-fit: contain;
  object-position: center;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(250, 240, 240, 0.2) 100%);
  padding: 10px;
}

.card-body {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(250, 240, 240, 0.95) 100%);
  padding: 20px;
}

.card-title {
  color: #5a4038 !important;
  font-weight: 700 !important;
  font-size: 22px !important;
  margin: 0 !important;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Modales */
.login-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(214, 51, 132, 0.15);
  backdrop-filter: blur(6px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 999;
  overflow-y: auto;
}

.login-overlay.active {
  display: flex;
}

.login-box {
  background: linear-gradient(135deg, #ffffff 0%, #f9f5f7 100%);
  padding: 40px 45px;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(214, 51, 132, 0.25);
  width: 100%;
  max-width: 700px;
  max-height: none;
  overflow-y: visible;
  animation: slideUp 0.5s ease-out;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border: 3px solid #d63384;
}
.login-box h1 {
  font-size: 32px;
  color: #d63384;
  margin-bottom: 8px;
  font-weight: 700;
  text-align: center;
  margin-top: 0;
  letter-spacing: -0.5px;
}
.login-box-subtitle {
  text-align: center;
  color: #999;
  font-size: 14px;
  margin-bottom: 25px;
  font-weight: 400;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-label {
  font-weight: 600;
  color: #555;
  font-size: 13px;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.form-control {
  border-radius: 10px;
  border: 2px solid #e8d5e0;
  padding: 10px 12px;
  font-size: 14px;
  background-color: #fafafa;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #d63384;
  background-color: #fff;
  box-shadow: 0 0 12px rgba(214, 51, 132, 0.25);
  outline: none;
}

.form-control::placeholder {
  color: #bbb;
  font-size: 13px;
}

.input-group .form-control {
  border-right: none;
}

.input-group .btn {
  border: 2px solid #e8d5e0;
  color: #d63384;
  background-color: #fafafa;
  transition: all 0.3s ease;
  border-radius: 10px;
  margin-left: 8px;
  padding: 8px 10px;
  font-size: 13px;
}

.input-group .btn:hover {
  background-color: #f0e5eb;
  border-color: #d63384;
}

.mb-3 {
  margin-bottom: 16px !important;
}

.row {
  margin-right: -8px;
  margin-left: -8px;
}

.col-md-6 {
  padding-right: 8px;
  padding-left: 8px;
}

.btnForm {
  background: linear-gradient(135deg, #d63384 0%, #c0297d 100%);
  color: white;
  padding: 14px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  width: 100%;
  font-weight: 600;
  font-size: 16px;
  margin-top: 24px;
  margin-bottom: 0;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(214, 51, 132, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btnForm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(214, 51, 132, 0.4);
}

.btnForm:active {
  transform: translateY(0px);
}
<style>
.btn-close-pink {
    background: none;
    border: none;
    font-size: 1.6rem;
    color: #d63384;
    opacity: 0.8;
    transition: color 0.2s, opacity 0.2s;
}
.btn-close-pink:hover {
    color: #b02a6f;
    opacity: 1;
}
</style>

  </head>
<body>
  
<div class="encabezado">
    <div class="botones">
        <?php if ($usuarioLogueado): ?>
            <!-- Mostrar el nombre del usuario y el botón de cerrar sesión -->
            <span >¡Bienvenido/a, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</span>
            <a href="logout.php" class="btn btn-Registrarse">Cerrar Sesión</a>
        <?php else: ?>
            <!-- Mostrar los botones de iniciar sesión y registrarse si no está logueado -->
            <a href="Login.php" class="btn-Iniciar" style="text-decoration: none;">Iniciar Sesión</a>
            <button class="btn-Registrarse" onclick="mostrarRegistro()">Regístrate Ahora</button>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Login -->
<div class="login-overlay" id="loginOverlay">
    <div class="login-box position-relative">
        <!-- Botón cerrar -->
        <button type="button" class="btn-close btn-close-pink" aria-label="Cerrar" onclick="cerrar('loginOverlay')" style="position:absolute;top:18px;right:18px;z-index:10;"></button>
        <form id="login" method="POST" action="Login.php">
            <h1 class="text-center">Iniciar Sesión</h1>

            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" name="contrasena" id="loginContrasena" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('loginContrasena', this)">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                <a href="#" onclick="mostrarRegistro(); cerrar('loginOverlay')" class="btn btn-outline-secondary">Registrarse</a>
            </div>

            <div class="mt-3">
                <div class="text-end mb-2">
                    <a href="RecuperarContrasena.php" class="text-decoration-none text-dark">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="text-center">
                    <p class="mb-0">¿No tienes cuenta? <a href="#" onclick="mostrarRegistro(); cerrar('loginOverlay')" class="fw-bold text-decoration-none" style="color: #d63384;">Regístrate</a></p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registro -->
<div class="login-overlay" id="registroOverlay">
    <div class="login-box position-relative">
        <!-- Botón cerrar -->
        <button type="button" class="btn-close btn-close-pink" aria-label="Cerrar" onclick="cerrar('registroOverlay')" style="position:absolute;top:18px;right:18px;z-index:10;"></button>
        <h1 class="text-center">✨ Crear Cuenta</h1>
        <form action="" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre" class="form-label"><i class="fas fa-user" style="color: #d63384;"></i>Nombre</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Tu nombre" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="apellido" class="form-label"><i class="fas fa-user" style="color: #d63384;"></i>Apellido</label>
                        <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Tu apellido" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fas fa-envelope" style="color: #d63384;"></i>Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com" required>
            </div>
            <div class="mb-3">
                <label for="celular" class="form-label"><i class="fas fa-phone" style="color: #d63384;"></i>Celular</label>
                <input type="tel" id="celular" name="celular" class="form-control" placeholder="3564827188" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="registroContrasena" class="form-label"><i class="fas fa-lock" style="color: #d63384;"></i>Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="registroContrasena" name="contrasena" class="form-control" placeholder="8+ caracteres" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('registroContrasena', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="confirmarContrasena" class="form-label"><i class="fas fa-lock" style="color: #d63384;"></i>Confirmar</label>
                        <div class="input-group">
                            <input type="password" id="confirmarContrasena" name="confirmar_contrasena" class="form-control" placeholder="Repite" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmarContrasena', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-grid mt-3">
                <button type="submit" class="btnForm">Crear Cuenta</button>
            </div>
        </form>
    </div>
</div>
<?php
include 'conexEstetica.php'; // Asegúrate de que este archivo contiene la función `conectarDB()`
$conex = conectarDB();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar y sanitizar los datos del formulario
    $nombre = mysqli_real_escape_string($conex, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($conex, trim($_POST['apellido']));
    $email = mysqli_real_escape_string($conex, filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $celular = mysqli_real_escape_string($conex, trim($_POST['celular']));
    $contrasena = mysqli_real_escape_string($conex, $_POST['contrasena']);

    // Validar formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '@') === false || strpos($email, '.com') === false) {
        echo "<script>alert('El correo electrónico debe contener @ y terminar en .com');</script>";
        exit();
    }
    // Validar celular (no debe contener 0 ni 15)
    if (preg_match('/(^0|15)/', $celular) || strpos($celular, '0') !== false || strpos($celular, '15') !== false) {
        echo "<script>alert('El celular no debe contener el 0 ni el 15. Ejemplo: 3564827188');</script>";
        exit();
    }

    // Validar longitud de la contraseña
    if (strlen($contrasena) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.');</script>";
        exit();
    }

    // Validar que las contraseñas coincidan
    if ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
        echo "<script>alert('Las contraseñas no coinciden.');</script>";
        exit();
    }

    $query_check = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
    $result_check = mysqli_query($conex, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>alert('El correo electrónico ya se encuentra registrado.');</script>";
        mysqli_close($conex);
        exit();
    }
    // Encriptar la contraseña antes de almacenarla
    $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);

    // Consulta para insertar los datos en la tabla usuarios
    $query_usuario = "INSERT INTO usuarios (nombre, apellido, email, celular, contrasena) 
                      VALUES ('$nombre', '$apellido', '$email', '$celular', '$contrasena_hash')";

    if (mysqli_query($conex, $query_usuario)) {
        echo "<script>alert('Registro exitoso. Ahora puede iniciar sesión.');</script>";
    } else {
        echo "<script>alert('Error al registrar el usuario: " . mysqli_error($conex) . "');</script>";
    }

    mysqli_close($conex);
}
?>

    <H1 class="bienvenida">Bienvenida/o</H1>

    <H3>Seleccione la estética en la que su belleza pueda deslumbrar!</H3>
  
<div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center align-items-center">
    <!-- Tarjeta Kore Estética -->
    <div class="col tarjeta">
        <a href="Kore_Estetica-Inicio.php?id_negocio=1" class="text-decoration-none">
            <div class="card">
                <img src="imagenes/KoreEstetica.png" class="card-img-top" alt="Kore Estética">
                <div class="card-body">
                    <h5 class="card-title text-center">Kore Estética</h5>
                </div>
            </div>
        </a>
    </div>
    <!-- Tarjeta Juliette Nails -->
    <div class="col tarjeta">
        <a href="JulietteNails.php?id_negocio=2" class="text-decoration-none">
            <div class="card">
                <img src="imagenes/JulietteNails.jpeg" class="card-img-top" alt="Juliette Nails">
                <div class="card-body">
                    <h5 class="card-title text-center">Juliette Nails</h5>
                </div>
            </div>
        </a>
    </div>
</div>
<script>
document.querySelector('#registroOverlay form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const celular = document.getElementById('celular').value.trim();
    const contrasena = document.getElementById('registroContrasena').value;
    const confirmar = document.getElementById('confirmarContrasena').value;

    if (!email.includes('@') || !email.endsWith('.com')) {
        alert('El correo electrónico debe contener @ y terminar en .com');
        e.preventDefault();
        return;
    }
    if (celular.includes('0') || celular.includes('15')) {
        alert('El celular no debe contener el 0 ni el 15. Ejemplo: 3564827188');
        e.preventDefault();
        return;
    }
    if (contrasena.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres.');
        e.preventDefault();
        return;
    }
    if (contrasena !== confirmar) {
        alert('Las contraseñas no coinciden.');
        e.preventDefault();
        return;
    }
});
</script>
</body>
</html>