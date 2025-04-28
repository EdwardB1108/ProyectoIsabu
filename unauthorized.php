<?php
require_once 'classes/Auth.php';

// Iniciar sesión si no está iniciada
Auth::startSession();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado - ISABU</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .unauthorized-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        
        h1 {
            color: #dc3545;
            margin-top: 0;
        }
        
        p {
            margin-bottom: 25px;
            font-size: 16px;
            color: #555;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-home {
            background-color: #254e2a;
            color: white;
        }
        
        .btn-home:hover {
            background-color: #306b34;
        }
        
        .btn-logout {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <h1>Acceso no autorizado</h1>
        <p>No tienes permisos para acceder a esta sección del sistema.</p>
        <p>Por favor, contacta al administrador si necesitas acceso.</p>
        
        <a href="dashboard.php" class="btn btn-home">Volver al inicio</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="btn btn-logout">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</body>
</html>