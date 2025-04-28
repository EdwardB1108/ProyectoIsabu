<?php
require_once './config/db.php';
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
$token = '';
$valid_token = false;
$user_id = 0;

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $user = new User();
    $token_info = $user->verifyRecoveryToken($token);
    
    if ($token_info) {
        $valid_token = true;
        $user_id = $token_info['id_usuario'];
    } else {
        $error = 'El enlace de recuperación no es válido o ha expirado';
    }
} else {
    $error = 'Token no proporcionado';
}

// Procesar formulario de restablecimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset']) && $valid_token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validación básica
    if (empty($password) || strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        $user = new User();
        
        if ($user->resetPassword($user_id, $token, $password)) {
            $success = 'Su contraseña ha sido restablecida correctamente. Ahora puede iniciar sesión.';
        } else {
            $error = 'Error al restablecer la contraseña. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISABU - Restablecer Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Barra GOV.CO -->
    <div class="gov-bar">
        <img src="assets/img/gov-co.png" alt="GOV.CO" class="gov-co-image">
    </div>

    <!-- Encabezado principal -->
    <header class="main-header">
        <div class="logo-container">
            <img src="assets/img/logo-isabu.png" alt="Clínica ISABU" class="logo">
        </div>
    </header>

    <!-- Contenedor principal -->
    <div class="login-container">
        <div class="login-box">
            <div class="tab-buttons">
                <button class="tab-btn active">Restablecer Contraseña</button>
            </div>

            <div class="form-tab active">
                <h2>Restablecer Contraseña</h2>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
                <div class="boton-container">
                    <a href="index.php" class="btn btn-primary">Volver al Login</a>
                </div>
                <?php elseif ($valid_token): ?>
                
                <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                    <input type="password" name="password" placeholder="Nueva contraseña" required>
                    <input type="password" name="confirm_password" placeholder="Confirmar nueva contraseña" required>
                    
                    <div class="boton-container">
                        <button type="submit" name="reset">Restablecer Contraseña</button>
                    </div>
                </form>
                
                <?php else: ?>
                <div class="boton-container">
                    <a href="index.php" class="btn btn-primary">Volver al Login</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer>
        <p>© <?php echo date('Y'); ?> Clínica ISABU - Todos los derechos reservados</p>
    </footer>

    <script>
        // Validación de contraseña en el cliente
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;
            
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                e.preventDefault();
                return false;
            }
            
            if (password !== confirm) {
                alert('Las contraseñas no coinciden');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>