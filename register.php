<?php
require_once './config/db.php';
require_once './classes/User.php';
require_once './classes/Auth.php';
require_once './classes/EPS.php'; // Asumimos que crearemos esta clase
require_once './classes/Paciente.php'; // Añadido: incluir clase Paciente

// Verificar si ya está logueado
Auth::startSession();
if (Auth::isLoggedIn()) {
    // Redirección según rol
    if ($_SESSION['rol'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: paciente/dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

// Cargar lista de EPS
$epsManager = new EPS();
$epsList = $epsManager->getActiveEPS();

// Inicializar el objeto User aquí para evitar errores
$user = new User();

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Obtener y limpiar datos del formulario
    $cedula = trim($_POST['cedula']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $id_eps = trim($_POST['id_eps']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validación básica
    if (empty($cedula) || empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($confirm_password) || empty($id_eps)) {
        $error = 'Por favor complete todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingrese un correo electrónico válido';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar si el correo ya existe
        if ($user->emailExists($email)) {
            $error = 'El correo electrónico ya está registrado';
        } 
        // Verificar si la cédula ya existe
        elseif ($user->cedulaExists($cedula)) {
            $error = 'El número de cédula ya está registrado';
        } else {
            // Configurar usuario
            $user->cedula = $cedula;
            $user->nombre = $nombre;
            $user->apellido = $apellido;
            $user->email = $email;
            $user->id_eps = $id_eps;
            $user->password = $password;
            $user->rol = 'paciente'; // Por defecto es paciente
            
            // Registrar usuario
            if ($user->register()) {
                // Obtener el ID del usuario recién creado
                $id_usuario = $user->getLastInsertedId();
                
                // Crear registro en tabla pacientes
                $paciente = new Paciente();
                if ($paciente->crearPacienteBasico($id_usuario, $id_eps)) {
                    $success = 'Registro exitoso. Ahora puede iniciar sesión.';
                } else {
                    // Si falla crear el paciente pero el usuario se creó, mostrar advertencia
                    $success = 'Registro exitoso. Ahora puede iniciar sesión.';
                }
            } else {
                $error = 'Error al registrar el usuario. Intente nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISABU - Registro de Usuario</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Barra GOV.CO -->
    <div class="gov-bar">
        <img src="assets/img/gov-co-logo.png" alt="GOV.CO" class="gov-co-image">
    </div>
    
    <!-- Encabezado principal -->
    <header class="main-header">
        <div class="logo-container">
            <img src="assets/img/isabu-logo.png" alt="ISABU Logo" class="logo">
        </div>
    </header>
    
    <!-- Contenedor principal -->
    <main class="login-container">
        <div class="login-box">
            <div class="tab-buttons">
                <button class="tab-btn" onclick="window.location.href='index.php'">Iniciar Sesión</button>
                <button class="tab-btn active">Registrarse</button>
            </div>
            
            <!-- Formulario de Registro -->
            <div class="form-tab active">
                <h2>Registro de Usuario</h2>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <input type="text" name="cedula" id="cedula" placeholder="Número de Cédula *" required value="<?php echo isset($_POST['cedula']) ? htmlspecialchars($_POST['cedula']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="nombre" id="nombre" placeholder="Nombre *" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="apellido" id="apellido" placeholder="Apellido *" required value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" id="email" placeholder="Correo Electrónico *" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <select name="id_eps" id="id_eps" required>
                            <option value="">Seleccione su EPS *</option>
                            <?php foreach($epsList as $eps): ?>
                            <option value="<?php echo $eps['id_eps']; ?>" <?php echo (isset($_POST['id_eps']) && $_POST['id_eps'] == $eps['id_eps']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($eps['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" id="password" placeholder="Contraseña *" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmar Contraseña *" required>
                    </div>
                    <div class="boton-container">
                        <button type="submit" name="register">Registrarse</button>
                    </div>
                </form>
                
                <a href="index.php" class="forgot-password">¿Ya tiene cuenta? Inicie sesión</a>
            </div>
        </div>
    </main>
    
    <!-- Pie de página -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>
</body>
</html>