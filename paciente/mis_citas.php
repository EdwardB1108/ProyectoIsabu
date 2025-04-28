<?php
require_once '../classes/Auth.php';
require_once '../classes/Cita.php';
require_once '../classes/Paciente.php';
require_once '../config/db.php';

Auth::checkRole(['paciente']);

$database = new Database();
$conn = $database->connect();

$cita = new Cita($conn);
$paciente = new Paciente($conn);

$datosPaciente = $paciente->obtenerPorUsuario($_SESSION['id_usuario']);
if ($datosPaciente === false) {
    die("Error: No se pudo obtener la información del paciente.");
}

$error = '';
$success = '';

// Mostrar mensaje de éxito si se canceló una cita
if (isset($_GET['success']) && $_GET['success'] === 'cita_cancelada') {
    $success = 'La cita ha sido cancelada correctamente.';
}

// Mostrar mensaje de error si no se encontró la cita
if (isset($_GET['error']) && $_GET['error'] === 'cita_no_encontrada') {
    $error = 'La cita solicitada no existe o no pertenece a usted.';
}

// Procesar cancelación de cita desde esta página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_cita'])) {
    $id_cita = $_POST['id_cita'] ?? '';
    
    if (!empty($id_cita)) {
        $resultado = $cita->cancelarCita($id_cita, $datosPaciente['id_paciente']);
        
        if ($resultado) {
            header('Location: mis_citas.php?success=cita_cancelada');
            exit;
        } else {
            $error = 'Error al cancelar la cita. Por favor intente nuevamente.';
        }
    }
}

$citas = $cita->obtenerCitasPorPaciente($datosPaciente['id_paciente']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .appointment-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-5px);
        }
        
        .appointment-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .appointment-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-badge.pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.confirmada {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.completada {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-badge.cancelada {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.no_asistio {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .appointment-body {
            padding: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .info-item i {
            margin-right: 10px;
            color: #6c757d;
            width: 20px;
            text-align: center;
        }
        
        .info-item.full-width {
            grid-column: 1 / -1;
        }
        
        .appointment-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            margin: 10px 0;
            color: #333;
        }
        
        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="gov-bar">
        <img src="../assets/img/gov-co-logo.png" alt="GOV.CO" class="gov-co-image">
    </div>
    
    <header class="main-header">
        <div class="logo-container">
            <img src="../assets/img/isabu-logo.png" alt="ISABU Logo" class="logo">
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
                <span class="user-role">Paciente</span>
            </div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
                <span class="user-role">Paciente</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
                <li><a href="agendar_cita.php"><i class="fas fa-calendar-plus"></i> <span>Agendar Cita</span></a></li>
                <li class="active"><a href="mis_citas.php"><i class="fas fa-calendar-alt"></i> <span>Mis Citas</span></a></li>
            </ul>
        </nav>
    </aside>
    
    <main class="dashboard-container">
        <section class="welcome-section animate__animated animate__fadeIn">
            <h1>Mis Citas Médicas</h1>
            <p class="welcome-message">Aquí puedes ver el historial de tus citas médicas agendadas.</p>
        </section>
        
        <?php if ($error): ?>
            <div class="alert alert-error animate__animated animate__shakeX">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <section class="appointment-section animate__animated animate__fadeInUp">
            <?php if (!empty($citas)): ?>
                <div class="appointments-grid">
                    <?php foreach ($citas as $cita): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <h3><?php echo htmlspecialchars($cita['nombre_especialidad'] ?? 'Especialidad no especificada'); ?></h3>
                                <span class="status-badge <?php echo htmlspecialchars($cita['estado'] ?? ''); ?>">
                                    <?php echo ucfirst($cita['estado'] ?? ''); ?>
                                </span>
                            </div>
                            
                            <div class="appointment-body">
                                <div class="appointment-info">
                                    <div class="info-item">
                                        <i class="fas fa-user-md"></i>
                                        <span>Dr. <?php echo htmlspecialchars($cita['nombre_medico'] ?? 'Médico no especificado'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="far fa-calendar-alt"></i>
                                        <span><?php echo date('d/m/Y', strtotime($cita['fecha_hora'] ?? '')); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="far fa-clock"></i>
                                        <span><?php echo date('h:i A', strtotime($cita['fecha_hora'] ?? '')); ?></span>
                                    </div>
                                    <?php if (!empty($cita['motivo'])): ?>
                                        <div class="info-item full-width">
                                            <i class="fas fa-comment-medical"></i>
                                            <span><?php echo htmlspecialchars($cita['motivo']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="appointment-actions">
                                    <a href="detalle_cita.php?id=<?php echo $cita['id_cita'] ?? ''; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver detalles
                                    </a>
                                    <?php if (($cita['estado'] ?? '') == 'pendiente' || ($cita['estado'] ?? '') == 'confirmada'): ?>
                                        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita?');">
                                            <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita'] ?? ''; ?>">
                                            <button type="submit" name="cancelar_cita" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No tienes citas registradas</h3>
                    <p>Puedes agendar una nueva cita haciendo clic en el botón de abajo</p>
                    <a href="agendar_cita.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Agendar Cita
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>
</body>
</html>