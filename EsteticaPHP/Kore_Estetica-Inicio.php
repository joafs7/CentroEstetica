<?php
session_start();

// Si no hay sesión, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

// Guardar el nombre en una variable
$nombreUsuario = $_SESSION['usuario_nombre'];
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
                        <button class="nav-btn active">Inicio</button>
                        <button class="nav-btn">Servicios</button>
                        <button class="nav-btn">Galeria</button>
                        <button class="nav-btn">Contacto</button>
                       
                    </div>
                </div>
            </div>
        </nav>

        <!-- Menú offcanvas -->
        <div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="staticBackdropLabel">Menú KORE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-home me-3 pink-text"></i> Inicio
                    </li>
                    <li class="list-group-item d-flex align-items-center selected-item">
                        <i class="fas fa-spa me-3 pink-text"></i> Servicios
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-images me-3 pink-text"></i> Galería
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-tags me-3 pink-text"></i> Promociones
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-shopping-bag me-3 pink-text"></i> Productos
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-phone me-3 pink-text"></i> Contacto
                    </li>
                </ul>
            </div>
        </div>

        <!-- Sección Hero -->


<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold">Bienvenida <?php echo htmlspecialchars($nombreUsuario); ?> a Kore Estética Corporal</h1>
        <p class="lead">Un espacio diseñado para que puedas relajarte y disfrutar de tratamientos que le hacen bien a tu cuerpo</p>
        <button class="btn btn-pink btn-lg mt-3" onclick="window.location.href='reservas-kore.php'">
            <i class="fas fa-calendar-check me-2"></i> Reservar turno
        </button>
    </div>
</section>


        <!-- Quiénes somos -->
        <section class="text-center py-5">
            <div class="light-pink-bg p-5 rounded-4">
                <h3 class="pink-text"><i class="fas fa-heart me-2"></i> Quiénes somos?</h3>
                <p class="fs-5">Beauty Kore Estética y Bienestar es un espacio diseñado y preparado para que puedas relajarte y disfrutar de los tratamientos que le hacen bien a tu cuerpo. Nuestro equipo de profesionales está comprometido con tu bienestar y belleza.</p>
            </div>
        </section>

        <!-- Tratamientos -->
        <section class="py-4">
            <div class="perfect-center">
                <h2 class="section-title">Nuestros Tratamientos</h2>
                <p class="text-muted">Selecciona una categoría para ver los tratamientos disponibles</p>
            </div>

            <!-- Pestañas de categorías -->
            <div class="treatment-tabs">
                <button class="treatment-tab active" data-category="corporales">Tratamientos Corporales</button>
                <button class="treatment-tab" data-category="faciales">Tratamientos Faciales</button>
                <button class="treatment-tab" data-category="masajes">Masajes</button>
                <button class="treatment-tab" data-category="combos">Combos Especiales</button>
            </div>

            <!-- Contenido de tratamientos corporales -->
            <div id="corporales" class="treatment-content active">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-wind fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Presoterapia para piernas cansadas</h5>
                                <p class="card-text">Tratamiento para aliviar piernas cansadas y mejorar circulación</p>
                                <div class="service-price">$8000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-running fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Sesión intensiva en piernas</h5>
                                <p class="card-text">Tratamiento completo para piernas con múltiples técnicas</p>
                                <div class="service-price">$12000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-wave-square fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Radiofrecuencia corporal</h5>
                                <p class="card-text">Reafirmación y reducción de medidas con tecnología</p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-wind fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Vacumterapia</h5>
                                <p class="card-text">Terapia de vacío para reducción localizada</p>
                                <div class="service-price">$8000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-microchip fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Aparatología</h5>
                                <p class="card-text">Tratamiento con equipos especializados</p>
                                <div class="service-price">$8500</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de tratamientos faciales -->
            <div id="faciales" class="treatment-content">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-syringe fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Tratamiento facial dermapen</h5>
                                <p class="card-text">Rejuvenecimiento con microagujas</p>
                                <div class="service-price">$10000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-bolt fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Radiofrecuencia facial</h5>
                                <p class="card-text">Estimulación de colágeno con radiofrecuencia</p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-lightbulb fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Máscara LED</h5>
                                <p class="card-text">Terapia con luz para diferentes condiciones cutáneas</p>
                                <div class="service-price">$8000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de masajes -->
            <div id="masajes" class="treatment-content">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-baby fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masaje para embarazadas</h5>
                                <p class="card-text">Masaje especializado para gestantes</p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-compress fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masajes reductores</h5>
                                <p class="card-text">Técnicas para reducción de medidas</p>
                                <div class="service-price">$8000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-hands fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masajes relajantes espalda/brazos/cuello</h5>
                                <p class="card-text">Alivio de tensiones en zona superior</p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-shoe-prints fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masajes relajantes piernas/pies</h5>
                                <p class="card-text">Relajación profunda para miembros inferiores</p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masaje cuerpo completo</h5>
                                <p class="card-text">Experiencia de relajación integral</p>
                                <div class="service-price">$12000</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-massage fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Masaje de vacum</h5>
                                <p class="card-text">Combinación de masaje y tecnología de vacío</p>
                                <div class="service-price">$8500</div>
                                <div class="service-duration">Duración: 60 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de combos especiales -->
            <div id="combos" class="treatment-content">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-dumbbell fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Sesión Booty Up</h5>
                                <p class="card-text">Tratamiento completo para glúteos</p>
                                <p class="card-text"><small>Incluye: Vacumterapia, Aparatología y Masaje de levantamiento</small></p>
                                <div class="service-price">$8200</div>
                                <div class="service-duration">Duración: 180 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-weight-scale fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Reducción Intensiva</h5>
                                <p class="card-text">Programa completo para reducción de medidas</p>
                                <p class="card-text"><small>Incluye: Masajes reductores, Aparatología/electrodos y Radiofrecuencia corporal</small></p>
                                <div class="service-price">$12000</div>
                                <div class="service-duration">Duración: 180 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-face-smile fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Jornadas Antiarrugas</h5>
                                <p class="card-text">Plan completo antienvejecimiento</p>
                                <p class="card-text"><small>Incluye: 1 sesión de dermapen facial + 3 sesiones de radiofrecuencia facial</small></p>
                                <div class="service-price">$16000</div>
                                <div class="service-duration">Duración: 240 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-battery-full fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Electrodos + Masajes reductores</h5>
                                <p class="card-text">Combo para modelado corporal</p>
                                <p class="card-text"><small>Incluye: Aparatología/electrodos y Masajes reductores</small></p>
                                <div class="service-price">$9000</div>
                                <div class="service-duration">Duración: 120 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-top d-flex align-items-center justify-content-center">
                                <i class="fas fa-walking fa-3x pink-text"></i>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Sesión Intensiva de piernas</h5>
                                <p class="card-text">Tratamiento completo para piernas</p>
                                <p class="card-text"><small>Incluye: Presoterapia, Masajes reductores y Radiofrecuencia corporal</small></p>
                                <div class="service-price">$12000</div>
                                <div class="service-duration">Duración: 180 minutos</div>
                                <a href="reservas-kore.html" class="btn btn-pink mt-3">Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Promociones -->
        <section class="py-5">
            <h2 class="section-title">Promociones Especiales</h2>
            
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card promo-card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title pink-text">Combo Reductor</h5>
                            <p class="card-text">
                                <i class="fas fa-spa pink-text me-2"></i> Masaje Reductores<br>
                                <i class="fas fa-bolt pink-text me-2"></i> Electrodos<br>
                                <i class="fas fa-wave-square pink-text me-2"></i> Radiofrecuencia
                            </p>
                            <p class="text-muted">Sesión de una hora y media</p>
                            <h4 class="promo-price">$4500</h4>
                            <a href="reservas-kore.html" class="btn btn-pink mt-3">Reservar ahora</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card promo-card h-100 selected-item">
                        <div class="card-body text-center">
                            <h5 class="card-title pink-text">Combo Piernas</h5>
                            <p class="card-text">
                                <i class="fas fa-wind pink-text me-2"></i> Presoterapia<br>
                                <i class="fas fa-hands pink-text me-2"></i> Masajes<br>
                                <i class="fas fa-wave-square pink-text me-2"></i> Radiofrecuencia
                            </p>
                            <p class="text-muted">Sesión de una hora y media</p>
                            <h4 class="promo-price">$4500</h4>
                            <a href="reservas-kore.html" class="btn btn-pink mt-3">Reservar ahora</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card promo-card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title pink-text">Doble Aparatología</h5>
                            <p class="card-text">
                                Combinación de dos aparatologías según tu necesidad
                            </p>
                            <p class="text-muted">En la misma zona o diferentes</p>
                            <h4 class="promo-price">$3800</h4>
                            <a href="reservas-kore.html" class="btn btn-pink mt-3">Reservar ahora</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Productos Corporales -->
        <section class="py-5">
            <h2 class="section-title">Productos Corporales</h2>
            
            <div class="light-pink-bg p-4 rounded-4">
                <table class="product-table">
                    <tr class="selected-item">
                        <td style="width: 100px">
                            <div class="product-img-container">
                                <img src="img/2.jpg" alt="Cellugel" class="product-img">
                            </div>
                        </td>
                        <td>Cellugel - Gel para celulitis 150ml</td>
                        <td class="pink-text fw-bold">$2400</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/7.jpg" alt="Prodermic" class="product-img">
                            </div>
                        </td>
                        <td>Prodermic - Lipo Hot cream 150ml</td>
                        <td class="pink-text fw-bold">$2100</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/5.jpg" alt="Super Fresh Body" class="product-img">
                            </div>
                        </td>
                        <td>Super Fresh Body - Gel frío tonificante 150ml</td>
                        <td class="pink-text fw-bold">$1900</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/3.jpg" alt="Hot Body Home" class="product-img">
                            </div>
                        </td>
                        <td>Hot Body Home - Gel de esculpido corporal 150ml</td>
                        <td class="pink-text fw-bold">$2500</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/1.jpg" alt="Legs Calm" class="product-img">
                            </div>
                        </td>
                        <td>Legs Calm - Crema relajante de piernas 250 g</td>
                        <td class="pink-text fw-bold">$2700</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/4.jpg" alt="Hot body intense" class="product-img">
                            </div>
                        </td>
                        <td>Hot body intense - Gel termoactivo para Gym 150 ml</td>
                        <td class="pink-text fw-bold">$2300</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="product-img-container">
                                <img src="img/6.jpg" alt="Firmo cream" class="product-img">
                            </div>
                        </td>
                        <td>Firmo cream - Crema anti glicación para flacidez 150 ml</td>
                        <td class="pink-text fw-bold">$2900</td>
                        <td><button class="btn btn-pink">Comprar</button></td>
                    </tr>
                </table>
            </div>
        </section>

        <!-- Contacto -->
        <section class="contact-section">
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

    <script>
        // Manejar la selección de elementos
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Manejar selección en la tabla de productos
            const tableRows = document.querySelectorAll('.product-table tr');
            tableRows.forEach(row => {
                row.addEventListener('click', function() {
                    tableRows.forEach(r => r.classList.remove('selected-item'));
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