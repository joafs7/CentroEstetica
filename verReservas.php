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

// Procesar cancelación de turno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_turno'])) {
    $id_historial = intval($_POST['id_historial']);
    
    // Obtener la fecha y hora de la reserva
    $query_check = "SELECT fecha_realizacion FROM historial WHERE id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("i", $id_historial);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $reserva = $result_check->fetch_assoc();
    $stmt_check->close();
    
    if ($reserva) {
        $fecha_reserva = new DateTime($reserva['fecha_realizacion']);
        $fecha_actual = new DateTime();
        
        // Calcular la diferencia en horas
        $diferencia = $fecha_reserva->getTimestamp() - $fecha_actual->getTimestamp();
        $horas_diferencia = $diferencia / 3600;
        
        // Verificar si faltan menos de 2 horas
        if ($horas_diferencia < 2 && $horas_diferencia > 0) {
            echo "<script>
                alert('No se puede cancelar el turno con menos de 2 horas de anticipación.\\nTiempo restante: " . round($horas_diferencia, 1) . " horas');
                window.location='verReservas.php';
            </script>";
            exit();
        }
        
        // Si ya pasó el horario
        if ($horas_diferencia < 0) {
            echo "<script>
                alert('No se puede cancelar un turno que ya pasó.');
                window.location='verReservas.php';
            </script>";
            exit();
        }
    }
    
    // Verificar que el turno pertenezca al usuario o sea admin
    if ($esAdmin) {
        $stmt_delete = $conexion->prepare("DELETE FROM historial WHERE id = ? AND id_negocio = ?");
        $stmt_delete->bind_param("ii", $id_historial, $id_negocio);
    } else {
        $stmt_delete = $conexion->prepare("DELETE FROM historial WHERE id = ? AND id_usuario = ? AND id_negocio = ?");
        $stmt_delete->bind_param("iii", $id_historial, $usuario_id, $id_negocio);
    }
    
    if ($stmt_delete->execute()) {
        echo "<script>alert('Turno cancelado exitosamente.'); window.location='verReservas.php';</script>";
    } else {
        echo "<script>alert('Error al cancelar el turno.');</script>";
    }
    $stmt_delete->close();
}

// Consulta base para traer los datos del historial (agregamos el id)
$query_base = "
    SELECT 
        h.id,
        CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        COALESCE(s.nombre, c.nombre) AS servicio,
        h.id_servicio,
        h.id_combo,
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
  margin-bottom: 30px; /* Separación agregada */
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

/* ----- TABLA ----- */
.tabla-reservas {
  background:white;
  border-radius:20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  overflow:hidden;
  animation:fadeIn 0.8s ease;
  margin-top: 30px; /* Separación adicional */
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
  vertical-align:middle;
}
tr:hover {
  background-color: #fff5f5;
  transition:0.3s;
}

/* ----- BOTONES DE ACCIÓN ----- */
.acciones-cell {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-accion {
  padding: 8px 16px;
  border: none;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.btn-accion i {
  font-size: 0.9rem;
}

.btn-modificar {
  background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
  color: white;
}

.btn-modificar:hover {
  background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 12px rgba(219, 39, 119, 0.3);
  color: white;
}

.btn-cancelar {
  background: linear-gradient(135deg, #f8b6b0 0%, #f6b8b3 100%);
  color: #831843;
}

.btn-cancelar:hover {
  background: linear-gradient(135deg, #f6b8b3 0%, #f4a4a0 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 12px rgba(248, 182, 176, 0.4);
}

.btn-cancelar:active,
.btn-modificar:active {
  transform: translateY(0);
}

/* ----- FOOTER ----- */
footer {
  text-align:center;
  padding:15px;
  margin-top:40px;
  color:#777;
}

/* Responsive */
@media (max-width: 768px) {
  .btn-accion {
    font-size: 0.75rem;
    padding: 6px 12px;
  }
  
  .acciones-cell {
    flex-direction: column;
    gap: 5px;
  }
  
  table {
    font-size: 0.9rem;
  }
  
  th, td {
    padding: 8px 5px;
  }
  
  header {
    margin-bottom: 20px;
  }
}

@media (max-width: 480px) {
  .btn-accion i {
    margin-right: 0;
  }
  
  .btn-accion span {
    display: none;
  }
  
  header {
    margin-bottom: 15px;
  }
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
 <?php if ($esAdmin): ?>
  <div class="filtros">
    <input type="text" id="filtro-nombre" placeholder="Buscar por nombre...">
    <input type="text" id="filtro-servicio" placeholder="Buscar por servicio...">
    <input type="date" id="filtro-fecha">
  </div>
  <?php endif; ?>

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
          <th>Acciones</th>
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
          <td>
            <!-- Botón Modificar -->
            <a href="reservas-JulietteNails.php?modificar=<?= $fila['id'] ?>&servicio_id=<?= $fila['id_servicio'] ?>&fecha=<?= date('Y-m-d', strtotime($fila['fecha'])) ?>&hora=<?= date('H', strtotime($fila['hora'])) ?>" 
               class="btn-accion btn-modificar">
              <i class="fas fa-edit"></i> Modificar
            </a>
            
            <!-- Botón Cancelar -->
            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de cancelar este turno?');">
              <input type="hidden" name="id_historial" value="<?= $fila['id'] ?>">
              <button type="submit" name="cancelar_turno" class="btn-accion btn-cancelar">
                <i class="fas fa-times"></i> Cancelar
              </button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <footer>© 2025 - Panel de administración</footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Solo añadir listeners si los elementos existen (cuando es admin)
    const filtroNombre = document.getElementById("filtro-nombre");
    const filtroServicio = document.getElementById("filtro-servicio");
    const filtroFecha = document.getElementById("filtro-fecha");
    
    if (filtroNombre) filtroNombre.addEventListener("keyup", filtrar);
    if (filtroServicio) filtroServicio.addEventListener("keyup", filtrar);
    if (filtroFecha) filtroFecha.addEventListener("change", filtrar);
});

function filtrar() {
  const filtroNombre = document.getElementById("filtro-nombre");
  const filtroServicio = document.getElementById("filtro-servicio");
  const filtroFecha = document.getElementById("filtro-fecha");
  
  // Si los filtros no existen (no es admin), no hacer nada
  if (!filtroNombre || !filtroServicio || !filtroFecha) return;
  
  const nombre = filtroNombre.value.toLowerCase();
  const servicio = filtroServicio.value.toLowerCase();
  const fecha = filtroFecha.value;
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
