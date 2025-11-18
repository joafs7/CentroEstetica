<?php
session_start();

// Si no hay sesión, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

// Guardar el nombre en una variable
$nombreUsuario = $_SESSION['usuario'];
// Definir si es admin
// Definir si es admin SOLO si es admin de Kore (id_negocio_admin = 1)
$id_negocio = 1;
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

// --- INICIO: Obtener notificaciones para el admin ---
$notificaciones_no_leidas = 0;
$lista_notificaciones = [];
if ($esAdmin) {
    include_once 'conexEstetica.php';
    $conexion_notif = conectarDB();
    $query_notif = "SELECT id, mensaje, fecha_creacion FROM notificaciones WHERE id_usuario_destino = ? AND id_negocio = ? AND leida = 0 ORDER BY fecha_creacion DESC";
    $stmt_notif = $conexion_notif->prepare($query_notif);
    $stmt_notif->bind_param('ii', $_SESSION['usuario_id'], $id_negocio);
    $stmt_notif->execute();
    $resultado_notif = $stmt_notif->get_result();
    $notificaciones_no_leidas = $resultado_notif->num_rows;
    while($fila = $resultado_notif->fetch_assoc()) $lista_notificaciones[] = $fila;
}
// --- FIN: Obtener notificaciones para el admin ---

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

        /* Estilos súper mejorados para el dropdown de notificaciones */
        .dropdown-menu-notifications {
            border-radius: 0.75rem !important;
            padding: 0 !important;
            border: none !important;
        }
        .dropdown-menu-notifications .dropdown-item {
            transition: background-color 0.2s ease-in-out;
            border-bottom: 1px solid #f8f9fa;
            padding: 1rem 1.25rem; /* Más espaciado */
        }
        .dropdown-menu-notifications .dropdown-item:last-child {
            border-bottom: none;
        }
        .dropdown-menu-notifications .dropdown-item:hover, .dropdown-menu-notifications .dropdown-item:focus {
            background-color: var(--light-pink) !important;
            color: var(--dark-pink) !important;
        }
        .dropdown-menu-notifications .dropdown-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border-top-left-radius: calc(0.75rem - 1px);
            border-top-right-radius: calc(0.75rem - 1px);
            padding: 1rem 1.25rem;
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
            .service-thumb {
                width: 100%;
                max-width: 180px;
                height: 140px;
                object-fit: cover;
                border-radius: 10px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            }
            @media (max-width:768px){ .service-thumb{ max-width:120px; height:100px; } }
                /* Estilos Copiados de Juliette Nails para Historial */
                .filtros {
                    background:white;
                    border-radius:12px;
                    padding:12px;
                    display:flex;
                    flex-wrap:wrap;
                    gap:10px;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:12px;
                }
                .filtros input, .filtros .form-select {
                    border:1px solid var(--primary-color);
                    border-radius:10px;
                    padding:8px 12px;
                    outline:none;
                }
                .filtros input:focus, .filtros .form-select:focus {
                    border-color: var(--dark-pink);
                    box-shadow: 0 0 4px var(--dark-pink);
                }
                .btn-filtrar {
                    background:var(--primary-color);
                    border:none;
                    color:white;
                    padding:8px 20px;
                    border-radius:20px;
                }
                .tabla-reservas {
                    background:white;
                    border-radius:12px;
                    overflow:hidden;
                    padding:0;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
                }
                .tabla-reservas th {
                    background-color: var(--primary-color);
                    color:white;
                    text-align:center;
                    padding:10px;
                }
                .tabla-reservas td {
                    text-align:center;
                    padding:10px;
                    border-bottom:1px solid #eee;
                }
                /* Estilos para el calendario del historial */
                .historial-calendar-container {
                    background: #fff5f5;
                    border-radius: 12px;
                    padding: 15px;
                    border: 1px solid var(--light-pink);
                }
                .historial-calendar-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;
                }
                .historial-calendar-nav-btn {
                    background: var(--primary-color);
                    color: white;
                    border: none;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    cursor: pointer;
                }
                .historial-calendar-grid {
                    display: grid;
                    grid-template-columns: repeat(7, 1fr);
                    gap: 5px;
                }
                .historial-calendar-day, .historial-calendar-day-name {
                    text-align: center;
                    padding: 5px;
                    border-radius: 5px;
                    font-size: 0.8rem;
                }
                .historial-calendar-day-name { font-weight: bold; }
                .historial-calendar-day.has-events {
                    background-color: var(--dark-pink);
                    color: white;
                    font-weight: bold;
                    cursor: pointer;
                }
                .historial-calendar-day.selected {
                    box-shadow: 0 0 0 2px var(--dark-pink);
                }
                .tabla-reservas tr:hover { background-color: #fff5f5; }
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
        <!-- Botón hamburguesa para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Menú colapsable -->
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
                <?php if ($esAdmin): ?>
                <div class="dropdown">
                    <button class="nav-btn position-relative" type="button" id="dropdownNotificaciones" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificaciones_no_leidas > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $notificaciones_no_leidas; ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notifications shadow-lg" aria-labelledby="dropdownNotificaciones" style="width: 380px; max-height: 450px; overflow-y: auto;">
                        <li class="dropdown-header fw-bold">Notificaciones</li>
                        <?php if (!empty($lista_notificaciones)): ?>
                            <?php foreach($lista_notificaciones as $notif): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-start notification-item" href="#" data-id="<?php echo $notif['id']; ?>" data-bs-toggle="modal" data-bs-target="#notificationModal">
                                    <i class="fas fa-calendar-check pink-text me-3 mt-1"></i>
                                    <div>
                                        <small class="text-muted notification-date"><?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?></small>
                                        <p class="mb-0 small lh-sm fw-normal text-wrap notification-message"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="dropdown-item text-muted text-center">No tienes notificaciones nuevas.</li>
                        <?php endif; ?>
                        <li><a class="dropdown-item text-center pink-text small py-2 bg-light" href="#" style="border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem;">Ver todas las notificaciones</a></li>
                    </ul>
                </div>
                <?php endif; ?>
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
                        <div class="col-12">
                         <?php if ($esAdmin): ?>
                        <a href="configuracion.php?id_negocio=<?php echo $id_negocio; ?>" class="btn btn-pink w-100">
                        <i class="fas fa-cog"></i> Configuración
                        </a>
                         <?php endif; ?>
                        </div>
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
$query_corporales = "SELECT nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '10'";
$resultado = mysqli_query($conexion, $query_corporales);
?>
<!--- Contenido de tratamientos corporales -->
<div id="corporales" class="treatment-content active">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <?php if (!empty($row['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" class="service-thumb">
                        <?php else: ?>
                            <i class="fas fa-spa fa-3x pink-text"></i>
                        <?php endif; ?>
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
$query_faciales = "SELECT nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '11'";
$resultado = mysqli_query($conexion, $query_faciales);
?>
            <!-- Contenido de tratamientos faciales -->
<div id="faciales" class="treatment-content">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <?php if (!empty($row['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" class="service-thumb">
                        <?php else: ?>
                            <i class="fas fa-smile fa-3x pink-text"></i>
                        <?php endif; ?>
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
$query_masajes = "SELECT nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '12'";
$resultado = mysqli_query($conexion, $query_masajes);
?>
            <!-- Contenido de masajes -->
<div id="masajes" class="treatment-content">
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top d-flex align-items-center justify-content-center">
                        <?php if (!empty($row['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" class="service-thumb">
                        <?php else: ?>
                            <i class="fas fa-hand-sparkles fa-3x pink-text"></i>
                        <?php endif; ?>
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
            
            // Consulta para obtener los combos/promociones
            $query_combos = "SELECT nombre, precio, descripcion, duracion_minutos, imagen_url FROM combos WHERE id_negocio = 1";
            $resultado_combos = mysqli_query($conexion, $query_combos);
            ?>
            <div class="row g-4 justify-content-center">
                <?php while ($row = mysqli_fetch_assoc($resultado_combos)) { ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card promo-card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" class="service-thumb">
                                <?php else: ?>
                                    <div class="pt-4">
                                        <i class="fas fa-gift fa-3x pink-text"></i>
                                    </div>
                                <?php endif; ?>
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

    <!-- Modal para ver Notificación -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header pink-gradient text-white">
            <h5 class="modal-title" id="notificationModalLabel"><i class="fas fa-bell me-2"></i>Detalle de la Notificación</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small" id="notificationDate"></p>
            <p id="notificationMessage"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Historial de Citas -->
    <div class="modal fade" id="historialModal" tabindex="-1" aria-labelledby="historialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header pink-gradient text-white">
                    <h5 class="modal-title" id="historialModalLabel"><i class="fas fa-history me-2"></i>Historial de Citas (Último Mes)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna del Calendario -->
                        <div class="col-md-4">
                            <h5>Calendario de Citas</h5>
                            <div class="historial-calendar-container mb-3">
                                <div class="historial-calendar-header">
                                    <button class="historial-calendar-nav-btn" id="historial-prev-month">&lt;</button>
                                    <span id="historial-current-month" class="fw-bold"></span>
                                    <button class="historial-calendar-nav-btn" id="historial-next-month">&gt;</button>
                                </div>
                                <div class="historial-calendar-grid" id="historial-calendar-grid"></div>
                            </div>
                            <div class="filtros">
                                <select class="form-select" id="periodoHistorial" style="width:100%;">
                                    <option value="semana" selected>Última semana</option>
                                    <option value="mes">Último mes</option>
                                    <option value="tres_meses">Últimos 3 meses</option>
                                    <option value="todos">Ver todo el historial</option>
                                </select>
                            </div>
                        </div>
                        <!-- Columna de la Tabla -->
                        <div class="col-md-8">
                            <div class="filtros">
                                <input type="text" id="filtro-nombre" placeholder="Buscar por cliente..." class="form-control" style="flex:1; min-width:140px;">
                                <input type="text" id="filtro-servicio" placeholder="Buscar por servicio..." class="form-control" style="flex:1; min-width:140px;">
                                <input type="date" id="filtro-fecha" class="form-control" style="width:160px;">
                            </div>
                            <div class="tabla-reservas">
                                <div class="table-responsive">
                                    <table id="historialTabla" class="table">
                                        <thead>
                                            <tr>
                                            <th>Cliente</th>
                                            <th>Servicio</th>
                                            <th>Categoría</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Precio</th>
                                        </tr>
                                        </thead>
                                        <tbody id="historialTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        // Mover la lógica de carga a su propia función para poder reutilizarla
        function cargarHistorial(periodo) {
                const tbody = document.getElementById('historialTableBody');
                // Limpiar la tabla INMEDIATAMENTE y mostrar el mensaje de carga.
                // Esto asegura que no se vea contenido viejo en ningún momento.
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Cargando historial...</td></tr>'; // Mensaje de carga

            // Hacer la petición AJAX para obtener el historial
            fetch(`obtener_historial.php?periodo=${periodo}&id_negocio=1&_=${new Date().getTime()}`, { 
                method: 'GET',
                cache: 'no-store', // No guardar en caché
                headers: { 'Cache-Control': 'no-cache' }, // Instrucción adicional para proxies y CDNs
                credentials: 'same-origin' 
            })
                .then(async response => {
                    const text = await response.text();
                    // Intentar parsear JSON sólo si es una respuesta con contenido JSON
                    try {
                        const data = text ? JSON.parse(text) : null;
                        if (!response.ok) {
                            const msg = (data && data.error) ? data.error : `Error del servidor (status ${response.status})`;
                            throw new Error(msg);
                        }
                        return data;
                    } catch (e) {
                        // Si no es JSON válido, lanzar error con el texto
                        throw new Error(text || e.message);
                    }
                })
                .then(data => {
                    // Limpiar tabla
                    tbody.innerHTML = '';

                    if (!data || (Array.isArray(data) && data.length === 0)) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No tienes citas en este período.</td></tr>';
                        return;
                    }

                    // Una vez cargados los datos, renderizar el calendario del historial
                    renderHistorialCalendar(data);

                    // Si la API devuelve un objeto con 'error' mostrarlo
                    if (!Array.isArray(data) && data.error) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.error}</td></tr>`;
                        return;
                    }

                    // Rellenar la tabla con los datos y añadir atributos data-* para filtrado robusto
                    data.forEach(cita => {
                        const fechaObj = new Date(cita.fecha_realizacion);
                        const fechaIso = fechaObj.toISOString().split('T')[0]; // YYYY-MM-DD
                        const fechaStr = fechaObj.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
                        const horaStr = fechaObj.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                        const categoria = cita.categoria_nombre || '';
                        const cliente = cita.cliente || '';
                        const servicio = cita.servicio || '';

                        const tr = document.createElement('tr');
                        // Datos para filtrar fácilmente
                        tr.dataset.fechaIso = fechaIso;
                        tr.dataset.cliente = (cliente || '').toLowerCase();
                        tr.dataset.servicio = (servicio || '').toLowerCase();
                        tr.dataset.categoria = (categoria || '').toLowerCase();

                        tr.innerHTML = `
                            <td>${escapeHtml(cliente)}</td>
                            <td>${escapeHtml(servicio)}</td>
                            <td>${escapeHtml(categoria)}</td>
                            <td>${fechaStr}</td>
                            <td>${horaStr}</td>
                            <td>$${formatearPrecio(cita.precio)}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    const msg = (error && error.message) ? error.message : 'Error al cargar el historial.';
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${msg}</td></tr>`;
                });
        }

        // --- Lógica para el calendario del historial ---
        let historialCurrentDate = new Date();

        function renderHistorialCalendar(citas) {
            const calendarGrid = document.getElementById('historial-calendar-grid');
            const currentMonthEl = document.getElementById('historial-current-month');
            if (!calendarGrid || !currentMonthEl) return;

            calendarGrid.innerHTML = '';
            const month = historialCurrentDate.getMonth();
            const year = historialCurrentDate.getFullYear();

            currentMonthEl.textContent = new Date(year, month).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const dayOffset = firstDay === 0 ? 6 : firstDay - 1;

            // Nombres de los días
            ['L', 'M', 'M', 'J', 'V', 'S', 'D'].forEach(dayName => {
                const dayNameEl = document.createElement('div');
                dayNameEl.className = 'historial-calendar-day-name';
                dayNameEl.textContent = dayName;
                calendarGrid.appendChild(dayNameEl);
            });

            // Días vacíos al inicio
            for (let i = 0; i < dayOffset; i++) {
                calendarGrid.appendChild(document.createElement('div'));
            }

            // Fechas con eventos
            const eventDates = citas.map(cita => new Date(cita.fecha_realizacion).toISOString().split('T')[0]);

            // Días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'historial-calendar-day';
                dayEl.textContent = day;
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                if (eventDates.includes(dateStr)) {
                    dayEl.classList.add('has-events');
                    dayEl.addEventListener('click', () => {
                        document.getElementById('filtro-fecha').value = dateStr;
                        filtrarHistorial();
                        document.querySelectorAll('.historial-calendar-day.selected').forEach(d => d.classList.remove('selected'));
                        dayEl.classList.add('selected');
                    });
                }
                calendarGrid.appendChild(dayEl);
            }
        }

        document.getElementById('historial-prev-month')?.addEventListener('click', () => {
            historialCurrentDate.setMonth(historialCurrentDate.getMonth() - 1);
            cargarHistorial(document.getElementById('periodoHistorial').value);
        });

        document.getElementById('historial-next-month')?.addEventListener('click', () => {
            historialCurrentDate.setMonth(historialCurrentDate.getMonth() + 1);
            cargarHistorial(document.getElementById('periodoHistorial').value);
        });

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
        // Pequeña función para escapar HTML en texto recibido del servidor
        function escapeHtml(unsafe) {
            if (!unsafe && unsafe !== 0) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        // Manejar la selección de elementos
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir listeners para filtrar la tabla de historial (igual que Juliette)
            const filtroNombre = document.getElementById('filtro-nombre');
            const filtroServicio = document.getElementById('filtro-servicio');
            const filtroFecha = document.getElementById('filtro-fecha');
            
            function filtrarHistorial() {
                const nombre = (filtroNombre.value || '').toLowerCase().trim();
                const servicio = (filtroServicio.value || '').toLowerCase().trim();
                const fecha = (filtroFecha.value || '').trim(); // formato YYYY-MM-DD

                // Si se borra la fecha del calendario, deseleccionar el día
                if (fecha === '') {
                    document.querySelectorAll('.historial-calendar-day.selected').forEach(d => d.classList.remove('selected'));
                }
                const filas = document.querySelectorAll('#historialTabla tbody tr');
                filas.forEach(fila => {
                    const rowCliente = (fila.dataset.cliente || '').toLowerCase();
                    const rowServicio = (fila.dataset.servicio || '').toLowerCase();
                    const rowCategoria = (fila.dataset.categoria || '').toLowerCase();
                    const rowFechaIso = (fila.dataset.fechaIso || '').trim();

                    const visibleNombre = nombre === '' || rowCliente.includes(nombre);
                    const visibleServicio = servicio === '' || rowServicio.includes(servicio) || rowCategoria.includes(servicio);
                    const visibleFecha = fecha === '' || rowFechaIso === fecha;

                    fila.style.display = (visibleNombre && visibleServicio && visibleFecha) ? '' : 'none';
                });
            }

            // Asignar eventos de 'input' para una respuesta inmediata al borrar
            if (filtroNombre) filtroNombre.addEventListener('input', filtrarHistorial);
            if (filtroServicio) filtroServicio.addEventListener('input', filtrarHistorial);
            if (filtroFecha) filtroFecha.addEventListener('input', filtrarHistorial);

            // Limpiar el filtro de fecha si se cambia el período general
            const periodoSelect = document.getElementById('periodoHistorial');
            periodoSelect.addEventListener('change', function() {
                if(filtroFecha) filtroFecha.value = ''; // Limpiar filtro de fecha
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

            // Manejar clic en notificaciones
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();

                    const notificationId = this.dataset.id;
                    const date = this.querySelector('.notification-date').textContent;
                    const message = this.querySelector('.notification-message').textContent;

                    // Poblar el modal
                    document.getElementById('notificationDate').textContent = date;
                    document.getElementById('notificationMessage').textContent = message;

                    // Marcar como leída vía AJAX
                    fetch('marcar_notificacion_leida.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: notificationId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Eliminar el item de la lista
                            this.closest('li').remove();

                            // Actualizar el contador
                            const badge = document.querySelector('#dropdownNotificaciones .badge');
                            if (badge) {
                                let count = parseInt(badge.textContent);
                                count--;
                                if (count > 0) {
                                    badge.textContent = count;
                                } else {
                                    badge.remove(); // Ocultar el contador si llega a cero
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error al marcar la notificación:', error));
                });
            });
        });
    </script>
</body>
</html>