<?php
session_start();
include_once 'conexEstetica.php';

// Usar el id de negocio de la sesión
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : (isset($_SESSION['id_negocio']) ? intval($_SESSION['id_negocio']) : 1);
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;
    
// Procesar la modificación masiva de servicios (nombre, desc, url, precio)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_servicios'])) {
    $conexion = conectarDB();
    $nombres = $_POST['servicio_nombre'] ?? [];
    $descripciones = $_POST['servicio_descripcion'] ?? [];
    $urls = $_POST['servicio_imagen_url'] ?? [];
    $precios = $_POST['servicio_precio'] ?? [];
    $duraciones = $_POST['servicio_duracion'] ?? [];

    foreach ($nombres as $id_servicio => $nombre) {
        $descripcion = $descripciones[$id_servicio] ?? '';
        $url = $urls[$id_servicio] ?? '';
        $precio = $precios[$id_servicio] ?? 0;
        $duracion = $duraciones[$id_servicio] ?? 45; // Por defecto 45 si no se envía

        $stmt = $conexion->prepare("UPDATE servicios SET nombre = ?, descripcion = ?, imagen_url = ?, precio = ?, duracion_minutos = ? WHERE id = ? AND id_negocio = ?");
        $stmt->bind_param('sssdiii', $nombre, $descripcion, $url, $precio, $duracion, $id_servicio, $id_negocio);
        $stmt->execute();
        $stmt->close();
    }
    $conexion->close();
    echo "<script>alert('Servicios actualizados correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-servicios';</script>";
    exit;
}
// Procesar agregar servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_servicio'])) {
    $conexion = conectarDB();
    $nombre = trim($_POST['servicio_nombre']);
    $precio = floatval($_POST['servicio_precio']);
    $categoria_id = intval($_POST['servicio_categoria']);
    $descripcion = trim($_POST['servicio_descripcion']);
    $imagen_url = trim($_POST['servicio_imagen_url']);
    $duracion_servicio = 45; // Duración por defecto para servicios

    // Añadimos duracion_minutos a la inserción
    $stmt = $conexion->prepare("INSERT INTO servicios (nombre, precio, categoria_id, descripcion, imagen_url, id_negocio, duracion_minutos) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sdissii', $nombre, $precio, $categoria_id, $descripcion, $imagen_url, $id_negocio, $duracion_servicio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Servicio agregado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-servicios';</script>";
    exit;
}




// Procesar eliminar servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_servicio'], $_POST['servicio_id'])) {
    $conexion = conectarDB();
    $servicio_id = intval($_POST['servicio_id']);
    $stmt = $conexion->prepare("DELETE FROM servicios WHERE id = ? AND id_negocio = ?");
    $stmt->bind_param('ii', $servicio_id, $id_negocio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Servicio eliminado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-servicios';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hacer_admin'], $_POST['usuario_id'])) {
    $conexion = conectarDB();
    $usuario_id = intval($_POST['usuario_id']);
    // Cambia el tipo y el id_negocio_admin solo para el negocio actual
    $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'admin', id_negocio_admin = ? WHERE id = ?");
    $stmt->bind_param('ii', $id_negocio, $usuario_id);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Rol de administrador asignado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-usuarios';</script>";
    exit;
}

// Procesar quitar rol de admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar_admin'], $_POST['usuario_id'])) {
    $conexion = conectarDB();
    $usuario_id = intval($_POST['usuario_id']);
    // Para quitar el rol, se cambia el tipo a 'cliente' y se limpia el id_negocio_admin
    $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'cliente', id_negocio_admin = NULL WHERE id = ? AND id_negocio_admin = ?");
    $stmt->bind_param('ii', $usuario_id, $id_negocio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Rol de administrador quitado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-usuarios';</script>";
    exit;
}
?>

<?php
// Procesar agregar combo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_combo'])) {
    $conexion = conectarDB();
    $nombre = trim($_POST['combo_nombre']);
    $descripcion = trim($_POST['combo_descripcion']);
    $precio = floatval($_POST['combo_precio']);
    $imagen_url = trim($_POST['combo_imagen_url']);
    $duracion_combo = 105; // Duración por defecto para combos
    $id_negocio = $id_negocio; // Ya definido arriba

    $stmt = $conexion->prepare("INSERT INTO combos (nombre, descripcion, precio, id_negocio, duracion_minutos, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdiis', $nombre, $descripcion, $precio, $id_negocio, $duracion_combo, $imagen_url);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Combo agregado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-promociones';</script>";
    exit;
}

// Procesar eliminar combo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_combo'], $_POST['combo_id'])) {
    $conexion = conectarDB();
    $combo_id = intval($_POST['combo_id']);
    $stmt = $conexion->prepare("DELETE FROM combos WHERE id = ? AND id_negocio = ?");
    $stmt->bind_param('ii', $combo_id, $id_negocio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Combo eliminado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-promociones';</script>";
    exit;
}

// Procesar modificar combo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_combo'], $_POST['combo_id'])) {
    $conexion = conectarDB();
    $combo_id = intval($_POST['combo_id']);
    $nombre = trim($_POST['combo_nombre']);
    $descripcion = trim($_POST['combo_descripcion']);
    $precio = floatval($_POST['combo_precio']);
    $imagen_url = trim($_POST['combo_imagen_url']);
    $duracion = intval($_POST['combo_duracion']);

    $stmt = $conexion->prepare("UPDATE combos SET nombre = ?, descripcion = ?, precio = ?, duracion_minutos = ?, imagen_url = ? WHERE id = ? AND id_negocio = ?");
    $stmt->bind_param('ssdiisii', $nombre, $descripcion, $precio, $duracion, $imagen_url, $combo_id, $id_negocio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Combo modificado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-promociones';</script>";
    exit;
}

// Procesar la modificación masiva de combos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_combos'])) {
    $conexion = conectarDB();
    $nombres = $_POST['combo_nombre'] ?? [];
    $descripciones = $_POST['combo_descripcion'] ?? [];
    $urls = $_POST['combo_imagen_url'] ?? [];
    $duraciones = $_POST['combo_duracion'] ?? [];
    $precios = $_POST['combo_precio'] ?? [];

    foreach ($nombres as $combo_id => $nombre) {
        $descripcion = $descripciones[$combo_id] ?? '';
        $url = $urls[$combo_id] ?? '';
        $duracion = $duraciones[$combo_id] ?? 105;
        $precio = $precios[$combo_id] ?? 0;

        $stmt = $conexion->prepare("UPDATE combos SET nombre = ?, descripcion = ?, imagen_url = ?, duracion_minutos = ?, precio = ? WHERE id = ? AND id_negocio = ?");
        $stmt->bind_param('sssidii', $nombre, $descripcion, $url, $duracion, $precio, $combo_id, $id_negocio);
        $stmt->execute();
        $stmt->close();
    }
    $conexion->close();
    echo "<script>alert('Combos actualizados correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-promociones';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Configuración</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8b6b0;
        }

        /* Layout general */
        .contenedor-principal {
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color: #f4a4a0;
            min-height: 100vh;
            padding-top: 20px;
        }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li {
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .sidebar li:hover,
        .sidebar li.activo {
            background-color: #f8b6b0;
            font-weight: bold;
        }

        .contenido {
            flex: 1;
            padding: 20px;
            text-align: center;
        }
        .contenido h2 { font-weight: bold; margin-bottom: 10px; }

        .btn-agregar {
            background-color: #f28b82;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            color: white;
            font-weight: bold;
        }

        /* Galería */
        .imagenes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            justify-items: center;
        }
        .imagen-item {
            width: 100px;
            height: 100px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            border-radius: 5px;
        }
        .btn-editar {
            margin-top: 20px;
            background-color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Secciones */
        .seccion {
            background-color: #f8d7da;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0px 8px 10px rgb(29, 29, 29);
            margin-top: 20px;
            display: none;
        }
        .seccion h2 { color: #140000; text-align: center; }

        /* Usuarios */
        .usuarios-container {
            background-color: #f8bcbc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.3);
        }
        .usuario-item {
            display: flex;
            align-items: center;
            background-color: #f6dada;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .usuario-foto {
            border-radius: 50%;
            margin-right: 10px;
            width: 40px;
            height: 40px;
        }
        .usuario-info { flex: 1; display: flex; flex-direction: column; }
        .usuario-info strong { font-size: 16px; }
        .usuario-info span { font-size: 12px; color: #555; }
        .btn-editar-usuario {
            background-color: #d9d9d9;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Estilos mejorados para el formulario de agregar servicio */
        .form-agregar-servicio {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid transparent;
            background-clip: padding-box;
            border: 2px solid #f8d7da;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .form-agregar-servicio h3 {
            font-weight: 600;
            color: #d81b60;
        }
        .input-group-text {
            background-color: #f8d7da;
            border-color: #f8d7da;
            color: #d81b60;
        }
        .btn-agregar-servicio {
            background: linear-gradient(135deg, #f8b6b0, #f4a4a0);
        }

        /* Tabla servicios */
        .table { background-color: white; }

        /* Promociones Configuración */
        .config-promo {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        /* Promociones Públicas */
        .promociones-section {
            background-color: #ffeef0;
            padding: 40px 20px;
            text-align: center;
        }
        .promociones-section h2 {
            color: #e91e63;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .promos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .promo-card {
            background: #fff;
            border: 2px dashed #e91e63;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            transition: transform .2s;
        }
        .promo-card:hover { transform: scale(1.03); }
        .promo-card h3 {
            color: #d81b60;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .promo-card ul {
            text-align: left;
            padding-left: 20px;
            margin-bottom: 15px;
        }
        .promo-card ul li { margin-bottom: 5px; }
        .promo-precio {
            font-size: 20px;
            font-weight: bold;
            color: #e91e63;
            margin: 10px 0;
        }
        .promo-btn {
            background: #e91e63;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .contenedor-principal { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; }
            .imagenes-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="contenedor-principal">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li class="activo" onclick="mostrarSeccion('seccion-servicios')"><i class="fas fa-tags"></i> Servicios y Precios</li>
                <li onclick="mostrarSeccion('seccion-usuarios')"><i class="fas fa-users"></i> Usuarios</li>
                <li onclick="mostrarSeccion('seccion-promociones')"><i class="fas fa-percent"></i> Promociones</li>
                <li onclick="window.location.href='index.php'"><i class="fas fa-arrow-left"></i> Volver al inicio</li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <div class="contenido">

<!-- Servicios -->
<div id="seccion-servicios" class="seccion" style="display:block;">
    <h2><strong>Servicios y Precios</strong></h2>
    <!-- Formulario mejorado para agregar servicio -->
    <div class="form-agregar-servicio mb-5">
        <h3 class="text-center mb-4"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Servicio</h3>
        <form method="post" action="configuracion.php?id_negocio=<?php echo $id_negocio; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        <input type="text" name="servicio_nombre" class="form-control" placeholder="Nombre del servicio" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" name="servicio_precio" class="form-control" placeholder="Precio" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list-alt"></i></span>
                        <select name="servicio_categoria" class="form-select" required>
    <option value="">Seleccionar categoría...</option>
    <?php if ($id_negocio == 1): // Categorías para Kore Estética ?>
        <option value="10">Corporales</option>
        <option value="11">Faciales</option>
        <option value="12">Masajes</option>
    <?php elseif ($id_negocio == 2): // Categorías para Juliette Nails ?>
        <option value="6">Capping</option>
        <option value="7">Capping Poly Gel</option>
        <option value="8">Esmaltado</option>
        <option value="9">Soft Gel</option>
    <?php else: ?>
        <option value="" disabled>No hay categorías para este negocio</option>
    <?php endif; ?>
</select>

                    </div>
                </div>
                <div class="col-md-12">
                    <textarea name="servicio_descripcion" class="form-control" rows="2" placeholder="Descripción breve del servicio..."></textarea>
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                        <input type="url" name="servicio_imagen_url" class="form-control" placeholder="URL de la imagen (opcional)">
                    </div>
                </div>
            </div>
            <button type="submit" name="agregar_servicio" class="btn btn-agregar-servicio text-white w-100 mt-3 fw-bold"><i class="fas fa-plus me-2"></i>Agregar Servicio</button>
        </form>
    </div>

    <h3 class="mt-5">Servicios Actuales</h3>
    <form id="form-servicios" method="post" action="configuracion.php?id_negocio=<?php echo $id_negocio; ?>">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Servicio</th>
                        <th style="min-width: 200px;">Descripción</th>
                        <th style="min-width: 200px;">URL Imagen</th>
                        <th>Duración (min)</th>
                        <th>Precio</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conexion = conectarDB();
                    $query = "SELECT id, nombre, descripcion, imagen_url, precio, duracion_minutos FROM servicios WHERE id_negocio = ? ORDER BY nombre ASC";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $id_negocio);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                            <td><input type="text" class="form-control" name="servicio_nombre[' . $row['id'] . ']" value="' . htmlspecialchars($row['nombre']) . '"></td>
                            <td><input type="text" class="form-control" name="servicio_descripcion[' . $row['id'] . ']" value="' . htmlspecialchars($row['descripcion']) . '"></td>
                            <td><input type="url" class="form-control" name="servicio_imagen_url[' . $row['id'] . ']" value="' . htmlspecialchars($row['imagen_url']) . '"></td>
                            <td><input type="number" class="form-control" name="servicio_duracion[' . $row['id'] . ']" value="' . htmlspecialchars($row['duracion_minutos']) . '" step="5"></td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="servicio_precio[' . $row['id'] . ']" value="' . htmlspecialchars($row['precio']) . '" step="0.01">
                                </div>
                            </td>
                            <td>
                                <form method="post" action="configuracion.php?id_negocio=' . $id_negocio . '" onsubmit="return confirm(\'¿Seguro que deseas eliminar este servicio?\');" style="display:inline;">
                                    <input type="hidden" name="servicio_id" value="' . $row['id'] . '">
                                    <button type="submit" name="eliminar_servicio" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>';
                    }
                    $stmt->close();
                    $conexion->close();
                    ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <button type="submit" name="modificar_servicios" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>

            <!-- Usuarios -->
            <div id="seccion-usuarios" class="seccion">
                <div class="usuarios-container">
                    <h2 class="text-center">Usuarios</h2>
                    
                    <?php
                    $conexion = conectarDB();
                    // Modificación: La consulta ahora une usuarios con su historial para obtener solo los del negocio actual,
                    // o aquellos que son administradores de este negocio.
                    $query = "SELECT DISTINCT u.id, u.nombre, u.apellido, u.email, u.tipo, u.id_negocio_admin
                              FROM usuarios u
                              LEFT JOIN historial h ON u.id = h.id_usuario
                              WHERE h.id_negocio = ? OR u.id_negocio_admin = ?
                              ORDER BY u.nombre ASC";
                    $stmt = $conexion->prepare($query);
                    // Se bindean los dos parámetros con el mismo id_negocio
                    $stmt->bind_param('ii', $id_negocio, $id_negocio);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $esAdminDeEsteNegocio = ($row['tipo'] == 'admin' && $row['id_negocio_admin'] == $id_negocio);
                        echo '<div class="usuario-item">
                            
                            <div class="usuario-info text-start">
                                <strong>' . htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) . '</strong>
                                <span>' . htmlspecialchars($row['email']) . '</span>
                                <span style="font-size:12px; color:' . ($esAdminDeEsteNegocio ? '#d81b60' : '#555') . ';">' . ($esAdminDeEsteNegocio ? 'Administrador de este negocio' : 'Cliente') . '</span>
                            </div>';
                        if ($esAdminDeEsteNegocio) {
                            echo '<form method="post" action="configuracion.php?id_negocio=' . $id_negocio . '" style="display:inline;">
                                    <input type="hidden" name="usuario_id" value="' . $row['id'] . '">
                                    <button type="submit" name="quitar_admin" class="btn btn-warning btn-sm">Quitar admin</button>
                                </form>';
                        } else {
                            echo '<form method="post" action="configuracion.php?id_negocio=' . $id_negocio . '" style="display:inline;">
                                    <input type="hidden" name="usuario_id" value="' . $row['id'] . '">
                                    <button type="submit" name="hacer_admin" class="btn-editar-usuario">Hacer admin</button>
                                </form>';
                        }
                        echo '
                           
                        </div>';
                    }
                    $stmt->close();
                    $conexion->close();
                    ?>
                </div>
            </div>
            
            <!-- Promociones Configuración -->
            <div id="seccion-promociones" class="seccion">
                <h2>Administrar Combos</h2>
                <!-- Formulario para agregar combo -->
                <div class="form-agregar-servicio mb-5">
                    <h3 class="text-center mb-4"><i class="fas fa-gift me-2"></i>Agregar Nuevo Combo</h3>
                    <form method="post" action="configuracion.php?id_negocio=<?php echo $id_negocio; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="combo_nombre" class="form-control" placeholder="Nombre del combo" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" name="combo_precio" class="form-control" placeholder="Precio" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <textarea name="combo_descripcion" class="form-control" rows="2" placeholder="Descripción (servicios separados por coma)..." required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    <input type="url" name="combo_imagen_url" class="form-control" placeholder="URL de la imagen (opcional)">
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="agregar_combo" class="btn btn-agregar-servicio text-white w-100 mt-3 fw-bold"><i class="fas fa-plus me-2"></i>Agregar Combo</button>
                    </form>
                </div>

                <h3 class="mt-5">Combos Actuales</h3>
                <form id="form-combos" method="post" action="configuracion.php?id_negocio=<?php echo $id_negocio; ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Título</th>
                                    <th style="min-width: 250px;">Descripción</th>
                                    <th style="min-width: 200px;">URL Imagen</th>
                                    <th>Duración (min)</th>
                                    <th>Precio</th>
                                    <th>Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $conexion = conectarDB();
                                $query = "SELECT id, nombre, descripcion, precio, duracion_minutos, imagen_url FROM combos WHERE id_negocio = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('i', $id_negocio);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>
                                        <td><input type="text" name="combo_nombre[' . $row['id'] . ']" value="' . htmlspecialchars($row['nombre']) . '" class="form-control" required></td>
                                        <td><input type="text" name="combo_descripcion[' . $row['id'] . ']" value="' . htmlspecialchars($row['descripcion']) . '" class="form-control" required></td>
                                        <td><input type="url" name="combo_imagen_url[' . $row['id'] . ']" value="' . htmlspecialchars($row['imagen_url']) . '" class="form-control"></td>
                                        <td><input type="number" name="combo_duracion[' . $row['id'] . ']" value="' . htmlspecialchars($row['duracion_minutos']) . '" class="form-control" step="5" required></td>
                                        <td><div class="input-group"><span class="input-group-text">$</span><input type="number" name="combo_precio[' . $row['id'] . ']" value="' . htmlspecialchars($row['precio']) . '" class="form-control" step="0.01" required></div></td>
                                        <td>
                                            <form method="post" action="configuracion.php?id_negocio=' . $id_negocio . '" onsubmit="return confirm(\'¿Seguro que deseas eliminar este combo?\');" style="display:inline;">
                                                <input type="hidden" name="combo_id" value="' . $row['id'] . '">
                                                <button type="submit" name="eliminar_combo" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>';
                                }
                                $stmt->close();
                                $conexion->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" name="modificar_combos" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script>
        function mostrarSeccion(id){
            document.querySelectorAll('.seccion').forEach(sec => sec.style.display = 'none');
            document.getElementById(id).style.display = 'block';
            document.querySelectorAll('.sidebar li').forEach(item => item.classList.remove('activo'));
            const items = document.querySelectorAll('.sidebar li');
            items.forEach(item => {
                if(item.getAttribute('onclick').includes(id)) item.classList.add('activo');
            });
        }

        window.addEventListener("DOMContentLoaded", () => {
            // Mostrar la sección de servicios por defecto
            const hash = window.location.hash;
            if (hash) {
                const seccionId = hash.substring(1); // remove #
                if (document.getElementById(seccionId)) {
                    mostrarSeccion(seccionId);
                } else {
                    mostrarSeccion('seccion-servicios');
                }
            } else {
                mostrarSeccion('seccion-servicios');
            }
        });
    </script>
</body>
</html>