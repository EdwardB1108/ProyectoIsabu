<?php
require_once '../classes/Auth.php';
require_once '../classes/Paciente.php';
require_once '../classes/Cita.php';
require_once '../config/db.php';

// Verificar que el usuario sea paciente
Auth::checkRole(['paciente']);

// Crear instancia de la conexión a la base de datos
$database = new Database();
$db = $database->connect();

$paciente = new Paciente($db);
$datosPaciente = $paciente->obtenerPorUsuario($_SESSION['id_usuario']);

// Obtener citas del paciente
$cita = new Cita($db);
$citasPaciente = $cita->obtenerCitasPorPaciente($datosPaciente['id_paciente']);

// Obtener la próxima cita (primera de la lista ordenada por fecha)
$proximaCita = !empty($citasPaciente) ? $citasPaciente[0] : null;

$error = '';
$success = '';

// Procesar actualización de información
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_info'])) {
    // Validar los datos antes de actualizar
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $alergias = trim($_POST['alergias'] ?? '');
    $condiciones_medicas = trim($_POST['condiciones_medicas'] ?? '');
    
    // Validar teléfono (opcional)
    if (!empty($telefono) && !preg_match('/^[0-9]{7,10}$/', $telefono)) {
        $error = 'El formato del teléfono no es válido';
    } else {
        $datosActualizar = [
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'alergias' => $alergias,
            'condiciones_medicas' => $condiciones_medicas
        ];
        
        if ($paciente->actualizarInfo($_SESSION['id_usuario'], $datosActualizar)) {
            $success = 'Información actualizada correctamente';
            // Refrescar datos del paciente después de actualizar
            $datosPaciente = $paciente->obtenerPorUsuario($_SESSION['id_usuario']);
        } else {
            $error = 'Error al actualizar la información. Por favor intente nuevamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
    
    <!-- Barra de navegación lateral -->
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
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i> 
                        <span>Inicio</span>
                    </a>
                </li>
             
                <li>
                    <a href="agendar_cita.php">
                        <i class="fas fa-calendar-plus"></i> 
                        <span>Agendar Cita</span>
                    </a>
                </li>
                <li>
                    <a href="mis_citas.php">
                        <i class="fas fa-calendar-alt"></i> 
                        <span>Mis Citas</span>
                    </a>
                </li>
            </ul>
       </nav>
    </aside>
    
    <!-- Contenedor principal -->
    <main class="dashboard-container">
        <!-- Sección de bienvenida -->
        <section class="welcome-section animate__animated animate__fadeIn">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
            <p class="welcome-message">Desde aquí puedes gestionar tus citas médicas y tu información personal.</p>
        </section>
        
        <!-- Alertas -->
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
        
        <!-- Sección de próxima cita -->
        <section class="appointment-section animate__animated animate__fadeInUp">
            <h2><i class="fas fa-calendar-check"></i> Próxima Cita</h2>
            
            <?php if ($proximaCita): ?>
                <div class="appointment-card">
                    <div class="appointment-details">
                        <div class="appointment-date">
                            <i class="far fa-calendar-alt"></i>
                            <span><?php echo date('d/m/Y', strtotime($proximaCita['fecha_hora'])); ?></span>
                        </div>
                        <div class="appointment-time">
                            <i class="far fa-clock"></i>
                            <span><?php echo date('H:i', strtotime($proximaCita['fecha_hora'])); ?></span>
                        </div>
                        <div class="appointment-doctor">
                            <i class="fas fa-user-md"></i>
                            <span>Dr. <?php echo htmlspecialchars($proximaCita['nombre_medico']); ?></span>
                        </div>
                        <div class="appointment-specialty">
                            <i class="fas fa-stethoscope"></i>
                            <span><?php echo htmlspecialchars($proximaCita['nombre_especialidad']); ?></span>
                        </div>
                        <div class="appointment-status <?php echo htmlspecialchars($proximaCita['estado']); ?>">
                            <i class="fas fa-info-circle"></i>
                            <span><?php echo ucfirst($proximaCita['estado']); ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="appointment-card empty-card">
                    <p>No tienes citas programadas</p>
                    <a href="agendar_cita.php" class="btn btn-primary">Agendar nueva cita</a>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Sección de información del paciente -->
        <section class="patient-info-section animate__animated animate__fadeInUp" id="perfil">
            <div class="section-header">
                <h2><i class="fas fa-user"></i> Información Personal</h2>
                <button class="btn btn-edit" id="edit-info-btn">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
            
            <div class="info-grid" id="info-display">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-id-card"></i> Nombre completo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-id-badge"></i> Cédula:</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['id_cedula']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-phone"></i> Teléfono:</span>
                    <span class="info-value"><?php echo htmlspecialchars($datosPaciente['telefono'] ?? 'No registrado'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-birthday-cake"></i> Fecha de nacimiento:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['fecha_nacimiento']) ? date('d/m/Y', strtotime($datosPaciente['fecha_nacimiento'])) : 'No registrada'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-venus-mars"></i> Género:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['genero']) ? ucfirst($datosPaciente['genero']) : 'No registrado'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-map-marker-alt"></i> Dirección:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['direccion']) ? htmlspecialchars($datosPaciente['direccion']) : 'No registrada'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-hospital"></i> EPS:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['nombre_eps']) ? htmlspecialchars($datosPaciente['nombre_eps']) : 'No registrada'; ?></span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label"><i class="fas fa-allergies"></i> Alergias:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['alergias']) ? htmlspecialchars($datosPaciente['alergias']) : 'Ninguna registrada'; ?></span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label"><i class="fas fa-file-medical"></i> Condiciones médicas:</span>
                    <span class="info-value"><?php echo !empty($datosPaciente['condiciones_medicas']) ? htmlspecialchars($datosPaciente['condiciones_medicas']) : 'Ninguna registrada'; ?></span>
                </div>
            </div>
            
            <!-- Formulario de edición (oculto inicialmente) -->
            <form method="POST" class="edit-form" id="edit-info-form" style="display: none;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha_nacimiento"><i class="fas fa-birthday-cake"></i> Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                            value="<?php echo htmlspecialchars($datosPaciente['fecha_nacimiento'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="genero"><i class="fas fa-venus-mars"></i> Género</label>
                        <select name="genero" id="genero">
                            <option value="">Seleccione...</option>
                            <option value="masculino" <?php echo ($datosPaciente['genero'] ?? '') === 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="femenino" <?php echo ($datosPaciente['genero'] ?? '') === 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                            <option value="otro" <?php echo ($datosPaciente['genero'] ?? '') === 'otro' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" 
                            value="<?php echo htmlspecialchars($datosPaciente['telefono'] ?? ''); ?>"
                            pattern="[0-9]{7,10}" title="Ingrese un número de teléfono válido (7-10 dígitos)">
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                        <input type="text" name="direccion" id="direccion" 
                            value="<?php echo htmlspecialchars($datosPaciente['direccion'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="alergias"><i class="fas fa-allergies"></i> Alergias</label>
                        <textarea name="alergias" id="alergias" rows="2"><?php echo htmlspecialchars($datosPaciente['alergias'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="condiciones_medicas"><i class="fas fa-file-medical"></i> Condiciones médicas</label>
                        <textarea name="condiciones_medicas" id="condiciones_medicas" rows="2"><?php echo htmlspecialchars($datosPaciente['condiciones_medicas'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-edit-btn">Cancelar</button>
                    <button type="submit" name="actualizar_info" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </section>
    </main>
    
    <!-- Pie de página -->
    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>

    <!-- Botón flotante de WhatsApp para soporte -->
    <a href="https://wa.me/573209539761?text=Hola,%20soy%20<?php echo urlencode($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?>%20y%20necesito%20soporte%20con%20mi%20cuenta%20de%20paciente" 
       class="whatsapp-float" 
       target="_blank"
       aria-label="Contactar soporte por WhatsApp">
       <i class="fab fa-whatsapp"></i>
    </a>

    <script>
        // Script para mostrar/ocultar el formulario de edición
        document.getElementById('edit-info-btn').addEventListener('click', function() {
            document.getElementById('info-display').style.display = 'none';
            document.getElementById('edit-info-form').style.display = 'block';
            this.style.display = 'none';
        });
        
        document.getElementById('cancel-edit-btn').addEventListener('click', function() {
            document.getElementById('info-display').style.display = 'grid';
            document.getElementById('edit-info-form').style.display = 'none';
            document.getElementById('edit-info-btn').style.display = 'block';
        });
        
        // Validación del formulario
        document.getElementById('edit-info-form').addEventListener('submit', function(event) {
            const telefono = document.getElementById('telefono').value.trim();
            if (telefono !== '' && !/^[0-9]{7,10}$/.test(telefono)) {
                alert('Por favor ingrese un número de teléfono válido (7-10 dígitos)');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>