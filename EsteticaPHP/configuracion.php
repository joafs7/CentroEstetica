<?php
session_start();
include_once 'conexEstetica.php';


// Usar el id de negocio de la sesión
$id_negocio = isset($_SESSION['id_negocio']) ? intval($_SESSION['id_negocio']) : 1;
$id_negocio = isset($_GET['id_negocio']) ? intval($_GET['id_negocio']) : (isset($_SESSION['id_negocio']) ? intval($_SESSION['id_negocio']) : 1);
$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;
// Procesar el guardado de precios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_precios'])) {
    $conexion = conectarDB();
    $nuevos_precios = $_POST['nuevo_precio'] ?? [];
    foreach ($nuevos_precios as $id_servicio => $nuevo_precio) {
        if ($nuevo_precio !== "" && is_numeric($nuevo_precio)) {
            $stmt = $conexion->prepare("UPDATE servicios SET precio = ? WHERE id = ? AND id_negocio = ?");
            $stmt->bind_param('iii', $nuevo_precio, $id_servicio, $id_negocio);
            $stmt->execute();
            $stmt->close();
        }
    }
    $conexion->close();
    echo "<script>alert('Precios actualizados correctamente.');window.location='configuracion.php#seccion-servicios';</script>";
// Procesar agregar servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_servicio'])) {
    $conexion = conectarDB();
    $nombre = trim($_POST['servicio_nombre']);
    $precio = floatval($_POST['servicio_precio']);
    // Puedes agregar más campos si lo necesitas (ej: categoria_id)
    $stmt = $conexion->prepare("INSERT INTO servicios (nombre, precio, id_negocio) VALUES (?, ?, ?)");
    $stmt->bind_param('sdi', $nombre, $precio, $id_negocio);
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
?>

<?php
// Procesar agregar combo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_combo'])) {
    $conexion = conectarDB();
    $nombre = trim($_POST['combo_nombre']);
    $descripcion = trim($_POST['combo_descripcion']);
    $precio = floatval($_POST['combo_precio']);
    $id_negocio = $id_negocio; // Ya definido arriba

    $stmt = $conexion->prepare("INSERT INTO combos (nombre, descripcion, precio, id_negocio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssdi', $nombre, $descripcion, $precio, $id_negocio);
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
    $stmt = $conexion->prepare("UPDATE combos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ? AND id_negocio = ?");
    $stmt->bind_param('ssdii', $nombre, $descripcion, $precio, $combo_id, $id_negocio);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    echo "<script>alert('Combo modificado correctamente.');window.location='configuracion.php?id_negocio=$id_negocio#seccion-promociones';</script>";
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
                <!-- <li onclick="mostrarSeccion('seccion-galeria')"><i class="fas fa-image"></i> Galería</li> -->
                <li onclick="mostrarSeccion('seccion-promociones')"><i class="fas fa-percent"></i> Promociones</li>
                
            </ul>
        </aside>

        <!-- Contenido principal -->
        <div class="contenido"> 
            <!-- Galería -->
               <!--  <div id="seccion-galeria" class="seccion" style="display: block;">
                    <h2>Galería</h2>
                    <button class="btn-agregar">+ Agregar imagen</button>
                    <div class="imagenes-grid">
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                        <div class="imagen-item"><i class="fas fa-image"></i></div>
                    </div>
                    <button class="btn-editar">EDITAR</button>
                </div> -->

            <!-- Servicios -->
<div id="seccion-servicios" class="seccion" style="display:block">
    <h2><strong>Servicios y Precios</strong></h2>
    <!-- Formulario para agregar servicio -->
    <div class="mb-4">
        <form method="post" class="row g-2 align-items-end justify-content-center">
            <div class="col-md-4">
                <input type="text" name="servicio_nombre" class="form-control" placeholder="Nombre del servicio" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="servicio_precio" class="form-control" placeholder="Precio" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="agregar_servicio" class="btn btn-success w-100">Agregar Servicio</button>
            </div>
        </form>
    </div>
    <form id="form-precios" method="post" action="configuracion.php">
        <table class="table table-bordered text-center mt-4">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Precio Actual</th>
                    <th>Precio Nuevo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conexion = conectarDB();
                $query = "SELECT id, nombre, precio FROM servicios WHERE id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                        <td>' . htmlspecialchars($row['nombre']) . '</td>
                        <td>$<span>' . number_format($row['precio'], 0, ',', '.') . '</span></td>
                        <td>
                            <input type="text" class="form-control text-center" name="nuevo_precio[' . $row['id'] . ']" placeholder="Nuevo precio">
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="servicio_id" value="' . $row['id'] . '">
                                <button type="submit" name="eliminar_servicio" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Seguro que deseas eliminar este servicio?\')">Eliminar</button>
                            </form>
                        </td>
                    </tr>';
                }
                $stmt->close();
                $conexion->close();
                ?>
            </tbody>
        </table>
        <div class="text-center mt-3">
            <button type="submit" name="guardar_precios" class="btn btn-danger">Guardar</button>
        </div>
    </form>
</div>
            <!-- Usuarios -->
<div id="seccion-usuarios" class="seccion" style="display:block">
    <div class="usuarios-container">
        <h2 class="text-center">Usuarios</h2>
        
        <?php
        $conexion = conectarDB();
       $query = "SELECT id, nombre, email, tipo, id_negocio_admin FROM usuarios";
        $stmt = $conexion->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo '<div class="usuario-item">
                
                <div class="usuario-info">
                    <strong>' . htmlspecialchars($row['nombre']) . '</strong>
                    <span>' . htmlspecialchars($row['email']) . '</span>
                    <span style="font-size:12px;color:#d81b60;">' . ($row['tipo'] == 'admin' ? 'Administrador' : 'Cliente') . '</span>
                </div>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="usuario_id" value="' . $row['id'] . '">
                    <button type="submit" name="hacer_admin" class="btn-editar-usuario" ' . ($row['tipo'] == 'admin' ? 'disabled' : '') . '>Hacer admin</button>
                </form>
               
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
    <div class="config-promo mb-4">
        <form method="post">
            <div class="mb-3">
                <label for="combo_nombre" class="form-label">Título</label>
                <input type="text" id="combo_nombre" name="combo_nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="combo_descripcion" class="form-label">Descripción</label>
                <input type="text" id="combo_descripcion" name="combo_descripcion" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="combo_precio" class="form-label">Precio</label>
                <input type="number" id="combo_precio" name="combo_precio" class="form-control" required>
            </div>
            <button type="submit" name="agregar_combo" class="btn btn-primary">Agregar Combo</button>
        </form>
    </div>

    <h3 class="mt-4">Combos actuales</h3>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conexion = conectarDB();
                $query = "SELECT id, nombre, descripcion, precio FROM combos WHERE id_negocio = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_negocio);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                        <form method="post">
                            <td><input type="text" name="combo_nombre" value="' . htmlspecialchars($row['nombre']) . '" class="form-control" required></td>
                            <td><input type="text" name="combo_descripcion" value="' . htmlspecialchars($row['descripcion']) . '" class="form-control" required></td>
                            <td><input type="number" name="combo_precio" value="' . htmlspecialchars($row['precio']) . '" class="form-control" required></td>
                            <td>
                                <input type="hidden" name="combo_id" value="' . $row['id'] . '">
                                <button type="submit" name="modificar_combo" class="btn btn-success btn-sm me-2">Modificar</button>
                                <button type="submit" name="eliminar_combo" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Seguro que deseas eliminar este combo?\')">Eliminar</button>
                            </td>
                        </form>
                    </tr>';
                }
                $stmt->close();
                $conexion->close();
                ?>
            </tbody>
        </table>
    </div>
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

        // Servicios
        const servicios = [
            { nombre: "Servicio 1", precio: "7000" },
            { nombre: "Servicio 2", precio: "8000" },
            { nombre: "Servicio 3", precio: "9000" }
        ];
        function cargarTabla(){
            const tabla = document.getElementById("tabla-precios");
            tabla.innerHTML = "";
            servicios.forEach(servicio => {
                tabla.innerHTML += `
                    <tr>
                        <td>${servicio.nombre}</td>
                        <td><input type="text" class="form-control text-center" value="${servicio.precio}" disabled></td>
                        <td><input type="text" class="form-control text-center" placeholder="Nuevo precio" disabled></td>
                    </tr>`;
            });
        }
        document.getElementById("btn-editar").addEventListener("click", () => {
            document.querySelectorAll("#tabla-precios input").forEach(i => i.disabled = false);
            document.getElementById("btn-guardar").disabled = false;
        });
        document.getElementById("btn-guardar").addEventListener("click", () => {
            const filas = document.querySelectorAll("#tabla-precios tr");
            filas.forEach((fila, index) => {
                const nuevo = fila.children[2].querySelector("input").value;
                if(nuevo){ servicios[index].precio = nuevo; }
            });
            cargarTabla();
            document.getElementById("btn-guardar").disabled = true;
            alert("Precios actualizados correctamente.");
        });
        cargarTabla();

        // Promociones
        function guardarPromo() {
            const titulo = document.getElementById("promoTitulo").value;
            const detalles = document.getElementById("promoDetalles").value.split(",");
            const descripcion = document.getElementById("promoDescripcion").value;
            const precio = document.getElementById("promoPrecio").value;

            if (!titulo || !precio) { alert("Título y precio son obligatorios"); return; }

            let promos = JSON.parse(localStorage.getItem("promos")) || [];
            promos.push({ titulo, detalles, descripcion, precio });
            localStorage.setItem("promos", JSON.stringify(promos));

            mostrarListaPromos();
            mostrarPromos();
            alert("Promoción guardada!");
        }
        function mostrarListaPromos() {
            const promos = JSON.parse(localStorage.getItem("promos")) || [];
            const lista = document.getElementById("listaPromos");
            lista.innerHTML = "";
            promos.forEach((promo, index) => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `
                    <span><b>${promo.titulo}</b> - $${promo.precio}</span>
                    <button class="btn btn-sm btn-danger" onclick="eliminarPromo(${index})">Eliminar</button>`;
                lista.appendChild(li);
            });
        }
        function eliminarPromo(index) {
            let promos = JSON.parse(localStorage.getItem("promos")) || [];
            promos.splice(index, 1);
            localStorage.setItem("promos", JSON.stringify(promos));
            mostrarListaPromos();
            mostrarPromos();
        }
        function mostrarPromos() {
            const promos = JSON.parse(localStorage.getItem("promos")) || [];
            const container = document.getElementById('promosContainer');
            container.innerHTML = "";
            promos.forEach(promo => {
                container.innerHTML += `
                    <div class="promo-card">
                        <h3>${promo.titulo}</h3>
                        <ul>${promo.detalles.map(d => `<li>${d.trim()}</li>`).join('')}</ul>
                        <p>${promo.descripcion}</p>
                        <div class="promo-precio">$${promo.precio}</div>
                        <button class="promo-btn">Reservar ahora</button>
                    </div>`;
            });
        }
        window.addEventListener("DOMContentLoaded", () => {
            mostrarListaPromos();
            mostrarPromos();
        });
    </script>
</body>
</html>