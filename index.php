<?php
require_once 'config/db.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

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

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $identifier = trim($_POST['login_identifier']);
    $password = trim($_POST['login_password']);
    
    // Validación básica
    if (empty($identifier) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $user = new User();
        
        // Intentar login
        if ($user->login($identifier, $password)) {
            Auth::setUserSession($user);
            
            // Redirección según rol
            if ($user->rol == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: paciente/dashboard.php');
            }
            exit;
        } else {
            $error = 'Credenciales incorrectas o usuario inactivo';
        }
    }
}

// Redirigir a registro si se hace clic en el botón
if (isset($_GET['action']) && $_GET['action'] == 'register') {
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISABU - Iniciar Sesión</title>
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
                <button class="tab-btn active">Iniciar Sesión</button>
                <a href="?action=register" class="tab-btn">Registrarse</a>
            </div>
            
            <!-- Formulario de Login -->
            <div class="form-tab active">
                <h2>Iniciar Sesión</h2>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="index.php" method="POST">
                    <div class="form-group">
                        <input type="text" name="login_identifier" id="login_identifier" placeholder="Correo electrónico o Cédula" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="login_password" id="login_password" placeholder="Contraseña" required>
                    </div>
                    <div class="boton-container">
                        <button type="submit" name="login">Iniciar Sesión</button>
                    </div>
                </form>
                
                <a href="forgot-password.php" class="forgot-password">¿Olvidó su contraseña?</a>
            </div>
        </div>
    </main>
    
    <!-- Pie de página -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> ISABU - Todos los derechos reservados</p>
    </footer>
</body>
</html>