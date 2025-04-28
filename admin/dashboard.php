<?php
require_once '../classes/Auth.php';
require_once '../config/db.php';

// Verificar que el usuario sea administrador
Auth::checkRole(['admin']);

// Crear instancia de la base de datos
$database = new Database();
$conexion = $database->connect();

// Obtener estadísticas básicas
function getStatistics($conn) {
    $stats = [
        'total_citas' => 0,
        'citas_hoy' => 0,
        'citas_pendientes' => 0,
        'total_pacientes' => 0
    ];
    
    // Total de citas
    $sql = "SELECT COUNT(*) as total FROM citas";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['total_citas'] = $result['total'];
    }
    
    // Citas de hoy
    $sql = "SELECT COUNT(*) as total FROM citas WHERE DATE(fecha_hora) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['citas_hoy'] = $result['total'];
    }
    
    // Citas pendientes
    $sql = "SELECT COUNT(*) as total FROM citas WHERE estado = 'pendiente'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['citas_pendientes'] = $result['total'];
    }
    
    // Total de pacientes
    $sql = "SELECT COUNT(*) as total FROM pacientes";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['total_pacientes'] = $result['total'];
    }
    
    return $stats;
}

// Obtener las citas del día
function getCitasHoy($conn) {
    $citas = [];
    $sql = "SELECT c.id_cita, c.fecha_hora, c.motivo, c.estado, 
            u.nombre, u.apellido, u.id_cedula as cedula, e.nombre as eps_nombre
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            JOIN eps e ON u.id_eps = e.id_eps
            WHERE DATE(c.fecha_hora) = CURDATE()
            ORDER BY c.fecha_hora ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stats = getStatistics($conexion);
$citasHoy = getCitasHoy($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

</head>
<body>
    <!-- Barra GOV.CO -->
    <div class="gov-bar">
        <img src="../assets/img/gov-co-logo.png" alt="GOV.CO" class="gov-co-image">
    </div>
    
    <!-- Encabezado principal -->
    <header class="main-header">
        <div class="logo-container">
            <img src="../assets/img/isabu-logo.png" alt="ISABU Logo" class="logo">
        </div>
    </header>
    
    <!-- Contenedor del Dashboard -->
    <div class="dashboard-container">
        <!-- Sidebar / Menú lateral -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3>Panel Administrativo</h3>
                <p><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></p>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Inicio</a></li>
                    <li><a href="agendar_cita.php"><i class="fas fa-calendar-plus"></i> Agendar Citas</a></li>
                    <li><a href="gestionar_citas.php"><i class="fas fa-calendar-check"></i> Gestionar Citas</a></li>
                    <li><a href="pacientes.php"><i class="fas fa-users"></i> Pacientes</a></li>
                    <li><a href="medicos.php"><i class="fas fa-user-md"></i> Médicos</a></li>
                    <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                    <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
        
        <!-- Contenido principal -->
        <main class="main-content">
            <h1 class="page-title"><p><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></p>Bienvenido Panel de Control </h1>
            
            <!-- Acciones rápidas -->
            <div class="actions">
                <a href="agendar_cita.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Cita</a>
                <a href="reportes.php" class="btn btn-export"><i class="fas fa-file-export"></i> Exportar Datos</a>
            </div>
            
            <!-- Tarjetas de estadísticas -->
            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3><?php echo $stats['total_citas']; ?></h3>
                    <p>Total de Citas</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3><?php echo $stats['citas_hoy']; ?></h3>
                    <p>Citas Hoy</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-hourglass-half"></i>
                    <h3><?php echo $stats['citas_pendientes']; ?></h3>
                    <p>Citas Pendientes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-injured"></i>
                    <h3><?php echo $stats['total_pacientes']; ?></h3>
                    <p>Pacientes Registrados</p>
                </div>
            </div>
            
            <!-- Citas de hoy -->
            <h2>Citas del Día</h2>
            <?php if (empty($citasHoy)): ?>
                <p>No hay citas programadas para hoy.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Cédula</th>
                            <th>EPS</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citasHoy as $cita): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($cita['fecha_hora'])); ?></td>
                                <td><?php echo $cita['nombre'] . ' ' . $cita['apellido']; ?></td>
                                <td><?php echo $cita['cedula']; ?></td>
                                <td><?php echo $cita['eps_nombre']; ?></td>
                                <td><?php echo $cita['motivo']; ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = '';
                                    switch ($cita['estado']) {
                                        case 'pendiente':
                                            $badgeClass = 'badge-pending';
                                            break;
                                        case 'confirmada':
                                            $badgeClass = 'badge-confirmed';
                                            break;
                                        case 'completada':
                                            $badgeClass = 'badge-completed';
                                            break;
                                        case 'cancelada':
                                            $badgeClass = 'badge-cancelled';
                                            break;
                                        case 'no_asistio':
                                            $badgeClass = 'badge-no-show';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($cita['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_cita.php?id=<?php echo $cita['id_cita']; ?>" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar_cita.php?id=<?php echo $cita['id_cita']; ?>" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="historial.php?id=<?php echo $cita['id_cita']; ?>" title="Historial médico">
                                        <i class="fas fa-notes-medical"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
        
    <!-- Pie de página -->
<footer>
    <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
</footer>

    <script>
        // Script para manejar el comportamiento interactivo del dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle para el sidebar en dispositivos móviles
            const toggleSidebarBtn = document.createElement('button');
            toggleSidebarBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleSidebarBtn.classList.add('toggle-sidebar');
            toggleSidebarBtn.style.position = 'fixed';
            toggleSidebarBtn.style.top = '75px';
            toggleSidebarBtn.style.left = '10px';
            toggleSidebarBtn.style.zIndex = '1000';
            toggleSidebarBtn.style.display = 'none';
            toggleSidebarBtn.style.padding = '10px';
            toggleSidebarBtn.style.backgroundColor = '#306b34';
            toggleSidebarBtn.style.color = 'white';
            toggleSidebarBtn.style.border = 'none';
            toggleSidebarBtn.style.borderRadius = '4px';
            
            document.body.appendChild(toggleSidebarBtn);
            
            const sidebar = document.querySelector('.sidebar');
            
            toggleSidebarBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Mostrar/ocultar botón según el tamaño de la pantalla
            function checkWidth() {
                if (window.innerWidth <= 768) {
                    toggleSidebarBtn.style.display = 'block';
                    sidebar.classList.remove('active');
                } else {
                    toggleSidebarBtn.style.display = 'none';
                    sidebar.classList.remove('active');
                }
            }
            
            // Comprobar al cargar y al cambiar el tamaño
            checkWidth();
            window.addEventListener('resize', checkWidth);
        });
    </script>
</body>
</html>