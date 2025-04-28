<?php
require_once '../classes/Auth.php';
require_once '../classes/Cita.php';
require_once '../classes/Paciente.php';
require_once '../config/db.php';

// Verificar que el usuario sea paciente
Auth::checkRole(['paciente']);

// Crear instancia de la conexión a la base de datos
$database = new Database();
$db = $database->connect(); // Cambiado de getConnection() a connect()

$cita = new Cita($db);
$paciente = new Paciente($db);
$datosPaciente = $paciente->obtenerPorUsuario($_SESSION['id_usuario']);


// Verificar si se obtuvo el paciente correctamente
if ($datosPaciente === false) {
    die("Error: No se pudo obtener la información del paciente. Por favor contacte al administrador.");
}

$error = '';
$success = '';

// Obtener especialidades disponibles
$especialidades = $cita->obtenerEspecialidades();

// Procesar el formulario de agendamiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    $id_especialidad = $_POST['id_especialidad'] ?? '';
    $id_medico = $_POST['id_medico'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    // Validaciones básicas
    if (empty($id_especialidad)) {
        $error = 'Debe seleccionar una especialidad';
    } elseif (empty($id_medico)) {
        $error = 'Debe seleccionar un médico';
    } elseif (empty($fecha)) {
        $error = 'Debe seleccionar una fecha';
    } elseif (empty($hora_inicio)) {
        $error = 'Debe seleccionar un horario disponible';
    } elseif (empty($motivo) || strlen($motivo) < 10) {
        $error = 'El motivo de la consulta debe tener al menos 10 caracteres';
    } else {
        $fecha_hora = $fecha . ' ' . $hora_inicio;
        $id_cita = $cita->crearCita(
            $datosPaciente['id_paciente'],
            $id_medico,
            $id_especialidad,
            $fecha_hora,
            $motivo
        );
        
        if ($id_cita) {
            $success = 'Cita agendada correctamente para el ' . date('d/m/Y', strtotime($fecha)) . 
                      ' a las ' . date('h:i A', strtotime($hora_inicio));
            unset($_POST);
        } else {
            $error = 'Error al agendar la cita. Por favor intente nuevamente.';
        }
    }
}

// Obtener médicos si se ha seleccionado una especialidad
$medicos = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_especialidad']) && !empty($_POST['id_especialidad'])) {
    $medicos = $cita->obtenerMedicosPorEspecialidad($_POST['id_especialidad']);
}

// Obtener horarios disponibles si se ha seleccionado médico y fecha
$horariosDisponibles = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_medico']) && isset($_POST['fecha']) && !empty($_POST['id_medico']) && !empty($_POST['fecha'])) {
    $horariosDisponibles = $cita->obtenerHorariosDisponibles($_POST['id_medico'], $_POST['fecha']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .time-slot {
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .time-slot:hover {
            background-color: #f0f4f8;
            border-color: #1e40af;
        }
        .time-slot.selected {
            background-color: #1e40af;
            color: white;
            border-color: #1e40af;
        }
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .no-slots {
            color: #666;
            font-style: italic;
            padding: 10px;
            text-align: center;
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
         
                <li class="active"><a href="agendar_cita.php"><i class="fas fa-calendar-plus"></i> <span>Agendar Cita</span></a></li>
                <li><a href="mis_citas.php"><i class="fas fa-calendar-alt"></i> <span>Mis Citas</span></a></li>
            </ul>
        </nav>
    </aside>
    
    <main class="dashboard-container">
        <section class="welcome-section animate__animated animate__fadeIn">
            <h1>Agendar Nueva Cita</h1>
            <p class="welcome-message">Complete el formulario para programar su cita médica.</p>
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
            <form method="POST" id="cita-form">
                <div class="form-step active" id="step1">
                    <div class="form-group">
                        <label for="id_especialidad"><i class="fas fa-stethoscope"></i> Especialidad Médica</label>
                        <select name="id_especialidad" id="id_especialidad" required>
                            <option value="">Seleccione una especialidad</option>
                            <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?php echo $especialidad['id_especialidad']; ?>"
                                    <?php echo isset($_POST['id_especialidad']) && $_POST['id_especialidad'] == $especialidad['id_especialidad'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($especialidad['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary" disabled>Anterior</button>
                        <button type="button" class="btn btn-primary next-btn" data-next="step2">Siguiente</button>
                    </div>
                </div>
                
                <div class="form-step" id="step2">
                    <div class="form-group">
                        <label for="id_medico"><i class="fas fa-user-md"></i> Médico</label>
                        <select name="id_medico" id="id_medico" required>
                            <option value="">Seleccione un médico</option>
                            <?php foreach ($medicos as $medico): ?>
                                <option value="<?php echo $medico['id_medico']; ?>"
                                    <?php echo isset($_POST['id_medico']) && $_POST['id_medico'] == $medico['id_medico'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary prev-btn" data-prev="step1">Anterior</button>
                        <button type="button" class="btn btn-primary next-btn" data-next="step3">Siguiente</button>
                    </div>
                </div>
                
                <div class="form-step" id="step3">
                    <div class="form-group">
                        <label for="fecha"><i class="far fa-calendar-alt"></i> Fecha de la cita</label>
                        <input type="date" name="fecha" id="fecha" min="<?php echo date('Y-m-d'); ?>" 
                               value="<?php echo $_POST['fecha'] ?? ''; ?>" required>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary prev-btn" data-prev="step2">Anterior</button>
                        <button type="button" class="btn btn-primary next-btn" data-next="step4">Siguiente</button>
                    </div>
                </div>
                
                <div class="form-step" id="step4">
                    <div class="form-group">
                        <label><i class="far fa-clock"></i> Horarios disponibles</label>
                        <div class="time-slots">
                            <?php if (!empty($horariosDisponibles)): ?>
                                <?php foreach ($horariosDisponibles as $horario): ?>
                                    <div class="time-slot" data-horario="<?php echo $horario['hora_inicio']; ?>">
                                        <?php echo date('h:i A', strtotime($horario['hora_inicio'])); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-slots">Seleccione un médico y una fecha para ver los horarios disponibles</p>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="hora_inicio" id="hora_inicio" required>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary prev-btn" data-prev="step3">Anterior</button>
                        <button type="button" class="btn btn-primary next-btn" data-next="step5">Siguiente</button>
                    </div>
                </div>
                
                <div class="form-step" id="step5">
                    <div class="form-group full-width">
                        <label for="motivo"><i class="fas fa-comment-medical"></i> Motivo de la consulta</label>
                        <textarea name="motivo" id="motivo" rows="4" required><?php echo $_POST['motivo'] ?? ''; ?></textarea>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary prev-btn" data-prev="step4">Anterior</button>
                        <button type="submit" name="agendar_cita" class="btn btn-primary">Agendar Cita</button>
                    </div>
                </div>
            </form>
        </section>
    </main>
    
    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.next-btn').click(function() {
                const nextStep = $(this).data('next');
                const currentStep = $(this).closest('.form-step').attr('id');
                
                if (validateStep(currentStep)) {
                    $('#' + currentStep).removeClass('active');
                    $('#' + nextStep).addClass('active');
                    
                    if (currentStep === 'step1') {
                        const idEspecialidad = $('#id_especialidad').val();
                        if (idEspecialidad) {
                            loadMedicos(idEspecialidad);
                        }
                    }
                    
                    if (currentStep === 'step2' || currentStep === 'step3') {
                        const idMedico = $('#id_medico').val();
                        const fecha = $('#fecha').val();
                        if (idMedico && fecha) {
                            loadHorarios(idMedico, fecha);
                        }
                    }
                }
            });
            
            $('.prev-btn').click(function() {
                const prevStep = $(this).data('prev');
                const currentStep = $(this).closest('.form-step').attr('id');
                $('#' + currentStep).removeClass('active');
                $('#' + prevStep).addClass('active');
            });
            
            $(document).on('click', '.time-slot', function() {
                $('.time-slot').removeClass('selected');
                $(this).addClass('selected');
                $('#hora_inicio').val($(this).data('horario'));
            });
            
            $('#id_especialidad').change(function() {
                const idEspecialidad = $(this).val();
                if (idEspecialidad) {
                    loadMedicos(idEspecialidad);
                }
            });
            
            $('#id_medico, #fecha').change(function() {
                const idMedico = $('#id_medico').val();
                const fecha = $('#fecha').val();
                if (idMedico && fecha) {
                    loadHorarios(idMedico, fecha);
                }
            });
            
            function validateStep(stepId) {
                let isValid = true;
                if (stepId === 'step1' && !$('#id_especialidad').val()) {
                    alert('Por favor seleccione una especialidad');
                    isValid = false;
                } else if (stepId === 'step2' && !$('#id_medico').val()) {
                    alert('Por favor seleccione un médico');
                    isValid = false;
                } else if (stepId === 'step3' && !$('#fecha').val()) {
                    alert('Por favor seleccione una fecha');
                    isValid = false;
                } else if (stepId === 'step4' && !$('#hora_inicio').val()) {
                    alert('Por favor seleccione un horario');
                    isValid = false;
                }
                return isValid;
            }
            
            function loadMedicos(idEspecialidad) {
                $.ajax({
                    url: 'ajax/get_medicos.php',
                    type: 'POST',
                    data: { id_especialidad: idEspecialidad },
                    success: function(response) {
                        $('#id_medico').html('<option value="">Seleccione un médico</option>' + response);
                    },
                    error: function() {
                        alert('Error al cargar los médicos');
                    }
                });
            }
            
            function loadHorarios(idMedico, fecha) {
                $.ajax({
                    url: 'ajax/get_horarios.php',
                    type: 'POST',
                    data: { id_medico: idMedico, fecha: fecha },
                    success: function(response) {
                        $('.time-slots').html(response);
                        $('#hora_inicio').val('');
                    },
                    error: function() {
                        $('.time-slots').html('<p class="no-slots">Error al cargar los horarios disponibles</p>');
                    }
                });
            }
        });
    </script>
</body>
</html>