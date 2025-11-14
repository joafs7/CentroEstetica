<?php
session_start();

// Cualquier usuario logueado puede acceder
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario'];
$usuario_id = $_SESSION['usuario_id'];
$id_negocio = 2; // Juliette Nails

$esAdmin = isset($_SESSION['tipo'], $_SESSION['id_negocio_admin']) 
    && $_SESSION['tipo'] == 'admin' 
    && $_SESSION['id_negocio_admin'] == $id_negocio;

// Conexión con la base de datos
$conexion = new mysqli("localhost", "root", "", "esteticadb");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta base para traer los datos del historial
$query_base = "
    SELECT 
        CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        COALESCE(s.nombre, c.nombre) AS servicio,
        h.precio AS precio,
        h.fecha_realizacion AS fecha,
        TIME(h.fecha_realizacion) AS hora
    FROM historial h
    LEFT JOIN usuarios u ON h.id_usuario = u.id
    LEFT JOIN servicios s ON h.id_servicio = s.id
    LEFT JOIN combos c ON h.id_combo = c.id
";

if ($esAdmin) {
    // El admin ve todas las reservas del negocio
    $query_final = $query_base . " WHERE h.id_negocio = ? ORDER BY h.fecha_realizacion DESC";
    $stmt = $conexion->prepare($query_final);
    $stmt->bind_param("i", $id_negocio);
} else {
    // El usuario solo ve sus propias reservas
    $query_final = $query_base . " WHERE h.id_usuario = ? AND h.id_negocio = ? ORDER BY h.fecha_realizacion DESC";
    $stmt = $conexion->prepare($query_final);
    $stmt->bind_param("ii", $usuario_id, $id_negocio);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Reservas - Juliette Nails</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
  --primary-color: #f8b6b0;
  --secondary-color: #f6b8b3;
  --dark-pink: #e91e63;
  --text-color: #4b5563;
}

body {
  background: linear-gradient(135deg, var(--secondary-color) 0%, #fff 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: var(--text-color);
  margin:0;
  padding:0;
}

.container {
  max-width:1200px;
  margin:auto;
  padding:30px;
}

/* ----- HEADER ----- */
header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background: linear-gradient(145deg, #ffffff 0%, #f8b6b0 100%);
  padding:15px 30px;
  border-radius:20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
header img {height:50px;}
header h2 {color:var(--dark-pink); font-weight:bold;}

/* ----- FILTROS ----- */
.filtros {
  background:white;
  border-radius:20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding:20px;
  margin:30px 0;
  display:flex;
  flex-wrap:wrap;
  gap:15px;
  justify-content:space-between;
  align-items:center;
}
.filtros input {
  border:1px solid #f8b6b0;
  border-radius:10px;
  padding:8px 12px;
  outline:none;
  width:30%;
}
.filtros input:focus {
  border-color: var(--dark-pink);
  box-shadow: 0 0 4px var(--dark-pink);
}
.btn-filtrar {
  background:var(--primary-color);
  border:none;
  color:white;
  padding:8px 20px;
  border-radius:20px;
  transition:0.3s;
}
.btn-filtrar:hover {
  background:var(--dark-pink);
  transform:translateY(-2px);
}

/* ----- TABLA ----- */
.tabla-reservas {
  background:white;
  border-radius:20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  overflow:hidden;
  animation:fadeIn 0.8s ease;
}
@keyframes fadeIn {from{opacity:0; transform:translateY(20px);} to{opacity:1; transform:translateY(0);} }

table {
  width:100%;
  border-collapse:collapse;
}
th {
  background-color: var(--primary-color);
  color:white;
  text-align:center;
  padding:12px;
}
td {
  text-align:center;
  padding:10px;
  border-bottom:1px solid #eee;
}
tr:hover {
  background-color: #fff5f5;
  transition:0.3s;
}

/* ----- FOOTER ----- */
footer {
  text-align:center;
  padding:15px;
  margin-top:40px;
  color:#777;
}
</style>
</head>
<body>

<div class="container">

  <!-- HEADER -->
  <header>
    <h2>Gestión de Reservas</h2>
    <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($nombreUsuario); ?></span>
  </header>

  <!-- FILTROS -->
  <div class="filtros">
    <input type="text" id="filtro-nombre" placeholder="Buscar por nombre...">
    <input type="text" id="filtro-servicio" placeholder="Buscar por servicio...">
    <input type="date" id="filtro-fecha">
  </div>

  <!-- TABLA DE RESERVAS -->
  <div class="tabla-reservas">
    <table id="tabla">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Servicio</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Precio</th>
        </tr>
      </thead>
      <tbody id="tabla-cuerpo">
        <?php while($fila = $resultado->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($fila['cliente']) ?></td>
          <td><?= htmlspecialchars($fila['servicio']) ?></td>
          <td><?= htmlspecialchars(date("Y-m-d", strtotime($fila['fecha']))) ?></td>
          <td><?= htmlspecialchars(date("H:i", strtotime($fila['hora']))) ?></td>
          <td>$<?= htmlspecialchars(number_format($fila['precio'], 0, ',', '.')) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <footer>© 2025 - Panel de administración</footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Añadir listeners para filtrar en tiempo real
    document.getElementById("filtro-nombre").addEventListener("keyup", filtrar);
    document.getElementById("filtro-servicio").addEventListener("keyup", filtrar);
    document.getElementById("filtro-fecha").addEventListener("change", filtrar);
});

function filtrar() {
  const nombre = document.getElementById("filtro-nombre").value.toLowerCase();
  const servicio = document.getElementById("filtro-servicio").value.toLowerCase();
  const fecha = document.getElementById("filtro-fecha").value;
  const filas = document.querySelectorAll("#tabla tbody tr");

    filas.forEach(fila => {
        const colNombre = fila.children[0].textContent.toLowerCase();
        const colServicio = fila.children[1].textContent.toLowerCase();
        const colFecha = fila.children[2].textContent;

        // Comprobación de visibilidad
        const visibleNombre = nombre === "" || colNombre.includes(nombre);
        const visibleServicio = servicio === "" || colServicio.includes(servicio);
        const visibleFecha = fecha === "" || colFecha === fecha;

        if (visibleNombre && visibleServicio && visibleFecha) {
            fila.style.display = ""; // Muestra la fila
        } else {
            fila.style.display = "none"; // Oculta la fila
        }
    });
}

</script>

</body>
</html>

<?php $stmt->close(); $conexion->close(); ?>
