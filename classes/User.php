<?php
require_once './config/db.php';

class User {
    public $id_usuario;
    public $cedula; // Esta propiedad se mantiene para compatibilidad con el código existente
    public $nombre;
    public $apellido;
    public $email;
    public $id_eps;
    public $password;
    public $rol;
    public $estado;
    
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Registra un nuevo usuario
     * @return bool True si el registro fue exitoso
     */
    public function register() {
        // Hash de la contraseña
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                INSERT INTO usuarios (id_cedula, nombre, apellido, email, password, rol, id_eps, estado) 
                VALUES (:cedula, :nombre, :apellido, :email, :password, :rol, :id_eps, 'activo')
            ");
            
            $stmt->bindParam(':cedula', $this->cedula);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellido', $this->apellido);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':rol', $this->rol);
            $stmt->bindParam(':id_eps', $this->id_eps, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->id_usuario = $conn->lastInsertId();
            }
            
            return $result;
        } catch (PDOException $e) {
            echo 'Error en registro: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Obtiene el ID del último usuario insertado
     * @return int|null ID del usuario o null si no hay ID
     */
    public function getLastInsertedId() {
        return $this->id_usuario;
    }
    
    /**
     * Inicia sesión con email o cédula
     * @param string $identifier Email o cédula
     * @param string $password Contraseña
     * @return bool True si el login fue exitoso
     */
    public function login($identifier, $password) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                SELECT id_usuario, id_cedula as cedula, nombre, apellido, email, password, rol, id_eps, estado 
                FROM usuarios 
                WHERE (email = :identifier OR id_cedula = :identifier) AND estado = 'activo'
            ");
            
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    // Asignar datos al objeto
                    $this->id_usuario = $user['id_usuario'];
                    $this->cedula = $user['cedula'];
                    $this->nombre = $user['nombre'];
                    $this->apellido = $user['apellido'];
                    $this->email = $user['email'];
                    $this->rol = $user['rol'];
                    $this->id_eps = $user['id_eps'];
                    $this->estado = $user['estado'];
                    
                    // Actualizar último login
                    $this->updateLastLogin();
                    
                    return true;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            echo 'Error en login: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Actualiza la fecha del último login
     */
    private function updateLastLogin() {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("UPDATE usuarios SET fecha_ultimo_login = NOW() WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $this->id_usuario);
            $stmt->execute();
        } catch (PDOException $e) {
            // Simplemente ignoramos el error ya que esto no es crítico
        }
    }
    
    /**
     * Verifica si un email ya existe
     * @param string $email Email a verificar
     * @return bool True si el email ya existe
     */
    public function emailExists($email) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Verifica si una cédula ya existe
     * @param string $cedula Cédula a verificar
     * @return bool True si la cédula ya existe
     */
    public function cedulaExists($cedula) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_cedula = :cedula");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Verifica un token de recuperación de contraseña
     * @param string $token Token a verificar
     * @return array|boolean Datos del usuario o false si el token no es válido
     */
    public function verifyRecoveryToken($token) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                SELECT id_usuario 
                FROM recuperacion_password 
                WHERE token = :token AND utilizado = 0
                AND fecha_expiracion > NOW()
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Restablece la contraseña de un usuario
     * @param int $user_id ID del usuario
     * @param string $token Token de recuperación
     * @param string $new_password Nueva contraseña
     * @return bool True si el restablecimiento fue exitoso
     */
    public function resetPassword($user_id, $token, $new_password) {
        try {
            $conn = $this->db->connect();
            
            // Iniciar transacción
            $conn->beginTransaction();
            
            // Hash de la nueva contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Actualizar contraseña
            $stmt1 = $conn->prepare("UPDATE usuarios SET password = :password WHERE id_usuario = :id_usuario");
            $stmt1->bindParam(':password', $hashed_password);
            $stmt1->bindParam(':id_usuario', $user_id);
            $stmt1->execute();
            
            // Marcar token como utilizado
            $stmt2 = $conn->prepare("UPDATE recuperacion_password SET utilizado = 1 WHERE token = :token");
            $stmt2->bindParam(':token', $token);
            $stmt2->execute();
            
            // Confirmar transacción
            $conn->commit();
            
            return true;
        } catch (PDOException $e) {
            // Deshacer cambios si hay error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return false;
        }
    }
}