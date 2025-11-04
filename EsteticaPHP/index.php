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
  transform: scale(1.03);
}

.card {
  border-radius: 16px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.card-img-top {
  height: 250px;
  object-fit: cover;
}

/* Modales */
.login-overlay {
 position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(214, 51, 132, 0.15); /* Fondo rosado transparente */
  backdrop-filter: blur(6px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.login-box {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 420px;
  animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}

.login-box h1 {
  font-size: 28px;
  color: #d63384;
  margin-bottom: 25px;
  font-weight: 600;
  text-align: center;
}

.form-label {
  font-weight: 500;
  color: #444;
}

.form-control {
  border-radius: 8px;
  border: 2px solid #e0e0e0;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control:focus {
  border-color: #d63384;
  box-shadow: 0 0 8px rgba(214, 51, 132, 0.3);
  outline: none;
}

.btnForm {
  background-color: #d63384;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  font-weight: bold;
  font-size: 16px;
  transition: background-color 0.3s, transform 0.2s;
}

.btnForm:hover {
  background-color: #b02a6f;
  transform: translateY(-2px);
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
            <button class="btn-Iniciar" onclick="mostrarLogin()">Iniciar Sesión</button>
            <button class="btn-Registrarse" onclick="mostrarRegistro()">Regístrate Ahora</button>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Login -->
<div class="login-overlay" id="loginOverlay">
    <div class="login-box">
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

            <div class="text-end mt-2">
                <a href="RecuperarContrasena.php" class="text-decoration-none text-dark">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registro -->
<div class="login-overlay" id="registroOverlay">
    <div class="login-box">
        <h1 class="text-center">Registrarse</h1>

        <form action="" method="POST">
          <div class="mb-3">
            <label>Nombre</label>
            <input type="text" id="nombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label>Apellido</label>
            <input type="text" id="apellido" name="apellido" required>
            </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" name="email" required>
            </div>
          <div class="mb-3">
            <label>Celular</label>
            <input type="tel" id="celular" name="celular" required placeholder="Sin el 0 ni el 15">
            </div>
          <div class="mb-3">
            <label>Contraseña</label>
            <div class="input-group">
                <input type="password" id="registroContrasena" name="contrasena" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('registroContrasena', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
          </div>
          <div class="mb-3">
            <label>Confirmar contraseña</label>
            <div class="input-group">
                <input type="password" id="confirmarContrasena" name="confirmar_contrasena" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmarContrasena', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
          </div>
            <div style="margin-top:10px;">
              <button type="submit" class="btnForm">Registrarse</button>
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
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El correo electrónico no es válido.');</script>";
        exit();
    }

    // Validar que las contraseñas coincidan
    if ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
        echo "<script>alert('Las contraseñas no coinciden.');</script>";
        exit();
    }

    // Validar longitud de la contraseña
    if (strlen($contrasena) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.');</script>";
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

    <H3>Seleccione la estetica en la que su belleza pueda deslumbrar!</H3>
  
<div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center align-items-center">
    <!-- Tarjeta Kore Estetica -->
    <div class="col tarjeta">
        <a href="Kore_Estetica-Inicio.php?id_negocio=1" class="text-decoration-none">
            <div class="card">
                <img src="imagenes/KoreEstetica.jpeg" class="card-img-top" alt="Kore Estetica">
                <div class="card-body">
                    <h5 class="card-title text-center">Kore Estetica</h5>
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

</body>
</html>