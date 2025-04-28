<?php
require_once 'classes/Auth.php';
require_once 'classes/User.php';

// Iniciar y verificar sesión
Auth::startSession();

// Verificar autenticación y rol de administrador
$allowed_roles = ['admin'];
Auth::checkRole($allowed_roles);

// Eliminamos la línea que causa el error ya que usamos directamente $_SESSION
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - ISABU</title>
    <style>
        /* Tus estilos CSS permanecen igual */
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel Administrativo - ISABU</h1>
    </div>
    
    <div class="content">
        <div class="welcome-message">
            <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></h2>
            <p>Rol: <?php echo htmlspecialchars($_SESSION['rol'] ?? 'Desconocido'); ?></p>
        </div>
        
        <p>Este es un panel administrativo sencillo. Aquí puedes gestionar los diferentes aspectos del sistema.</p>
        
        <form action="logout.php" method="post">
            <button type="submit" class="logout-btn">Cerrar Sesión</button>
        </form>
    </div>
</body>
</html>