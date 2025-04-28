<?php
class Auth {
    /**
     * Inicia la sesión si no está iniciada
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Establece los datos de sesión del usuario
     * @param User $user Objeto de usuario con los datos
     */
    public static function setUserSession($user) {
        $_SESSION['id_usuario'] = $user->id_usuario;
        $_SESSION['nombre'] = $user->nombre;
        $_SESSION['apellido'] = $user->apellido;
        $_SESSION['id_cedula'] = $user->cedula;
        $_SESSION['email'] = $user->email;
        $_SESSION['rol'] = $user->rol;
    }
    
    /**
     * Verifica si el usuario está logueado
     * @return bool True si está logueado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['id_usuario']);
    }
    
    /**
     * Verifica si el usuario tiene un rol permitido
     * @param array $allowed_roles Lista de roles permitidos
     * @return void Redirecciona si no tiene permiso
     */
    public static function checkRole($allowed_roles) {
        self::startSession();
        
        // Verificar si está logueado
        if (!self::isLoggedIn()) {
            header('Location: ../index.php');
            exit;
        }
        
        // Verificar si tiene rol permitido
        if (!in_array($_SESSION['rol'], $allowed_roles)) {
            // Redirigir según rol
            if ($_SESSION['rol'] == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../paciente/dashboard.php');
            }
            exit;
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public static function logout() {
        self::startSession();
        
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Borrar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }
}