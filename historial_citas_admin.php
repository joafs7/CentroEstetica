<?php
session_start();
// Redirigir si no es admin o no ha iniciado sesión
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.php'); // O a la página de inicio de sesión
    exit();
}
$id_negocio = $_SESSION['id_negocio_admin'] ?? 1; // Asume 1 si no está definido
$nombre_negocio = ($id_negocio == 2) ? "Juliette Nails" : "Kore Estética";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Citas - <?php echo htmlspecialchars($nombre_negocio); ?></title>
    <!-- Incluye aquí tus CSS, por ejemplo Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 2rem;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .table-responsive {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Historial de Citas - <?php echo htmlspecialchars($nombre_negocio); ?></h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p>Filtrar por período:</p>
                    <div class="btn-group" role="group" aria-label="Filtros de período">
                        <button type="button" class="btn btn-outline-primary active" data-periodo="semana">Última Semana</button>
                        <button type="button" class="btn btn-outline-primary" data-periodo="mes">Último Mes</button>
                        <button type="button" class="btn btn-outline-primary" data-periodo="tres_meses">Últimos 3 Meses</button>
                        <button type="button" class="btn btn-outline-primary" data-periodo="todos">Todos</button>
                    </div>
                </div>

                <div id="cargando" class="text-center" style="display: none;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody id="historial-body">
                            <!-- Las filas se insertarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <?php 
                        // Determina a qué página volver según el negocio del admin
                        $pagina_volver = ($id_negocio == 2) ? "JulietteNails.php" : "Kore_Estetica-Inicio.php";
                    ?>
                    <a href="<?php echo $pagina_volver; ?>" class="btn btn-secondary">Volver al Panel</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idNegocio = <?php echo json_encode($id_negocio); ?>;
        const historialBody = document.getElementById('historial-body');
        const cargandoDiv = document.getElementById('cargando');
        const botonesFiltro = document.querySelectorAll('.btn-group .btn');

        function cargarHistorial(periodo = 'semana') {
            cargandoDiv.style.display = 'block';
            historialBody.innerHTML = '';

            fetch(`obtener_historial.php?id_negocio=${idNegocio}&periodo=${periodo}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta de la red');
                    }
                    return response.json();
                })
                .then(data => {
                    cargandoDiv.style.display = 'none';
                    if (data.length === 0) {
                        historialBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay registros para el período seleccionado.</td></tr>';
                        return;
                    }

                    data.forEach(item => {
                        const fecha = new Date(item.fecha_realizacion).toLocaleString('es-ES');
                        const precio = parseFloat(item.precio).toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });

                        const fila = `
                            <tr>
                                <td>${fecha}</td>
                                <td>${item.cliente || 'N/A'}</td>
                                <td>${item.servicio || 'No especificado'}</td>
                                <td>${item.categoria_nombre || 'General'}</td>
                                <td>${precio}</td>
                            </tr>
                        `;
                        historialBody.innerHTML += fila;
                    });
                })
                .catch(error => {
                    cargandoDiv.style.display = 'none';
                    console.error('Error al cargar el historial:', error);
                    historialBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar el historial.</td></tr>';
                });
        }

        botonesFiltro.forEach(boton => {
            boton.addEventListener('click', function() {
                // Quitar clase activa de todos los botones
                botonesFiltro.forEach(b => b.classList.remove('active'));
                // Añadir clase activa al botón clickeado
                this.classList.add('active');
                
                const periodo = this.getAttribute('data-periodo');
                cargarHistorial(periodo);
            });
        });

        // Carga inicial del historial (última semana)
        cargarHistorial('semana');
    });
    </script>
    <!-- Incluye aquí tus JS, por ejemplo Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>