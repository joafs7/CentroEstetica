<?php
session_start();

// Headers agresivos para prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("ETag: " . md5(microtime()));

// Permitir acceso sin sesión - solo proteger las acciones de reserva
// Variables de sesión (pueden no estar definidas si el usuario no está logueado)
$nombreUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : 1;
$id_negocio_admin = isset($_SESSION['id_negocio_admin']) ? $_SESSION['id_negocio_admin'] : null;
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

// Si es admin, usar su id_negocio_admin para las notificaciones
$id_negocio_para_notif = $esAdmin && $id_negocio_admin ? $id_negocio_admin : $id_negocio;

// DEBUG: Log para verificar valores
error_log("DEBUG Notificaciones - usuario_id: $usuario_id, esAdmin: " . ($esAdmin ? 'true' : 'false') . ", id_negocio: $id_negocio, id_negocio_admin: " . ($id_negocio_admin ?? 'NULL') . ", id_negocio_para_notif: $id_negocio_para_notif");

// --- INICIO: Obtener notificaciones (para admin y usuarios) ---
$notificaciones_no_leidas = 0;
$lista_notificaciones = [];
if ($usuario_id) {  // Si hay usuario logueado (admin o usuario normal)
    include_once 'conexEstetica.php';
    $conexion_notif = conectarDB();
    $query_notif = "SELECT id, mensaje, fecha_creacion FROM notificaciones WHERE id_usuario_destino = ? AND id_negocio = ? AND leida = 0 ORDER BY fecha_creacion DESC";
    $stmt_notif = $conexion_notif->prepare($query_notif);
    $stmt_notif->bind_param('ii', $usuario_id, $id_negocio_para_notif);
    $stmt_notif->execute();
    $resultado_notif = $stmt_notif->get_result();
    $notificaciones_no_leidas = $resultado_notif->num_rows;
    while($fila = $resultado_notif->fetch_assoc()) $lista_notificaciones[] = $fila;
}
// --- FIN: Obtener notificaciones (para admin y usuarios) ---

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
        /* Estilo para notificaciones sin leer */
        .dropdown-menu-notifications .unread-notification {
            background-color: #fde8e6 !important;
            font-weight: 500;
        }
        .dropdown-menu-notifications .unread-notification:hover, .dropdown-menu-notifications .unread-notification:focus {
            background-color: var(--light-pink) !important;
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
            border: 2px solid #b02a6f;
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
        
        /* Animaciones para el botón de reservar */
        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 5px rgba(232, 156, 148, 0.5), 0 0 10px rgba(216, 112, 106, 0.3);
            }
            50% {
                box-shadow: 0 0 20px rgba(232, 156, 148, 0.8), 0 0 30px rgba(216, 112, 106, 0.6);
            }
            100% {
                box-shadow: 0 0 5px rgba(232, 156, 148, 0.5), 0 0 10px rgba(216, 112, 106, 0.3);
            }
        }
        
        @keyframes gentle-bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }
        
        .btn-highlight {
            animation: pulse-glow 2s ease-in-out infinite, gentle-bounce 2s ease-in-out infinite;
            position: relative;
            overflow: hidden;
            background-color: var(--dark-pink) !important;
            border: 3px solid #a85a52 !important;
            font-weight: 700;
        }
        
        .btn-highlight::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
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
            border: 2px solid #b02a6f;
            background-color: white;
        }
        
        .discount-badge {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d63384 0%, #b02a6f 100%);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(214, 51, 132, 0.4);
            animation: rotateStar 3s linear infinite;
        }
        
        .discount-badge i {
            color: white;
            font-size: 32px;
            margin-bottom: 2px;
        }
        
        .discount-badge span {
            color: white;
            font-weight: 700;
            font-size: 18px;
            margin-top: -5px;
        }
        
        @keyframes rotateStar {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Estilos para modales de cancelación */
        .modal-exito-cancelacion {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeInModal 0.3s ease;
        }

        .contenedor-exito-cancelacion {
            background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);
            padding: 50px 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUpModal 0.4s ease;
        }

        .icono-exito {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
            animation: bounceIcon 0.6s ease;
        }

        .contenedor-exito-cancelacion h2 {
            color: #d63384;
            font-size: 28px;
            font-weight: 700;
            margin: 15px 0;
        }

        .contenedor-exito-cancelacion p {
            color: #666;
            font-size: 16px;
            margin: 10px 0;
        }

        .contenedor-exito-cancelacion .subtexto {
            color: #999;
            font-size: 14px;
            font-style: italic;
        }

        .btn-cerrar-exito {
            background: linear-gradient(135deg, #d63384 0%, #b02a6f 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(214, 51, 132, 0.3);
        }

        .btn-cerrar-exito:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(214, 51, 132, 0.4);
        }

        /* Modal de Error */
        .modal-error-cancelacion {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeInModal 0.3s ease;
        }

        .contenedor-error-cancelacion {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUpModal 0.4s ease;
            border-left: 5px solid #dc3545;
        }

        .icono-error {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
            animation: shakeIcon 0.5s ease;
        }

        .contenedor-error-cancelacion h2 {
            color: #d63384;
            font-size: 28px;
            font-weight: 700;
            margin: 15px 0;
        }

        .contenedor-error-cancelacion p {
            color: #666;
            font-size: 16px;
            margin: 10px 0;
        }

        .btn-cerrar-error {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-cerrar-error:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        @keyframes fadeInModal {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUpModal {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes bounceIcon {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        @keyframes shakeIcon {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
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
            padding: 20px 0;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .footer .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .footer .logo-placeholder {
            align-self: flex-start;
            margin-left: 20px;
        }
        
        .footer p {
            margin: 0;
            margin-top: 5px;
            text-align: center;
            font-size: 0.95rem;
            color: var(--text-color);
        }
        
        .logo-placeholder {
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            border-radius: 10px;
            padding: 8px 15px;
            width: fit-content;
        }
        
        .logo-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }
        
        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .logo-text {
            font-weight: bold;
            font-size: 1.5rem;
            color: white;
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
                
                /* Estilos para los tabs */
                .nav-tabs {
                    border-bottom: 2px solid var(--light-pink);
                    padding: 0 0 -2px 0;
                }
                .nav-tabs .nav-link {
                    color: var(--text-color);
                    border: none;
                    border-bottom: 3px solid transparent;
                    transition: all 0.3s ease;
                    font-weight: 500;
                    margin-bottom: -2px;
                }
                .nav-tabs .nav-link:hover {
                    color: var(--dark-pink);
                    border-bottom-color: var(--secondary-color);
                }
                .nav-tabs .nav-link.active {
                    color: var(--dark-pink);
                    background-color: transparent;
                    border-bottom-color: var(--primary-color);
                    font-weight: 600;
                }
                .tab-pane {
                    animation: fadeIn 0.3s ease-in;
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
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
        <div class="logo-placeholder">
            <div class="logo-circle">
                <img src="imagenes/KoreEstetica.png" alt="Kore Estética Logo">
            </div>
            <span class="logo-text">KORE</span>
        </div>
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
                
                <!-- Botón para abrir sidebar de usuario (Mi cuenta) -->
                <button class="nav-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#userSidebar" aria-controls="userSidebar">
                    <i class="fas fa-user-circle"></i> Mi cuenta
                </button>
                
                <!-- CAMPANITA DE NOTIFICACIONES (para usuarios logueados) -->
                <?php if ($usuario_id): ?>
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
                                <a class="dropdown-item d-flex align-items-start notification-item unread-notification" href="#" data-id="<?php echo $notif['id']; ?>" data-bs-toggle="modal" data-bs-target="#notificationModal">
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
                        <li><a class="dropdown-item text-center pink-text small py-2 bg-light" href="#" id="verTodasNotificaciones" data-bs-toggle="modal" data-bs-target="#todasNotificacionesModal" style="border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem;">Ver todas las notificaciones</a></li>
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
                $fotoPerfil = isset($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : '';

                // Si falta algún dato en la sesión, intentar obtenerlo desde la BD
                if ($usuarioId && (empty($usuarioApellido) || empty($usuarioEmail) || empty($usuarioCelular) || empty($fotoPerfil))) {
                    if (file_exists('conexEstetica.php')) {
                        include_once 'conexEstetica.php';
                        $conexionTmp = conectarDB();
                        if ($conexionTmp) {
                            $stmt = mysqli_prepare($conexionTmp, "SELECT nombre, apellido, email, celular, foto_perfil FROM usuarios WHERE id = ? LIMIT 1");
                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
                                mysqli_stmt_execute($stmt);
                                mysqli_stmt_bind_result($stmt, $dbNombre, $dbApellido, $dbEmail, $dbCelular, $dbFoto);
                                if (mysqli_stmt_fetch($stmt)) {
                                    if (empty($usuarioNombre)) $usuarioNombre = $dbNombre;
                                    if (empty($usuarioApellido)) $usuarioApellido = $dbApellido;
                                    if (empty($usuarioEmail)) $usuarioEmail = $dbEmail;
                                    if (empty($usuarioCelular)) $usuarioCelular = $dbCelular;
                                    if (empty($fotoPerfil)) $fotoPerfil = $dbFoto;
                                }
                                mysqli_stmt_close($stmt);
                            }
                            mysqli_close($conexionTmp);
                        }
                    }
                }
                ?>

                <div class="mb-4 text-center">
                    <div style="position: relative; width: fit-content; margin: 0 auto;">
                        <?php 
                        // Mostrar foto cargada o Gravatar
                        if (!empty($fotoPerfil) && file_exists($fotoPerfil)) {
                            echo '<img id="imgPerfil" src="' . htmlspecialchars($fotoPerfil) . '?v=' . time() . '" alt="Foto de perfil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--dark-pink);">';
                        } elseif (!empty($usuarioEmail)) {
                            $gravatarHash = md5(strtolower(trim($usuarioEmail)));
                            $gravatarUrl = "https://www.gravatar.com/avatar/{$gravatarHash}?d=identicon&s=200";
                            echo '<img id="imgPerfil" src="' . htmlspecialchars($gravatarUrl) . '" alt="Foto de perfil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--dark-pink);">';
                        } else {
                            echo '<div style="font-size:72px;color:var(--dark-pink)"><i class="fas fa-user-circle"></i></div>';
                        }
                        ?>
                        <?php if ($usuarioId): ?>
                        <label for="inputFoto" style="position: absolute; bottom: 0; right: 0; background-color: var(--dark-pink); color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: all 0.3s;" title="Cambiar foto de perfil">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="inputFoto" style="display: none;" accept="image/*">
                        <?php endif; ?>
                    </div>
                    <h5 class="mt-2"><?php echo htmlspecialchars($usuarioNombre . ' ' . $usuarioApellido); ?></h5>
                </div>

                <?php if ($usuarioId): ?>
                <!-- Formulario para editar datos del usuario (solo si está logueado) -->
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
                        <?php if ($usuarioId): ?>
                        <div class="col-12">
                            <button type="button" class="btn w-100" style="background-color: var(--primary-color); color: white;" onclick="mostrarHistorial()">
                                <i class="fas fa-history"></i> Ver Historial de Citas
                            </button>
                        </div>
                        <?php endif; ?>
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
                    <div class="alert alert-success mt-3" id="alertaExito">
                        <i class="fas fa-check-circle me-2"></i>Perfil actualizado correctamente.
                    </div>
                    <script>
                        // Abrir la barra lateral SOLO si viene directamente del formulario de edición
                        const params = new URLSearchParams(window.location.search);
                        if (params.get('updated') === '1' && params.get('openSidebar') === '1') {
                            const userSidebar = document.getElementById('userSidebar');
                            if (userSidebar) {
                                const offcanvasInstance = new bootstrap.Offcanvas(userSidebar);
                                offcanvasInstance.show();
                            }
                            
                            // Limpiar la URL para que no vuelva a abrirse en recargas posteriores
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                        
                        // Desaparecer el mensaje después de 4 segundos
                        setTimeout(function() {
                            const alertaExito = document.getElementById('alertaExito');
                            if (alertaExito) {
                                alertaExito.style.transition = 'opacity 0.5s ease-out';
                                alertaExito.style.opacity = '0';
                                setTimeout(function() {
                                    alertaExito.remove();
                                }, 500);
                            }
                        }, 4000);
                    </script>
                <?php } ?>

                <?php else: ?>
                <!-- Opciones cuando NO hay sesión iniciada -->
                <div class="text-center">
                    <p class="text-muted mb-4">No has iniciado sesión</p>
                    <div class="d-grid gap-2">
                        <a href="Login.php" class="btn text-white fw-bold" style="background-color: var(--dark-pink);">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </a>
                        <a href="index.php#registro" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección Hero -->


<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold">Te damos la bienvenida <?php echo htmlspecialchars($nombreUsuario); ?> a Kore Estética Corporal</h1>
        <p class="lead">Un espacio diseñado para que puedas relajarte y disfrutar de tratamientos que le hacen bien a tu cuerpo</p>
        <a href="reservas-kore.php?id_negocio=<?php echo $id_negocio; ?>" class="btn btn-pink btn-lg mt-3 btn-highlight">
            <i class="fas fa-calendar-check me-2"></i> Reservar turno
        </a>
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
                        <a href="reservas-kore.php?id_negocio=<?php echo $id_negocio; ?>&servicio=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Agendar</a>
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
                        <a href="reservas-kore.php?id_negocio=<?php echo $id_negocio; ?>&servicio=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Agendar</a>
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
            $query_combos = "SELECT nombre, precio, descripcion, duracion_minutos, imagen_url FROM combos WHERE id_negocio = ?";
            $stmt_combos = $conexion->prepare($query_combos);
            $stmt_combos->bind_param('i', $id_negocio);
            $stmt_combos->execute();
            $resultado_combos = $stmt_combos->get_result();
            ?>
            <div class="row g-4 justify-content-center">
                <?php while ($row = mysqli_fetch_assoc($resultado_combos)) { 
                    // Determinar descuento según el nombre del combo
                    $nombre_combo = strtolower(trim($row['nombre']));
                    $descuento = 0;
                    
                    // Búsqueda flexible para 15%
                    if (strpos($nombre_combo, 'booty') !== false || 
                        strpos($nombre_combo, 'jornada') !== false || 
                        (strpos($nombre_combo, 'electrodos') !== false && strpos($nombre_combo, 'masajes') !== false)) {
                        $descuento = 15;
                    } 
                    // Búsqueda flexible para 20%
                    elseif (strpos($nombre_combo, 'piernas') !== false || 
                            strpos($nombre_combo, 'reducción') !== false ||
                            strpos($nombre_combo, 'reduccion') !== false) {
                        $descuento = 20;
                    }
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card promo-card h-100 position-relative">
                            <?php if ($descuento > 0): ?>
                            <div class="discount-badge">
                                <i class="fas fa-star"></i>
                                <span>-<?php echo $descuento; ?>%</span>
                            </div>
                            <?php endif; ?>
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
                                <h4 class="promo-price">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></H4>
                                <a href="reservas-kore.php?id_negocio=<?php echo $id_negocio; ?>&combo=<?php echo urlencode($row['nombre']); ?>" class="btn btn-pink mt-3">Reservar ahora</a>
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
                            <a href="https://www.instagram.com/kore.esteticabienestar/" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.facebook.com/people/Kore-Est%C3%A9tica-Bienestar/100090615667527/" target="_blank" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://wa.me/543564618278" target="_blank" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="logo-placeholder">
                    <div class="logo-circle">
                        <img src="imagenes/KoreEstetica.png" alt="Kore Estética Logo">
                    </div>
                    <span class="logo-text">KORE</span>
                </div>
                <p>Beauty Kore Estética y Bienestar - Tu espacio de belleza y relajación</p>
                <p>© 2025 Kore Estética Corporal. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>

    <!-- Modal para ver Notificación -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);">
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

    <!-- Modal Todas las Notificaciones -->
    <div class="modal fade" id="todasNotificacionesModal" tabindex="-1" aria-labelledby="todasNotificacionesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);">
                <div class="modal-header pink-gradient text-white">
                    <h5 class="modal-title" id="todasNotificacionesModalLabel"><i class="fas fa-bell me-2"></i>Todas las Notificaciones</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="filtro-notif-cliente" placeholder="Buscar por cliente..." class="form-control">
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="filtro-notif-servicio" placeholder="Buscar por servicio..." class="form-control">
                        </div>
                        <div class="col-md-4">
                            <input type="date" id="filtro-notif-fecha" class="form-control">
                        </div>
                    </div>

                    <!-- Tabla de Notificaciones -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Mensaje</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="todasNotificacionesTableBody">
                                <tr><td colspan="5" class="text-center py-4">Cargando notificaciones...</td></tr>
                            </tbody>
                        </table>
                    </div>
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
                                </select>
                            </div>
                        </div>
                        <!-- Columna de la Tabla -->
                        <div class="col-md-8">
                            <!-- Tabs para Citas Activas y Canceladas -->
                            <ul class="nav nav-tabs mb-3" id="historialTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="citasActivas-tab" data-bs-toggle="tab" data-bs-target="#citasActivas" type="button" role="tab" aria-controls="citasActivas" aria-selected="true">
                                        <i class="fas fa-calendar-check me-2"></i>Citas Activas
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="citasCanceladas-tab" data-bs-toggle="tab" data-bs-target="#citasCanceladas" type="button" role="tab" aria-controls="citasCanceladas" aria-selected="false">
                                        <i class="fas fa-ban me-2"></i>Citas Canceladas
                                    </button>
                                </li>
                            </ul>

                            <!-- Contenido de las Tabs -->
                            <div class="tab-content" id="historialTabsContent">
                                <!-- Tab de Citas Activas -->
                                <div class="tab-pane fade show active" id="citasActivas" role="tabpanel" aria-labelledby="citasActivas-tab">
                                    <div class="filtros mb-3">
                                        <input type="text" id="filtro-nombre" placeholder="Buscar por cliente..." class="form-control" style="flex:1; min-width:140px;">
                                        <input type="text" id="filtro-servicio" placeholder="Buscar por servicio..." class="form-control" style="flex:1; min-width:140px;">
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
                                                    <th>Acciones</th>
                                                </tr>
                                                </thead>
                                                <tbody id="historialTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab de Citas Canceladas -->
                                <div class="tab-pane fade" id="citasCanceladas" role="tabpanel" aria-labelledby="citasCanceladas-tab">
                                    <div class="tabla-reservas">
                                        <div class="table-responsive">
                                            <table id="historialCanceladoTabla" class="table">
                                                <thead>
                                                    <tr>
                                                    <th>Cliente</th>
                                                    <th>Servicio</th>
                                                    <th>Categoría</th>
                                                    <th>Fecha Original</th>
                                                    <th>Fecha Cancelación</th>
                                                    <th>Precio</th>
                                                </tr>
                                                </thead>
                                                <tbody id="historialCanceladoTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        // Variable global para saber si el usuario es admin
        const esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;

        // Variables globales (removidas - ahora usamos atributo data del modal)

        // Mover la lógica de carga a su propia función para poder reutilizarla
        function cargarHistorial(periodo) {
                const tbody = document.getElementById('historialTableBody');
                // Limpiar la tabla INMEDIATAMENTE y mostrar el mensaje de carga.
                // Esto asegura que no se vea contenido viejo en ningún momento.
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Cargando historial...</td></tr>'; // Mensaje de carga

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
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No tienes citas en este período.</td></tr>';
                        return;
                    }

                    // Una vez cargados los datos, renderizar el calendario del historial
                    renderHistorialCalendar(data);

                    // Si la API devuelve un objeto con 'error' mostrarlo
                    if (!Array.isArray(data) && data.error) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${data.error}</td></tr>`;
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
                        tr.dataset.id = cita.id;

                        tr.innerHTML = `
                            <td>${escapeHtml(cliente)}</td>
                            <td>${escapeHtml(servicio)}</td>
                            <td>${escapeHtml(categoria)}</td>
                            <td>${fechaStr}</td>
                            <td>${horaStr}</td>
                            <td>$${formatearPrecio(cita.precio)}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="abrirModalCancelacion(${cita.id})" title="Cancelar cita">
                                    <i class="fas fa-trash"></i> Cancelar
                                </button>
                            </td>
                        `;
                        
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    const msg = (error && error.message) ? error.message : 'Error al cargar el historial.';
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${msg}</td></tr>`;
                });
        }

        function cargarHistorialCancelado(periodo) {
                const tbody = document.getElementById('historialCanceladoTableBody');
                // Limpiar la tabla INMEDIATAMENTE y mostrar el mensaje de carga.
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Cargando citas canceladas...</td></tr>';

            // Hacer la petición AJAX para obtener el historial cancelado
            fetch(`obtener_historial_cancelado.php?periodo=${periodo}&id_negocio=1&_=${new Date().getTime()}`, { 
                method: 'GET',
                cache: 'no-store', // No guardar en caché
                headers: { 'Cache-Control': 'no-cache' },
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
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No tienes citas canceladas en este período.</td></tr>';
                        return;
                    }

                    // Si la API devuelve un objeto con 'error' mostrarlo
                    if (!Array.isArray(data) && data.error) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.error}</td></tr>`;
                        return;
                    }

                    // Rellenar la tabla con los datos
                    data.forEach(cita => {
                        const fechaObj = new Date(cita.fecha_realizacion);
                        const fechaStr = fechaObj.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
                        const fechaCancelObj = new Date(cita.fecha_cancelacion);
                        const fechaCancelStr = fechaCancelObj.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
                        const categoria = cita.categoria_nombre || '';
                        const cliente = cita.cliente || '';
                        const servicio = cita.servicio || '';

                        const tr = document.createElement('tr');
                        tr.dataset.cliente = (cliente || '').toLowerCase();
                        tr.dataset.servicio = (servicio || '').toLowerCase();
                        tr.dataset.categoria = (categoria || '').toLowerCase();

                        tr.innerHTML = `
                            <td>${escapeHtml(cliente)}</td>
                            <td>${escapeHtml(servicio)}</td>
                            <td>${escapeHtml(categoria)}</td>
                            <td>${fechaStr}</td>
                            <td>${fechaCancelStr}</td>
                            <td>$${formatearPrecio(cita.precio)}</td>
                        `;
                        
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    const msg = (error && error.message) ? error.message : 'Error al cargar el historial cancelado.';
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
                        // Filtrar por fecha seleccionada
                        const filas = document.querySelectorAll('#historialTabla tbody tr');
                        filas.forEach(fila => {
                            const rowFechaIso = (fila.dataset.fechaIso || '').trim();
                            fila.style.display = rowFechaIso === dateStr ? '' : 'none';
                        });
                        
                        // Actualizar selección visual del día
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
            cargarHistorialCancelado(periodoSelect.value);

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
        
        // Función para cancelar una cita desde el historial
        function cancelarCitaHistorial(citaId) {
            fetch('cancelar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_historial: citaId })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        // Mostrar un modal bonito de éxito
                        mostrarModalExitoCancelacion();
                        // Recargar el historial
                        setTimeout(() => {
                            cargarHistorial(document.getElementById('periodoHistorial').value);
                            cargarHistorialCancelado(document.getElementById('periodoHistorial').value);
                        }, 1500);
                    } else {
                        // Mostrar un modal bonito de error
                        mostrarModalErrorCancelacion(data.error || data.message || 'Error desconocido');
                    }
                } catch (e) {
                    console.error('Error al parsear JSON:', e);
                    console.error('Respuesta del servidor:', text);
                    mostrarModalErrorCancelacion('Error al procesar la respuesta del servidor');
                }
            })
            .catch(error => {
                console.error('Error en cancelarCitaHistorial:', error);
                mostrarModalErrorCancelacion('Error al procesar la cancelación: ' + error.message);
            });
        }
        
        // Función para mostrar modal de éxito de cancelación
        function mostrarModalExitoCancelacion() {
            const modal = document.createElement('div');
            modal.className = 'modal-exito-cancelacion';
            modal.innerHTML = `
                <div class="contenedor-exito-cancelacion">
                    <div class="icono-exito">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>¡Cita Cancelada!</h2>
                    <p>La cita ha sido cancelada correctamente.</p>
                    <p class="subtexto">Se ha notificado al estético sobre la cancelación.</p>
                    <button onclick="this.closest('.modal-exito-cancelacion').remove()" class="btn-cerrar-exito">Entendido</button>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Auto cerrar después de 3 segundos
            setTimeout(() => {
                const elem = document.querySelector('.modal-exito-cancelacion');
                if (elem) elem.remove();
            }, 3000);
        }
        
        // Función para mostrar modal de error de cancelación
        function mostrarModalErrorCancelacion(mensaje) {
            const modal = document.createElement('div');
            modal.className = 'modal-error-cancelacion';
            modal.innerHTML = `
                <div class="contenedor-error-cancelacion">
                    <div class="icono-error">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2>Error al Cancelar</h2>
                    <p>${mensaje}</p>
                    <button onclick="this.closest('.modal-error-cancelacion').remove()" class="btn-cerrar-error">Entendido</button>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // Variables para la confirmación de cancelación
        
        // Función para abrir el modal de cancelación
        function abrirModalCancelacion(idCita) {
            console.log('abrirModalCancelacion:', idCita);
            const modal = document.getElementById('modalConfirmCancelacion');
            modal.dataset.citaId = idCita;
            
            // Mostrar el subtexto correcto según si es admin o usuario
            const subtextoAdmin = document.getElementById('subtexto-admin');
            const subtextoUsuario = document.getElementById('subtexto-usuario');
            
            if (esAdmin) {
                subtextoAdmin.style.display = 'block';
                subtextoUsuario.style.display = 'none';
            } else {
                subtextoAdmin.style.display = 'none';
                subtextoUsuario.style.display = 'block';
            }
            
            modal.classList.add('show');
        }
        
        function mostrarModalConfirmCancelacion(citaId) {
            console.log('mostrarModalConfirmCancelacion llamado con:', citaId, 'tipo:', typeof citaId);
            const modal = document.getElementById('modalConfirmCancelacion');
            if (modal) {
                modal.setAttribute('data-cita-id', citaId);
                
                // Mostrar el subtexto correcto según si es admin o usuario
                const subtextoAdmin = document.getElementById('subtexto-admin');
                const subtextoUsuario = document.getElementById('subtexto-usuario');
                
                if (esAdmin) {
                    subtextoAdmin.style.display = 'block';
                    subtextoUsuario.style.display = 'none';
                } else {
                    subtextoAdmin.style.display = 'none';
                    subtextoUsuario.style.display = 'block';
                }
                
                modal.classList.add('show');
                console.log('Modal mostrado con ID:', modal.getAttribute('data-cita-id'));
            } else {
                console.error('Modal modalConfirmCancelacion no encontrado');
            }
        }
        
        function cerrarModalConfirmCancelacion() {
            const modal = document.getElementById('modalConfirmCancelacion');
            if (modal) {
                modal.classList.remove('show');
                modal.dataset.citaId = '';
            }
        }
        
        function confirmarCancelacionCita() {
            const modal = document.getElementById('modalConfirmCancelacion');
            const idCita = modal.dataset.citaId || modal.getAttribute('data-cita-id');
            console.log('confirmarCancelacionCita ejecutada, idCita=', idCita, 'tipo:', typeof idCita);
            
            if (idCita && parseInt(idCita) > 0) {
                const idAcancelar = parseInt(idCita);
                cerrarModalConfirmCancelacion();
                cancelarCitaHistorial(idAcancelar);
            } else {
                console.error('ID de cita inválido:', idCita);
                alert('Error: No se pudo obtener el ID de la cita');
            }
        }
        
        // Manejar la selección de elementos
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners para los botones del modal de confirmación de cancelación
            const btnMantener = document.getElementById('btnMantenerCita');
            const btnConfirmar = document.getElementById('btnConfirmarCancelacion');
            const modal = document.getElementById('modalConfirmCancelacion');
            
            if (btnMantener) {
                btnMantener.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Botón mantener clickeado');
                    cerrarModalConfirmCancelacion();
                });
            }
            
            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Botón confirmar clickeado, dataset.citaId=', modal.dataset.citaId);
                    confirmarCancelacionCita();
                });
            }
            
            // Cerrar modal al hacer clic fuera
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        cerrarModalConfirmCancelacion();
                    }
                });
            }
            
            // Ocultar filtro de nombre si no es admin
            if (!esAdmin) {
                const filtroNombreContainer = document.getElementById('filtro-nombre');
                if (filtroNombreContainer) {
                    filtroNombreContainer.parentElement.style.display = 'none';
                }
            }
            
            // Añadir listeners para filtrar la tabla de historial
            const filtroNombre = document.getElementById('filtro-nombre');
            const filtroServicio = document.getElementById('filtro-servicio');
            
            function filtrarHistorial() {
                const nombre = (filtroNombre.value || '').toLowerCase().trim();
                const servicio = (filtroServicio.value || '').toLowerCase().trim();

                const filas = document.querySelectorAll('#historialTabla tbody tr');
                filas.forEach(fila => {
                    const rowCliente = (fila.dataset.cliente || '').toLowerCase();
                    const rowServicio = (fila.dataset.servicio || '').toLowerCase();
                    const rowCategoria = (fila.dataset.categoria || '').toLowerCase();

                    const visibleNombre = nombre === '' || rowCliente.includes(nombre);
                    const visibleServicio = servicio === '' || rowServicio.includes(servicio) || rowCategoria.includes(servicio);

                    fila.style.display = (visibleNombre && visibleServicio) ? '' : 'none';
                });
            }

            // Asignar eventos de 'input' para una respuesta inmediata al borrar
            if (filtroNombre) filtroNombre.addEventListener('input', filtrarHistorial);
            if (filtroServicio) filtroServicio.addEventListener('input', filtrarHistorial);

            // Limpiar el filtro cuando se cambia el período general
            const periodoSelect = document.getElementById('periodoHistorial');
            periodoSelect.addEventListener('change', function() {
                cargarHistorial(this.value);
                cargarHistorialCancelado(this.value);
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
            
            // Manejo de carga de foto de perfil
            const inputFoto = document.getElementById('inputFoto');
            if (inputFoto) {
                inputFoto.addEventListener('change', function(e) {
                    const archivo = e.target.files[0];
                    if (!archivo) return;

                    // Mostrar spinner de carga
                    const imgPerfil = document.getElementById('imgPerfil');
                    const originalSrc = imgPerfil.src;

                    const formData = new FormData();
                    formData.append('foto', archivo);

                    fetch('cargar_foto_perfil.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la imagen con timestamp para evitar caché
                            imgPerfil.src = data.foto_url + '?v=' + new Date().getTime();
                            // Limpiar input
                            inputFoto.value = '';
                        } else {
                            alert('Error al cargar la foto: ' + data.error);
                            imgPerfil.src = originalSrc;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar la foto');
                        imgPerfil.src = originalSrc;
                    });
                });
            }
            
            // Proteger enlaces y botones de reserva - redirigir a login si no hay sesión
            const usuarioLogueado = <?php echo isset($_SESSION['usuario_id']) ? 'true' : 'false'; ?>;
            
            // Función para ir a reservas - verifica si usuario está logueado
            function irAReservas() {
                if (!usuarioLogueado) {
                    const modal = new bootstrap.Modal(document.getElementById('reservaRestringidaModal'));
                    modal.show();
                } else {
                    window.location.href = 'reservas-kore.php?id_negocio=<?php echo $id_negocio; ?>';
                }
            }
            
            // Usar event delegation para proteger enlaces dinámicamente
            document.addEventListener('click', function(e) {
                if (!usuarioLogueado && e.target.closest('a[href*="reservas-kore.php"]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
                    modal.show();
                    return false;
                }
            });
            
            // Usar event delegation para proteger botones dinámicamente
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!usuarioLogueado && btn && (btn.textContent.includes('Reservar') || btn.textContent.includes('Agendar'))) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = new bootstrap.Modal(document.getElementById('reservaRestringidaModal'));
                    modal.show();
                    return false;
                }
            });
            
            // Manejar clics en enlaces Agendar
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a.agendar-btn');
                if (!usuarioLogueado && link) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = new bootstrap.Modal(document.getElementById('reservaRestringidaModal'));
                    modal.show();
                    return false;
                }
            });

            // Event listeners para los filtros del historial cancelado
            const filtroNombreCancelado = document.createElement('input');
            const filtroServicioCancelado = document.createElement('input');
            
            // Los filtros ya existen en la tab de canceladas (compartidos con los de activas)
            // Escuchar cambios en los inputs cuando se cambia la tab
            const citasActivasTab = document.getElementById('citasActivas-tab');
            const citasCanceladasTab = document.getElementById('citasCanceladas-tab');
            
            // Cuando se hace clic en la tab de citas canceladas
            if (citasCanceladasTab) {
                citasCanceladasTab.addEventListener('shown.bs.tab', function() {
                    // Limpiar los filtros
                    if (filtroNombre) filtroNombre.value = '';
                    if (filtroServicio) filtroServicio.value = '';
                    
                    // Actualizar la función de filtrado para que use la tabla de canceladas
                    filtrarHistorialCancelado();
                    
                    // Reasignar los listeners a la tabla de canceladas
                    if (filtroNombre) {
                        filtroNombre.removeEventListener('input', filtrarHistorial);
                        filtroNombre.addEventListener('input', filtrarHistorialCancelado);
                    }
                    if (filtroServicio) {
                        filtroServicio.removeEventListener('input', filtrarHistorial);
                        filtroServicio.addEventListener('input', filtrarHistorialCancelado);
                    }
                });
            }

            // Cuando se hace clic en la tab de citas activas
            if (citasActivasTab) {
                citasActivasTab.addEventListener('shown.bs.tab', function() {
                    // Limpiar los filtros
                    if (filtroNombre) filtroNombre.value = '';
                    if (filtroServicio) filtroServicio.value = '';
                    
                    // Reasignar los listeners a la tabla de activas
                    if (filtroNombre) {
                        filtroNombre.removeEventListener('input', filtrarHistorialCancelado);
                        filtroNombre.addEventListener('input', filtrarHistorial);
                    }
                    if (filtroServicio) {
                        filtroServicio.removeEventListener('input', filtrarHistorialCancelado);
                        filtroServicio.addEventListener('input', filtrarHistorial);
                    }
                });
            }

            // Función para filtrar la tabla de historial cancelado
            function filtrarHistorialCancelado() {
                const nombre = (filtroNombre?.value || '').toLowerCase().trim();
                const servicio = (filtroServicio?.value || '').toLowerCase().trim();

                const filas = document.querySelectorAll('#historialCanceladoTabla tbody tr');
                filas.forEach(fila => {
                    const rowCliente = (fila.dataset.cliente || '').toLowerCase();
                    const rowServicio = (fila.dataset.servicio || '').toLowerCase();
                    const rowCategoria = (fila.dataset.categoria || '').toLowerCase();

                    const visibleNombre = nombre === '' || rowCliente.includes(nombre);
                    const visibleServicio = servicio === '' || rowServicio.includes(servicio) || rowCategoria.includes(servicio);

                    fila.style.display = (visibleNombre && visibleServicio) ? '' : 'none';
                });
            }
        });
    </script>

    <!-- Modales Personalizados con colores de Kore Estética -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #f6b8b3 0%, #e89c94 100%);">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-sparkles me-2"></i>Agendar Cita
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-dark fw-5 mb-3">Para agendar tu cita en <strong style="color: #d8706a;">Kore Estética</strong>, necesitas iniciar sesión primero.</p>
                    <p class="text-dark mb-0"><i class="fas fa-heart" style="color: #d8706a;"></i> ¡Te esperamos! <i class="fas fa-spa" style="color: #d8706a;"></i></p>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white fw-bold" style="background-color: #d8706a;" onclick="window.location.href = 'Login.php'">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reservaRestringidaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 100%);">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #f6b8b3 0%, #e89c94 100%);">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-lock me-2"></i>Acceso Restringido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-dark fw-5 mb-3">Para reservar tus servicios de belleza y bienestar con nosotras, debes iniciar sesión primero.</p>
                    <p class="text-dark mb-0"><i class="fas fa-sparkles" style="color: #d8706a;"></i> ¡Inicia sesión y agenda ahora! <i class="fas fa-heart" style="color: #d8706a;"></i></p>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white fw-bold" style="background-color: #d8706a;" onclick="window.location.href = 'Login.php'">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    // Cargar todas las notificaciones cuando se abre el modal
    document.getElementById('todasNotificacionesModal').addEventListener('show.bs.modal', function() {
        cargarTodasNotificaciones();
    });

    function cargarTodasNotificaciones() {
        const tbody = document.getElementById('todasNotificacionesTableBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Cargando notificaciones...</td></tr>';

        fetch('obtener_todas_notificaciones.php', {
            method: 'GET',
            cache: 'no-store',
            headers: { 'Cache-Control': 'no-cache' },
            credentials: 'same-origin'
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = text ? JSON.parse(text) : [];
                if (!response.ok) {
                    throw new Error('Error al cargar notificaciones');
                }

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No hay notificaciones.</td></tr>';
                    return;
                }

                // Renderizar notificaciones
                tbody.innerHTML = '';
                data.forEach(item => {
                    const fecha = new Date(item.fecha_creacion).toLocaleString('es-ES', {
                        day: '2-digit', month: '2-digit', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                    const estado = item.leida == 1 ? '<span class="badge bg-secondary">Leída</span>' : '<span class="badge bg-danger">Sin leer</span>';
                    
                    const row = document.createElement('tr');
                    row.dataset.fecha = item.fecha_creacion;
                    row.dataset.cliente = (item.cliente || '').toLowerCase();
                    row.dataset.servicio = (item.servicio || '').toLowerCase();
                    
                    row.innerHTML = `
                        <td>${fecha}</td>
                        <td>${item.cliente || '-'}</td>
                        <td>${item.servicio || '-'}</td>
                        <td>${item.mensaje}</td>
                        <td>${estado}</td>
                    `;
                    tbody.appendChild(row);
                });
            } catch (e) {
                console.error('Error:', e);
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar notificaciones</td></tr>';
            }
        });
    }

    // Filtrado de notificaciones
    document.getElementById('filtro-notif-cliente').addEventListener('input', filtrarNotificacionesTabla);
    document.getElementById('filtro-notif-servicio').addEventListener('input', filtrarNotificacionesTabla);
    document.getElementById('filtro-notif-fecha').addEventListener('input', filtrarNotificacionesTabla);

    function filtrarNotificacionesTabla() {
        const cliente = document.getElementById('filtro-notif-cliente').value.toLowerCase().trim();
        const servicio = document.getElementById('filtro-notif-servicio').value.toLowerCase().trim();
        const fecha = document.getElementById('filtro-notif-fecha').value.trim();

        const filas = document.querySelectorAll('#todasNotificacionesTableBody tr');
        filas.forEach(fila => {
            const rowCliente = (fila.dataset.cliente || '').toLowerCase();
            const rowServicio = (fila.dataset.servicio || '').toLowerCase();
            const rowFecha = (fila.dataset.fecha || '').split(' ')[0]; // YYYY-MM-DD

            const visibleCliente = cliente === '' || rowCliente.includes(cliente);
            const visibleServicio = servicio === '' || rowServicio.includes(servicio);
            const visibleFecha = fecha === '' || rowFecha === fecha;

            fila.style.display = (visibleCliente && visibleServicio && visibleFecha) ? '' : 'none';
        });
    }
</script>

<!-- Modal de Confirmación de Cancelación de Cita -->
<div id="modalConfirmCancelacion" class="modal-confirm-cancelacion" data-cita-id="">
    <div class="modal-confirm-content">
        <div class="modal-confirm-header">
            <i class="fas fa-question-circle"></i>
        </div>
        <p class="modal-confirm-text">¿Estás seguro de que deseas cancelar esta cita?</p>
        <p class="modal-confirm-subtitle" id="subtexto-admin" style="display: none;">
            El cliente será notificado y podrá agendar una nueva cita en cualquier momento.
        </p>
        <p class="modal-confirm-subtitle" id="subtexto-usuario" style="display: none;">
            La esteticista será notificada. Puedes agendar una nueva cita en cualquier momento.
        </p>
        <div class="modal-confirm-divider"></div>
        <div class="modal-confirm-buttons">
            <button type="button" id="btnMantenerCita" class="btn-confirm-keep">
                <i class="fas fa-check-circle"></i> Mantener Cita
            </button>
            <button type="button" id="btnConfirmarCancelacion" class="btn-confirm-cancel">
                <i class="fas fa-times-circle"></i> Sí, Cancelar
            </button>
        </div>
    </div>
</div>

<style>
    .modal-confirm-cancelacion {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9998;
        backdrop-filter: blur(4px);
    }
    
    .modal-confirm-cancelacion.show {
        display: flex;
    }
    
    .modal-confirm-content {
        background: linear-gradient(135deg, #fadcd9 0%, #f6b8b3 50%, #e89c94 100%);
        padding: 50px 40px;
        border-radius: 20px;
        box-shadow: 0 25px 80px rgba(216, 112, 106, 0.25);
        max-width: 500px;
        text-align: center;
        border-top: 6px solid #d8706a;
        animation: slideInConfirm 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
    }
    
    .modal-confirm-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 8px;
        background: linear-gradient(90deg, #d8706a, #c55a55, #d8706a);
        box-shadow: 0 2px 10px rgba(216, 112, 106, 0.3);
    }
    
    .modal-confirm-header {
        font-size: 70px;
        color: white;
        margin-bottom: 20px;
        animation: bounceConfirm 0.6s ease;
        text-shadow: 0 2px 8px rgba(216, 112, 106, 0.3);
    }
    
    .modal-confirm-title {
        font-size: 32px;
        color: #8b4a44;
        margin: 0 0 15px 0;
        font-weight: 700;
        letter-spacing: -0.5px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .modal-confirm-text {
        font-size: 18px;
        color: #5a4038;
        margin: 0 0 10px 0;
        font-weight: 600;
    }
    
    .modal-confirm-subtitle {
        font-size: 14px;
        color: #5a4038;
        margin: 0 0 25px 0;
        line-height: 1.6;
        font-weight: 500;
    }
    
    .modal-confirm-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(216, 112, 106, 0.3), transparent);
        margin: 25px 0;
    }
    
    .modal-confirm-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-confirm-keep,
    .btn-confirm-cancel {
        border: none;
        padding: 14px 32px;
        border-radius: 12px;
        font-size: 15px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-confirm-keep {
        background: linear-gradient(135deg, #d8706a 0%, #c55a55 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(216, 112, 106, 0.25);
    }
    
    .btn-confirm-keep:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(216, 112, 106, 0.35);
        background: linear-gradient(135deg, #c55a55 0%, #b04a47 100%);
    }
    
    .btn-confirm-keep:active {
        transform: translateY(-1px);
    }
    
    .btn-confirm-cancel {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.25);
    }
    
    .btn-confirm-cancel:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(231, 76, 60, 0.35);
        background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    }
    
    .btn-confirm-cancel:active {
        transform: translateY(-1px);
    }
    
    .btn-confirm-keep i,
    .btn-confirm-cancel i {
        font-size: 16px;
    }
    
    @keyframes slideInConfirm {
        from {
            transform: translateY(-40px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes bounceConfirm {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .btn-confirm-keep:hover {
        background-color: #ccc;
    }
    
    .btn-confirm-cancel {
        background: linear-gradient(135deg, #e89c94 0%, #f6b8b3 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-confirm-cancel:hover {
        background: linear-gradient(135deg, #d8807c 0%, #e89c94 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
</style>

<!-- Cerrar modal al hacer clic fuera -->
<script>
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('modalConfirmCancelacion');
        if (event.target === modal) {
            cerrarModalConfirmCancelacion();
        }
    });
</script>

</body>
</html>