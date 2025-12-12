<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>PRUEBA DE CAMBIOS - KORE ESTETICA</title>
</head>
<body>
    <h1 style="color: red; text-align: center;">🔴 ARCHIVO DE PRUEBA - CAMBIOS FUNCIONAN 🔴</h1>
    
    <div style="background: yellow; padding: 20px; text-align: center; font-size: 24px; font-weight: bold;">
        ✅ SI VES ESTE MENSAJE, LOS CAMBIOS FUNCIONAN CORRECTAMENTE
    </div>
    
    <h2>Información del Sistema:</h2>
    <ul>
        <li>Timestamp actual: <?php echo date('Y-m-d H:i:s'); ?></li>
        <li>Usuario logueado: <?php echo isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'NO'; ?></li>
        <li>IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></li>
        <li>Archivo: <?php echo __FILE__; ?></li>
    </ul>
    
    <h2>Redes Sociales de KORE:</h2>
    <p>
        <a href="https://www.instagram.com/kore.esteticabienestar/" target="_blank">📸 Instagram</a><br>
        <a href="https://www.facebook.com/people/Kore-Est%C3%A9tica-Bienestar/100090615667527/" target="_blank">👍 Facebook</a><br>
        <a href="https://wa.me/543564618278" target="_blank">💬 WhatsApp: +54 9 3564 618278</a>
    </p>
    
    <h2>Nota importante:</h2>
    <p style="color: blue; font-weight: bold;">
        Si ves este archivo con el timestamp cambiando cada vez que recargues,
        significa que el servidor está funcionando correctamente y los cambios SÍ se aplican.
    </p>
</body>
</html>
