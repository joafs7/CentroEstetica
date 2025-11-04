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
    <title>Kore Estética Corporal</title>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #e89c94;
            --secondary-color: #f6b8b3;
            --light-pink: #fadcd9;
            --dark-pink: #d8706a;
            --text-color: #4b5563;
        }
        
        body {
            background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .pink-gradient {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .light-pink-bg {
            background-color: var(--light-pink);
        }
        
        .pink-text {
            color: var(--dark-pink);
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }
        
        .nav-btn {
            background-color: transparent;
            border: none;
            color: var(--dark-pink);
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
            padding: 8px 15px;
            border-radius: 20px;
            position: relative;
        }
        
        .nav-btn:hover {
            background-color: var(--light-pink);
            color: var(--dark-pink);
        }
        
        /* Efecto de selección para botones activos */
        .nav-btn.active {
            background-color: var(--light-pink);
            color: var(--dark-pink);
            box-shadow: 0 0 0 3px rgba(216, 112, 106, 0.3);
        }
        
        .nav-btn.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background-color: var(--dark-pink);
            border-radius: 3px;
        }
        
        .offcanvas-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
        }
        
        .hero-section {
            background: linear-gradient(rgba(232, 156, 148, 0.8), rgba(246, 184, 179, 0.8)), url('https://images.unsplash.com/photo-1534258936925-c58bed479fc3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 20px;
            border-radius: 15px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(216, 112, 106, 0.15);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
            background-color: var(--light-pink);
        }
        
        .card-title {
            color: var(--dark-pink);
            font-weight: 600;
        }
        
        .btn-pink {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            transition: all 0.3s;
        }
        
        .btn-pink:hover {
            background-color: var(--dark-pink);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(216, 112, 106, 0.3);
        }
        
        .section-title {
            position: relative;
            display: block;
            margin: 40px 0 30px;
            padding-bottom: 10px;
            color: var(--dark-pink);
            text-align: center;
            width: 100%;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 70%;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 3px;
        }
        
        .promo-card {
            border: 2px dashed var(--primary-color);
            background-color: white;
        }
        
        .promo-price {
            color: var(--dark-pink);
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .product-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
            margin: 30px 0;
        }
        
        .product-table tr {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .product-table tr:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(216, 112, 106, 0.15);
        }
        
        .product-table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .product-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            background-color: var(--light-pink);
        }
        
        .contact-section {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 40px 0;
            border-radius: 15px;
            margin-top: 50px;
        }
        
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background-color: white;
            color: var(--primary-color);
            border-radius: 50%;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        
        .social-icon:hover {
            transform: translateY(-5px);
            background-color: var(--dark-pink);
            color: white;
        }
        
        .footer {
            background-color: white;
            padding: 30px 0;
            margin-top: 50px;
            text-align: center;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .logo-placeholder {
            width: 180px;
            height: 50px;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        /* Efecto para elementos seleccionados */
        .selected-item {
            position: relative;
            box-shadow: 0 0 0 3px rgba(216, 112, 106, 0.3);
            border-radius: 15px;
            z-index: 1;
        }
        
        .selected-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid var(--dark-pink);
            border-radius: 15px;
            z-index: -1;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.02);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* Centrado perfecto para títulos */
        .perfect-center {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        /* Estilos para pestañas de tratamientos */
        .treatment-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .treatment-tab {
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .treatment-tab:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .treatment-tab.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 15px rgba(232, 156, 148, 0.4);
        }
        
        .treatment-content {
            display: none;
        }
        
        .treatment-content.active {
            display: block;
        }
        
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .service-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #e67700;
            background: rgba(230, 119, 0, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .service-duration {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-top: 10px;
        }
        html {
            scroll-behavior: smooth;
            }
    </style>
</head>
<body>
    <div class="container">
        <!-- Barra de navegación moderna -->
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <div class="logo-placeholder">KORE</div>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="navbar-nav ms-auto">
                        <a href="#inicio" class="nav-btn active">Inicio</a>
                        <a href="#servicio" class="nav-btn">Servicios</a>
                        <a href="#promos" class="nav-btn">Combos</a>
                        <a href="#contacto" class="nav-btn">Contacto</a>
                        <!-- Botón para abrir sidebar de usuario -->
                        <button class="nav-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#userSidebar" aria-controls="userSidebar">
                            <i class="fas fa-user-circle"></i> Mi cuenta
                        </button>
                       
                    </div>
                </div>
            </div>
        </nav>


        <!-- Offcanvas lateral: Perfil de usuario -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="userSidebar" aria-labelledby="userSidebarLabel">
            <div class="offcanvas-header pink-gradient text-white">
                <h5 class="offcanvas-title" id="userSidebarLabel">Mi cuenta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php
                // Mostrar datos básicos de la sesión
                $usuarioId = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';
                $usuarioNombre = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
                $usuarioApellido = isset($_SESSION['apellido']) ? $_SESSION['apellido'] : '';
                $usuarioEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
                $usuarioCelular = isset($_SESSION['celular']) ? $_SESSION['celular'] : '';

                // Si falta algún dato en la sesión, intentar obtenerlo desde la BD
                if ($usuarioId && (empty($usuarioApellido) || empty($usuarioEmail) || empty($usuarioCelular))) {
                    if (file_exists('conexEstetica.php')) {
                        include_once 'conexEstetica.php';
                        $conexionTmp = conectarDB();
                        if ($conexionTmp) {
                            $stmt = mysqli_prepare($conexionTmp, "SELECT nombre, apellido, email, celular FROM usuarios WHERE id = ? LIMIT 1");
                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
                                mysqli_stmt_execute($stmt);
                                mysqli_stmt_bind_result($stmt, $dbNombre, $dbApellido, $dbEmail, $dbCelular);
                                if (mysqli_stmt_fetch($stmt)) {
                                    if (empty($usuarioNombre)) $usuarioNombre = $dbNombre;
                                    if (empty($usuarioApellido)) $usuarioApellido = $dbApellido;
                                    if (empty($usuarioEmail)) $usuarioEmail = $dbEmail;
                                    if (empty($usuarioCelular)) $usuarioCelular = $dbCelular;
                                }
                                mysqli_stmt_close($stmt);
                            }
                            mysqli_close($conexionTmp);
                        }
                    }
                }
                ?>

                <div class="mb-4 text-center">
                    <div style="font-size:72px;color:var(--dark-pink)"><i class="fas fa-user-circle"></i></div>
                    <h5 class="mt-2"><?php echo htmlspecialchars($usuarioNombre . ' ' . $usuarioApellido); ?></h5>
                </div>

                <!-- Formulario para editar datos del usuario -->
                <form action="editar_perfil.php" method="post">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuarioNombre); ?>" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuarioApellido); ?>">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuarioEmail); ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" value="<?php echo htmlspecialchars($usuarioCelular); ?>">
                        </div>
                    </div>
                    <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuarioId); ?>">
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <button type="button" class="btn w-100" style="background-color: var(--primary-color); color: white;" onclick="mostrarHistorial()">
                                <i class="fas fa-history"></i> Ver Historial de Citas
                            </button>
                        </div>
                        <?php if($esAdmin): ?>
                        <div class="col-12">
                            <a href="config.php" class="btn w-100 btn-pink" style="background-color: var(--primary-color); color: white;">
                                <i class="fas fa-cog me-2"></i> Configuración
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <button type="submit" class="btn btn-pink w-100">Guardar cambios</button>
                        </div>
                        <div class="col-12">
                            <a href="logout.php" class="btn btn-outline-secondary w-100">Cerrar sesión</a>
                        </div>
                    </div>
                </form>

                <?php if (isset($_GET['updated']) && $_GET['updated'] == '1') { ?>
                    <div class="alert alert-success mt-3">Perfil actualizado correctamente.</div>
                <?php } ?>
            </div>
        </div>

        <!-- Sección Hero -->


<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold">Te damos la bienvenida <?php echo htmlspecialchars($nombreUsuario); ?> a Kore Estética Corporal</h1>
        <p class="lead">Un espacio diseñado para que puedas relajarte y disfrutar de tratamientos que le hacen bien a tu cuerpo</p>
        <button class="btn btn-pink btn-lg mt-3" onclick="window.location.href='reservas-kore.php'">
            <i class="fas fa-calendar-check me-2"></i> Reservar turno
        </button>
    </div>
</section>


        <!-- Quiénes somos -->
        <section id="inicio" class="text-center py-5">
            <div class="light-pink-bg p-5 rounded-4">
                <h3 class="pink-text"><i class="fas fa-heart me-2"></i> Quiénes somos?</h3>
                <p class="fs-5">Beauty Kore Estética y Bienestar es un espacio diseñado y preparado para que puedas relajarte y disfrutar de los tratamientos que le hacen bien a tu cuerpo. Nuestro equipo de profesionales está comprometido con tu bienestar y belleza.</p>
            </div>
        </section>

        <!-- Tratamientos -->
        <section id="servicio" class="py-4">
            <div class="perfect-center">
                <h2 class="section-title">Nuestros Tratamientos</h2>
                <p class="text-muted">Selecciona una categoría para ver los tratamientos disponibles</p>
            </div>

            <!-- Pestañas de categorías -->
            <div class="treatment-tabs">
                <button class="treatment-tab active" data-category="corporales">Tratamientos Corporales</button>
                <button class="treatment-tab" data-category="faciales">Tratamientos Faciales</button>
                <button class="treatment-tab" data-category="masajes">Masajes</button>
            </div>

<?php 
include_once 'conexEstetica.php';
$conexion = conectarDB();

// Consulta para obtener los tratamientos corporales
$query_corporales = "SELECT nombre, descripcion, precio, duracion_minutos FROM servicios WHERE categoria_id = '10'";
$resultado = mysqli_query($conexion, $query_corporales);
?>
<!--- Contenido de tratamientos corporales -->
<div id="corporales" class="treatment-content active">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <i class="fas fa-spa fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> minutos</div>
                        <a href="reservas-kore.php?servicio=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Agendar</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php 
// Consulta para obtener los tratamientos corporales
$query_faciales = "SELECT nombre, descripcion, precio, duracion_minutos FROM servicios WHERE categoria_id = '11'";
$resultado = mysqli_query($conexion, $query_faciales);
?>
            <!-- Contenido de tratamientos faciales -->
<div id="faciales" class="treatment-content">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <i class="fas fa-smile fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> minutos</div>
                        <a href="reservas-kore.php?servicio=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Agendar</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php 
// Consulta para obtener los tratamientos corporales
$query_masajes = "SELECT nombre, descripcion, precio, duracion_minutos FROM servicios WHERE categoria_id = '12'";
$resultado = mysqli_query($conexion, $query_masajes);
?>
            <!-- Contenido de masajes -->
<div id="masajes" class="treatment-content">
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <i class="fas fa-hand-sparkles fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> minutos</div>
                        <a href="reservas-kore.php?servicio=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Agendar</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</section>

        <!-- Promociones -->
        <section id="promos" class="py-5">
            <h2 class="section-title">Promociones Especiales</h2>
            <?php
            // Función para asignar un ícono basado en el nombre del tratamiento
            function obtenerIconoParaTratamiento($tratamiento) {
                $tratamiento = strtolower(trim($tratamiento));
                if (strpos($tratamiento, 'electrodo') !== false) return 'fas fa-bolt';
                if (strpos($tratamiento, 'radiofrecuencia') !== false) return 'fas fa-wave-square';
                if (strpos($tratamiento, 'presoterapia') !== false) return 'fas fa-wind';
                if (strpos($tratamiento, 'masaje') !== false) return 'fas fa-spa';
                if (strpos($tratamiento, 'doble aparatología') !== false) return 'fas fa-cogs';
                // Ícono por defecto si no se encuentra una coincidencia
                return 'fas fa-star';
            }
            ?>
            <?php 
            // Consulta para obtener los combos/promociones
            $query_combos = "SELECT nombre, precio, descripcion, 105 AS duracion_minutos FROM combos WHERE id_negocio = 1";
            $resultado_combos = mysqli_query($conexion, $query_combos);
            ?>
            <div class="row g-4 justify-content-center">
                <?php while ($row = mysqli_fetch_assoc($resultado_combos)) { ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card promo-card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center pt-4">
                                <i class="fas fa-gift fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title pink-text"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <div class="card-text flex-grow-1 text-start px-3">
                                    <ul class="list-unstyled">
                                    <?php
                                    $tratamientos = explode(',', $row['descripcion']);
                                    foreach ($tratamientos as $tratamiento) {
                                        $icono = obtenerIconoParaTratamiento($tratamiento);
                                        echo '<li><i class="' . $icono . ' pink-text me-2"></i>' . htmlspecialchars(trim($tratamiento)) . '</li>';
                                    }
                                    ?>
                                    </ul>
                                </div>
                                <?php if (!empty($row['duracion_minutos'])) { ?>
                                    <p class="text-muted">Duración: <?php echo (int)$row['duracion_minutos']; ?> minutos</p>
                                <?php } ?>
                                <h4 class="promo-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></h4>
                                <a href="reservas-kore.php" class="btn btn-pink mt-3">Reservar ahora</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!-- Productos Corporales -->
        

        <!-- Contacto -->
        <section id="contacto" class="contact-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                        <h2><i class="fas fa-phone-alt me-3"></i> Contacto</h2>
                        <p class="mt-3"><i class="fas fa-map-marker-alt me-3"></i> Juan Díaz de Solís 2766, San Francisco, Córdoba</p>
                        <p><i class="fas fa-clock me-3"></i> Horario: Lunes a Viernes 8:00 - 12:00 y 16:00 - 20:00</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <h3>Síguenos en redes</h3>
                        <div class="mt-4">
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="logo-placeholder">KORE</div>
                <p class="mt-3">Beauty Kore Estética y Bienestar - Tu espacio de belleza y relajación</p>
                <p>© 2023 Kore Estética Corporal. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>

    <!-- Modal Historial de Citas -->
    <div class="modal fade" id="historialModal" tabindex="-1" aria-labelledby="historialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header pink-gradient text-white">
                    <h5 class="modal-title" id="historialModalLabel"><i class="fas fa-history me-2"></i>Historial de Citas (Último Mes)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="periodoHistorial" class="form-label fw-bold">Mostrar citas de:</label>
                        <select class="form-select" id="periodoHistorial">
                            <option value="semana" selected>Última semana</option>
                            <option value="mes">Último mes</option>
                            <option value="tres_meses">Últimos 3 meses</option>
                            <option value="todos">Ver todo el historial</option>
                        </select>
                    </div>
                    <p>Aquí se muestran tus reservas para el período seleccionado.</p>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Fecha y Hora</th>
                                    <th scope="col">Servicio/Combo</th>
                                    <th scope="col">Precio</th>
                                </tr>
                            </thead>
                            <tbody id="historialTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mover la lógica de carga a su propia función para poder reutilizarla
        function cargarHistorial(periodo) {
            const tbody = document.getElementById('historialTableBody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center">Cargando historial...</td></tr>'; // Mensaje de carga

            // Hacer la petición AJAX para obtener el historial
            fetch(`obtener_historial.php?periodo=${periodo}`)
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = ''; // Limpiar contenido anterior
                    
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center">No tienes citas en este período.</td></tr>';
                        return;
                    }

                    data.forEach(cita => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${formatearFecha(cita.fecha_realizacion)}</td>
                            <td>${cita.nombre}</td>
                            <td>$${formatearPrecio(cita.precio)}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error al cargar el historial.</td></tr>';
                });
        }

        function mostrarHistorial() {
            const modal = new bootstrap.Modal(document.getElementById('historialModal'));
            const periodoSelect = document.getElementById('periodoHistorial');

            // Cargar el historial con el valor por defecto (última semana)
            cargarHistorial(periodoSelect.value);

            modal.show();
        }

        function formatearFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatearPrecio(precio) {
            return new Intl.NumberFormat('es-CL').format(precio);
        }
        // Manejar la selección de elementos
        document.addEventListener('DOMContentLoaded', function() {
            // Evento para cambiar el período del historial
            const periodoSelect = document.getElementById('periodoHistorial');
            periodoSelect.addEventListener('change', function() {
                cargarHistorial(this.value);
            });

            // Manejar selección en la barra de navegación
            const navBtns = document.querySelectorAll('.nav-btn');
            navBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    navBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Manejar selección de tarjetas
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    cards.forEach(c => c.classList.remove('selected-item'));
                    this.classList.add('selected-item');
                });
            });
            

            
            // Manejar las pestañas de tratamientos
            const treatmentTabs = document.querySelectorAll('.treatment-tab');
            const treatmentContents = document.querySelectorAll('.treatment-content');
            
            treatmentTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Quitar clase active de todas las pestañas
                    treatmentTabs.forEach(t => t.classList.remove('active'));
                    // Añadir clase active a la pestaña seleccionada
                    this.classList.add('active');
                    
                    // Ocultar todos los contenidos
                    treatmentContents.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Mostrar el contenido correspondiente
                    const category = this.getAttribute('data-category');
                    document.getElementById(category).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>