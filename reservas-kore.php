<?php
session_start();

// Si no hay sesión, muestra un modal y espera a que el usuario haga clic
if (!isset($_SESSION['usuario_id'])) {
    // No redirigimos automáticamente, permitimos que la página cargue con el modal
    $mostrar_modal_login = true;
}

include 'conexEstetica.php';
$conexion = conectarDB();

// Obtener id_negocio del parámetro GET o de sesión
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kore Estética Corporal - Reservas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ----- ESTILOS GENERALES ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
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
        
        /* ----- HEADER ----- */
        header {
            background: linear-gradient(135deg, #e89c94 0%, #f6b8b3 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
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
        
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .btn-home {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-home:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.1);
        }
        
        /* ----- MENÚ CATEGORÍAS ----- */
        .categories-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            background: #fadcd9;
            border-bottom: 2px solid #f6b8b3;
        }
        
        .category-btn {
            background: linear-gradient(135deg, #e89c94 0%, #f6b8b3 100%);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .category-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .category-btn.active {
            background: linear-gradient(135deg, #f6b8b3 0%, #e89c94 100%);
            box-shadow: 0 4px 15px rgba(230, 156, 148, 0.4);
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
            margin-bottom: 40px;
            position: relative;
        }
        
        .service-title {
            font-size: 2rem;
            color: #f6b8b3;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .service-name {
            font-size: 1.8rem;
            color: #e89c94;
            font-weight: bold;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        /* ----- SECCIÓN DE SERVICIOS ----- */
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
            background: #e89c94;
            border-radius: 3px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .service-card {
            background: #fadcd9;
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #f6b8b3;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: #e89c94;
        }
        
        .service-card.selected {
            background: #f6b8b3;
            border-color: #d8706a;
            box-shadow: 0 6px 12px rgba(216, 112, 106, 0.4);
        }

        .service-card.selected::after {
            content: '\f00c'; /* Código del ícono de check de Font Awesome */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900; /* Necesario para los íconos sólidos de FA6 */
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.2rem;
            color: #2f9e44; /* Un verde para el check */
            background: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transform: scale(0);
            animation: pop-in 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
        }

        @keyframes pop-in {
            to {
                transform: scale(1);
            }
        }
        
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #f6b8b3;
        }
        
        .service-name-card {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 10px;
        }
        
        .service-details {
            font-size: 0.95rem;
            color: #495057;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .service-price {
            font-size: 1.3rem;
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
            color: #e89c94;
            font-weight: 600;
            margin-top: 10px;
        }
        
        /* ----- SECCIÓN DE CALENDARIO ----- */
        .calendar-section {
            margin-bottom: 40px;
        }
        
        .calendar-container {
            background: #fadcd9;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f6b8b3;
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
            background: #e89c94;
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
            background: #f6b8b3;
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
            background: #f6b8b3;
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
            background: #f6b8b3;
        }
        
        .calendar-day.available {
            background: #f6b8b3;
            color: #2d3436;
        }
        
        .calendar-day.selected {
            background: #f6b8b3;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(246, 184, 179, 0.4);
        }
        
        .calendar-day.unavailable {
            background: #fadcd9;
            color: #f6b8b3;
            cursor: not-allowed;
        }
        
        .calendar-day.weekend {
            background: #ffe8cc;
            color: #e67700;
        }
        
        /* ----- SECCIÓN DE HORARIOS ----- */
        .time-slots-section {
            text-align: center;
            margin-top: 30px;
        }

        .time-slots-section h3 {
            font-size: 1.5rem;
            color: #2f3542;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .time-slots-section h3::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #e89c94;
            border-radius: 3px;
        }
        
        .time-slots {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .time-slot {
            background: #f6b8b3;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #f6b8b3;
            font-weight: 600;
            color: #495057;
        }
        
        .time-slot:hover {
            background: #f6b8b3;
        }
        
        .time-slot.selected {
            background: #e89c94;
            color: white;
            border-color: #f6b8b3;
        }
        
        .time-slot.unavailable {
            background: #fadcd9;
            color: #f6b8b3;
            cursor: not-allowed;
        }
        
        .time-slot.auto-disabled {
            background: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
            position: relative;
            border-color: #dee2e6;
        }

        .time-slot.auto-disabled::after {
            content: '\f023'; /* Ícono de candado de Font Awesome */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            color: rgba(0, 0, 0, 0.2);
        }

        .time-slots-group {
            flex: 1;
            min-width: 250px;
            background: rgba(255, 255, 255, 0.5);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #f6b8b3;
        }

        .time-slots-group h4 {
            font-size: 1.2rem;
            color: #e89c94;
            margin-bottom: 15px;
        }
        
        /* ----- SECCIÓN DE RESUMEN ----- */
        .resumen-section {
            background: #fadcd9;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 2px dashed #f6b8b3;
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
            background: #e89c94;
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
            border-bottom: 1px solid #f6b8b3;
        }
        
        .resumen-label {
            font-weight: 600;
            color: #495057;
        }
        
        .resumen-value {
            font-weight: 600;
            color: #2d3436;
            text-align: right;
        }
        
        .resumen-total {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #e89c94;
            font-size: 1.3rem;
            font-weight: bold;
            color: #f6b8b3;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-confirm {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #e89c94 0%, #f6b8b3 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(246, 184, 179, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(246, 184, 179, 0.6);
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
            background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
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
            background: linear-gradient(90deg, #e89c94, #f6b8b3);
        }
        
        .modal-title {
            text-align: center;
            color: #f6b8b3;
            font-size: 2rem;
            margin-bottom: 25px;
            position: relative;
        }
        
        .modal-title i {
            display: block;
            font-size: 3rem;
            margin-bottom: 15px;
            color: #e89c94;
        }
        
        .modal-content {
            padding: 20px 0;
            border-top: 1px solid #f6b8b3;
            border-bottom: 1px solid #f6b8b3;
            margin: 20px 0;
        }
        
        .modal-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #f6b8b3;
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
            color: #f6b8b3;
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
            background: linear-gradient(135deg, #f6b8b3 0%, #e89c94 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(246, 184, 179, 0.4);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #868e96 0%, #495057 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(134, 142, 150, 0.4);
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
            background: #fadcd9;
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
            background: #f6b8b3;
            transform: rotate(90deg);
        }
        
        /* Estilo para la imagen en la tarjeta de reserva */
        .service-card .card-img-top img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
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
            }
            
            .services-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        }
        
        @media (max-width: 480px) {
            header {
                padding: 20px;
            }
            
            header h1 {
                font-size: 2rem;
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
        }
    </style>
</head>
<body>
<div class="container">
    
    <header>
        <a href="Kore_Estetica-Inicio.php" class="btn-home" title="Volver a inicio">
            <i class="fas fa-home"></i>
        </a>
        <div class="logo">
            <i class="fas fa-spa"></i>
        </div>
        <h1>Kore Estética Corporal</h1>
        <p>Tu bienestar es nuestra prioridad</p>
    </header>

    <!-- Menú de categorías -->
    <div class="categories-menu">
        <button class="category-btn active" data-category="corporales">Tratamientos Corporales</button>
        <button class="category-btn" data-category="faciales">Tratamientos Faciales</button>
        <button class="category-btn" data-category="masajes">Masajes</button>
        <button class="category-btn" data-category="combos">Combos Especiales</button>
    </div>

    <div class="main-content">
        <div class="service-section">
            <div class="service-title">Elija un tratamiento o promoción especial</div>
        </div>
        <!-- Servicios corporales -->
        <div id="corporales" class="services-section">
            <h2 class="section-title"></h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
                <?php
                $query = "SELECT id, nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '10' AND id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>" data-duracion="<?php echo $row['duracion_minutos']; ?>">
                            <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-spa fa-3x pink-text"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <p class="service-details flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> min</div>
                                <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php }
                mysqli_free_result($result); ?>
            </div>
        </div>

        <!-- Servicios faciales -->
        <div id="faciales" class="services-section" style="display:none;">
            <h2 class="section-title"></h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
                <?php
                $query = "SELECT id, nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '11' AND id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>" data-duracion="<?php echo $row['duracion_minutos']; ?>">
                            <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-smile fa-3x pink-text"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <p class="service-details flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> min</div>
                                <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php }
                mysqli_free_result($result); ?>
            </div>
        </div>

        <!-- Servicios masajes -->
        <div id="masajes" class="services-section" style="display:none;">
            <h2 class="section-title"></h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
                <?php
                $query = "SELECT id, nombre, descripcion, precio, duracion_minutos, imagen_url FROM servicios WHERE categoria_id = '12' AND id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>" data-duracion="<?php echo $row['duracion_minutos']; ?>">
                            <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-hand-sparkles fa-3x pink-text"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <p class="service-details flex-grow-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> min</div>
                                <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php }
                mysqli_free_result($result); ?>
            </div>
        </div>

        <!--combos-->
        <div id="combos" class="services-section" style="display:none;">
            <h2 class="section-title"></h2>
            <?php
            // Función para asignar un ícono basado en el nombre del tratamiento
            function obtenerIconoParaTratamiento($tratamiento) {
                $tratamiento = strtolower(trim($tratamiento));
                if (strpos($tratamiento, 'electrodo') !== false) return 'fas fa-bolt';
                if (strpos($tratamiento, 'radiofrecuencia') !== false) return 'fas fa-wave-square';
                if (strpos($tratamiento, 'presoterapia') !== false) return 'fas fa-wind';
                if (strpos($tratamiento, 'masaje') !== false) return 'fas fa-spa';
                if (strpos($tratamiento, 'doble aparatología') !== false) return 'fas fa-cogs';
                return 'fas fa-star'; // Ícono por defecto
            }
            ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 services-grid">
                <?php
                $query = "SELECT id, nombre, descripcion, precio, duracion_minutos, imagen_url FROM combos WHERE id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 service-card" data-id="<?php echo $row['id']; ?>" data-duracion="<?php echo $row['duracion_minutos']; ?>">
                            <div class="card-img-top d-flex align-items-center justify-content-center pt-3">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-gift fa-3x pink-text"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="service-name-card"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <div class="service-details flex-grow-1 text-start px-3">
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
                                <div class="service-duration">Duración: <?php echo (int)$row['duracion_minutos']; ?> min</div>
                                <div class="service-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php }
                mysqli_free_result($result); ?>
            </div>
        </div>

        <!-- Calendario -->
        <div class="calendar-section">
            <h2 class="section-title">Selecciona una fecha</h2>
            <div class="calendar-container">
                 <div class="calendar-header">
                    <div class="calendar-nav">
                        <button class="nav-btn" id="prev-month"><i class="fas fa-chevron-left"></i></button>
                        <div class="current-month" id="current-month"></div>
                        <button class="nav-btn" id="next-month"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-grid"></div>
            </div>
            <div class="time-slots-section">
                <h3 id="time-slots-title" style="display: none;">Horarios Disponibles</h3>
                <div id="time-slots" class="time-slots"></div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="resumen-section">
            <h3 class="resumen-title">Resumen de tu reserva</h3>
            <div class="resumen-content">
                <div class="resumen-item">
                    <span class="resumen-label">Tratamiento:</span>
                    <span class="resumen-value" id="resumen-servicio">-</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Fecha:</span>
                    <span class="resumen-value" id="resumen-fecha">-</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Hora:</span>
                    <span class="resumen-value" id="resumen-hora">-</span>
                </div>
                <div class="resumen-total">
                    <span class="resumen-label">Total:</span>
                    <span class="resumen-value" id="resumen-total">$0</span>
                </div>
            </div>
            <button class="btn-confirm" id="confirm-btn">Reservar Turno</button>
        </div>
    </div>

    <footer>
        <p>© 2025 Kore Estética Corporal | Todos los derechos reservados</p>
        <p>Horario de atención: Lunes a Viernes de 8:00 a 12:00 y 16:00 a 20:00</p>
    </footer>
</div>

<!-- Modal -->
<div class="modal-overlay" id="confirmation-modal">
    <div class="confirmation-modal">
        <div class="modal-close" id="modal-close-btn">
            <i class="fas fa-times"></i>
        </div>
        <h2 class="modal-title"><i class="fas fa-calendar-check"></i> Confirmar Reserva</h2>
        <div class="modal-content">
            <div class="modal-item"><span class="modal-label">Tratamiento:</span> <span class="modal-value" id="modal-servicio">-</span></div>
            <div class="modal-item"><span class="modal-label">Duración:</span> <span class="modal-value" id="modal-duracion">-</span></div>
            <div class="modal-item"><span class="modal-label">Fecha:</span> <span class="modal-value" id="modal-fecha">-</span></div>
            <div class="modal-item"><span class="modal-label">Hora:</span> <span class="modal-value" id="modal-hora">-</span></div>
            <div class="modal-total"><span class="modal-label">Total:</span> <span class="modal-value" id="modal-total">$0</span></div>
        </div>
        <div class="modal-buttons">
            <button class="modal-btn btn-confirm-modal" id="modal-confirm-btn"><i class="fas fa-check-circle"></i> Confirmar</button>
            <button class="modal-btn btn-modify" id="modal-modify-btn"><i class="fas fa-edit"></i> Modificar</button>
            <button class="modal-btn btn-cancel" id="modal-cancel-btn"><i class="fas fa-times-circle"></i> Cancelar</button>
        </div>
    </div>
</div>

<script>
    let reservedSlots = {}; // { 'YYYY-MM-DD': [hora, hora, ...] } // Almacenará las reservas existentes
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let selectedCategory = "corporales";
    let selectedServices = []; // Ahora será un array de un solo elemento
    let selectedDate = null;
    let selectedTime = null;

    // Función para obtener las reservas del mes
    function fetchReservedSlots(year, month) {
        // El mes en JS es 0-11, en PHP es 1-12. Sumamos 1.
        fetch(`obtener_reservas.php?year=${year}&month=${month + 1}&id_negocio=1`)
            .then(response => response.json())
            .then(data => {
                reservedSlots = data;
                generateTimeSlots(); // Volver a generar los horarios si ya hay una fecha seleccionada
            });
    }

    // Referencias DOM
    const calendarGrid = document.querySelector('.calendar-grid');
    const currentMonthEl = document.getElementById('current-month');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const timeSlotsEl = document.getElementById('time-slots');
    const categoryButtons = document.querySelectorAll('.category-btn');
    const modalOverlay = document.getElementById('confirmation-modal');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');
    const modalModifyBtn = document.getElementById('modal-modify-btn');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const confirmBtn = document.getElementById('confirm-btn');

    // Fecha actual
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    // Renderizar calendario
    function renderCalendar(month, year) {
        fetchReservedSlots(year, month); // Obtener reservas para el mes que se va a renderizar
        calendarGrid.innerHTML = "";
        const monthNames = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        const dayNames = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
        dayNames.forEach(name => {
            const dayNameEl = document.createElement('div');
            dayNameEl.classList.add('day-name');
            dayNameEl.textContent = name;
            calendarGrid.appendChild(dayNameEl);
        });
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const firstDayOfWeek = firstDay.getDay();
        let dayOffset = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

        for (let i = 0; i < dayOffset; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('calendar-day','unavailable');
            calendarGrid.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');
            dayElement.textContent = day;

            const dayOfWeek = date.getDay();
            if (dayOfWeek === 0 || dayOfWeek === 6 || date < today) {
                dayElement.classList.add('unavailable');
            } else {
                dayElement.classList.add('available');
                dayElement.addEventListener('click', () => selectDate(date, dayElement));
            }

            if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                dayElement.classList.add('selected');
            }
            calendarGrid.appendChild(dayElement);
        }
    }

    // Seleccionar fecha
    function selectDate(date, element) {
        selectedDate = date;
        selectedTime = null;
        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
        element.classList.add('selected');
        generateTimeSlots();
        updateSummary();
    }

    // Generar horarios
    function generateTimeSlots() {
        timeSlotsEl.innerHTML = '';
        if (!selectedDate) return;
        document.getElementById('time-slots-title').style.display = 'inline-block';

        const isToday = selectedDate.toDateString() === new Date().toDateString();
        const currentHour = new Date().getHours();

        const morningContainer = document.createElement('div');
        morningContainer.className = 'time-slots-group';
        morningContainer.innerHTML = '<h4>Mañana</h4>';
        const morningGrid = document.createElement('div');
        morningGrid.style.display = 'grid';
        morningGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(100px, 1fr))';
        morningGrid.style.gap = '10px';
        for (let hour = 8; hour <= 11; hour++) {
            if (isToday && hour <= currentHour) continue;
            morningGrid.appendChild(createTimeSlot(hour));
        }
        morningContainer.appendChild(morningGrid);

        const afternoonContainer = document.createElement('div');
        afternoonContainer.className = 'time-slots-group';
        afternoonContainer.innerHTML = '<h4>Tarde</h4>';
        const afternoonGrid = document.createElement('div');
        afternoonGrid.style.display = 'grid';
        afternoonGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(100px, 1fr))';
        afternoonGrid.style.gap = '10px';
        for (let hour = 16; hour <= 19; hour++) {
            if (isToday && hour <= currentHour) continue;
            afternoonGrid.appendChild(createTimeSlot(hour));
        }
        afternoonContainer.appendChild(afternoonGrid);

        timeSlotsEl.appendChild(morningContainer);
        timeSlotsEl.appendChild(afternoonContainer);
    }

    function createTimeSlot(hour) {
        const slot = document.createElement('div');
        slot.classList.add('time-slot');
        slot.textContent = `${hour}:00`;

        // Verificar si el slot está reservado
        const fechaFormateada = selectedDate.toISOString().split('T')[0];
        if (reservedSlots[fechaFormateada] && reservedSlots[fechaFormateada].includes(hour)) {
            slot.classList.add('unavailable');
            slot.title = 'Horario no disponible';
            return slot; // Devolver el slot deshabilitado
        }

        if (selectedTime === hour) slot.classList.add('selected');
        slot.addEventListener('click', () => {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            selectedTime = hour;
            updateSummary();
            updateBlockedSlots();
        });
        return slot;
    }

    // Selección de servicios
    function attachServiceListeners() {
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', () => {
                // Deseleccionar todas las tarjetas primero
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));

                const serviceId = card.dataset.id;
                const isAlreadySelected = selectedServices.length > 0 && selectedServices[0].id === serviceId;

                if (isAlreadySelected) {
                    // Si se hace clic en el mismo, se deselecciona
                    selectedServices = [];
                    card.classList.remove('selected');
                } else {
                    // Seleccionar el nuevo servicio
                    card.classList.add('selected');
                    const isCombo = card.closest('#combos') !== null;
                    const serviceData = { id: serviceId, nombre: card.querySelector('.service-name-card').textContent, precio: parseFloat(card.querySelector('.service-price').textContent.replace('$', '').replace(/\./g, '')), duracion: parseInt(card.dataset.duracion), is_combo: isCombo };
                    selectedServices = [serviceData]; // Reemplazar con el nuevo servicio
                }

                updateSummary();
                updateBlockedSlots();
            });
        });
    }

    // Actualizar resumen
function updateSummary() {
    const resumenServicioEl = document.getElementById('resumen-servicio');
    const resumenFechaEl = document.getElementById('resumen-fecha');
    const resumenHoraEl = document.getElementById('resumen-hora');
    const resumenTotalEl = document.getElementById('resumen-total');

    if (selectedServices.length > 0) {
        const service = selectedServices[0];
        resumenServicioEl.innerHTML = service.nombre;

        let totalPrice = service.precio;
        resumenTotalEl.textContent = `$${totalPrice.toLocaleString('es-CL')}`;
    } else {
        resumenServicioEl.textContent = "-";
        resumenTotalEl.textContent = "$0";
    }

    resumenFechaEl.textContent = selectedDate ? selectedDate.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : "-";

    resumenHoraEl.textContent = selectedTime ? `${selectedTime}:00` : "-";
}

    // Mostrar modal
function showConfirmationModal() {
    if (selectedServices.length === 0) return;

    const service = selectedServices[0];
    const serviceName = service.is_combo ? `Combo: ${service.nombre}` : service.nombre;
    const duration = service.duracion;
    const totalPrice = service.precio;

    let horaTexto = `${selectedTime}:00`;
    if (duration > 60) {
        horaTexto += ` a ${selectedTime + Math.ceil(duration / 60)}:00`;
    }

    document.getElementById('modal-servicio').innerHTML = serviceName;
    document.getElementById('modal-duracion').textContent = `${duration} min`;
    document.getElementById('modal-fecha').textContent = document.getElementById('resumen-fecha').textContent;
    document.getElementById('modal-hora').textContent = horaTexto;
    document.getElementById('modal-total').textContent = `$${totalPrice.toLocaleString('es-CL')}`;
    modalOverlay.classList.add('active');
}

    // Bloquear el siguiente turno si es un combo
    function updateBlockedSlots() {
        // 1. Limpiar bloqueos automáticos previos
        document.querySelectorAll('.time-slot.auto-disabled').forEach(slot => {
            slot.classList.remove('auto-disabled', 'unavailable');
        });

        // 2. Verificar si se debe bloquear
        if (selectedServices.length > 0 && selectedTime !== null) {
            const service = selectedServices[0];
            // Si la duración es mayor a 60 min (ej. 105 min para combos)
            if (service.duracion > 60) {
                const nextHour = selectedTime + 1;
                const nextSlot = Array.from(document.querySelectorAll('.time-slot')).find(slot => slot.textContent === `${nextHour}:00`);
                
                if (nextSlot) {
                    nextSlot.classList.add('auto-disabled', 'unavailable');
                }
            }
        }
    }

    function closeConfirmationModal() { modalOverlay.classList.remove('active'); }

    // Eventos
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.services-section').forEach(sec => sec.style.display='none');
            document.getElementById(btn.dataset.category).style.display='block';
        });
    });

    confirmBtn.addEventListener('click', () => {
        if (selectedServices.length === 0) { mostrarModalMensaje('Advertencia', 'Selecciona al menos un tratamiento o combo'); return; }
        if (!selectedDate) { mostrarModalMensaje('Advertencia', 'Selecciona una fecha'); return; }
        if (!selectedTime) { mostrarModalMensaje('Advertencia', 'Selecciona un horario'); return; }
        showConfirmationModal();
    });

    modalConfirmBtn.addEventListener('click', () => {
        if (selectedServices.length === 0 || !selectedDate || selectedTime === null) {
            mostrarModalMensaje('Advertencia', 'Faltan datos para confirmar la reserva.');
        return;
    }

  const fechaFormateada = selectedDate.toISOString().split('T')[0];
  const horaFormateada = `${selectedTime}:00`;
  const servicio = selectedServices[0]; // solo uno permitido
  const datos = {
    fecha: fechaFormateada,
    hora: horaFormateada,
    // El backend espera un array de servicios
    servicios: [servicio],
    id_negocio: <?php echo $id_negocio; ?>
  };

  fetch('guardar_historial.php', {
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
        mostrarModalMensaje('¡Reserva Confirmada!', 'Tu cita ha sido agendada exitosamente. ¡Te esperamos!', 'exito');
        closeConfirmationModal();

        // Añadir el nuevo turno y todos los horarios bloqueados a la lista de reservados
        const fechaClave = selectedDate.toISOString().split('T')[0];
        if (!reservedSlots[fechaClave]) reservedSlots[fechaClave] = [];
        
        // Obtener la duración en minutos del servicio seleccionado
        const servicio = selectedServices[0];
        const duracion_minutos = servicio.duracion || 60;
        const duracion_horas = Math.ceil(duracion_minutos / 60);
        
        // Agregar todos los horarios necesarios a reservedSlots
        for (let h = 0; h < duracion_horas; h++) {
          const hora_bloqueada = selectedTime + h;
          if (!reservedSlots[fechaClave].includes(hora_bloqueada)) {
            reservedSlots[fechaClave].push(hora_bloqueada);
          }
        }

        // Limpiar selección
        selectedServices = [];
        selectedTime = null;
        document.querySelectorAll('.service-card.selected').forEach(c => c.classList.remove('selected'));
        updateSummary();
        
        // Recargar los horarios bloqueados del mes actual para sincronizar con otros usuarios
        fetchReservedSlots(currentYear, currentMonth);
        
        // Regenerar los horarios para mostrar las horas bloqueadas correctamente
        generateTimeSlots();
      } else {
        mostrarModalMensaje('Error', 'Error al guardar la reserva: ' + data.message, 'error');
        if (data.message.includes('Ya existe una reserva')) {
          generateTimeSlots(); // regenerar horarios
        }
      }
    } catch (e) {
      throw new Error('Respuesta no válida del servidor: ' + text);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    mostrarModalMensaje('Error', 'Error al procesar la solicitud: ' + error.message, 'error');
  });
});

    modalModifyBtn.addEventListener('click', closeConfirmationModal);
    modalCloseBtn.addEventListener('click', closeConfirmationModal);
    modalCancelBtn.addEventListener('click', closeConfirmationModal);
    prevMonthBtn.addEventListener('click', () => { currentMonth--; if(currentMonth<0){currentMonth=11;currentYear--;} renderCalendar(currentMonth,currentYear); });
    nextMonthBtn.addEventListener('click', () => { currentMonth++; if(currentMonth>11){currentMonth=0;currentYear++;} renderCalendar(currentMonth,currentYear); });

    // Inicializar
    renderCalendar(currentMonth, currentYear);
    attachServiceListeners();
    
    // Seleccionar automáticamente un servicio o combo si viene en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const servicioParam = urlParams.get('servicio');
    const comboParam = urlParams.get('combo');
    
    if (servicioParam || comboParam) {
        // Esperar a que los servicios se carguen en el DOM
        setTimeout(() => {
            const searchTerm = servicioParam || comboParam;
            const serviceCard = Array.from(document.querySelectorAll('.service-card')).find(card => 
                card.querySelector('.service-name-card').textContent.trim() === decodeURIComponent(searchTerm)
            );
            if (serviceCard) {
                // Simular un clic en la tarjeta del servicio o combo
                serviceCard.click();
            }
        }, 100);
    }
});
</script>

<!-- Modal para mensajes personalizados (éxito, error, advertencia) -->
<div id="modalMensaje" class="modal-overlay-msg" style="display: none;">
    <div class="modal-content-msg">
        <div id="iconoMensaje" style="font-size: 60px; margin-bottom: 15px;"></div>
        <h2 id="tituloMensaje" style="margin-bottom: 15px; color: #333; font-size: 24px;"></h2>
        <p id="contenidoMensaje" style="margin-bottom: 25px; color: #666; line-height: 1.6;"></p>
        <button onclick="cerrarModalMensaje()" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">
            Aceptar
        </button>
    </div>
</div>

<style>
    .modal-overlay-msg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none !important;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-overlay-msg.show {
        display: flex !important;
    }
    
    .modal-content-msg {
        background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        max-width: 400px;
        text-align: center;
        border-top: 5px solid #e89c94;
    }
    
    /* Estilo para el botón dentro del modal - con gradiente rosa */
    .modal-content-msg .btn-primary {
        background: linear-gradient(135deg, #e89c94 0%, #f6b8b3 100%) !important;
        color: white !important;
        border: none !important;
        transition: all 0.3s ease;
    }
    
    .modal-content-msg .btn-primary:hover {
        background: linear-gradient(135deg, #d8807c 0%, #e89c94 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
    }
</style>

<script>
    function mostrarModalMensaje(titulo, contenido, tipo = 'advertencia') {
        const modal = document.getElementById('modalMensaje');
        const iconoDiv = document.getElementById('iconoMensaje');
        const tituloDiv = document.getElementById('tituloMensaje');
        const contenidoDiv = document.getElementById('contenidoMensaje');
        
        tituloDiv.textContent = titulo;
        contenidoDiv.textContent = contenido;
        
        // Establecer icono según el tipo
        if (tipo === 'exito') {
            iconoDiv.innerHTML = '✅';
            iconoDiv.style.color = '#4CAF50';
        } else if (tipo === 'error') {
            iconoDiv.innerHTML = '❌';
            iconoDiv.style.color = '#f28b82';
        } else {
            iconoDiv.innerHTML = '⚠️';
            iconoDiv.style.color = '#ff9800';
        }
        
        modal.classList.add('show');
    }
    
    function cerrarModalMensaje() {
        document.getElementById('modalMensaje').classList.remove('show');
    }
    
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('modalMensaje');
        if (event.target === modal) {
            cerrarModalMensaje();
        }
    });
</script>

<!-- Modal para solicitar login si no hay sesión -->
<?php if (isset($mostrar_modal_login) && $mostrar_modal_login): ?>
<div id="modalLoginRequired" class="modal-overlay" style="display: flex;">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <h2 style="margin-bottom: 20px;">Debes Iniciar Sesión</h2>
        <p style="margin-bottom: 30px; color: #666;">Para realizar una reserva, debes iniciar sesión o registrarte primero.</p>
        <button onclick="window.location.href='Login.php';" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px; cursor: pointer;">
            Iniciar Sesión
        </button>
    </div>
</div>
<style>
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }
</style>
<?php endif; ?>

</body>
</html>