<?php
require_once '../classes/Auth.php';
require_once '../classes/Cita.php';
require_once '../classes/Paciente.php';
require_once '../config/db.php';

// Verificar autenticación y rol
Auth::checkRole(['paciente']);

// Establecer conexión a la base de datos
$database = new Database();
$conn = $database->connect();

// Inicializar objetos
$cita = new Cita($conn);
$paciente = new Paciente($conn);

// Obtener datos del paciente
$datosPaciente = $paciente->obtenerPorUsuario($_SESSION['id_usuario']);
if ($datosPaciente === false) {
    die("Error: No se pudo obtener la información del paciente. Por favor contacte al administrador.");
}

// Inicializar variables
$error = '';
$success = '';

// Verificar ID de cita
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mis_citas.php');
    exit;
}

$id_cita = $_GET['id'];

// Obtener detalles de la cita
$detalleCita = $cita->obtenerDetalleCita($id_cita, $datosPaciente['id_paciente']);
if (!$detalleCita) {
    header('Location: mis_citas.php?error=cita_no_encontrada');
    exit;
}

// Procesar cancelación de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_cita'])) {
    if ($cita->cancelarCita($id_cita, $datosPaciente['id_paciente'])) {
        header('Location: mis_citas.php?success=cita_cancelada');
        exit;
    } else {
        $error = 'Error al cancelar la cita. Por favor intente nuevamente.';
    }
}

// Procesar modificación de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_cita'])) {
    $id_especialidad = $_POST['id_especialidad'] ?? '';
    $id_medico = $_POST['id_medico'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    if (empty($id_especialidad) || empty($id_medico) || empty($fecha) || empty($hora_inicio) || empty($motivo)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (strlen($motivo) < 10) {
        $error = 'El motivo de la consulta debe tener al menos 10 caracteres';
    } else {
        $fecha_hora = $fecha . ' ' . $hora_inicio;
        if ($cita->modificarCita($id_cita, $datosPaciente['id_paciente'], $id_medico, $id_especialidad, $fecha_hora, $motivo)) {
            $success = 'Cita modificada correctamente.';
            $detalleCita = $cita->obtenerDetalleCita($id_cita, $datosPaciente['id_paciente']);
        } else {
            $error = 'Error al modificar la cita. Por favor intente nuevamente.';
        }
    }
}

// Obtener datos para formularios
$especialidades = $cita->obtenerEspecialidades();
$medicos = $cita->obtenerMedicosPorEspecialidad($detalleCita['id_especialidad']);
$horariosDisponibles = [];

if (isset($_POST['id_medico']) && isset($_POST['fecha'])) {
    $horariosDisponibles = $cita->obtenerHorariosDisponibles($_POST['id_medico'], $_POST['fecha'], $id_cita);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Cita - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .cita-detalle {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cita-titulo {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
        }
        
        .cita-estado {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .cita-estado.pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .cita-estado.confirmada {
            background: #d4edda;
            color: #155724;
        }
        
        .cita-estado.completada {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .cita-estado.cancelada {
            background: #f8d7da;
            color: #721c24;
        }
        
        .cita-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-grupo {
            margin-bottom: 10px;
        }
        
        .info-etiqueta {
            display: block;
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-valor {
            font-weight: 500;
            color: #333;
        }
        
        .info-grupo.full-width {
            grid-column: 1 / -1;
        }
        
        .cita-acciones {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-contenido {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 70%;
            max-width: 800px;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-titulo {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        
        .cerrar-modal {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .cerrar-modal:hover,
        .cerrar-modal:focus {
            color: #333;
            text-decoration: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
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
        
        .no-slots {
            color: #666;
            font-style: italic;
            padding: 10px;
            text-align: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            <h1>Detalle de Cita Médica</h1>
            <p class="welcome-message">Información detallada de su cita médica.</p>
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
        
        <section class="cita-detalle animate__animated animate__fadeInUp">
            <div class="cita-header">
                <h2 class="cita-titulo"><?php echo htmlspecialchars($detalleCita['nombre_especialidad']); ?></h2>
                <span class="cita-estado <?php echo htmlspecialchars($detalleCita['estado']); ?>">
                    <?php echo ucfirst(htmlspecialchars($detalleCita['estado'])); ?>
                </span>
            </div>
            
            <div class="cita-info">
                <div class="info-grupo">
                    <span class="info-etiqueta"><i class="fas fa-user-md"></i> Médico</span>
                    <span class="info-valor">Dr. <?php echo htmlspecialchars($detalleCita['nombre_medico']); ?></span>
                </div>
                
                <div class="info-grupo">
                    <span class="info-etiqueta"><i class="fas fa-stethoscope"></i> Especialidad</span>
                    <span class="info-valor"><?php echo htmlspecialchars($detalleCita['nombre_especialidad']); ?></span>
                </div>
                
                <div class="info-grupo">
                    <span class="info-etiqueta"><i class="far fa-calendar-alt"></i> Fecha</span>
                    <span class="info-valor"><?php echo date('d/m/Y', strtotime($detalleCita['fecha_hora'])); ?></span>
                </div>
                
                <div class="info-grupo">
                    <span class="info-etiqueta"><i class="far fa-clock"></i> Hora</span>
                    <span class="info-valor"><?php echo date('h:i A', strtotime($detalleCita['fecha_hora'])); ?></span>
                </div>
                
                <?php if (!empty($detalleCita['motivo'])): ?>
                <div class="info-grupo full-width">
                    <span class="info-etiqueta"><i class="fas fa-comment-medical"></i> Motivo de la consulta</span>
                    <span class="info-valor"><?php echo htmlspecialchars($detalleCita['motivo']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="cita-acciones">
                <a href="mis_citas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                
                <?php if ($detalleCita['estado'] == 'pendiente' || $detalleCita['estado'] == 'confirmada'): ?>
                    <button id="btn-modificar" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modificar Cita
                    </button>
                    
                    <button id="btn-cancelar" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancelar Cita
                    </button>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <!-- Modal para modificar cita -->
    <div id="modal-modificar" class="modal">
        <div class="modal-contenido">
            <div class="modal-header">
                <h3 class="modal-titulo">Modificar Cita</h3>
                <span class="cerrar-modal" id="cerrar-modificar">&times;</span>
            </div>
            
            <form method="POST" id="form-modificar">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_especialidad"><i class="fas fa-stethoscope"></i> Especialidad Médica</label>
                        <select name="id_especialidad" id="id_especialidad" required>
                            <option value="">Seleccione una especialidad</option>
                            <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?php echo $especialidad['id_especialidad']; ?>"
                                    <?php echo $detalleCita['id_especialidad'] == $especialidad['id_especialidad'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($especialidad['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_medico"><i class="fas fa-user-md"></i> Médico</label>
                        <select name="id_medico" id="id_medico" required>
                            <option value="">Seleccione un médico</option>
                            <?php foreach ($medicos as $medico): ?>
                                <option value="<?php echo $medico['id_medico']; ?>"
                                    <?php echo $detalleCita['id_medico'] == $medico['id_medico'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha"><i class="far fa-calendar-alt"></i> Fecha de la cita</label>
                        <input type="date" name="fecha" id="fecha" min="<?php echo date('Y-m-d'); ?>" 
                               value="<?php echo date('Y-m-d', strtotime($detalleCita['fecha_hora'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="far fa-clock"></i> Horarios disponibles</label>
                        <div class="time-slots" id="horarios-container">
                            <?php if (!empty($horariosDisponibles)): ?>
                                <?php foreach ($horariosDisponibles as $horario): ?>
                                    <div class="time-slot <?php echo date('H:i:s', strtotime($detalleCita['fecha_hora'])) == $horario['hora_inicio'] ? 'selected' : ''; ?>" 
                                         data-horario="<?php echo $horario['hora_inicio']; ?>">
                                        <?php echo date('h:i A', strtotime($horario['hora_inicio'])); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="time-slot selected" data-horario="<?php echo date('H:i:s', strtotime($detalleCita['fecha_hora'])); ?>">
                                    <?php echo date('h:i A', strtotime($detalleCita['fecha_hora'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="hora_inicio" id="hora_inicio" 
                               value="<?php echo date('H:i:s', strtotime($detalleCita['fecha_hora'])); ?>" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="motivo"><i class="fas fa-comment-medical"></i> Motivo de la consulta</label>
                        <textarea name="motivo" id="motivo" rows="4" required><?php echo htmlspecialchars($detalleCita['motivo']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" id="cancelar-modificacion">Cancelar</button>
                    <button type="submit" name="modificar_cita" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para confirmar cancelación -->
    <div id="modal-cancelar" class="modal">
        <div class="modal-contenido" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-titulo">Cancelar Cita</h3>
                <span class="cerrar-modal" id="cerrar-cancelar">&times;</span>
            </div>
            
            <div style="padding: 20px 0;">
                <p>¿Está seguro de que desea cancelar esta cita?</p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($detalleCita['fecha_hora'])); ?> a las <?php echo date('h:i A', strtotime($detalleCita['fecha_hora'])); ?></p>
                <p><strong>Médico:</strong> Dr. <?php echo htmlspecialchars($detalleCita['nombre_medico']); ?></p>
                <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($detalleCita['nombre_especialidad']); ?></p>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button class="btn btn-secondary" id="cancelar-eliminacion">No, volver</button>
                <form method="POST">
                    <button type="submit" name="cancelar_cita" class="btn btn-danger">Sí, cancelar cita</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejo de los modales
            const modalModificar = document.getElementById("modal-modificar");
            const modalCancelar = document.getElementById("modal-cancelar");
            const btnModificar = document.getElementById("btn-modificar");
            const btnCancelar = document.getElementById("btn-cancelar");
            const cerrarModificar = document.getElementById("cerrar-modificar");
            const cerrarCancelar = document.getElementById("cerrar-cancelar");
            const cancelarModificacion = document.getElementById("cancelar-modificacion");
            const cancelarEliminacion = document.getElementById("cancelar-eliminacion");

            // Abrir modales
            btnModificar.onclick = function() {
                modalModificar.style.display = "block";
            }
            
            btnCancelar.onclick = function() {
                modalCancelar.style.display = "block";
            }
            
            // Cerrar modales
            cerrarModificar.onclick = function() {
                modalModificar.style.display = "none";
            }
            
            cerrarCancelar.onclick = function() {
                modalCancelar.style.display = "none";
            }
            
            cancelarModificacion.onclick = function() {
                modalModificar.style.display = "none";
            }
            
            cancelarEliminacion.onclick = function() {
                modalCancelar.style.display = "none";
            }
            
            // Cerrar modal al hacer clic fuera
            window.onclick = function(event) {
                if (event.target == modalModificar) {
                    modalModificar.style.display = "none";
                }
                if (event.target == modalCancelar) {
                    modalCancelar.style.display = "none";
                }
            }
            
            // Manejar la selección de horarios
            $(document).on('click', '.time-slot', function() {
                $('.time-slot').removeClass('selected');
                $(this).addClass('selected');
                $('#hora_inicio').val($(this).data('horario'));
            });
            
            // Cargar médicos al cambiar especialidad
            $('#id_especialidad').change(function() {
                const idEspecialidad = $(this).val();
                if (idEspecialidad) {
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
            });
            
            // Cargar horarios al cambiar médico o fecha
            $('#id_medico, #fecha').change(function() {
                const idMedico = $('#id_medico').val();
                const fecha = $('#fecha').val();
                const idCita = <?php echo $id_cita; ?>;
                
                if (idMedico && fecha) {
                    $.ajax({
                        url: 'ajax/get_horarios.php',
                        type: 'POST',
                        data: { 
                            id_medico: idMedico, 
                            fecha: fecha,
                            id_cita: idCita
                        },
                        success: function(response) {
                            $('#horarios-container').html(response);
                            $('#hora_inicio').val('');
                        },
                        error: function() {
                            $('#horarios-container').html('<p class="no-slots">Error al cargar los horarios disponibles</p>');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>