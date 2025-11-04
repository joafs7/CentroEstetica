<?php
session_start();

// Si no hay sesión, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

include 'conexEstetica.php';
$conexion = conectarDB();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juliette Nails - Reservas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ----- ESTILOS GENERALES ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* CAMBIO: Fondo completo de la página a color claro */
		body {
			background: #fef6fb; /* Color rosa muy claro */
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 20px;
			position: relative;
			overflow-x: hidden;
		}
        
        .container {
            max-width: 1200px;
            width: 100%;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }
        
        /* ----- HEADER MEJORADO ----- */
        header {
            background: linear-gradient(135deg, #f8b6b0 0%, #f472b6 100%);
            color: white;
            padding: 15px 40px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
            width: 100%;
        }
        
        .logo {
            height: 100px;
            width: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            overflow: hidden;
            border: 3px solid white;
        }
        
        .logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 50%;
        }
        
        .logo-text {
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        header p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 5px auto 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            font-style: italic;
        }
        
        /* ----- MENÚ CATEGORÍAS ----- */
        .categories-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            background: #fff5f7;
            border-bottom: 2px solid #fbcfe8;
        }
        
        .category-btn {
            background: linear-gradient(135deg, #f9a8d4 0%, #f472b6 100%);
            color: white;
            border: none;
            padding: 14px 22px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            min-width: 220px;
        }
        
        .category-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .category-btn.active {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            box-shadow: 0 4px 15px rgba(219, 39, 119, 0.4);
        }
        
        .category-btn::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: all 0.5s ease;
        }
        
        .category-btn:hover::after {
            transform: rotate(30deg) translate(20%, 20%);
        }
        
        /* ----- CONTENIDO PRINCIPAL ----- */
        .main-content {
            padding: 40px;
        }
        
        .service-section {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .service-title {
            font-size: 2rem;
            color: #ec4899;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .service-name {
            font-size: 1.8rem;
            color: #db2777;
            font-weight: bold;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        /* ----- SECCIÓN DE SERVICIOS MEJORADA ----- */
        .services-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2f3542;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
            padding: 0 20px;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #ec4899;
            border-radius: 3px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .service-card {
            background: #fdf2f8;
            border-radius: 15px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #fbcfe8;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: #ec4899;
        }
        
        .service-card.selected {
            background: #fff9db;
            border-color: #ffd43b;
            box-shadow: 0 6px 12px rgba(255, 212, 59, 0.3);
        }
        
        .retirado-card {
			background: #e0dbff;
			border-color: #9775fa;
			grid-column: span 1;
		}
        
        .retirado-card.selected {
            background: #d0c3ff;
            border-color: #7950f2;
            box-shadow: 0 6px 12px rgba(121, 80, 242, 0.3);
        }
        
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ec4899;
        }
        
        .retirado-card .service-icon {
            color: #7950f2;
        }
        
        .service-name-card {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .service-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #e67700;
            background: rgba(230, 119, 0, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .retirado-card .service-price {
            color: #5f3dc4;
            background: rgba(95, 61, 196, 0.1);
        }
        
        /* ----- SECCIÓN DE CALENDARIO ----- */
        .calendar-section {
            margin-bottom: 40px;
        }
        
        .calendar-container {
            background: #fdf2f8;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #fbcfe8;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-btn {
            background: #ec4899;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .nav-btn:hover {
            background: #db2777;
            transform: scale(1.1);
        }
        
        .current-month {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3436;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        
        .day-name {
            text-align: center;
            padding: 12px 0;
            font-weight: 600;
            color: #495057;
            background: #fce7f3;
            border-radius: 8px;
        }
        
        .calendar-day {
            text-align: center;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            border: 2px solid transparent;
            font-weight: 500;
        }
        
        .calendar-day:hover {
            background: #fbcfe8;
        }
        
        .calendar-day.available {
            background: #fbcfe8;
            color: #db2777;
        }
        
        .calendar-day.selected {
            background: #ec4899;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(236, 72, 153, 0.4);
        }
        
        .calendar-day.unavailable {
            background: #fdf2f8;
            color: #f9a8d4;
            cursor: not-allowed;
        }
        
        .calendar-day.weekend {
            background: #ffe8cc;
            color: #e67700;
        }
        
        /* ----- SECCIÓN DE HORARIOS ----- */
        .time-slots-section {
            margin-top: 30px;
        }
        
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .time-slot {
            background: #fce7f3;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #fbcfe8;
            font-weight: 600;
            color: #495057;
        }
        
        .time-slot:hover {
            background: #fbcfe8;
        }
        
        .time-slot.selected {
            background: #f9a8d4;
            color: #831843;
            border-color: #ec4899;
        }
        
        .time-slot.unavailable {
            background: #fdf2f8;
            color: #f9a8d4;
            cursor: not-allowed;
        }
        
        /* ----- SECCIÓN DE RESUMEN ----- */
        .resumen-section {
            background: #fdf2f8;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 2px dashed #fbcfe8;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        .resumen-title {
            text-align: center;
            margin-bottom: 20px;
            color: #2d3436;
            font-size: 1.4rem;
            position: relative;
        }
        
        .resumen-title::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: #ec4899;
            border-radius: 3px;
        }
        
        .resumen-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #fbcfe8;
        }
        
        .resumen-label {
            font-weight: 600;
            color: #495057;
        }
        
        .resumen-value {
            font-weight: 600;
            color: #2d3436;
        }
        
        .resumen-total {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #ec4899;
            font-size: 1.3rem;
            font-weight: bold;
            color: #ec4899;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-confirm {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(236, 72, 153, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(236, 72, 153, 0.6);
        }
        
        .btn-confirm:active {
            transform: translateY(0);
        }
        
        .btn-confirm::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: all 0.5s ease;
        }
        
        .btn-confirm:hover::after {
            transform: rotate(30deg) translate(20%, 20%);
        }
        
        /* ----- FOOTER ----- */
        footer {
            background: #2f3542;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            position: relative;
        }
        
        /* ----- MODAL DE CONFIRMACIÓN ----- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .confirmation-modal {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            padding: 40px 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            transform: translateY(30px);
            opacity: 0;
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        
        .modal-overlay.active .confirmation-modal {
            transform: translateY(0);
            opacity: 1;
        }
        
        .confirmation-modal::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #ec4899, #db2777);
        }
        
        .modal-title {
            text-align: center;
            color: #ec4899;
            font-size: 2rem;
            margin-bottom: 25px;
            position: relative;
        }
        
        .modal-title i {
            display: block;
            font-size: 3rem;
            margin-bottom: 15px;
            color: #db2777;
        }
        
        .modal-content {
            padding: 20px 0;
            border-top: 1px solid #fbcfe8;
            border-bottom: 1px solid #fbcfe8;
            margin: 20px 0;
        }
        
        .modal-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #fbcfe8;
        }
        
        .modal-label {
            font-weight: 600;
            color: #495057;
        }
        
        .modal-value {
            font-weight: 600;
            color: #2d3436;
            text-align: right;
        }
        
        .modal-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.4rem;
            font-weight: bold;
            color: #ec4899;
            padding: 15px 0;
            margin-top: 10px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 20px;
            margin-top: 25px;
        }
        
        .modal-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-confirm-modal {
            background: linear-gradient(135deg, #40c057 0%, #2f9e44 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(64, 192, 87, 0.4);
        }
        
        .btn-modify {
            background: linear-gradient(135deg, #4dabf7 0%, #228be6 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(77, 171, 247, 0.4);
        }
        
        .modal-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            background: #fdf2f8;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #495057;
            font-size: 1.2rem;
        }
        
        .modal-close:hover {
            background: #fbcfe8;
            transform: rotate(90deg);
        }
        
        /* ----- MEDIA QUERIES (RESPONSIVE) ----- */
        @media (max-width: 768px) {
            .main-content {
                padding: 25px;
            }
            
            .categories-menu {
                flex-direction: column;
                align-items: center;
            }
            
            .category-btn {
                width: 100%;
                margin-bottom: 10px;
                min-width: auto;
            }
            
            .services-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }
            
            .calendar-grid {
                gap: 5px;
            }
            
            .day-name, .calendar-day {
                padding: 8px 0;
                font-size: 0.9rem;
            }
            
            .modal-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .logo {
                height: 85px;
                width: 85px;
            }
            
            .logo img {
                width: 75px;
                height: 75px;
            }
            
            .logo-text {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 480px) {
            header {
                padding: 15px;
            }
            
            .logo {
                height: 75px;
                width: 75px;
            }
            
            .logo img {
                width: 65px;
                height: 65px;
            }
            
            .logo-text {
                font-size: 1.8rem;
            }
            
            .service-title {
                font-size: 1.6rem;
            }
            
            .service-name {
                font-size: 1.4rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .resumen-total {
                font-size: 1.1rem;
            }
            
            .category-btn {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }

        .card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
    background: #fdf2f8;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.pink-text {
    color: #ec4899;
}

.btn-pink {
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 25px;
    transition: 0.3s;
}

.btn-pink:hover {
    background: #db2777;
    transform: translateY(-2px);
    color: white;
}
    </style>
</head>
<body>
<div class="container">
<div class="container">
        <header>
            <div class="logo-container">
                <div class="logo">
                    <!-- Logo de muestra - reemplazar con tu logo -->
						<img src="C:\Users\Usuario\Desktop\Proyecto Estética 2025\img\Juliette\logo_juliette.jpg" alt="Juliette Nails Logo"></div>
                <div class="logo-text">Juliette Nails</div>
                <p>Tu belleza es nuestra pasión</p>
            </div>
        </header>
        
        <!-- Menú de categorías -->
        <div class="categories-menu">
            <button class="category-btn active" data-category="esmaltado">Esmaltado Semipermanente</button>
            <button class="category-btn" data-category="softgel">Soft Gel</button>
            <button class="category-btn" data-category="capping-polygel">Capping en Poly Gel</button>
            <button class="category-btn" data-category="capping">Capping</button>
        </div>
        
        <div class="main-content">
            <div class="service-section">
                <div class="service-title">Servicio a realizar:</div>
                <div class="service-name">Esmaltado Semipermanente</div>
            </div>  

<div id="esmaltado" class="services-section">
    <h2 class="section-title"></h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
        <?php
        // Consulta SQL para obtener servicios
        $query = "SELECT id, nombre, precio FROM servicios WHERE categoria_id = '8' AND id_negocio = '2'";
        $result = mysqli_query($conexion, $query);

        while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>">
                    <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                        <i class="fas fa-palette fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                </div>
                </div>
            </div>
        <?php }
        mysqli_free_result($result); 
        ?>
    </div>
</div>
<div id="softgel" class="services-section" style="display: none;">
    <h2 class="section-title"></h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
        <?php
        // Consulta SQL para obtener servicios
        $query = "SELECT id, nombre, precio FROM servicios WHERE categoria_id = '9' AND id_negocio = '2'";
        $result = mysqli_query($conexion, $query);

        while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>">
                    <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                        <i class="fas fa-gem fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        
                    </div>
                </div>
            </div>
        <?php }
        mysqli_free_result($result); 
        ?>
    </div>
</div>

<div id="capping-polygel" class="services-section" style="display: none;">
    <h2 class="section-title"></h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
        <?php
        // Consulta SQL para obtener servicios
        $query = "SELECT id, nombre, precio FROM servicios WHERE categoria_id = '7' AND id_negocio = '2'";
        $result = mysqli_query($conexion, $query);

        while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>">
                    <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                        <i class="fas fa-hand-sparkles fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        
                    </div>
                </div>
            </div>
        <?php }
        mysqli_free_result($result); 
        ?>
    </div>
</div>
<div id="capping" class="services-section" style="display: none;">
    <h2 class="section-title"></h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
        <?php
        // Consulta SQL para obtener servicios
        $query = "SELECT id, nombre, precio FROM servicios WHERE categoria_id = '6' AND id_negocio = '2'";
        $result = mysqli_query($conexion, $query);

        while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>">
                    <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                        <i class="fas fa-shield-alt fa-3x pink-text"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                        <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                        <br>
                        
                    </div>
                </div>
            </div>
        <?php }
        mysqli_free_result($result); 
        ?>
    </div>
</div>

                
                <!-- Servicio adicional de retirado - ahora integrado en la misma cuadrícula -->
                <div class="services-grid">
                    <div class="service-card retirado-card" id="retirado-card">
                        <div class="service-icon">
                            <i class="fas fa-eraser"></i>
                        </div>
                        <div class="service-name-card">Retirado</div>
                        <div class="service-price" id="retirado-price">$3000</div>
                    </div>
                </div>
            </div>
            
            <!-- Aquí irá tu código PHP para mostrar los servicios desde la BD -->
            
            <!-- Sección de calendario -->
            <div class="calendar-section">
                <h2 class="section-title">Selecciona una fecha</h2>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button class="nav-btn" id="prev-month">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="current-month" id="current-month"></div>
                            <button class="nav-btn" id="next-month">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        <!-- Se generará con JavaScript -->
                    </div>
                </div>
                
                <div class="time-slots-section">
                    <h2 class="section-title">Horarios disponibles</h2>
                    <div class="time-slots" id="time-slots"></div>
                </div>
            </div>
        </div>
        
        <footer>
            <p>© 2025 Juliette Nails | Todos los derechos reservados</p>
            <p>Horario de atención: Lunes a Viernes de 8:00 a 12:00 y 16:00 a 20:00</p>
        </footer>
</div>
</div>
<div class="modal-container">
    <div class="modal-overlay" id="confirmation-modal">
        <div class="confirmation-modal">
            <div class="modal-close" id="modal-close-btn">
                <i class="fas fa-times"></i>
            </div>
            <br>
            <h2 class="modal-title">
                <i class="fas fa-calendar-check"></i>
                Confirmar Reserva
            </h2>
            
            <div class="modal-content">
                <!-- Contenido del modal -->
            </div>
            
            <div class="modal-buttons">
                <button class="modal-btn btn-confirm-modal" id="modal-confirm-btn">
                    <i class="fas fa-check-circle"></i> Confirmar
                </button>
                <button class="modal-btn btn-modify" id="modal-modify-btn">
                    <i class="fas fa-edit"></i> Modificar
                </button>
            </div>
        </div>
    </div>
</div>
       
    <script>
        // Variables globales para fecha y hora
    let selectedService = null;
    let selectedDate = null;
    let selectedTime = null;
    let isRetiradoSelected = false;
    const retiradoPrice = 3000;

        
   document.addEventListener('DOMContentLoaded', function() {
    // Manejo de categorías
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            
            // Obtener la categoría seleccionada
            const categoria = this.dataset.category;
            
            // Ocultar todas las secciones
            document.querySelectorAll('.services-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Mostrar la sección correspondiente
            document.getElementById(categoria).style.display = 'block';
            
            // Actualizar el título del servicio
            const categoryName = this.textContent;
            document.querySelector('.service-name').textContent = categoryName;
        });
    });
    
    
    // Referencias DOM adicionales
    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'btn-confirm';
    confirmBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirmar Reserva';
    
    // Configurar el botón de retirado
    const retiradoCard = document.getElementById('retirado-card');
    retiradoCard.addEventListener('click', function() {
        this.classList.toggle('selected');
        isRetiradoSelected = this.classList.contains('selected');
        updateResumen();
    });

    // Función para actualizar el resumen
    function updateResumen() {
        let total = 0;
        let resumenHtml = '';

        if (selectedService) {
            const precio = parseInt(selectedService.precio.replace(/[^0-9]/g, ''));
            total += precio;
            resumenHtml += `
                <div class="modal-item">
                    <span class="modal-label">Servicio:</span>
                    <span class="modal-value">${selectedService.nombre}</span>
                </div>
                <div class="modal-item">
                    <span class="modal-label">Precio:</span>
                    <span class="modal-value">${selectedService.precio}</span>
                </div>
            `;
        }

        if (isRetiradoSelected) {
            total += retiradoPrice;
            resumenHtml += `
                <div class="modal-item">
                    <span class="modal-label">Adicional:</span>
                    <span class="modal-value">Retirado</span>
                </div>
                <div class="modal-item">
                    <span class="modal-label">Precio adicional:</span>
                    <span class="modal-value">$${retiradoPrice}</span>
                </div>
            `;
        }

        if (selectedDate) {
            resumenHtml += `
                <div class="modal-item">
                    <span class="modal-label">Fecha:</span>
                    <span class="modal-value">${selectedDate.toLocaleDateString()}</span>
                </div>
            `;
        }

        if (selectedTime) {
            resumenHtml += `
                <div class="modal-item">
                    <span class="modal-label">Hora:</span>
                    <span class="modal-value">${selectedTime}:00</span>
                </div>
            `;
        }

        resumenHtml += `
            <div class="modal-total">
                <span class="modal-label">Total:</span>
                <span class="modal-value">$${total}</span>
            </div>
        `;

        document.querySelector('.modal-content').innerHTML = resumenHtml;
    }

    // Modificar la función setupServiceCards
    function setupServiceCards() {
        const serviceCards = document.querySelectorAll('.service-card:not(.retirado-card)');
        serviceCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección previa
                serviceCards.forEach(c => c.classList.remove('selected'));
                
                // Agregar clase selected a la tarjeta clickeada
                this.classList.add('selected');

                // Obtener datos del servicio
                selectedService = {
                    id: this.dataset.id,
                    nombre: this.querySelector('.service-name-card').textContent,
                    precio: this.querySelector('.service-price').textContent
                };

                // Actualizar título
                document.querySelector('.service-name').textContent = selectedService.nombre;
                
                // Agregar botón confirmar si no existe
                if (!document.querySelector('.btn-confirm')) {
                    document.querySelector('.calendar-section').after(confirmBtn);
                }

                updateResumen();
            });
        });
    }

    // Configurar el botón de confirmar
     confirmBtn.addEventListener('click', () => {
        console.log('Estado actual:', {
            servicio: selectedService,
            fecha: selectedDate,
            hora: selectedTime
        });

        if (!selectedService) {
            alert('Por favor selecciona un servicio');
            return;
        }
        if (!selectedDate) {
            alert('Por favor selecciona una fecha');
            return;
        }
        if (!selectedTime) {
            alert('Por favor selecciona un horario');
            return;
        }
        showConfirmationModal();
        updateResumen();
    });

    setupServiceCards();
});
        // Referencias DOM
     const calendarGrid = document.querySelector('.calendar-grid');
    const currentMonthEl = document.getElementById('current-month');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const timeSlotsEl = document.getElementById('time-slots');
    const modalOverlay = document.getElementById('confirmation-modal');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');
    const modalModifyBtn = document.getElementById('modal-modify-btn');
    const modalCloseBtn = document.getElementById('modal-close-btn');
        
        // Fecha actual
        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        
        // Inicializar calendario
        function initCalendar() {
            renderCalendar(currentMonth, currentYear);
        }
        
        // Event listeners para la navegación del calendario
prevMonthBtn.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar(currentMonth, currentYear);
});

nextMonthBtn.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar(currentMonth, currentYear);
});

        // Renderizar calendario
        function renderCalendar(month, year) {
                const maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 3);
    
    if (new Date(year, month) > maxDate) {
        currentMonth = maxDate.getMonth();
        currentYear = maxDate.getFullYear();
        month = currentMonth;
        year = currentYear;
    }
            while (calendarGrid.children.length > 7) {
                calendarGrid.removeChild(calendarGrid.lastChild);
            }
            
            const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                               "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            currentMonthEl.textContent = `${monthNames[month]} ${year}`;
            
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const firstDayOfWeek = firstDay.getDay();
            
            let dayOffset = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
            
            for (let i = 0; i < dayOffset; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.classList.add('calendar-day', 'unavailable');
                calendarGrid.appendChild(emptyDay);
            }
            
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dayElement = document.createElement('div');
                dayElement.classList.add('calendar-day');
                dayElement.textContent = day;
                
                const dayOfWeek = date.getDay();
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    dayElement.classList.add('weekend', 'unavailable');
                } else {
                    if (date < today && date.toDateString() !== today.toDateString()) {
                        dayElement.classList.add('unavailable');
                    } else {
                        dayElement.classList.add('available');
                        dayElement.addEventListener('click', () => selectDate(date));
                    }
                }
                
                if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                    dayElement.classList.add('selected');
                }
                
                calendarGrid.appendChild(dayElement);
            }
        }
        
        // Seleccionar una fecha
         function selectDate(date) {
        selectedDate = date;
        selectedTime = null;
        console.log('Fecha seleccionada:', selectedDate);
        
        renderCalendar(currentMonth, currentYear);
        generateTimeSlots();
        updateResumen();
    }
        
        // Generar horarios disponibles
        function generateTimeSlots() {
            timeSlotsEl.innerHTML = '';
            
            if (!selectedDate) return;
            
            const isToday = selectedDate.toDateString() === today.toDateString();
            const currentHour = new Date().getHours();
            
            // Horarios de mañana
            for (let hour = 8; hour <= 11; hour++) {
                if (isToday && hour < currentHour) continue;
                createTimeSlot(hour);
            }
            
            // Horarios de tarde
            for (let hour = 16; hour <= 19; hour++) {
                if (isToday && hour < currentHour) continue;
                createTimeSlot(hour);
            }
        }
        
        // Crear slot de horario
        function createTimeSlot(hour) {
            const timeSlot = document.createElement('div');
            timeSlot.classList.add('time-slot');
            timeSlot.textContent = `${hour}:00`;
            
            if (selectedTime === hour) {
                timeSlot.classList.add('selected');
            }
            
            timeSlot.addEventListener('click', () => {
                selectedTime = hour;
                document.querySelectorAll('.time-slot').forEach(slot => {
                    slot.classList.remove('selected');
                });
                timeSlot.classList.add('selected');
            });
            
            timeSlotsEl.appendChild(timeSlot);
        }

        // Mostrar modal de confirmación
        function showConfirmationModal() {
            modalOverlay.classList.add('active');
        }
        
        // Cerrar modal
        function closeConfirmationModal() {
            modalOverlay.classList.remove('active');
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', initCalendar);
        
        modalConfirmBtn.addEventListener('click', () => {
     if (!selectedService || !selectedDate || !selectedTime) {
        alert('Por favor selecciona servicio, fecha y hora');
        return;
    }

    const fechaFormateada = selectedDate.toISOString().split('T')[0];
    const horaFormateada = `${selectedTime}:00`;
    
    const datos = {
        fecha: fechaFormateada,
        hora: horaFormateada,
        servicio_id: parseInt(selectedService.id),
        usuario_id: <?php echo $_SESSION['usuario_id']; ?>,
        retirado: isRetiradoSelected ? 1 : 0
    };

    console.log('Enviando datos:', datos); // Para debugging

     fetch('guardar_reserva.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(async response => {
        const text = await response.text();
        console.log('Respuesta del servidor:', text);
        
        try {
            const data = JSON.parse(text);
        if (data.success) {
            alert('Reserva guardada exitosamente');
            closeConfirmationModal();

            // Eliminar el horario seleccionado del DOM
            if (selectedTime !== null) {
                document.querySelectorAll('.time-slot').forEach(slot => {
                    if (slot.textContent === `${selectedTime}:00`) {
                        slot.remove();
                    }
                });
                selectedTime = null; // Limpiar selección
            }
            } else {
                // Mostrar mensaje específico si el horario está ocupado
                if (data.message.includes('Ya existe una reserva')) {
                    alert(data.message);
                    // Regenerar horarios disponibles
                    generateTimeSlots();
                } else {
                    alert('Error al guardar la reserva: ' + data.message);
                }
            }
        } catch (e) {
            throw new Error('Respuesta no válida del servidor: ' + text);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud: ' + error.message);
    });
        });
        
        modalModifyBtn.addEventListener('click', closeConfirmationModal);
        modalCloseBtn.addEventListener('click', closeConfirmationModal);
    </script>
</body>
</html>