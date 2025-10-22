<?php
session_start();

// Si no hay sesión, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

// Guardar el nombre en una variable
$nombreUsuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Juliette Nails</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<style>
:root {
  --primary-color: #f8b6b0;
  --secondary-color: #f6b8b3;
  --dark-pink: #e91e63;
  --text-color: #4b5563;
}

html {scroll-behavior:smooth;}
body {
  background: linear-gradient(135deg, var(--secondary-color) 0%, #fff 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: var(--text-color);
  margin:0;
}

/* ----- Estilo Glass White ----- */
.glass-white {
  background: linear-gradient(145deg, #ffffff 0%, #f8b6b0 100%);
  border-radius: 40px;
  box-shadow: 8px 8px 16px rgba(0,0,0,0.15),
              -4px -4px 12px rgba(255,255,255,0.7);
  padding: 15px 30px;
  transition: all 0.3s ease;
}
.glass-white:hover {
  transform: scale(1.03);
  box-shadow: 12px 12px 20px rgba(0,0,0,0.2),
              -4px -4px 12px rgba(255,255,255,0.9);
}

h1, h2 {
  color:var(--dark-pink);
  text-align:center;
  margin:20px 0;
}

/* ----- HEADER ----- */
header {
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:20px;
}
header img {height:50px;}
header a {
  border:none;background:transparent;margin:0 5px;
  padding:8px 15px;border-radius:20px;
  color:var(--dark-pink);font-weight:500;transition:all 0.3s;
  text-decoration:none;
}
header a:hover, header a.active {
  background:var(--primary-color);
  color:white;
}

/* ----- SERVICIOS ----- */
.card {
  border:none;
  text-align:center;
  padding:15px;
}
.card img {
  width:60%;
  max-width:100px;
  margin:15px auto;
  display:block;
}
.btn-pink {
  background:var(--primary-color);
  border:none;
  color:white;
  border-radius:25px;
  padding:8px 20px;
  transition:0.3s;
  text-decoration:none;
  display:inline-block;
}
.btn-pink:hover {
  background:var(--dark-pink);
  transform:translateY(-2px);
}

/* ----- GALERÍA ----- */
.carousel-inner img {
  width:20% !important;
  border-radius:20px;
  box-shadow:0 4px 12px rgba(0,0,0,0.3);
  margin:auto;
  transition:transform 0.3s ease, box-shadow 0.3s ease;
}
.carousel-inner img:hover {
  transform:scale(1.05);
  box-shadow:0 6px 15px rgba(0,0,0,0.4);
}
.carousel-control-prev-icon,
.carousel-control-next-icon {filter:invert(1);}
section {margin-top:40px;}

/* ----- CONTACTO ----- */
.contacto {
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
  color:#4b5563;
}
.contacto h3 {
  font-weight:bold;
  margin-bottom:15px;
}
.contacto-info p {
  margin:5px 0;
  font-size:16px;
  display:flex;
  align-items:center;
  gap:10px;
}
.redes-links {
  display:flex;
  justify-content:center;
  gap:15px;
}
.redes-links a {
  width:45px;
  height:45px;
  background:white;
  border-radius:50%;
  display:flex;
  justify-content:center;
  align-items:center;
  color:var(--dark-pink);
  font-size:20px;
  transition:all 0.3s ease;
}
.redes-links a:hover {
  background:var(--dark-pink);
  color:white;
  transform:translateY(-3px);
}

footer {
  text-align:center;
  padding:15px;
  margin-top:20px;
  font-size:14px;
  color:var(--text-color);
}
</style>
</head>

<body>

<div class="container">

  <!-- HEADER -->
  <header class="glass-white">
    <img src="Imagenes/LogoJuliettenails.png" alt="Juliette Nails">
    <div>
      <a href="#inicio" class="active">Inicio</a>
      <a href="#servicios">Servicios</a>
      <a href="#galeria">Galería</a>
      <a href="#contacto">Contacto</a>
      <a href="verReservas.php">Ver Reservas</a>
    </div>
  </header>

  <section id="inicio">
    <h1>¡Te damos la bienvenida <?php echo htmlspecialchars($nombreUsuario); ?> a Juliette Nails!</h1>
    <p class="text-center">Gracias por confiar en nosotr@s. Le ofrecemos una experiencia exclusiva, atención personalizada y resultados que reflejan elegancia y distinción.</p>
  </section>
<?php
include 'conexEstetica.php';
$conexion = conectarDB();

$query_servicios = "SELECT id, nombre, descripcion, precio, duracion_minutos FROM servicios WHERE categoria_id = '6'";
$resultado_servicios = mysqli_query($conexion, $query_servicios);
?>
  <!-- SERVICIOS -->
  <section id="servicios">
    <h2>Servicios</h2>
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card glass-white">
          <img src="Imagenes/Escudo.svg" alt="Capping">
          <div class="card-body">
            <h5>Capping</h5>
            <a href="reservas-JulietteNails.php" class="btn btn-pink">Agendar</a>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card glass-white">
          <img src="Imagenes/Pincel.svg" alt="Capping Polygel">
          <div class="card-body">
            <h5>Capping Polygel</h5>
            <a href="reservas-JulietteNails.php" class="btn btn-pink">Agendar</a>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card glass-white">
          <img src="Imagenes/Gotita.svg" alt="Soft Gel">
          <div class="card-body">
            <h5>Soft Gel</h5>
            <a href="reservas-JulietteNails.php" class="btn btn-pink">Agendar</a>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card glass-white">
          <img src="Imagenes/botella.svg" alt="Esmaltado Semi">
          <div class="card-body">
            <h5>Esmaltado Semi</h5>
            <a href="reservas-JulietteNails.php" class="btn btn-pink">Agendar</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- GALERÍA -->
  <section id="galeria">
    <h2>Galería</h2>
    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active"><img src="Imagenes/Uñas1.jpeg" class="d-block" alt="Foto 1"></div>
        <div class="carousel-item"><img src="Imagenes/Uñas2.jpeg" class="d-block" alt="Foto 2"></div>
        <div class="carousel-item"><img src="Imagenes/Uñas1.jpeg" class="d-block" alt="Foto 3"></div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span><span class="visually-hidden">Siguiente</span>
      </button>
    </div>
  </section>

  <!-- CONTACTO -->
  <section id="contacto" class="contacto glass-white">
    <div class="contacto-info">
      <h3>Contacto</h3>
      <p><i class="fa-solid fa-location-dot"></i> Suipacha 1153, San Francisco, Córdoba</p>
      <p><i class="fa-brands fa-whatsapp"></i> <a href="https://wa.me/5493564827188" style="color:#4b5563;text-decoration:none;">+54 9 3564 827188</a></p>
    </div>
    <div class="contacto-redes">
      <h3>Síguenos en redes</h3>
      <div class="redes-links">
        <a href="https://instagram.com/juliette_naails"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://wa.me/5493564827188"><i class="fa-brands fa-whatsapp"></i></a>
        <a href="https://www.tiktok.com/@juliette_naails?lang=es"><i class="fa-brands fa-tiktok"></i></a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="glass-white">
    © 2025 Juliette Nails - Todos los derechos reservados
  </footer>

</div>

</body>
</html>
