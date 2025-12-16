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
        h.id,
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
    // El admin ve todas las reservas del negocio (solo no canceladas)
    $query_final = $query_base . " WHERE h.id_negocio = ? AND h.cancelada = 0 ORDER BY h.fecha_realizacion DESC";
    $stmt = $conexion->prepare($query_final);
    $stmt->bind_param("i", $id_negocio);
} else {
    // El usuario solo ve sus propias reservas (solo no canceladas)
    $query_final = $query_base . " WHERE h.id_usuario = ? AND h.id_negocio = ? AND h.cancelada = 0 ORDER BY h.fecha_realizacion DESC";
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

/* Estilos para los tabs */
.nav-tabs {
  border-bottom: 2px solid #f8b6b0;
  padding: 0;
  margin-bottom: 0;
  background: white;
  border-radius: 20px 20px 0 0;
  padding: 0 20px;
}

.nav-tabs .nav-link {
  color: var(--text-color);
  border: none;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
  font-weight: 500;
  margin-bottom: -2px;
  padding: 15px 20px;
}

.nav-tabs .nav-link:hover {
  color: var(--dark-pink);
  border-bottom-color: var(--primary-color);
}

.nav-tabs .nav-link.active {
  color: var(--dark-pink);
  background-color: transparent;
  border-bottom-color: var(--dark-pink);
  font-weight: 600;
}

.tab-content {
  background: white;
  border-radius: 0 0 20px 20px;
}

.tab-pane {
  animation: fadeIn 0.3s ease-in;
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
    <!-- Tabs para Reservas Activas y Canceladas -->
    <ul class="nav nav-tabs" id="reservasTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="reservasActivas-tab" data-bs-toggle="tab" data-bs-target="#reservasActivas" type="button" role="tab" aria-controls="reservasActivas" aria-selected="true">
          <i class="fas fa-calendar-check me-2"></i>Reservas Activas
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="reservasCanceladas-tab" data-bs-toggle="tab" data-bs-target="#reservasCanceladas" type="button" role="tab" aria-controls="reservasCanceladas" aria-selected="false">
          <i class="fas fa-ban me-2"></i>Reservas Canceladas
        </button>
      </li>
    </ul>

    <!-- Contenido de las tabs -->
    <div class="tab-content" id="reservasTabsContent">
      <!-- Tab de Reservas Activas -->
      <div class="tab-pane fade show active" id="reservasActivas" role="tabpanel" aria-labelledby="reservasActivas-tab">
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
            <?php 
            // Recargar los resultados para esta tabla
            $conexion2 = new mysqli("localhost", "root", "", "esteticadb");
            if ($conexion2->connect_error) {
                die("Error de conexión: " . $conexion2->connect_error);
            }

            $query_base = "
                SELECT 
                    h.id,
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
                $query_final = $query_base . " WHERE h.id_negocio = ? AND h.cancelada = 0 ORDER BY h.fecha_realizacion DESC";
                $stmt2 = $conexion2->prepare($query_final);
                $stmt2->bind_param("i", $id_negocio);
            } else {
                $query_final = $query_base . " WHERE h.id_usuario = ? AND h.id_negocio = ? AND h.cancelada = 0 ORDER BY h.fecha_realizacion DESC";
                $stmt2 = $conexion2->prepare($query_final);
                $stmt2->bind_param("ii", $usuario_id, $id_negocio);
            }

            $stmt2->execute();
            $resultado2 = $stmt2->get_result();

            while($fila = $resultado2->fetch_assoc()): 
            ?>
            <tr data-id="<?= $fila['id'] ?>">
              <td><?= htmlspecialchars($fila['cliente']) ?></td>
              <td><?= htmlspecialchars($fila['servicio']) ?></td>
              <td><?= htmlspecialchars(date("Y-m-d", strtotime($fila['fecha']))) ?></td>
              <td><?= htmlspecialchars(date("H:i", strtotime($fila['hora']))) ?></td>
              <td>$<?= htmlspecialchars(number_format($fila['precio'], 0, ',', '.')) ?></td>
              <td>
                <button class="btn btn-sm btn-danger cancelar-btn" data-id="<?= $fila['id'] ?>" title="Cancelar reserva">
                  <i class="fas fa-trash"></i> Cancelar
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Tab de Reservas Canceladas -->
      <div class="tab-pane fade" id="reservasCanceladas" role="tabpanel" aria-labelledby="reservasCanceladas-tab">
        <table id="tabla-canceladas">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Servicio</th>
              <th>Fecha Original</th>
              <th>Fecha Cancelación</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody id="tabla-canceladas-cuerpo">
            <?php 
            $query_canceladas = "
                SELECT 
                    h.id,
                    CONCAT(u.nombre, ' ', u.apellido) AS cliente,
                    COALESCE(s.nombre, c.nombre) AS servicio,
                    h.precio AS precio,
                    h.fecha_realizacion AS fecha,
                    h.fecha_cancelacion AS fecha_cancelacion
                FROM historial h
                LEFT JOIN usuarios u ON h.id_usuario = u.id
                LEFT JOIN servicios s ON h.id_servicio = s.id
                LEFT JOIN combos c ON h.id_combo = c.id
            ";

            if ($esAdmin) {
                $query_final_cancel = $query_canceladas . " WHERE h.id_negocio = ? AND h.cancelada = 1 ORDER BY h.fecha_cancelacion DESC";
                $stmt3 = $conexion2->prepare($query_final_cancel);
                $stmt3->bind_param("i", $id_negocio);
            } else {
                $query_final_cancel = $query_canceladas . " WHERE h.id_usuario = ? AND h.id_negocio = ? AND h.cancelada = 1 ORDER BY h.fecha_cancelacion DESC";
                $stmt3 = $conexion2->prepare($query_final_cancel);
                $stmt3->bind_param("ii", $usuario_id, $id_negocio);
            }

            $stmt3->execute();
            $resultado3 = $stmt3->get_result();

            while($fila = $resultado3->fetch_assoc()): 
            ?>
            <tr data-id="<?= $fila['id'] ?>">
              <td><?= htmlspecialchars($fila['cliente']) ?></td>
              <td><?= htmlspecialchars($fila['servicio']) ?></td>
              <td><?= htmlspecialchars(date("Y-m-d", strtotime($fila['fecha']))) ?></td>
              <td><?= htmlspecialchars(date("Y-m-d", strtotime($fila['fecha_cancelacion']))) ?></td>
              <td>$<?= htmlspecialchars(number_format($fila['precio'], 0, ',', '.')) ?></td>
            </tr>
            <?php endwhile; $stmt3->close(); $conexion2->close(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <footer>© 2025 - Panel de administración</footer>
</div>

<!-- Modal de Confirmación de Cancelación -->
<div id="confirmCancelModal" class="modal-overlay-confirm" style="display: none;">
    <div class="modal-content-confirm">
        <h2 style="margin-bottom: 15px; color: #333;">¿Cancelar Reserva?</h2>
        <p style="margin-bottom: 25px; color: #666; line-height: 1.6;">¿Estás seguro de que deseas cancelar esta reserva? Esta acción no se puede deshacer.</p>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button onclick="cerrarModalConfirm()" class="btn-confirm-cancelar">Mantener Reserva</button>
            <button onclick="confirmarCancelacion()" class="btn-confirm-aceptar">Sí, Cancelar</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay-confirm {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }
    
    .modal-content-confirm {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        text-align: center;
        border-top: 5px solid #f8b6b0;
    }
    
    .btn-confirm-cancelar {
        background-color: #ddd;
        color: #333;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-confirm-cancelar:hover {
        background-color: #ccc;
    }
    
    .btn-confirm-aceptar {
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
    
    .btn-confirm-aceptar:hover {
        background: linear-gradient(135deg, #d8807c 0%, #e89c94 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
</style>

<script>
let reservaIdPendiente = null;

document.addEventListener('DOMContentLoaded', function() {
    // Añadir listeners para filtrar en tiempo real (solo para tabla activa)
    document.getElementById("filtro-nombre").addEventListener("keyup", filtrar);
    document.getElementById("filtro-servicio").addEventListener("keyup", filtrar);
    document.getElementById("filtro-fecha").addEventListener("change", filtrar);
    
    // Agregar listeners para botones de cancelar en tabla activa
    agregarListenersCancelarActivas();

    // Agregar listeners para cambio de tabs
    const reservasActivasTab = document.getElementById('reservasActivas-tab');
    const reservasCanceladasTab = document.getElementById('reservasCanceladas-tab');

    if (reservasActivasTab) {
        reservasActivasTab.addEventListener('shown.bs.tab', function() {
            // Limpiar filtros
            document.getElementById("filtro-nombre").value = '';
            document.getElementById("filtro-servicio").value = '';
            document.getElementById("filtro-fecha").value = '';
            
            // Re-agregar listeners para tabla activa
            agregarListenersCancelarActivas();
        });
    }

    if (reservasCanceladasTab) {
        reservasCanceladasTab.addEventListener('shown.bs.tab', function() {
            // Limpiar filtros (no se usan en canceladas)
            document.getElementById("filtro-nombre").value = '';
            document.getElementById("filtro-servicio").value = '';
            document.getElementById("filtro-fecha").value = '';
        });
    }
});

function agregarListenersCancelarActivas() {
    document.querySelectorAll('#tabla-cuerpo .cancelar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Obtener el ID del atributo data-id
            const idFromDataAttr = this.dataset.id;
            const idFromAttribute = this.getAttribute('data-id');
            
            console.log('Dataset ID:', idFromDataAttr);
            console.log('Attribute ID:', idFromAttribute);
            
            // Usar el ID más confiable
            reservaIdPendiente = idFromDataAttr || idFromAttribute;
            
            if (!reservaIdPendiente || isNaN(reservaIdPendiente)) {
                console.error('No se pudo obtener el ID de la fila:', {
                    dataset: idFromDataAttr,
                    attribute: idFromAttribute,
                    rowData: this.closest('tr')?.dataset
                });
                alert('Error: No se pudo obtener el ID de la cita. Por favor recargue la página.');
                return;
            }
            
            mostrarModalConfirm();
        });
    });
}

function mostrarModalConfirm() {
    document.getElementById('confirmCancelModal').style.display = 'flex';
}

function cerrarModalConfirm() {
    document.getElementById('confirmCancelModal').style.display = 'none';
    reservaIdPendiente = null;
}

function confirmarCancelacion() {
    if (reservaIdPendiente) {
        cerrarModalConfirm();
        cancelarReserva(reservaIdPendiente);
    }
}

function cancelarReserva(reservaId) {
    // Validación del ID
    if (!reservaId || reservaId === 'undefined' || isNaN(reservaId)) {
        console.error('ID de reserva inválido:', reservaId);
        alert('Error: No se pudo obtener el ID de la cita. Por favor recargue la página.');
        return;
    }

    console.log('Cancelando reserva con ID:', reservaId);

    fetch('cancelar_reserva.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_historial: parseInt(reservaId) })
    })
    .then(response => {
        console.log('Respuesta HTTP status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Texto de respuesta:', text);
        try {
            const data = JSON.parse(text);
            console.log('JSON parseado:', data);
            
            if (data.success) {
                alert('Reserva cancelada correctamente');
                
                // Remover la fila del DOM inmediatamente
                const fila = document.querySelector(`tr[data-id="${reservaId}"]`);
                if (fila) {
                    fila.remove();
                    console.log('Fila removida del DOM');
                }
                
                // Recargar la página después de un pequeño delay
                setTimeout(() => {
                    console.log('Recargando página...');
                    window.location.reload(true); // true = fuerza a ignorar el caché
                }, 1000);
            } else {
                alert('Error al cancelar: ' + (data.error || data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            console.error('Respuesta del servidor:', text);
            alert('Respuesta inválida del servidor');
        }
    })
    .catch(error => {
        console.error('Error en la cancelación:', error);
        alert('Error al procesar la cancelación: ' + error.message);
    });
}

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

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(event) {
    const modal = document.getElementById('confirmCancelModal');
    if (event.target === modal) {
        cerrarModalConfirm();
    }
});

</script>

</body>
</html>

<?php $stmt->close(); $conexion->close(); ?>
