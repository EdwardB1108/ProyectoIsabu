<?php
require_once '../classes/Auth.php';
require_once '../config/db.php';

// Verificar que el usuario sea administrador
Auth::checkRole(['admin']);

// Crear instancia de la base de datos
$database = new Database();
$conexion = $database->connect();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Obtener y validar los datos del formulario
        $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        $id_especialidad = filter_input(INPUT_POST, 'id_especialidad', FILTER_VALIDATE_INT);
        $id_medico = filter_input(INPUT_POST, 'id_medico', FILTER_VALIDATE_INT);
        $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);
        $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
        $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
        
        // Verificar datos obligatorios
        if (!$id_usuario || !$id_medico || !$id_especialidad || empty($fecha) || empty($hora)) {
            throw new Exception("Todos los campos marcados con * son obligatorios");
        }
        
        // Formatear fecha y hora
        $fecha_hora = $fecha . ' ' . $hora;
        
        // Verificar que el médico tenga la especialidad seleccionada
        $sql_verif_especialidad = "SELECT COUNT(*) as total FROM medicos WHERE id_medico = ? AND id_especialidad = ?";
        $stmt_verif_esp = $conexion->prepare($sql_verif_especialidad);
        $stmt_verif_esp->execute([$id_medico, $id_especialidad]);
        $result_esp = $stmt_verif_esp->fetch(PDO::FETCH_ASSOC);
        
        if ($result_esp['total'] == 0) {
            throw new Exception("El médico seleccionado no atiende la especialidad elegida");
        }
        
        // Obtener el id_paciente del usuario si existe, o crear un registro nuevo en la tabla pacientes
        $sql_check_paciente = "SELECT id_paciente FROM pacientes WHERE id_usuario = ?";
        $stmt_check = $conexion->prepare($sql_check_paciente);
        $stmt_check->execute([$id_usuario]);
        $paciente = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($paciente) {
            $id_paciente = $paciente['id_paciente'];
        } else {
            // Crear un nuevo registro en la tabla pacientes
            $sql_new_paciente = "INSERT INTO pacientes (id_usuario, id_eps) 
                                SELECT id_usuario, id_eps FROM usuarios WHERE id_usuario = ?";
            $stmt_new = $conexion->prepare($sql_new_paciente);
            $stmt_new->execute([$id_usuario]);
            $id_paciente = $conexion->lastInsertId();
        }
        
        // Verificar disponibilidad del médico en esa fecha y hora
        $sql_verificar = "SELECT hm.id_horario FROM horarios_medicos hm 
                         WHERE hm.id_medico = ? 
                         AND hm.dia_semana = LOWER(DATE_FORMAT(?, '%W'))
                         AND ? BETWEEN hm.hora_inicio AND DATE_SUB(hm.hora_fin, INTERVAL 1 MINUTE)
                         AND hm.estado = 'activo'";
        
        $stmt_verificar = $conexion->prepare($sql_verificar);
        $stmt_verificar->execute([$id_medico, $fecha_hora, $hora]);
        $horario_disponible = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        
        if (!$horario_disponible) {
            throw new Exception("El médico no está disponible en ese horario");
        }
        
        $id_horario = $horario_disponible['id_horario'];
        
        // Verificar si ya existe una cita en esa fecha y hora con ese médico
        $sql_cita_existente = "SELECT COUNT(*) as total FROM citas 
                              WHERE id_medico = ? 
                              AND fecha_hora = ?
                              AND estado != 'cancelada'";
        $stmt_cita = $conexion->prepare($sql_cita_existente);
        $stmt_cita->execute([$id_medico, $fecha_hora]);
        $resultado = $stmt_cita->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            throw new Exception("Ya existe una cita programada para esta fecha y hora");
        }
        
        // Insertar la cita en la base de datos
        $sql_insertar = "INSERT INTO citas (id_paciente, id_medico, id_horario, fecha_hora, motivo, estado) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insertar = $conexion->prepare($sql_insertar);
        $resultado = $stmt_insertar->execute([
            $id_paciente, 
            $id_medico,
            $id_horario, 
            $fecha_hora, 
            $motivo, 
            $estado ?? 'pendiente'
        ]);
        
        if (!$resultado) {
            throw new Exception("Error al crear la cita");
        }
        
        // Enviar notificación al paciente (opcional)
        $id_cita = $conexion->lastInsertId();
        
        // Confirmar la transacción
        $conexion->commit();
        
        // Mensaje de éxito
        $mensaje_exito = "Cita creada correctamente";
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conexion->rollBack();
        $mensaje_error = $e->getMessage();
    }
}

// Obtener lista de pacientes desde la tabla usuarios
function getPacientes($conn) {
    $sql = "SELECT u.id_usuario, u.cedula, u.nombre, u.apellido, e.nombre as eps
            FROM usuarios u
            LEFT JOIN eps e ON u.id_eps = e.id_eps
            WHERE u.rol = 'paciente' AND u.estado = 'activo'
            ORDER BY u.nombre ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener lista de especialidades
function getEspecialidades($conn) {
    $sql = "SELECT id_especialidad, nombre, descripcion 
            FROM especialidades 
            WHERE estado = 'activa' 
            ORDER BY nombre ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener lista de médicos con sus especialidades
function getMedicos($conn, $id_especialidad = null) {
    $params = [];
    $sql = "SELECT m.id_medico, u.nombre, u.apellido, m.licencia_medica, e.nombre as especialidad, e.id_especialidad
            FROM medicos m
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            LEFT JOIN especialidades e ON m.id_especialidad = e.id_especialidad
            WHERE m.estado = 'activo'";
    
    if ($id_especialidad) {
        $sql .= " AND m.id_especialidad = ?";
        $params[] = $id_especialidad;
    }
    
    $sql .= " ORDER BY u.nombre ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Cargar datos
$pacientes = getPacientes($conexion);
$especialidades = getEspecialidades($conexion);
$medicos = getMedicos($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - ISABU</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
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
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Inicio</a></li>
                    <li><a href="agendar_cita.php" class="active"><i class="fas fa-calendar-plus"></i> Agendar Citas</a></li>
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
            <h1 class="page-title">Agendar Nueva Cita</h1>
            
            <?php
            // Mostrar mensajes de error o éxito
            if (!empty($mensaje_error)) {
                echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $mensaje_error . '</div>';
            }
            if (!empty($mensaje_exito)) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $mensaje_exito . '</div>';
            }
            ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="id_usuario">Paciente *</label>
                            <select name="id_usuario" id="id_usuario" required>
                                <option value="">Seleccione un paciente</option>
                                <?php foreach ($pacientes as $paciente): ?>
                                    <option value="<?php echo $paciente['id_usuario']; ?>">
                                        <?php echo $paciente['nombre'] . ' ' . $paciente['apellido'] . ' - CC: ' . $paciente['cedula'] . ' - EPS: ' . $paciente['eps']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-actions">
                                <a href="pacientes.php?action=new" class="btn-link"><i class="fas fa-plus"></i> Nuevo Paciente</a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_especialidad">Tipo de Cita / Especialidad *</label>
                            <select name="id_especialidad" id="id_especialidad" required>
                                <option value="">Seleccione una especialidad</option>
                                <?php foreach ($especialidades as $especialidad): ?>
                                    <option value="<?php echo $especialidad['id_especialidad']; ?>">
                                        <?php echo $especialidad['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_medico">Médico *</label>
                            <select name="id_medico" id="id_medico" required>
                                <option value="">Seleccione primero una especialidad</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha">Fecha *</label>
                                <input type="date" name="fecha" id="fecha" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="hora">Hora *</label>
                                <input type="time" name="hora" id="hora" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="motivo">Motivo de la consulta</label>
                            <textarea name="motivo" id="motivo" rows="3" placeholder="Describa brevemente el motivo de la consulta"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Agendar Cita</button>
                            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Información sobre horarios disponibles -->
            <div class="info-section" id="horarios-disponibles" style="display: none;">
                <h2>Horarios Disponibles</h2>
                <div id="horarios-contenido">
                    <p>Seleccione un médico y una fecha para ver los horarios disponibles.</p>
                </div>
            </div>
        </main>
    </div>
        
    <!-- Pie de página -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>

    <script>
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
            
            // Carga dinámica de médicos según la especialidad seleccionada
            const especialidadSelect = document.getElementById('id_especialidad');
            const medicoSelect = document.getElementById('id_medico');
            
            especialidadSelect.addEventListener('change', function() {
                const idEspecialidad = this.value;
                
                // Limpiar selector de médicos
                medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                
                if (idEspecialidad) {
                    // En un sistema real, aquí se haría una petición AJAX para cargar los médicos
                    // Para este ejemplo, simulamos datos estáticos
                    fetch(`get_medicos.php?especialidad=${idEspecialidad}`)
                        .then(response => response.json())
                        .then(medicos => {
                            if (medicos.length > 0) {
                                medicos.forEach(medico => {
                                    const option = document.createElement('option');
                                    option.value = medico.id_medico;
                                    option.textContent = `${medico.nombre} ${medico.apellido} - ${medico.especialidad}`;
                                    medicoSelect.appendChild(option);
                                });
                            } else {
                                const option = document.createElement('option');
                                option.value = "";
                                option.textContent = "No hay médicos disponibles para esta especialidad";
                                medicoSelect.appendChild(option);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // En caso de error, mostrar médicos de ejemplo para esta demo
                            const medicosEjemplo = [
                                { id: 1, nombre: "Dr. Juan Pérez - Especialista en " + especialidadSelect.options[especialidadSelect.selectedIndex].text },
                                { id: 2, nombre: "Dra. María Gómez - Especialista en " + especialidadSelect.options[especialidadSelect.selectedIndex].text },
                                { id: 3, nombre: "Dr. Carlos Rodríguez - Especialista en " + especialidadSelect.options[especialidadSelect.selectedIndex].text }
                            ];
                            
                            medicosEjemplo.forEach(medico => {
                                const option = document.createElement('option');
                                option.value = medico.id;
                                option.textContent = medico.nombre;
                                medicoSelect.appendChild(option);
                            });
                        });
                }
            });
            
            // Funcionalidad para verificar disponibilidad
            const fechaInput = document.getElementById('fecha');
            const horaInput = document.getElementById('hora');
            const horariosSection = document.getElementById('horarios-disponibles');
            const horariosContenido = document.getElementById('horarios-contenido');
            
            // Verificar disponibilidad cuando cambia médico o fecha
            medicoSelect.addEventListener('change', verificarDisponibilidad);
            fechaInput.addEventListener('change', verificarDisponibilidad);
            
            function verificarDisponibilidad() {
                const idMedico = medicoSelect.value;
                const fecha = fechaInput.value;
                
                if (idMedico && fecha) {
                    // Mostrar cargando
                    horariosSection.style.display = 'block';
                    horariosContenido.innerHTML = '<p>Cargando horarios disponibles...</p>';
                    
                    // En un sistema real, aquí se haría una petición AJAX al servidor
                    // Para simplificar, mostraremos un mensaje estático después de un tiempo
                    setTimeout(() => {
                        const nombreMedico = medicoSelect.options[medicoSelect.selectedIndex].text;
                        const nombreEspecialidad = especialidadSelect.options[especialidadSelect.selectedIndex].text;
                        
                        horariosContenido.innerHTML = `
                            <p>Los horarios disponibles para <strong>${nombreMedico}</strong> 
                            en la especialidad de <strong>${nombreEspecialidad}</strong> 
                            para la fecha <strong>${fecha}</strong> son:</p>
                            <ul class="horarios-lista">
                                <li>8:00 AM - 8:30 AM</li>
                                <li>9:00 AM - 9:30 AM</li>
                                <li>10:00 AM - 10:30 AM</li>
                                <li>11:00 AM - 11:30 AM</li>
                                <li>2:00 PM - 2:30 PM</li>
                                <li>3:00 PM - 3:30 PM</li>
                            </ul>
                            <p class="nota"><i class="fas fa-info-circle"></i> Seleccione una de estas horas en el campo "Hora" para asegurar la disponibilidad.</p>
                        `;
                    }, 500);
                } else {
                    horariosSection.style.display = 'none';
                }
            }
            
            // Validación de formulario antes de enviar
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                let valid = true;
                const requiredFields = ['id_usuario', 'id_especialidad', 'id_medico', 'fecha', 'hora'];
                
                requiredFields.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value) {
                        element.classList.add('error');
                        valid = false;
                    } else {
                        element.classList.remove('error');
                    }
                });
                
                if (!valid) {
                    event.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                }
            });
        });
    </script>
</body>
</html>